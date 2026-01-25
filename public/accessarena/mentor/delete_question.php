<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';

if (!isset($_SESSION['user_id'])) exit;

$id = (int)$_GET['id'];
$quiz_id = (int)$_GET['quiz_id'];
$uid = $_SESSION['user_id'];

/* Ensure ownership + draft */
$stmt = $pdo->prepare("
  DELETE q FROM accessarena_questions q
  JOIN accessarena_quizzes quiz ON quiz.id = q.quiz_id
  WHERE q.id=? AND quiz.creator_id=? AND quiz.status='draft'
");
$stmt->execute([$id, $uid]);

/* Update count */
$pdo->prepare("
  UPDATE accessarena_quizzes
  SET total_questions = GREATEST(total_questions-1,0)
  WHERE id=?
")->execute([$quiz_id]);

header("Location: add_questions.php?quiz_id=$quiz_id");
exit;
