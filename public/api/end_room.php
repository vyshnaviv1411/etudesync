<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

$userId = $_SESSION['user_id'] ?? 0;
$roomId = (int)($_POST['room_id'] ?? 0);

if (!$userId || !$roomId) {
  echo json_encode(['success'=>false]);
  exit;
}

/* Only host can end */
$stmt = $pdo->prepare("SELECT host_user_id FROM rooms WHERE room_id=?");
$stmt->execute([$roomId]);
$hostId = $stmt->fetchColumn();

if ($hostId != $userId) {
  echo json_encode(['success'=>false,'error'=>'Unauthorized']);
  exit;
}

/* DELETE ROOM FILES ONLY */
$pdo->prepare("DELETE FROM room_files WHERE room_id=?")->execute([$roomId]);

echo json_encode(['success'=>true]);
