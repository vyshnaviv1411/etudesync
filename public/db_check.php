<?php
// public/db_check.php — temporary debug helper
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "DB connection OK\n";

try {
    $stmt = $pdo->query('SELECT COUNT(*) AS c FROM users');
    $row = $stmt->fetch();
    $count = $row ? (int)$row['c'] : 0;
    echo "users table row count: $count\n";
} catch (Exception $e) {
    echo "Error querying users table: " . $e->getMessage() . "\n";
}

echo "\nEnvironment:\n";
echo "PHP version: " . phpversion() . "\n";
echo "Document root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n";

?>
