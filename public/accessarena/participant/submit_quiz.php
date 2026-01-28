<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}
requirePremium();

$user_id   = (int) $_SESSION['user_id'];
$quiz_code = trim($_POST['quiz_code'] ?? '');
$answers   = $_POST['answers'] ?? [];

if ($quiz_code === '') {
    die('Invalid submission');
}

/* -------------------------
   FETCH QUIZ
-------------------------- */
$stmt = $pdo->prepare("
    SELECT id 
    FROM accessarena_quizzes 
    WHERE quiz_code = ?
");
$stmt->execute([$quiz_code]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die('Quiz not found');
}

$quiz_id = (int) $quiz['id'];

/* -------------------------
   FETCH PARTICIPANT
-------------------------- */
$stmt = $pdo->prepare("
    SELECT id, completed
    FROM accessarena_participants 
    WHERE quiz_id = ? AND user_id = ?
");
$stmt->execute([$quiz_id, $user_id]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    die('Participant not found');
}

/* ✅ Prevent double submission */
if ((int)$participant['completed'] === 1) {
    header("Location: result.php?code=" . urlencode($quiz_code));
    exit;
}

$participant_id = (int) $participant['id'];

/* -------------------------
   FETCH QUESTIONS
-------------------------- */
$stmt = $pdo->prepare("
    SELECT id, correct_option
    FROM accessarena_questions
    WHERE quiz_id = ?
");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$score = 0;

/* -------------------------
   SAVE ANSWERS
-------------------------- */
foreach ($questions as $q) {
    $qid = (int) $q['id'];
    $selected = isset($answers[$qid]) ? $answers[$qid] : null;
    $is_correct = ($selected === $q['correct_option']) ? 1 : 0;

    if ($is_correct) {
        $score++;
    }

    $stmt = $pdo->prepare("
        INSERT INTO accessarena_answers
            (participant_id, question_id, selected_option, is_correct)
        VALUES
            (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            selected_option = VALUES(selected_option),
            is_correct = VALUES(is_correct)
    ");
    $stmt->execute([
        $participant_id,
        $qid,
        $selected,
        $is_correct
    ]);
}

/* -------------------------
   MARK QUIZ COMPLETED
-------------------------- */
$stmt = $pdo->prepare("
    UPDATE accessarena_participants
    SET completed = 1, score = ?
    WHERE id = ?
");
$stmt->execute([$score, $participant_id]);

/* -------------------------
   REDIRECT TO RESULT
-------------------------- */
header("Location: result.php?code=" . urlencode($quiz_code));
exit;
