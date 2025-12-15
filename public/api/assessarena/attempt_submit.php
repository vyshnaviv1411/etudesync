<?php
// public/api/assessarena/attempt_submit.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../db.php';

$quiz_id = (int)($_POST['quiz_id'] ?? 0);
$started_at = trim($_POST['started_at'] ?? '');
$answers = $_POST['answers'] ?? [];
$user_id = (int)$_SESSION['user_id'];

if (!$quiz_id || !$started_at || !is_array($answers)) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid submission data']);
    exit;
}

try {
    // Get quiz and all correct answers
    $stmt = $pdo->prepare("
        SELECT id, text, correct_option
        FROM questions
        WHERE quiz_id = :quiz_id
        ORDER BY position ASC
    ");
    $stmt->execute([':quiz_id' => $quiz_id]);
    $questions = $stmt->fetchAll();

    if (count($questions) === 0) {
        echo json_encode(['ok' => false, 'msg' => 'Quiz has no questions']);
        exit;
    }

    // Calculate score
    $score = 0;
    $results = [];

    foreach ($questions as $q) {
        $question_id = $q['id'];
        $user_answer = strtoupper(trim($answers[$question_id] ?? ''));
        $correct = $q['correct_option'];
        $is_correct = ($user_answer === $correct);

        if ($is_correct) {
            $score++;
        }

        $results[] = [
            'question_id' => $question_id,
            'user_answer' => $user_answer,
            'correct_answer' => $correct,
            'is_correct' => $is_correct
        ];
    }

    $total_questions = count($questions);
    $submitted_at = date('Y-m-d H:i:s');

    // Calculate duration
    $start_time = strtotime($started_at);
    $end_time = strtotime($submitted_at);
    $duration_seconds = max(0, $end_time - $start_time);

    // Generate unique attempt ID
    $attempt_id = uniqid('attempt_', true);

    // Save attempt
    $stmt = $pdo->prepare("
        INSERT INTO attempts (attempt_id, quiz_id, user_id, score, total_questions, duration_seconds, started_at, submitted_at, answers)
        VALUES (:attempt_id, :quiz_id, :user_id, :score, :total, :duration, :started_at, :submitted_at, :answers)
    ");
    $stmt->execute([
        ':attempt_id' => $attempt_id,
        ':quiz_id' => $quiz_id,
        ':user_id' => $user_id,
        ':score' => $score,
        ':total' => $total_questions,
        ':duration' => $duration_seconds,
        ':started_at' => $started_at,
        ':submitted_at' => $submitted_at,
        ':answers' => json_encode($results)
    ]);

    echo json_encode([
        'ok' => true,
        'attempt_id' => $attempt_id,
        'score' => $score,
        'total_questions' => $total_questions,
        'percentage' => round(($score / $total_questions) * 100, 2),
        'duration_seconds' => $duration_seconds,
        'results' => $results,
        'msg' => 'Quiz submitted successfully'
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
