<?php
// public/seed_test_user.php — temporary helper to insert a test user (local dev only)
// Usage: open http://localhost/etudesync/public/seed_test_user.php in your browser
// IMPORTANT: Delete this file after use to avoid leaving a seeding script on a public server.

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');

$email = 'admin@example.com';
$username = 'admin';
$password = 'admin123';

try {
    // Check if user exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row) {
        echo "User with email $email already exists (id: " . $row['id'] . ").\n";
        echo "If you want to recreate it, delete the row from the database and re-run this script.\n";
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO users (username, email, password_hash, is_premium, created_at) VALUES (?, ?, ?, 0, NOW())');
    $ins->execute([$username, $email, $hash]);

    echo "Inserted test user successfully.\n";
    echo "Email: $email\nPassword: $password\n";
    echo "Please delete public/seed_test_user.php (and public/hash.php) after you finish testing.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
