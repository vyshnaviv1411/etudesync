<?php
// public/api/create_room.php
// Create room ONLY (host is NOT a participant)

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

/* ---------- AUTH ---------- */
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* ---------- INPUT ---------- */
$title = trim($_POST['title'] ?? '');
$topic = trim($_POST['topic'] ?? '');
$scheduled = null;

if ($title === '') {
    echo json_encode(['success' => false, 'error' => 'Room title is required']);
    exit;
}


/* ---------- ROOM CODE ---------- */
function generateRoomCode($len = 6) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $len; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

try {
    // Generate unique room code
    do {
        $room_code = generateRoomCode();
        $chk = $pdo->prepare("SELECT room_id FROM rooms WHERE room_code = ?");
        $chk->execute([$room_code]);
    } while ($chk->fetch());

    // ✅ Insert room ONLY
    $stmt = $pdo->prepare("
        INSERT INTO rooms (title, topic, room_code, host_user_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $title,
        $topic !== '' ? $topic : null,
        $room_code,
        $user_id
    ]);

    $room_id = (int)$pdo->lastInsertId();

    // ❌ DO NOT INSERT HOST INTO room_participants

    $_SESSION['current_room_id'] = $room_id;

    echo json_encode([
        'success'   => true,
        'room_id'   => $room_id,
        'room_code' => $room_code,
        'redirect' => 'room.php?code=' . $room_code

    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
    exit;
}

