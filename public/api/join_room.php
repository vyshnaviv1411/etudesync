<?php
// public/api/join_room.php
// Joins a user to a room by room_code (or room_id) and returns JSON

// DEV: show errors while debugging (remove/disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

// Adjust path: from public/api, go up two levels to project root, then into includes/
$includes_db = __DIR__ . '/../../includes/db.php';

// simple logger (ensure public/tmp exists & is writable)
$logfile = __DIR__ . '/../tmp/api_log.txt';
if (!is_dir(dirname($logfile))) {
    @mkdir(dirname($logfile), 0755, true);
}
function logit($m) {
    global $logfile;
    @file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $m . PHP_EOL, FILE_APPEND);
}

logit("join_room called. REQUEST_METHOD=" . ($_SERVER['REQUEST_METHOD'] ?? ''));

// require DB (adjusted path)
if (!file_exists($includes_db)) {
    logit("ERROR: includes/db.php not found at: $includes_db");
    echo json_encode(['success' => false, 'error' => 'Server configuration error (missing db include).']);
    exit;
}

require_once $includes_db; // should provide $pdo

// require authentication
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$room_code = trim($_POST['room_code'] ?? '');

if ($room_code === '') {
    echo json_encode(['success' => false, 'error' => 'Room code is required.']);
    exit;
}

try {
    // Try to find room by code (case-insensitive)
    $stmt = $pdo->prepare("SELECT room_id, room_code, title FROM rooms WHERE LOWER(room_code) = LOWER(:code) LIMIT 1");
    $stmt->execute([':code' => $room_code]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        echo json_encode(['success' => false, 'error' => 'Room not found. Check the code and try again.']);
        exit;
    }

    $room_id = (int)$room['room_id'];
    $room_code = $room['room_code']; // normalized

    // check if user already participant
    $stmt2 = $pdo->prepare("SELECT id FROM room_participants WHERE room_id = :room AND user_id = :user LIMIT 1");
    $stmt2->execute([':room' => $room_id, ':user' => $user_id]);
    $exists = $stmt2->fetchColumn();

    if ($exists) {
        // update joined_at to now (refresh presence)
        $stmtUp = $pdo->prepare("UPDATE room_participants SET joined_at = NOW() WHERE id = :id");
        $stmtUp->execute([':id' => $exists]);
    } else {
        // insert new participant
        $stmtIns = $pdo->prepare("INSERT INTO room_participants (room_id, user_id, joined_at) VALUES (:room, :user, NOW())");
        $stmtIns->execute([':room' => $room_id, ':user' => $user_id]);
    }

   
    // success
    echo json_encode([
        'success' => true,
        'room_id' => $room_id,
        'room_code' => $room_code,
        'title' => $room['title'] ?? ''
    ]);
    exit;

} catch (PDOException $e) {
    logit("PDOException: " . $e->getMessage());
    // dev: return error message (hide in production)
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Throwable $e) {
    logit("Throwable: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit;
}
