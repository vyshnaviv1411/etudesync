<?php
// public/api/fetch_participants.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';

$room_id = (int)($_GET['room_id'] ?? 0);
if ($room_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid room']);
    exit;
}

try {
   $stmt = $pdo->prepare("
    SELECT 
        rp.user_id,
        u.username,
        u.avatar
    FROM room_participants rp
    JOIN users u ON u.id = rp.user_id
    WHERE rp.room_id = :room
      AND rp.last_active > NOW() - INTERVAL 30 SECOND
    ORDER BY rp.last_active DESC
");

    $stmt->execute([':room' => $room_id]);

    $participants = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $participants[] = [
            'user_id' => (int)$r['user_id'],
            'name'    => $r['username'] ?? 'User',
            'avatar'  => $r['avatar'] ?? null
        ];
    }

    echo json_encode([
        'success' => true,
        'participants' => $participants
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
