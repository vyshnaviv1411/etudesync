<?php
// public/api/assessarena/question_add.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

require_once __DIR__ . '/../db.php';

$quiz_id = (int)($_POST['quiz_id'] ?? 0);
$text = trim($_POST['text'] ?? '');
$option_a = trim($_POST['option_a'] ?? '');
$option_b = trim($_POST['option_b'] ?? '');
$option_c = trim($_POST['option_c'] ?? '');
$option_d = trim($_POST['option_d'] ?? '');
$correct_option = strtoupper(trim($_POST['correct_option'] ?? ''));
$user_id = (int)$_SESSION['user_id'];

// Validation
if (!$quiz_id || !$text || !$option_a || !$option_b || !$option_c || !$option_d) {
    echo json_encode(['ok' => false, 'msg' => 'All fields are required']);
    exit;
}

if (!in_array($correct_option, ['A', 'B', 'C', 'D'])) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid correct option']);
    exit;
}

try {
    // Verify quiz ownership
    $stmt = $pdo->prepare("SELECT owner_id FROM quizzes WHERE id = :quiz_id");
    $stmt->execute([':quiz_id' => $quiz_id]);
    $quiz = $stmt->fetch();

    if (!$quiz || $quiz['owner_id'] != $user_id) {
        echo json_encode(['ok' => false, 'msg' => 'Unauthorized or quiz not found']);
        exit;
    }

    // Get next position
    $stmt = $pdo->prepare("SELECT MAX(position) as max_pos FROM questions WHERE quiz_id = :quiz_id");
    $stmt->execute([':quiz_id' => $quiz_id]);
    $result = $stmt->fetch();
    $position = ($result['max_pos'] ?? 0) + 1;

    // Insert question
    $stmt = $pdo->prepare("
        INSERT INTO questions (quiz_id, position, text, option_a, option_b, option_c, option_d, correct_option)
        VALUES (:quiz_id, :position, :text, :a, :b, :c, :d, :correct)
    ");
    $stmt->execute([
        ':quiz_id' => $quiz_id,
        ':position' => $position,
        ':text' => $text,
        ':a' => $option_a,
        ':b' => $option_b,
        ':c' => $option_c,
        ':d' => $option_d,
        ':correct' => $correct_option
    ]);

    echo json_encode([
        'ok' => true,
        'question_id' => $pdo->lastInsertId(),
        'position' => $position,
        'msg' => 'Question added successfully'
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
