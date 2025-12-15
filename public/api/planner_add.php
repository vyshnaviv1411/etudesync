<?php
// public/api/planner_add.php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$title = $input['title'] ?? '';
date_default_timezone_set('UTC');
$when = $input['when'] ?? null;
if (!$title || !$when) {
    echo json_encode(['success' => false, 'error' => 'Missing title or when']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO focus_planner (title, scheduled_at, created_at) VALUES (?, ?, NOW())');
    $stmt->execute([$title, $when]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
