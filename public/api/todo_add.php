<?php
// public/api/todo_add.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok'=>false,'msg'=>'Not logged in']);
    exit;
}
require_once __DIR__ . '/db.php';

$task = trim($_POST['task'] ?? '');
$due = $_POST['due_date'] ?? null;
$user_id = (int)$_SESSION['user_id'];

if ($task === '') {
    echo json_encode(['ok'=>false,'msg'=>'Task required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO todos (user_id, task, due_date) VALUES (:uid, :task, :due)");
    $stmt->execute([':uid'=>$user_id, ':task'=>$task, ':due'=>$due ?: null]);
    echo json_encode(['ok'=>true, 'id'=>$pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
}

?>
