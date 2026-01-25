<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user_id'])) exit;

require_once __DIR__ . '/../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$id   = (int)($data['id'] ?? 0);
$x    = isset($data['x']) ? (int)$data['x'] : null;
$y    = isset($data['y']) ? (int)$data['y'] : null;
$text = trim($data['text'] ?? '');

if (!$id) {
  echo json_encode(['status'=>'error']);
  exit;
}

/* 🚫 DO NOT overwrite text with empty value */
if ($text === '') {
  $stmt = $pdo->prepare("
    UPDATE mindmap_nodes
    SET x = ?, y = ?
    WHERE id = ?
  ");
  $stmt->execute([$x, $y, $id]);

  echo json_encode(['status'=>'ok']);
  exit;
}

/* ✅ Normal update */
$stmt = $pdo->prepare("
  UPDATE mindmap_nodes
  SET text = ?, x = ?, y = ?
  WHERE id = ?
");
$stmt->execute([$text, $x, $y, $id]);

echo json_encode(['status'=>'ok']);
