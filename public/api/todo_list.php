<?php
// public/api/todo_list.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
require_once __DIR__ . '/db.php';
$user_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, task, DATE_FORMAT(due_date, '%Y-%m-%d') AS due_date, status FROM todos WHERE user_id = :uid ORDER BY created_at DESC");
$stmt->execute([':uid'=>$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows);
?>
