<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$poll_id = (int)($_POST['poll_id'] ?? 0);
$option = $_POST['option'] ?? '';

if (!$user_id || !$poll_id || !in_array($option, ['A','B'])) {
  echo json_encode(['success'=>false]);
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO poll_votes (poll_id, user_id, selected_option)
  VALUES (:poll, :user, :opt)
  ON DUPLICATE KEY UPDATE selected_option = VALUES(selected_option)
");
$stmt->execute([
  ':poll'=>$poll_id,
  ':user'=>$user_id,
  ':opt'=>$option
]);

echo json_encode(['success'=>true]);
