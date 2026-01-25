<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

/* -------------------------
   AUTH CHECK
-------------------------- */
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Authentication required'
    ]);
    exit;
}

$user_id   = (int) $_SESSION['user_id'];
$room_code = trim($_POST['room_code'] ?? '');

if ($room_code === '') {
    echo json_encode([
        'success' => false,
        'error'   => 'Room code required'
    ]);
    exit;
}

/* -------------------------
   FETCH ROOM
-------------------------- */
$stmt = $pdo->prepare("
    SELECT room_id, room_code, title, host_user_id
    FROM rooms
    WHERE LOWER(room_code) = LOWER(:code)
    LIMIT 1
");
$stmt->execute([':code' => $room_code]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo json_encode([
        'success' => false,
        'error'   => 'Room not found'
    ]);
    exit;
}

$room_id = (int) $room['room_id'];

/* -------------------------
   PREVENT HOST JOIN
-------------------------- */
if ((int) $room['host_user_id'] === $user_id) {
    echo json_encode([
        'success' => false,
        'error'   => 'Host cannot join their own room'
    ]);
    exit;
}

/* -------------------------
   INSERT / UPDATE PARTICIPANT
   (THIS IS THE FIX)
-------------------------- */
try {
    $pdo->prepare("
        INSERT INTO room_participants (room_id, user_id, joined_at)
        VALUES (:room_id, :user_id, NOW())
        ON DUPLICATE KEY UPDATE joined_at = NOW()
    ")->execute([
        ':room_id' => $room_id,
        ':user_id' => $user_id
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Database error'
    ]);
    exit;
}

/* -------------------------
   SUCCESS RESPONSE
-------------------------- */
echo json_encode([
    'success'    => true,
    'room_id'   => $room_id,
    'room_code' => $room['room_code'],
    'title'     => $room['title']
]);
exit;
