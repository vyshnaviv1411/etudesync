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

$id = (int)($data['id'] ?? 0);
$status = $data['status'] ?? '';

if ($id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $completed_at = $status === 'completed' ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare("
        UPDATE todos
        SET status = :status, completed_at = :completed_at
        WHERE id = :id AND user_id = :user_id
    ");

    $stmt->execute([
        ':status' => $status,
        ':completed_at' => $completed_at,
        ':id' => $id,
        ':user_id' => $user_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Task updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Task not found or not authorized']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
