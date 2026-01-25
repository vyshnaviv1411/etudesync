<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
  exit;
}

require_once __DIR__ . '/../../includes/db.php';

$uid = (int)$_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');

if (!$title) {
  header('Location: ../infovault_mindmaps.php');
  exit;
}

/* Create mindmap */
$stmt = $pdo->prepare("
  INSERT INTO mindmaps (user_id, title)
  VALUES (?, ?)
");
$stmt->execute([$uid, $title]);

$mindmap_id = $pdo->lastInsertId();

/* Create root node automatically */
$stmt = $pdo->prepare("
  INSERT INTO mindmap_nodes (mindmap_id, parent_id, text, x, y)
  VALUES (?, NULL, ?, 300, 200)
");
$stmt->execute([$mindmap_id, $title]);

header("Location: ../mindmap_editor.php?mindmap_id=$mindmap_id");
exit;
