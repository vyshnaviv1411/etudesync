<?php
/**
 * AssessArena Database Setup Script
 * Run this file once to create the necessary database tables
 */

require_once __DIR__ . '/../includes/db.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>AssessArena Setup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #7c4dff;
            margin-bottom: 10px;
        }
        .success {
            padding: 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            color: #155724;
            margin: 10px 0;
        }
        .error {
            padding: 15px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            color: #721c24;
            margin: 10px 0;
        }
        .info {
            padding: 15px;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            color: #0c5460;
            margin: 10px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(90deg, #7c4dff, #47d7d3);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🎯 AssessArena Database Setup</h1>
        <p>This script will create the necessary database tables for AssessArena.</p>
";

try {
    // Read SQL schema
    $sqlFile = __DIR__ . '/../sql/assessarena_schema.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("SQL schema file not found at: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "<div class='info'><strong>Found " . count($statements) . " SQL statements to execute...</strong></div>";

    $successCount = 0;
    $errors = [];

    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;

        try {
            $pdo->exec($statement);
            $successCount++;

            // Extract table name from CREATE TABLE statement
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                echo "<div class='success'>✓ Created table: <code>{$matches[1]}</code></div>";
            }
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
            echo "<div class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

    echo "<hr style='margin: 30px 0; border: none; border-top: 2px solid #e0e0e0;'>";

    if ($successCount > 0 && empty($errors)) {
        echo "<div class='success'>
            <h3 style='margin-top: 0;'>✓ Setup Complete!</h3>
            <p>All database tables have been created successfully.</p>
            <p><strong>Tables created:</strong></p>
            <ul>
                <li><code>quizzes</code> - Stores quiz information</li>
                <li><code>questions</code> - Stores quiz questions and answers</li>
                <li><code>attempts</code> - Stores user quiz attempts and scores</li>
            </ul>
        </div>";

        echo "<a href='assessarena.php' class='btn'>Go to AssessArena →</a>";

        echo "<div class='info' style='margin-top: 20px;'>
            <strong>Next Steps:</strong>
            <ol>
                <li>Delete or secure this setup file (<code>setup_assessarena.php</code>)</li>
                <li>Start creating quizzes in AssessArena</li>
                <li>Share quiz codes with your friends!</li>
            </ol>
        </div>";

    } else if ($successCount > 0) {
        echo "<div class='info'>
            <h3 style='margin-top: 0;'>⚠ Partial Success</h3>
            <p>Some tables were created, but there were errors. This might be okay if the tables already exist.</p>
            <p><strong>Successful operations:</strong> $successCount</p>
            <p><strong>Errors:</strong> " . count($errors) . "</p>
        </div>";

        echo "<a href='assessarena.php' class='btn'>Try AssessArena Anyway →</a>";
    } else {
        echo "<div class='error'>
            <h3 style='margin-top: 0;'>✗ Setup Failed</h3>
            <p>No tables were created. Please check the errors above and try again.</p>
        </div>";
    }

    // Show table status
    echo "<hr style='margin: 30px 0; border: none; border-top: 2px solid #e0e0e0;'>";
    echo "<h3>Database Table Status:</h3>";

    $tables = ['quizzes', 'questions', 'attempts'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                // Get row count
                $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $countStmt->fetch()['count'];
                echo "<div class='success'>✓ Table <code>$table</code> exists ($count rows)</div>";
            } else {
                echo "<div class='error'>✗ Table <code>$table</code> does not exist</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>✗ Cannot check table <code>$table</code>: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

} catch (Exception $e) {
    echo "<div class='error'>
        <h3 style='margin-top: 0;'>✗ Fatal Error</h3>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
    </div>";
}

echo "
    </div>
</body>
</html>";
