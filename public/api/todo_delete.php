<?php
// public/api/todo_delete.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok'=>false]); exit;
}
require_once __DIR__ . '/db.php';

$id = intval($_POST['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM todos WHERE id = :id AND user_id = :uid");
$res = $stmt->execute([':id'=>$id, ':uid'=> (int)$_SESSION['user_id']]);
echo json_encode(['ok'=>(bool)$res]);

?>
