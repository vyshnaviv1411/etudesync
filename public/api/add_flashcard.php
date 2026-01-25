<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../includes/db.php';

$uid = (int)$_SESSION['user_id'];
$set_id = (int)$_POST['set_id'];
$q = trim($_POST['question']);
$a = trim($_POST['answer']);

if (!$q || !$a) exit;

$stmt = $pdo->prepare("
  INSERT INTO flashcards (set_id, question, answer)
  VALUES (?, ?, ?)
");
$stmt->execute([$set_id, $q, $a]);

header("Location: ../flashcard_set.php?set_id=$set_id");
exit;
