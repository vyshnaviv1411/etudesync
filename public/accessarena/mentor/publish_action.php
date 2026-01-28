<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die('Invalid quiz');
}

$quiz_id = (int)$_GET['quiz_id'];
$uid = $_SESSION['user_id'];

/* Ensure quiz belongs to mentor and is draft */
$stmt = $pdo->prepare("
  SELECT id
  FROM accessarena_quizzes
  WHERE id = ? AND creator_id = ? AND status = 'draft'
");
$stmt->execute([$quiz_id, $uid]);

if (!$stmt->fetch()) {
    die('Quiz not found or already published');
}

/* Generate quiz code if not exists */
$quizCode = strtoupper(substr(md5(uniqid()), 0, 6));

/* ✅ PUBLISH QUIZ */
$pdo->prepare("
  UPDATE accessarena_quizzes
  SET status = 'published', quiz_code = ?
  WHERE id = ?
")->execute([$quizCode, $quiz_id]);

/* Redirect to Published page */
header('Location: publish_quiz.php?published=1');
exit;
