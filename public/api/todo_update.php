<?php
// public/api/todo_update.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok'=>false]); exit;
}
require_once __DIR__ . '/db.php';

$id = intval($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'pending';
if (!in_array($status, ['pending','done'])) $status = 'pending';

$stmt = $pdo->prepare("UPDATE todos SET status = :status WHERE id = :id AND user_id = :uid");
$res = $stmt->execute([':status'=>$status, ':id'=>$id, ':uid'=> (int)$_SESSION['user_id']]);
echo json_encode(['ok'=>(bool)$res]);

?>
