<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$day_of_week = (int)($data['day_of_week'] ?? -1);
$start_time = $data['start_time'] ?? '';
$end_time = $data['end_time'] ?? '';
$subject = trim($data['subject'] ?? '');

if (empty($title) || $day_of_week < 0 || $day_of_week > 6 || empty($start_time) || empty($end_time)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO study_plans (user_id, title, description, day_of_week, start_time, end_time, subject)
        VALUES (:user_id, :title, :description, :day, :start, :end, :subject)
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':title' => $title,
        ':description' => $description,
        ':day' => $day_of_week,
        ':start' => $start_time,
        ':end' => $end_time,
        ':subject' => $subject
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Study block added successfully',
        'id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
