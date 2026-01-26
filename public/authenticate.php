<?php
// public/authenticate.php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/premium_check.php';

$disable_dashboard_bg = false;

// Flash helper
function flash_redirect($key, $msg, $loc = 'login.php') {
    $_SESSION[$key] = $msg;
    header('Location: ' . $loc);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    flash_redirect('error', 'Email and password are required.');
}

// Fetch user
$stmt = $pdo->prepare(
    'SELECT id, username, email, password_hash, avatar
     FROM users
     WHERE email = ?
     LIMIT 1'
);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    flash_redirect('error', 'Invalid email or password.');
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    flash_redirect('error', 'Invalid email or password.');
}

// ✅ Login success
session_regenerate_id(true);

$_SESSION['user_id']     = $user['id'];
$_SESSION['user_name']   = $user['username'];
$_SESSION['user_email']  = $user['email'];
$_SESSION['user_avatar'] = $user['avatar'] ?? 'assets/images/avatar-default.jpg';

// ❌ DO NOT SET PREMIUM HERE
// Premium is checked dynamically using DB (isPremiumUser)

// Redirect
$redirect = $_SESSION['after_login_redirect'] ?? 'dashboard.php';
unset($_SESSION['after_login_redirect']);

header('Location: ' . $redirect);
exit;
