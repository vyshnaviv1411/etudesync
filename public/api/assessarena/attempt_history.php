<?php
// public/api/assessarena/attempt_history.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../db.php';

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT
            a.attempt_id,
            a.score,
            a.total_questions,
            a.duration_seconds,
            a.submitted_at,
            q.title as quiz_title,
            q.code as quiz_code
        FROM attempts a
        JOIN quizzes q ON a.quiz_id = q.id
        WHERE a.user_id = :user_id
        ORDER BY a.submitted_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $attempts = $stmt->fetchAll();

    // Add percentage and formatted time
    foreach ($attempts as &$attempt) {
        $attempt['percentage'] = round(($attempt['score'] / $attempt['total_questions']) * 100, 2);
        $attempt['duration_formatted'] = gmdate('i:s', $attempt['duration_seconds']);
    }

    echo json_encode([
        'ok' => true,
        'attempts' => $attempts
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
