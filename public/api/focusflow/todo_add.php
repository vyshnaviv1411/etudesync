<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/validate_date.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$due_date = $data['due_date'] ?? null;
$priority = $data['priority'] ?? 'medium';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

// CRITICAL: Server-side validation for past dates (centralized)
validateDateOrExit($due_date, 'due date');

try {
    $stmt = $pdo->prepare("
        INSERT INTO todos (user_id, title, description, due_date, priority, status)
        VALUES (:user_id, :title, :description, :due_date, :priority, 'pending')
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':title' => $title,
        ':description' => $description,
        ':due_date' => $due_date ?: null,
        ':priority' => $priority
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Task added successfully',
        'id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
