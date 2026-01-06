<?php
/**
 * AssessArena Database Diagnostic Tool
 * Shows EXACTLY what's wrong with your database
 *
 * Access: http://localhost/etudesync/public/admin/check_assessarena.php
 */

session_start();
require_once __DIR__ . '/../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die('❌ Please login first');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>AssessArena Diagnostic</title>
    <style>
        body { font-family: monospace; background: #0a0e27; color: #fff; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #1a1f3a; padding: 30px; border-radius: 10px; }
        h1 { color: #47d7d3; border-bottom: 2px solid #47d7d3; padding-bottom: 10px; }
        h2 { color: #7c4dff; margin-top: 30px; }
        .error { background: #ff4444; color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #00C851; color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #ffbb33; color: #000; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #333; }
        th { background: #2a2f4a; color: #47d7d3; }
        pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; border-left: 3px solid #47d7d3; }
        .fix-button { background: linear-gradient(90deg, #7c4dff, #47d7d3); color: white; padding: 15px 30px;
                      border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;
                      text-decoration: none; display: inline-block; margin: 20px 0; }
        .fix-button:hover { transform: scale(1.05); }
        code { background: #2a2f4a; padding: 2px 6px; border-radius: 3px; color: #47d7d3; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 AssessArena Database Diagnostic</h1>
    <p>Checking your database for issues...</p>

<?php
$issues = [];
$fixes = [];

// CHECK 1: Does quizzes table exist?
echo "<h2>✓ Check 1: Table Existence</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'quizzes'");
    if ($stmt->rowCount() == 0) {
        echo "<div class='error'>❌ CRITICAL: Table 'quizzes' does NOT exist!</div>";
        $issues[] = "Table 'quizzes' missing";
        $fixes[] = "create_table";
    } else {
        echo "<div class='success'>✅ Table 'quizzes' exists</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking tables: " . $e->getMessage() . "</div>";
}

// CHECK 2: What columns exist?
echo "<h2>✓ Check 2: Current Table Structure</h2>";
try {
    $stmt = $pdo->query("DESCRIBE quizzes");
    $currentColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Current columns in 'quizzes' table:</p>";
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

    $columnNames = [];
    foreach ($currentColumns as $col) {
        $columnNames[] = $col['Field'];
        echo "<tr>";
        echo "<td><code>{$col['Field']}</code></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Cannot read table structure: " . $e->getMessage() . "</div>";
    $columnNames = [];
}

// CHECK 3: Required columns
echo "<h2>✓ Check 3: Required Columns Check</h2>";
$required = [
    'code' => 'VARCHAR - Unique quiz identifier',
    'owner_id' => 'INT - User who created the quiz',
    'title' => 'VARCHAR - Quiz title',
    'time_limit_minutes' => 'INT - Optional time limit',
    'shuffle_questions' => 'BOOLEAN - Randomize question order'
];

$missing = [];
foreach ($required as $colName => $description) {
    if (in_array($colName, $columnNames)) {
        echo "<div class='success'>✅ Column <code>$colName</code> exists - $description</div>";
    } else {
        echo "<div class='error'>❌ MISSING: Column <code>$colName</code> - $description</div>";
        $missing[] = $colName;
        $issues[] = "Missing column: $colName";
    }
}

// CHECK 4: What PHP code expects
echo "<h2>✓ Check 4: PHP Code Expectations</h2>";
echo "<p>Your PHP backend expects these columns:</p>";
echo "<pre>";
echo "quiz_create.php expects:\n";
echo "  INSERT INTO quizzes (code, owner_id, title, time_limit_minutes, shuffle_questions)\n\n";
echo "quiz_get.php expects:\n";
echo "  SELECT id, title, time_limit_minutes, shuffle_questions FROM quizzes WHERE code = ?";
echo "</pre>";

// SUMMARY
echo "<h2>📋 Diagnostic Summary</h2>";

if (count($issues) == 0) {
    echo "<div class='success'>";
    echo "<h3>🎉 No Issues Found!</h3>";
    echo "<p>Your database schema looks correct. If you're still getting errors:</p>";
    echo "<ul>";
    echo "<li>Clear your browser cache</li>";
    echo "<li>Restart your PHP server (XAMPP)</li>";
    echo "<li>Check the browser console for JavaScript errors</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Issues Found: " . count($issues) . "</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";

    echo "<div class='warning'>";
    echo "<h3>🔧 Fix Required</h3>";

    if (count($missing) > 0) {
        echo "<p>Missing columns: <strong>" . implode(', ', $missing) . "</strong></p>";
        echo "<p>You need to add these columns to your database.</p>";

        echo "<h4>Option 1: Run Automatic Fix (RECOMMENDED)</h4>";
        echo "<p>Click the button below to automatically fix your database:</p>";
        echo "<a href='fix_assessarena_now.php' class='fix-button'>🚀 FIX DATABASE NOW</a>";

        echo "<h4>Option 2: Manual SQL Fix</h4>";
        echo "<p>Copy and run this SQL in phpMyAdmin:</p>";
        echo "<pre>";
        echo "USE etudesync;\n\n";

        foreach ($missing as $col) {
            switch ($col) {
                case 'code':
                    echo "ALTER TABLE quizzes ADD COLUMN code VARCHAR(12) UNIQUE NOT NULL AFTER id;\n";
                    break;
                case 'time_limit_minutes':
                    echo "ALTER TABLE quizzes ADD COLUMN time_limit_minutes INT NULL AFTER title;\n";
                    break;
                case 'shuffle_questions':
                    echo "ALTER TABLE quizzes ADD COLUMN shuffle_questions BOOLEAN DEFAULT FALSE;\n";
                    break;
                case 'owner_id':
                    echo "ALTER TABLE quizzes ADD COLUMN owner_id INT NOT NULL AFTER code;\n";
                    break;
                case 'title':
                    echo "ALTER TABLE quizzes ADD COLUMN title VARCHAR(200) NOT NULL AFTER owner_id;\n";
                    break;
            }
        }

        // Generate codes if code column is being added
        if (in_array('code', $missing)) {
            echo "\n-- Generate unique codes for existing quizzes\n";
            echo "UPDATE quizzes SET code = UPPER(SUBSTRING(MD5(CONCAT(id, RAND())), 1, 8)) WHERE code IS NULL;\n";
        }

        echo "</pre>";
    }

    if (in_array("Table 'quizzes' missing", $issues)) {
        echo "<p><strong>CRITICAL:</strong> The entire 'quizzes' table is missing!</p>";
        echo "<a href='fix_assessarena_now.php' class='fix-button'>🚀 CREATE TABLE NOW</a>";
    }

    echo "</div>";
}

// CURRENT STATE
echo "<h2>📊 Current State</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM quizzes");
    $result = $stmt->fetch();
    echo "<p>Total quizzes in database: <strong>" . $result['count'] . "</strong></p>";

    if ($result['count'] > 0 && in_array('code', $columnNames)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM quizzes WHERE code IS NOT NULL AND code != ''");
        $result = $stmt->fetch();
        echo "<p>Quizzes with codes: <strong>" . $result['count'] . "</strong></p>";
    }
} catch (Exception $e) {
    echo "<p class='warning'>Could not count quizzes: " . $e->getMessage() . "</p>";
}

?>

    <h2>🔗 Next Steps</h2>
    <ul>
        <li><strong>If issues found:</strong> Click "FIX DATABASE NOW" button above</li>
        <li><strong>If no issues:</strong> Test creating a quiz in AssessArena</li>
        <li><strong>Still having problems?</strong> Check browser console for JavaScript errors</li>
    </ul>

    <p style="margin-top: 40px; text-align: center;">
        <a href="../assessarena.php" style="color: #47d7d3;">← Back to AssessArena</a>
    </p>
</div>
</body>
</html>
