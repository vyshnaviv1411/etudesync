<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../includes/db.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$file_id = (int)($_POST['file_id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT file_path FROM infovault_files
  WHERE id = ? AND user_id = ?
");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if ($file) {
  $fullPath = __DIR__ . '/../../' . $file['file_path'];
  if (file_exists($fullPath)) unlink($fullPath);

  $pdo->prepare("DELETE FROM infovault_files WHERE id = ?")
      ->execute([$file_id]);
}

header('Location: ../infovault_files.php');
exit;
