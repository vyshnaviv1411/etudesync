<?php
// public/api/fetch_files.php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

$room_id = (int)($_GET['room_id'] ?? 0);

if ($room_id <= 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid room_id'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            f.file_id,
            f.file_name,
            f.file_path,
            f.size_bytes,
            f.uploaded_at,
            u.username AS user_name
        FROM files f
        LEFT JOIN users u ON u.id = f.user_id
        WHERE f.room_id = :room
        ORDER BY f.uploaded_at DESC
    ");

    $stmt->execute([':room' => $room_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // add readable file size
    foreach ($files as &$f) {
        $f['size_readable'] = humanFileSize((int)$f['size_bytes']);
    }

    echo json_encode([
        'success' => true,
        'files'   => $files
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
    exit;
}

/* ---------- helper ---------- */
function humanFileSize($bytes, $decimals = 2) {
    $units = ['B','KB','MB','GB','TB'];
    $factor = floor((strlen((string)$bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
}
