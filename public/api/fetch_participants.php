<?php
// public/api/fetch_participants.php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db.php';

$room_id = (int)($_GET['room_id'] ?? 0);

if ($room_id <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

/* ---------- FETCH ROOM + HOST ---------- */
$stmt = $pdo->prepare("
    SELECT r.host_user_id, u.username, u.avatar
    FROM rooms r
    JOIN users u ON u.id = r.host_user_id
    WHERE r.room_id = :room
    LIMIT 1
");
$stmt->execute([':room' => $room_id]);
$host = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$host) {
    echo json_encode(['success' => false]);
    exit;
}

$participants = [];

/* ---------- ADD HOST ---------- */
$participants[] = [
    'user_id'  => (int)$host['host_user_id'],
    'username' => $host['username'],
    'avatar'   => $host['avatar'],
    'is_host'  => true
];

/* ---------- FETCH PARTICIPANTS (NOT REMOVED) ---------- */
$stmt = $pdo->prepare("
    SELECT rp.user_id, u.username, u.avatar
    FROM room_participants rp
    JOIN users u ON u.id = rp.user_id
    WHERE rp.room_id = :room
      AND rp.user_id != :host
      AND rp.removed_at IS NULL
    ORDER BY rp.joined_at ASC
");
$stmt->execute([
    ':room' => $room_id,
    ':host' => $host['host_user_id']
]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $participants[] = [
        'user_id'  => (int)$row['user_id'],
        'username' => $row['username'],
        'avatar'   => $row['avatar'],
        'is_host'  => false
    ];
}

echo json_encode([
    'success' => true,
    'participants' => $participants
]);
exit;
