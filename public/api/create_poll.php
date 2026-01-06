<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success'=>false, 'error'=>'Login required']);
  exit;
}

$room_id = (int)($_POST['room_id'] ?? 0);
$question = trim($_POST['question'] ?? '');
$opt1 = trim($_POST['opt1'] ?? '');
$opt2 = trim($_POST['opt2'] ?? '');

if ($room_id <= 0 || $question === '' || $opt1 === '' || $opt2 === '') {
  echo json_encode(['success'=>false, 'error'=>'Missing data']);
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO polls (room_id, question, option_a, option_b)
  VALUES (:room, :q, :a, :b)
");
$stmt->execute([
  ':room'=>$room_id,
  ':q'=>$question,
  ':a'=>$opt1,
  ':b'=>$opt2
]);

echo json_encode(['success'=>true]);
