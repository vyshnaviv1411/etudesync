<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';

$room_id = (int)($_GET['room_id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT p.poll_id, p.question, p.option_a, p.option_b,
    (SELECT COUNT(*) FROM poll_votes WHERE poll_id = p.poll_id AND selected_option = 'A') AS votes_a,
    (SELECT COUNT(*) FROM poll_votes WHERE poll_id = p.poll_id AND selected_option = 'B') AS votes_b
  FROM polls p
  WHERE p.room_id = :room
  ORDER BY p.created_at DESC
");
$stmt->execute([':room'=>$room_id]);

echo json_encode([
  'success'=>true,
  'polls'=>$stmt->fetchAll(PDO::FETCH_ASSOC)
]);
