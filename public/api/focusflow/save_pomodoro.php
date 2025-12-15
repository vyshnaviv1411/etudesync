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

$duration_minutes = isset($data['duration_minutes']) ? (int)$data['duration_minutes'] : 25;
$completed = isset($data['completed']) ? (int)$data['completed'] : 1;

try {
    $stmt = $pdo->prepare("
        INSERT INTO pomodoro_sessions (user_id, duration_minutes, completed, completed_at)
        VALUES (:user_id, :duration, :completed, :completed_at)
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':duration' => $duration_minutes,
        ':completed' => $completed,
        ':completed_at' => $completed ? date('Y-m-d H:i:s') : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Session saved',
        'id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
