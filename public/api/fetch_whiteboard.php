<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

$room_id = (int)($_GET['room_id'] ?? 0);

if ($room_id <= 0) {
    echo json_encode(['success'=>false]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT data, updated_at
        FROM whiteboard_data
        WHERE room_id = :room
        LIMIT 1
    ");
    $stmt->execute([':room'=>$room_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'=>true,
        'data' => $row['data'] ?? null,
        'updated_at' => $row['updated_at'] ?? null
    ]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
