<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';

$uid = $_SESSION['user_id'] ?? 0;
if (!$uid) exit(json_encode(['success'=>false]));

$stmt = $pdo->prepare("
  SELECT * FROM infovault_files
  WHERE user_id = ?
  ORDER BY uploaded_at DESC
");
$stmt->execute([$uid]);

echo json_encode([
  'success'=>true,
  'files'=>$stmt->fetchAll(PDO::FETCH_ASSOC)
]);
