<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
  exit;
}

require_once __DIR__ . '/../../includes/db.php';

$uid = (int)$_SESSION['user_id'];
$mindmap_id = (int)($_POST['mindmap_id'] ?? 0);

if (!$mindmap_id) {
  header('Location: ../infovault_mindmaps.php');
  exit;
}

/* Ownership check + delete */
$stmt = $pdo->prepare("
  DELETE FROM mindmaps
  WHERE id = ? AND user_id = ?
");
$stmt->execute([$mindmap_id, $uid]);

header('Location: ../infovault_mindmaps.php');
exit;
