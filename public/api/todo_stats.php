<?php
// public/api/todo_stats.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
$user_id = (int)($_SESSION['user_id'] ?? 0);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = :uid AND status='done'");
$stmt->execute([':uid'=>$user_id]);
$done = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = :uid");
$stmt->execute([':uid'=>$user_id]);
$total = (int)$stmt->fetchColumn();

echo json_encode(['done'=>$done, 'total'=>$total]);
?>
