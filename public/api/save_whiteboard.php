<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Login required']);
    exit;
}

$room_id = (int)($_POST['room_id'] ?? 0);
$data    = $_POST['data'] ?? '';

if ($room_id <= 0 || $data === '') {
    echo json_encode(['success'=>false,'error'=>'Invalid data']);
    exit;
}

try {
    // Upsert whiteboard data
    $stmt = $pdo->prepare("
        INSERT INTO whiteboard_data (room_id, data)
        VALUES (:room, :data)
        ON DUPLICATE KEY UPDATE
        data = VALUES(data),
        updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([
        ':room' => $room_id,
        ':data' => $data
    ]);

    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
