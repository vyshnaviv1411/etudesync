<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$card_id = (int)($_POST['card_id'] ?? 0);
$set_id  = (int)($_POST['set_id'] ?? 0);

if (!$card_id || !$set_id) {
    header("Location: ../infovault_flashcards.php");
    exit;
}

/* Delete flashcard */
$stmt = $pdo->prepare("
  DELETE FROM flashcards
  WHERE id = ?
");
$stmt->execute([$card_id]);

/* Redirect back to set */
header("Location: ../flashcard_set.php?set_id=$set_id");
exit;
