<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/premium_check.php';

$uid = $_SESSION['user_id'] ?? 0;
if (!$uid) exit(json_encode(['success'=>false]));

// CRITICAL: Require premium access
requirePremiumAPI();

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
