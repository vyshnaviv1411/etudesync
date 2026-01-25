<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Login required']);
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$fileId   = (int)($_POST['file_id'] ?? 0);
$roomCode = trim($_POST['room_code'] ?? '');

if ($fileId <= 0 || $roomCode === '') {
    echo json_encode(['success'=>false,'error'=>'Invalid data']);
    exit;
}

/* 1️⃣ Resolve room_code → room_id */
$stmt = $pdo->prepare("
  SELECT room_id FROM rooms
  WHERE room_code = ?
  LIMIT 1
");
$stmt->execute([$roomCode]);
$roomId = (int)$stmt->fetchColumn();

if (!$roomId) {
    echo json_encode(['success'=>false,'error'=>'Room not found']);
    exit;
}

/* 2️⃣ Verify ownership */
$stmt = $pdo->prepare("
  SELECT id FROM infovault_files
  WHERE id = ? AND user_id = ?
");
$stmt->execute([$fileId, $userId]);

if (!$stmt->fetch()) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

/* 3️⃣ Prevent duplicate */
$stmt = $pdo->prepare("
  SELECT id FROM room_files
  WHERE room_id = ? AND infovault_file_id = ?
");
$stmt->execute([$roomId, $fileId]);

if ($stmt->fetch()) {
    echo json_encode(['success'=>false,'error'=>'Already shared']);
    exit;
}

/* 4️⃣ Share */
$stmt = $pdo->prepare("
  INSERT INTO room_files (room_id, infovault_file_id, shared_by)
  VALUES (?, ?, ?)
");
$stmt->execute([$roomId, $fileId, $userId]);

echo json_encode(['success'=>true]);
