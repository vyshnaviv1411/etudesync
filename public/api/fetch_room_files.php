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
    SELECT 
        rf.id AS room_file_id,
        iv.file_name,
        iv.file_path,
        iv.uploaded_at,
        u.username AS shared_by
    FROM room_files rf
    JOIN infovault_files iv ON iv.id = rf.infovault_file_id
    JOIN users u ON u.id = rf.shared_by
    WHERE rf.room_id = ?
    ORDER BY rf.shared_at DESC
");

$stmt->execute([$room_id]);

echo json_encode([
    'success' => true,
    'files' => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
