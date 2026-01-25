<?php
// public/api/fetch_files.php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

$room_id = (int)($_GET['room_id'] ?? 0);

if ($room_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid room']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        iv.id AS file_id,
        iv.file_name,
        iv.file_path,
        iv.size_bytes,
        rf.shared_at,
        u.username AS user_name
    FROM room_files rf
    JOIN infovault_files iv ON iv.id = rf.infovault_file_id
    JOIN users u ON u.id = rf.shared_by
    WHERE rf.room_id = ?
    ORDER BY rf.shared_at DESC
");

$stmt->execute([$room_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($files as &$f) {
    $f['size_readable'] = formatSize($f['size_bytes']);
}

echo json_encode([
    'success' => true,
    'files' => $files
]);

function formatSize($bytes) {
    if (!$bytes) return '';
    $units = ['B','KB','MB','GB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}
