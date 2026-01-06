<?php
// authenticate.php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Simple helper to set flash and redirect
function flash_redirect($key, $msg, $loc = 'login.php') {
    $_SESSION[$key] = $msg;
    header('Location: ' . $loc);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// get input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    flash_redirect('error', 'Email and password are required.');
}

// find user by email
$stmt = $pdo->prepare(
  'SELECT id, username, email, password_hash, is_premium 
   FROM users 
   WHERE email = ? 
   LIMIT 1'
);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    flash_redirect('error', 'Invalid email or password.');
}

// verify password
if (!password_verify($password, $user['password_hash'])) {
    flash_redirect('error', 'Invalid email or password.');
}

// Successful login — set session and redirect to dashboard
// Use minimal info in session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['user_avatar'] = $user['avatar'] ?? 'assets/images/avatar-default.jpg';
$_SESSION['is_premium'] = (int)$user['is_premium'];



// regenerate session id for security
session_regenerate_id(true);

// Optional: redirect to previous page if stored
$redirect = $_SESSION['after_login_redirect'] ?? 'dashboard.php';
unset($_SESSION['after_login_redirect']);

header('Location: ' . $redirect);
exit;
