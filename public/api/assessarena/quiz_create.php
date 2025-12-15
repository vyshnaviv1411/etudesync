<?php
// public/api/assessarena/quiz_create.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../db.php';

$title = trim($_POST['title'] ?? '');
$time_limit = !empty($_POST['time_limit']) ? (int)$_POST['time_limit'] : null;
$shuffle = isset($_POST['shuffle_questions']) ? (bool)$_POST['shuffle_questions'] : false;
$user_id = (int)$_SESSION['user_id'];

if ($title === '') {
    echo json_encode(['ok' => false, 'msg' => 'Quiz title required']);
    exit;
}

try {
    // Generate unique 8-character code
    $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    $stmt = $pdo->prepare("
        INSERT INTO quizzes (code, owner_id, title, time_limit_minutes, shuffle_questions)
        VALUES (:code, :owner_id, :title, :time_limit, :shuffle)
    ");
    $stmt->execute([
        ':code' => $code,
        ':owner_id' => $user_id,
        ':title' => $title,
        ':time_limit' => $time_limit,
        ':shuffle' => $shuffle ? 1 : 0
    ]);

    $quiz_id = $pdo->lastInsertId();

    echo json_encode([
        'ok' => true,
        'quiz_id' => $quiz_id,
        'code' => $code,
        'msg' => 'Quiz created successfully'
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
