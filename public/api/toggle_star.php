<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db.php';

if (empty($_SESSION['user_id'])) {
    die('Not logged in');
}

$user_id = (int)$_SESSION['user_id'];
$file_id = (int)($_POST['file_id'] ?? 0);

if ($file_id <= 0) {
    die('Invalid file id');
}

$stmt = $pdo->prepare("
    UPDATE infovault_files
    SET is_starred = CASE
        WHEN is_starred = 1 THEN 0
        ELSE 1
    END
    WHERE id = ? AND user_id = ?
");

if (!$stmt->execute([$file_id, $user_id])) {
    die('DB update failed');
}

/* ABSOLUTE redirect */
header('Location: /etudesync/public/infovault_files.php');
exit;
