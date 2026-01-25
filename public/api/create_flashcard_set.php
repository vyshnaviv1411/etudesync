<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}

$title = trim($_POST['title'] ?? '');
if ($title === '') {
  header('Location: ../infovault_flashcards.php');
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO flashcard_sets (user_id, title)
  VALUES (?, ?)
");
$stmt->execute([(int)$_SESSION['user_id'], $title]);

header('Location: ../infovault_flashcards.php');
exit;
