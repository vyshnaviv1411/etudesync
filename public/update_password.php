<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($token === '' || $password === '' || $confirm === '') {
    $_SESSION['error'] = 'All fields required.';
    header("Location: reset_password.php?token=$token");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match.';
    header("Location: reset_password.php?token=$token");
    exit;
}

// verify token again
$stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $_SESSION['error'] = 'Invalid reset attempt.';
    header('Location: forgot.php');
    exit;
}

// update password
$hash = password_hash($password, PASSWORD_DEFAULT);

$pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
    ->execute([$hash, $row['user_id']]);

// delete token
$pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

$_SESSION['success'] = 'Password updated successfully. Please log in.';
header('Location: login.php');
exit;
