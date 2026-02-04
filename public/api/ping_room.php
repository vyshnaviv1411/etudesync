<?php
// public/api/ping_room.php

session_start();
require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$room_id = (int)($_POST['room_id'] ?? 0);

if ($room_id <= 0) {
    http_response_code(400);
    exit;
}

/* ---------------- HOST ALWAYS ALLOWED ---------------- */
$stmt = $pdo->prepare("
    SELECT host_user_id
    FROM rooms
    WHERE room_id = :room
    LIMIT 1
");
$stmt->execute([':room' => $room_id]);

$host_id = (int)$stmt->fetchColumn();

if ($host_id === $user_id) {
    http_response_code(200);
    exit;
}

/* ---------------- UPDATE PRESENCE ---------------- */
$stmt = $pdo->prepare("
    UPDATE room_participants
    SET last_active = NOW()
    WHERE room_id = :room AND user_id = :user
");
$stmt->execute([
    ':room' => $room_id,
    ':user' => $user_id
]);

if ($stmt->rowCount() === 0) {
    // user removed
    http_response_code(403);
    exit;
}

http_response_code(200);
exit;
