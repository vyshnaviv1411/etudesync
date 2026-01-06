<?php
// public/api/upload_file.php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Login required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$room_id = (int)($_POST['room_id'] ?? 0);

if ($room_id <= 0) {
    echo json_encode(['success'=>false,'error'=>'Invalid room']);
    exit;
}

if (empty($_FILES['file'])) {
    echo json_encode(['success'=>false,'error'=>'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'error'=>'Upload error']);
    exit;
}

// size limit: 50MB
if ($file['size'] > 50 * 1024 * 1024) {
    echo json_encode(['success'=>false,'error'=>'File too large']);
    exit;
}

/* ---------- MIME BEFORE MOVE ---------- */
$mime = mime_content_type($file['tmp_name']);

/* ---------- UPLOAD PATH (FIXED) ---------- */
$uploadDir = __DIR__ . '/../../assets/uploads/room_files/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeName = bin2hex(random_bytes(12)) . ($ext ? '.' . $ext : '');
$targetPath = $uploadDir . $safeName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success'=>false,'error'=>'Failed to save file']);
    exit;
}

$publicPath = 'assets/uploads/room_files/' . $safeName;

try {
    $stmt = $pdo->prepare("
        INSERT INTO files 
            (room_id, user_id, file_name, file_path, mime_type, size_bytes)
        VALUES
            (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $room_id,
        $user_id,
        $file['name'],
        $publicPath,
        $mime,
        (int)$file['size']
    ]);

    echo json_encode(['success'=>true]);
    exit;

} catch (PDOException $e) {
    if (file_exists($targetPath)) unlink($targetPath);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}
