<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user_id'])) exit;

require_once __DIR__ . '/../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$mindmap_id = (int)$data['mindmap_id'];
$parent_id  = $data['parent_id'] !== null ? (int)$data['parent_id'] : null;

/* Default position */
$x = 400;
$y = 260;

/* Place child near parent */
if ($parent_id) {
  $stmt = $pdo->prepare("SELECT x,y FROM mindmap_nodes WHERE id=?");
  $stmt->execute([$parent_id]);
  if ($p = $stmt->fetch()) {
    $x = $p['x'] + rand(-160, 160);
    $y = $p['y'] + rand(90, 160);
  }
}

$stmt = $pdo->prepare("
  INSERT INTO mindmap_nodes (mindmap_id, parent_id, text, x, y)
  VALUES (?, ?, 'New Node', ?, ?)
");
$stmt->execute([$mindmap_id, $parent_id, $x, $y]);

echo json_encode([
  'id' => $pdo->lastInsertId(),
  'text' => 'New Node',
  'x' => $x,
  'y' => $y,
  'parent_id' => $parent_id
]);
