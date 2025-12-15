<?php
// public/api/planner_list.php
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query('SELECT id, title, scheduled_at FROM focus_planner ORDER BY scheduled_at ASC');
    $rows = $stmt->fetchAll();
    echo json_encode(['success' => true, 'items' => $rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
