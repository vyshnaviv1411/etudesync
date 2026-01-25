<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (empty($_SESSION['user_id'])) {
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$card_id = (int)($_POST['card_id'] ?? 0);
$set_id  = (int)($_POST['set_id'] ?? 0);
$q       = trim($_POST['question'] ?? '');
$a       = trim($_POST['answer'] ?? '');

if (!$card_id || !$set_id || !$q || !$a) {
    header("Location: ../flashcard_set.php?set_id=$set_id");
    exit;
}

/* Update */
$stmt = $pdo->prepare("
  UPDATE flashcards
  SET question = ?, answer = ?
  WHERE id = ?
");
$stmt->execute([$q, $a, $card_id]);

header("Location: ../flashcard_set.php?set_id=$set_id");
exit;
