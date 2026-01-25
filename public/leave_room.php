<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$room_id = (int)($_POST['room_id'] ?? 0);

if ($room_id <= 0) {
    header('Location: collabsphere.php');
    exit;
}

/* Get host */
$stmt = $pdo->prepare("
    SELECT host_user_id
    FROM rooms
    WHERE room_id = ?
");
$stmt->execute([$room_id]);
$hostId = (int)$stmt->fetchColumn();

/* ============================
   HOST ENDS SESSION
   ✔ Remove ALL room files
   ✔ Files gone for everyone
   ✖ InfoVault untouched
   ✖ History untouched
============================= */
if ($hostId === $user_id) {

    // 🔥 Delete ALL shared files from the room
    $pdo->prepare("
        DELETE FROM room_files
        WHERE room_id = ?
    ")->execute([$room_id]);

    // ❌ DO NOT delete room_participants
    // ❌ DO NOT delete infovault_files
}

/* ============================
   NORMAL USER LEAVES
   ✖ Delete NOTHING
============================= */

// Optional cleanup
unset($_SESSION['current_room_id']);

header('Location: collabsphere.php');
exit;
