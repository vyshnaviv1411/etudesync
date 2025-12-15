<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT id, title, description, day_of_week, start_time, end_time, subject, is_active, created_at
        FROM study_plans
        WHERE user_id = :user_id AND is_active = 1
        ORDER BY day_of_week ASC, start_time ASC
    ");

    $stmt->execute([':user_id' => $user_id]);
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'plans' => $plans
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
