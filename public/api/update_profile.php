<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success'=>false,'error'=>'Authentication required']);
  exit;
}

$uid = (int) $_SESSION['user_id'];
$payload = json_decode(file_get_contents('php://input'), true);

$username = trim($payload['username'] ?? '');
$bio = trim($payload['bio'] ?? '');

if ($username === '') {
  echo json_encode(['success'=>false,'error'=>'Username required']);
  exit;
}

$stmt = $pdo->prepare("
  UPDATE users 
  SET username = :username, bio = :bio 
  WHERE id = :id
");
$stmt->execute([
  ':username' => $username,
  ':bio' => $bio ?: null,
  ':id' => $uid
]);

// ✅ KEEP SESSION IN SYNC
$_SESSION['user_name'] = $username;

echo json_encode(['success'=>true]);
