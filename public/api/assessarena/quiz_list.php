<?php
// public/api/assessarena/quiz_list.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../db.php';

$user_id = (int)$_SESSION['user_id'];

try {
    // Get user's created quizzes with question count
    $stmt = $pdo->prepare("
        SELECT
            q.id,
            q.code,
            q.title,
            q.time_limit_minutes,
            q.created_at,
            COUNT(qs.id) as question_count
        FROM quizzes q
        LEFT JOIN questions qs ON q.id = qs.quiz_id
        WHERE q.owner_id = :user_id
        GROUP BY q.id
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $quizzes = $stmt->fetchAll();

    echo json_encode([
        'ok' => true,
        'quizzes' => $quizzes
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
