<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success'=>false,'error'=>'Login required']);
  exit;
}

$uid = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT is_premium FROM users WHERE id=?");
$stmt->execute([$uid]);
if (!$stmt->fetchColumn()) {
  echo json_encode(['success'=>false,'error'=>'Premium required']);
  exit;
}

if (empty($_FILES['file'])) {
  echo json_encode(['success'=>false,'error'=>'No file']);
  exit;
}

$uploadDir = __DIR__ . '/../../assets/uploads/infovault/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$file = $_FILES['file'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$safe = bin2hex(random_bytes(10)) . ($ext ? ".$ext" : '');
$path = $uploadDir . $safe;

if (!move_uploaded_file($file['tmp_name'], $path)) {
  echo json_encode(['success'=>false,'error'=>'Upload failed']);
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO infovault_files (user_id, file_name, file_path, mime_type, size_bytes)
  VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([
  $uid,
  $file['name'],
  'assets/uploads/infovault/' . $safe,
  mime_content_type($path),
  filesize($path)
]);

header('Location: ../infovault_files.php');
exit;
