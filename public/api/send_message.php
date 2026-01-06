<?php
// public/api/send_message.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Login required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$room_id = (int)($_POST['room_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($room_id <= 0 || $message === '') {
    echo json_encode(['success'=>false,'error'=>'Invalid input']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (room_id, user_id, message)
        VALUES (:room, :user, :msg)
    ");
    $stmt->execute([
        ':room' => $room_id,
        ':user' => $user_id,
        ':msg'  => $message
    ]);

    // fetch message with user info
    $stmt = $pdo->prepare("
        SELECT m.message_id, m.message, m.created_at, u.username, u.avatar
        FROM messages m
        JOIN users u ON u.id = m.user_id
        WHERE m.message_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $pdo->lastInsertId()]);
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => $msg
    ]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
