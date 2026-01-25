<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

$room_id = (int)($_GET['room_id'] ?? 0);
if ($room_id <= 0) {
    echo json_encode(['success'=>false]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT data, updated_at
    FROM whiteboard_data
    WHERE room_id = ?
    LIMIT 1
");
$stmt->execute([$room_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['success'=>true,'data'=>null]);
    exit;
}

echo json_encode([
    'success'=>true,
    'data'=>$row['data'],
    'updated_at'=>$row['updated_at']
]);
