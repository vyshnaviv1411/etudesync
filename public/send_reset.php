<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../includes/db.php';


$email = trim($_POST['email'] ?? '');

if ($email === '') {
    $_SESSION['error'] = 'Email is required';
    header('Location: forgot.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = 'No account found with this email';
    header('Location: forgot.php');
    exit;
}

// remove old tokens
$pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")
    ->execute([$user['id']]);

$token = bin2hex(random_bytes(32));
$expires = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');


$pdo->prepare(
    "INSERT INTO password_resets (user_id, token, expires_at)
     VALUES (?, ?, ?)"
)->execute([$user['id'], $token, $expires]);

// store ONLY the link
$_SESSION['reset_link'] =
    "http://localhost/etudesync/public/reset_password.php?token=$token";

header('Location: forgot.php');
exit;
