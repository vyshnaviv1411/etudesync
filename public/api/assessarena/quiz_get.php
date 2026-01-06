<?php
// public/api/assessarena/quiz_get.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

// CRITICAL: Require premium access
requirePremiumAPI();

require_once __DIR__ . '/../db.php';

$code = strtoupper(trim($_GET['code'] ?? ''));

if (!$code) {
    echo json_encode(['ok' => false, 'msg' => 'Quiz code required']);
    exit;
}

try {
    // Get quiz details
    $stmt = $pdo->prepare("SELECT id, title, time_limit_minutes, shuffle_questions FROM quizzes WHERE code = :code");
    $stmt->execute([':code' => $code]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        echo json_encode(['ok' => false, 'msg' => 'Quiz not found']);
        exit;
    }

    // Get questions (without correct answers for taking quiz)
    $stmt = $pdo->prepare("
        SELECT id, text, option_a, option_b, option_c, option_d, position
        FROM questions
        WHERE quiz_id = :quiz_id
        ORDER BY position ASC
    ");
    $stmt->execute([':quiz_id' => $quiz['id']]);
    $questions = $stmt->fetchAll();

    // Shuffle if enabled
    if ($quiz['shuffle_questions']) {
        shuffle($questions);
    }

    echo json_encode([
        'ok' => true,
        'quiz' => [
            'id' => $quiz['id'],
            'title' => $quiz['title'],
            'time_limit_minutes' => $quiz['time_limit_minutes'],
            'total_questions' => count($questions)
        ],
        'questions' => $questions
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
