<?php
/**
 * ✅ DEFINITIVE AssessArena Database Fix
 *
 * This script resolves the ROOT CAUSE of SQL column errors.
 *
 * PROBLEM IDENTIFIED:
 * - Conflicting schema files defined different column names
 * - focusflow_assessarena_schema.sql used: quiz_code, creator_id, time_limit
 * - assessarena_schema.sql uses: code, owner_id, time_limit_minutes
 * - PHP code expects: code, owner_id, time_limit_minutes
 *
 * SOLUTION:
 * - Detect which columns exist in current database
 * - If wrong columns (quiz_code), rename them to correct names (code)
 * - If missing columns, add them
 * - Ensure table matches canonical schema exactly
 *
 * Access: http://localhost/etudesync/public/admin/FIX_ASSESSARENA_DEFINITIVE.php
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
    <title>Definitive AssessArena Fix</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0a0e27; color: #fff; padding: 20px; line-height: 1.6; }
        .container { max-width: 1100px; margin: 0 auto; background: #1a1f3a; padding: 30px; border-radius: 10px; }
        h1 { color: #47d7d3; border-bottom: 3px solid #47d7d3; padding-bottom: 10px; }
        h2 { color: #7c4dff; margin-top: 30px; }
        .step { background: #2a2f4a; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #7c4dff; }
        .success { border-left-color: #00C851; color: #00C851; }
        .error { border-left-color: #ff4444; color: #ff4444; }
        .warning { border-left-color: #ffbb33; color: #ffbb33; }
        .info { color: #47d7d3; }
        pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; border-left: 3px solid #47d7d3; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #333; }
        th { background: #2a2f4a; color: #47d7d3; }
        .btn { background: linear-gradient(90deg, #7c4dff, #47d7d3); color: white; padding: 15px 30px;
               border: none; border-radius: 8px; font-size: 16px; text-decoration: none; display: inline-block; margin: 10px 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Definitive AssessArena Database Fix</h1>
    <p class="info">Analyzing your database and applying the correct schema...</p>

<?php
$steps = [];
$errors = [];
$warnings = [];

try {
    // ========================================
    // STEP 1: Check if quizzes table exists
    // ========================================
    echo "<div class='step'>";
    echo "<h2>Step 1: Checking if 'quizzes' table exists</h2>";

    $stmt = $pdo->query("SHOW TABLES LIKE 'quizzes'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        echo "<p class='warning'>⚠️  Table 'quizzes' does NOT exist. Will create from canonical schema.</p>";

        // Create table from canonical schema
        $createSQL = "CREATE TABLE quizzes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(12) UNIQUE NOT NULL,
            owner_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            time_limit_minutes INT NULL,
            shuffle_questions BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_owner (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($createSQL);
        echo "<p class='success'>✅ Table 'quizzes' created with correct schema!</p>";
        $steps[] = "Created quizzes table from canonical schema";

        // Also create questions and attempts tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            position INT NOT NULL,
            text TEXT NOT NULL,
            option_a VARCHAR(500) NOT NULL,
            option_b VARCHAR(500) NOT NULL,
            option_c VARCHAR(500) NOT NULL,
            option_d VARCHAR(500) NOT NULL,
            correct_option ENUM('A', 'B', 'C', 'D') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            INDEX idx_quiz_position (quiz_id, position)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            attempt_id VARCHAR(36) UNIQUE NOT NULL,
            quiz_id INT NOT NULL,
            user_id INT NULL,
            score INT NOT NULL,
            total_questions INT NOT NULL,
            duration_seconds INT NOT NULL,
            started_at DATETIME NOT NULL,
            submitted_at DATETIME NOT NULL,
            answers JSON NOT NULL,
            is_valid BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            INDEX idx_quiz (quiz_id),
            INDEX idx_user (user_id),
            INDEX idx_score (quiz_id, score DESC, duration_seconds ASC),
            INDEX idx_attempt_id (attempt_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $steps[] = "Created questions and attempts tables";
        echo "<p class='success'>✅ All AssessArena tables created!</p>";

    } else {
        echo "<p class='success'>✅ Table 'quizzes' exists. Checking columns...</p>";
    }
    echo "</div>";

    // ========================================
    // STEP 2: Analyze current table structure
    // ========================================
    if ($tableExists) {
        echo "<div class='step'>";
        echo "<h2>Step 2: Analyzing current table structure</h2>";

        $stmt = $pdo->query("DESCRIBE quizzes");
        $columns = $stmt->fetchAll();
        $columnNames = array_column($columns, 'Field');

        echo "<p>Current columns:</p>";
        echo "<table>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
        }
        echo "</table>";

        // Check for WRONG column names
        $hasQuizCode = in_array('quiz_code', $columnNames);
        $hasCreatorId = in_array('creator_id', $columnNames);
        $hasTimeLimit = in_array('time_limit', $columnNames);
        $hasRandomizeQuestions = in_array('randomize_questions', $columnNames);

        // Check for CORRECT column names
        $hasCode = in_array('code', $columnNames);
        $hasOwnerId = in_array('owner_id', $columnNames);
        $hasTimeLimitMinutes = in_array('time_limit_minutes', $columnNames);
        $hasShuffleQuestions = in_array('shuffle_questions', $columnNames);

        if ($hasQuizCode || $hasCreatorId || $hasTimeLimit || $hasRandomizeQuestions) {
            echo "<p class='error'>❌ CRITICAL: Table has WRONG column names from old schema!</p>";
            echo "<p class='warning'>Found wrong columns:</p>";
            echo "<ul>";
            if ($hasQuizCode) echo "<li class='error'>quiz_code (should be: code)</li>";
            if ($hasCreatorId) echo "<li class='error'>creator_id (should be: owner_id)</li>";
            if ($hasTimeLimit) echo "<li class='error'>time_limit (should be: time_limit_minutes)</li>";
            if ($hasRandomizeQuestions) echo "<li class='error'>randomize_questions (should be: shuffle_questions)</li>";
            echo "</ul>";

            echo "<p class='warning'>🔧 Will rename columns to match canonical schema...</p>";
            $warnings[] = "Table had wrong column names - renaming to correct names";

        } else if ($hasCode && $hasOwnerId) {
            echo "<p class='success'>✅ Table has CORRECT column names!</p>";
            $steps[] = "Verified correct column names";
        } else {
            echo "<p class='warning'>⚠️  Table exists but structure is unclear. Will fix...</p>";
        }

        echo "</div>";

        // ========================================
        // STEP 3: Fix column names if wrong
        // ========================================
        echo "<div class='step'>";
        echo "<h2>Step 3: Fixing column names</h2>";

        if ($hasQuizCode) {
            echo "<p>Renaming 'quiz_code' to 'code'...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes CHANGE quiz_code code VARCHAR(12) UNIQUE NOT NULL");
                echo "<p class='success'>✅ Renamed quiz_code → code</p>";
                $steps[] = "Renamed quiz_code to code";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to rename quiz_code: " . $e->getMessage();
            }
        }

        if ($hasCreatorId) {
            echo "<p>Renaming 'creator_id' to 'owner_id'...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes CHANGE creator_id owner_id INT NOT NULL");
                echo "<p class='success'>✅ Renamed creator_id → owner_id</p>";
                $steps[] = "Renamed creator_id to owner_id";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to rename creator_id: " . $e->getMessage();
            }
        }

        if ($hasTimeLimit) {
            echo "<p>Renaming 'time_limit' to 'time_limit_minutes'...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes CHANGE time_limit time_limit_minutes INT NULL");
                echo "<p class='success'>✅ Renamed time_limit → time_limit_minutes</p>";
                $steps[] = "Renamed time_limit to time_limit_minutes";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to rename time_limit: " . $e->getMessage();
            }
        }

        if ($hasRandomizeQuestions) {
            echo "<p>Renaming 'randomize_questions' to 'shuffle_questions'...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes CHANGE randomize_questions shuffle_questions BOOLEAN DEFAULT FALSE");
                echo "<p class='success'>✅ Renamed randomize_questions → shuffle_questions</p>";
                $steps[] = "Renamed randomize_questions to shuffle_questions";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to rename randomize_questions: " . $e->getMessage();
            }
        }

        // Refresh column names after renaming
        $stmt = $pdo->query("DESCRIBE quizzes");
        $columns = $stmt->fetchAll();
        $columnNames = array_column($columns, 'Field');

        $hasCode = in_array('code', $columnNames);
        $hasOwnerId = in_array('owner_id', $columnNames);
        $hasTimeLimitMinutes = in_array('time_limit_minutes', $columnNames);
        $hasShuffleQuestions = in_array('shuffle_questions', $columnNames);

        echo "</div>";

        // ========================================
        // STEP 4: Add missing columns if needed
        // ========================================
        echo "<div class='step'>";
        echo "<h2>Step 4: Adding any missing columns</h2>";

        if (!$hasCode) {
            echo "<p>Adding 'code' column...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes ADD COLUMN code VARCHAR(12) NULL AFTER id");
                echo "<p class='success'>✅ Added 'code' column</p>";
                $steps[] = "Added code column";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to add code: " . $e->getMessage();
            }
        }

        if (!$hasOwnerId) {
            echo "<p>Adding 'owner_id' column...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes ADD COLUMN owner_id INT NOT NULL DEFAULT 0 AFTER code");
                echo "<p class='success'>✅ Added 'owner_id' column</p>";
                $steps[] = "Added owner_id column";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to add owner_id: " . $e->getMessage();
            }
        }

        if (!in_array('title', $columnNames)) {
            echo "<p>Adding 'title' column...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes ADD COLUMN title VARCHAR(200) NOT NULL DEFAULT '' AFTER owner_id");
                echo "<p class='success'>✅ Added 'title' column</p>";
                $steps[] = "Added title column";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to add title: " . $e->getMessage();
            }
        }

        if (!$hasTimeLimitMinutes) {
            echo "<p>Adding 'time_limit_minutes' column...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes ADD COLUMN time_limit_minutes INT NULL AFTER title");
                echo "<p class='success'>✅ Added 'time_limit_minutes' column</p>";
                $steps[] = "Added time_limit_minutes column";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to add time_limit_minutes: " . $e->getMessage();
            }
        }

        if (!$hasShuffleQuestions) {
            echo "<p>Adding 'shuffle_questions' column...</p>";
            try {
                $pdo->exec("ALTER TABLE quizzes ADD COLUMN shuffle_questions BOOLEAN DEFAULT FALSE AFTER time_limit_minutes");
                echo "<p class='success'>✅ Added 'shuffle_questions' column</p>";
                $steps[] = "Added shuffle_questions column";
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
                $errors[] = "Failed to add shuffle_questions: " . $e->getMessage();
            }
        }

        echo "<p class='info'>ℹ️  Column check complete</p>";
        echo "</div>";

        // ========================================
        // STEP 5: Generate codes for existing quizzes
        // ========================================
        echo "<div class='step'>";
        echo "<h2>Step 5: Generating quiz codes</h2>";

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM quizzes WHERE code IS NULL OR code = ''");
        $result = $stmt->fetch();
        $needCodeCount = $result['count'];

        if ($needCodeCount > 0) {
            echo "<p>Found $needCodeCount quiz(zes) without codes. Generating...</p>";

            $stmt = $pdo->query("SELECT id FROM quizzes WHERE code IS NULL OR code = ''");
            $quizzesNeedingCodes = $stmt->fetchAll();

            foreach ($quizzesNeedingCodes as $quiz) {
                $newCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
                $stmt = $pdo->prepare("UPDATE quizzes SET code = ? WHERE id = ?");
                $stmt->execute([$newCode, $quiz['id']]);
            }

            echo "<p class='success'>✅ Generated codes for $needCodeCount quiz(zes)</p>";
            $steps[] = "Generated $needCodeCount quiz codes";
        } else {
            echo "<p class='success'>✅ All quizzes have codes</p>";
        }

        echo "</div>";

        // ========================================
        // STEP 6: Set constraints
        // ========================================
        echo "<div class='step'>";
        echo "<h2>Step 6: Setting column constraints</h2>";

        try {
            $pdo->exec("ALTER TABLE quizzes MODIFY code VARCHAR(12) NOT NULL");
            echo "<p class='success'>✅ Code column set to NOT NULL</p>";
            $steps[] = "Set code to NOT NULL";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "<p class='info'>ℹ️  Code column already NOT NULL</p>";
            } else {
                echo "<p class='warning'>⚠️  {$e->getMessage()}</p>";
            }
        }

        try {
            $pdo->exec("ALTER TABLE quizzes ADD UNIQUE INDEX idx_code_unique (code)");
            echo "<p class='success'>✅ Added UNIQUE index on code</p>";
            $steps[] = "Added unique index on code";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "<p class='info'>ℹ️  Unique index already exists</p>";
            } else {
                echo "<p class='warning'>⚠️  {$e->getMessage()}</p>";
            }
        }

        echo "</div>";
    }

    // ========================================
    // STEP 7: Final verification
    // ========================================
    echo "<div class='step success'>";
    echo "<h2>Step 7: Final Verification</h2>";

    $stmt = $pdo->query("DESCRIBE quizzes");
    $columns = $stmt->fetchAll();

    echo "<p><strong>Final table structure:</strong></p>";
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verify required columns exist
    $columnNames = array_column($columns, 'Field');
    $required = ['id', 'code', 'owner_id', 'title', 'time_limit_minutes', 'shuffle_questions'];
    $missing = array_diff($required, $columnNames);

    if (empty($missing)) {
        echo "<p class='success'>✅ All required columns exist!</p>";
    } else {
        echo "<p class='error'>❌ Missing columns: " . implode(', ', $missing) . "</p>";
        $errors[] = "Still missing columns: " . implode(', ', $missing);
    }

    // Count quizzes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes");
    $result = $stmt->fetch();
    echo "<p>📊 Total quizzes: <strong>{$result['total']}</strong></p>";

    // Count with codes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes WHERE code IS NOT NULL AND code != ''");
    $result = $stmt->fetch();
    echo "<p>🔑 Quizzes with codes: <strong>{$result['total']}</strong></p>";

    echo "</div>";

} catch (Exception $e) {
    echo "<div class='step error'>";
    echo "<h2>❌ Critical Error</h2>";
    echo "<p>{$e->getMessage()}</p>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
    echo "</div>";
    $errors[] = $e->getMessage();
}

// ========================================
// SUMMARY
// ========================================
echo "<div class='step'>";
if (count($errors) == 0) {
    echo "<h1 style='color: #00C851;'>🎉 Database Fixed Successfully!</h1>";
    echo "<p><strong>Operations completed:</strong></p>";
    echo "<ul>";
    foreach ($steps as $step) {
        echo "<li class='success'>✅ $step</li>";
    }
    echo "</ul>";

    if (count($warnings) > 0) {
        echo "<p><strong>Warnings:</strong></p>";
        echo "<ul>";
        foreach ($warnings as $warning) {
            echo "<li class='warning'>⚠️  $warning</li>";
        }
        echo "</ul>";
    }

    echo "<h3 class='success'>✅ AssessArena is now fully operational!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>✅ Create quizzes without SQL errors</li>";
    echo "<li>✅ Load quizzes by code</li>";
    echo "<li>✅ Set optional time limits</li>";
    echo "<li>✅ Use shuffle_questions feature</li>";
    echo "<li>✅ All CRUD operations work</li>";
    echo "</ul>";

    echo "<p style='margin-top: 30px;'>";
    echo "<a href='../assessarena.php' class='btn'>🚀 Go to AssessArena</a> ";
    echo "<a href='check_assessarena.php' class='btn'>🔍 Run Diagnostic</a>";
    echo "</p>";

} else {
    echo "<h1 style='color: #ff4444;'>⚠️  Fix Completed with Errors</h1>";
    echo "<p><strong>Errors encountered:</strong></p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li class='error'>❌ $error</li>";
    }
    echo "</ul>";

    if (count($steps) > 0) {
        echo "<p><strong>Successful steps:</strong></p>";
        echo "<ul>";
        foreach ($steps as $step) {
            echo "<li class='success'>✅ $step</li>";
        }
        echo "</ul>";
    }

    echo "<p>Please review errors above and try again, or contact support.</p>";
}
echo "</div>";

?>
</div>
</body>
</html>
