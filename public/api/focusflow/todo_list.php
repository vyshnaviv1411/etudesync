<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

try {
    $sql = "
        SELECT id, title, description, due_date, priority, status, created_at, completed_at
        FROM todos
        WHERE user_id = :user_id
    ";

    if ($filter !== 'all') {
        $sql .= " AND status = :status";
    }

    $sql .= " ORDER BY
        CASE
            WHEN status = 'completed' THEN 3
            WHEN status = 'in_progress' THEN 1
            ELSE 2
        END,
        CASE priority
            WHEN 'high' THEN 1
            WHEN 'medium' THEN 2
            ELSE 3
        END,
        due_date ASC,
        created_at DESC
    ";

    $stmt = $pdo->prepare($sql);

    if ($filter !== 'all') {
        $stmt->execute([':user_id' => $user_id, ':status' => $filter]);
    } else {
        $stmt->execute([':user_id' => $user_id]);
    }

    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'todos' => $todos
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
