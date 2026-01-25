<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user_id'])) exit;

require_once __DIR__ . '/../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if (!$id) {
  echo json_encode(['status'=>'error']);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM mindmap_nodes WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['status'=>'ok']);
