<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

$room_id  = (int)($_GET['room_id'] ?? 0);
$after_id = (int)($_GET['after_id'] ?? 0);

if ($room_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid room']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.message_id,
            m.message,
            m.created_at,
            u.username,
            u.avatar
        FROM messages m
        JOIN users u ON u.id = m.user_id
        WHERE m.room_id = :room
          AND m.message_id > :after
        ORDER BY m.message_id ASC
        LIMIT 50
    ");

    $stmt->execute([
        ':room'  => $room_id,
        ':after' => $after_id
    ]);

    echo json_encode([
        'success'  => true,
        'messages' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
