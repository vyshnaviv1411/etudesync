<?php
// public/api/remove_participant.php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/db.php';

/* ---------- AUTH CHECK ---------- */
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

$host_id = (int) $_SESSION['user_id'];

/* ---------- INPUT ---------- */
$data = json_decode(file_get_contents('php://input'), true);

$room_id = (int)($data['room_id'] ?? 0);
$user_id = (int)($data['user_id'] ?? 0);

if ($room_id <= 0 || $user_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit;
}

/* ---------- PREVENT SELF REMOVE ---------- */
if ($host_id === $user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'You cannot remove yourself'
    ]);
    exit;
}

/* ---------- VERIFY HOST ---------- */
$stmt = $pdo->prepare("
    SELECT host_user_id
    FROM rooms
    WHERE room_id = :room
    LIMIT 1
");
$stmt->execute([':room' => $room_id]);

$room_host = (int)$stmt->fetchColumn();

if ($room_host !== $host_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized action'
    ]);
    exit;
}

/* ---------- SOFT REMOVE PARTICIPANT ---------- */
$upd = $pdo->prepare("
    UPDATE room_participants
    SET removed_at = NOW(),
        removed_by = :host
    WHERE room_id = :room
      AND user_id = :user
      AND removed_at IS NULL
");

$upd->execute([
    ':room' => $room_id,
    ':user' => $user_id,
    ':host' => $host_id
]);

echo json_encode([
    'success' => true,
    'message' => 'Participant removed successfully'
]);
exit;
