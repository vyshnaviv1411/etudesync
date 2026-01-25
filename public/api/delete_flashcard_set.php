<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$uid   = (int)$_SESSION['user_id'];
$setId = (int)($_POST['set_id'] ?? 0);

if (!$setId) {
    header('Location: ../infovault_flashcards.php');
    exit;
}

/* 1️⃣ Delete all flashcards belonging to the set */
$stmt = $pdo->prepare("
  DELETE FROM flashcards
  WHERE set_id = ?
");
$stmt->execute([$setId]);

/* 2️⃣ Delete the flashcard set (ownership check here) */
$stmt = $pdo->prepare("
  DELETE FROM flashcard_sets
  WHERE id = ? AND user_id = ?
");
$stmt->execute([$setId, $uid]);

header('Location: ../infovault_flashcards.php');
exit;
