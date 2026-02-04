<?php
// public/api/check_room_access.php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['allowed' => false]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$room_id = (int)($_POST['room_id'] ?? $_GET['room_id'] ?? 0);

if ($room_id <= 0) {
    echo json_encode(['allowed' => false]);
    exit;
}

/* ---------- HOST ALWAYS ALLOWED ---------- */
$stmt = $pdo->prepare("
    SELECT host_user_id
    FROM rooms
    WHERE room_id = :room
    LIMIT 1
");
$stmt->execute([':room' => $room_id]);

if ((int)$stmt->fetchColumn() === $user_id) {
    echo json_encode(['allowed' => true]);
    exit;
}

/* ---------- PARTICIPANT CHECK (NOT REMOVED) ---------- */
$stmt = $pdo->prepare("
    SELECT 1
    FROM room_participants
    WHERE room_id = :room
      AND user_id = :user
      AND removed_at IS NULL
    LIMIT 1
");
$stmt->execute([
    ':room' => $room_id,
    ':user' => $user_id
]);

echo json_encode([
    'allowed' => (bool)$stmt->fetchColumn()
]);
exit;
