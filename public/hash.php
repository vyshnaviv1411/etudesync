<?php
// public/hash.php - temporary helper to generate a password hash for a test user
// Usage: open http://localhost/etudesync/public/hash.php in your browser
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');
echo "Password hash for 'admin123':\n";
echo password_hash('admin123', PASSWORD_DEFAULT) . "\n";

?>
