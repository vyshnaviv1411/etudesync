<?php
/**
 * Premium Payment System - Setup Verification
 * public/premium_setup_check.php
 * 
 * Run this to verify all premium payment components are properly installed.
 * Delete after verification.
 */

session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/premium_check.php';

header('Content-Type: text/html; charset=utf-8');

$checks = [];
$allPassed = true;

// Check 1: Database tables exist
try {
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $tables = ['subscription_plans', 'user_subscriptions', 'payment_orders'];
    $tablesMissing = [];
    
    foreach ($tables as $table) {
        $stmt->execute([$table]);
        if (!$stmt->fetch()) {
            $tablesMissing[] = $table;
        }
    }
    
    if (empty($tablesMissing)) {
        $checks[] = ['✓', 'Database Tables', 'All subscription tables exist'];
    } else {
        $checks[] = ['✗', 'Database Tables', 'Missing: ' . implode(', ', $tablesMissing)];
        $allPassed = false;
    }
} catch (Exception $e) {
    $checks[] = ['✗', 'Database Tables', $e->getMessage()];
    $allPassed = false;
}

// Check 2: Subscription plans exist
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM subscription_plans WHERE is_active = 1');
    $stmt->execute();
    $result = $stmt->fetch();
    $count = (int) $result['count'];
    
    if ($count > 0) {
        $checks[] = ['✓', 'Subscription Plans', "$count active plan(s) found"];
    } else {
        $checks[] = ['✗', 'Subscription Plans', 'No active plans found'];
        $allPassed = false;
    }
} catch (Exception $e) {
    $checks[] = ['✗', 'Subscription Plans', $e->getMessage()];
    $allPassed = false;
}

// Check 3: API endpoints exist
$apiFiles = [
    'api/premium/process_upgrade.php',
    'api/premium/get_plans.php'
];


foreach ($apiFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $checks[] = ['✓', "API: " . basename($file), 'File exists'];
    } else {
        $checks[] = ['✗', "API: " . basename($file), 'File missing'];
        $allPassed = false;
    }
}

// Check 4: CSS & JS files exist
$assetFiles = [
    'assets/css/premium.css',
    'assets/js/premium.js'
];

foreach ($assetFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $fileSize = filesize($path);
        $checks[] = ['✓', "Assets: " . basename($file), "File exists ({$fileSize} bytes)"];
    } else {
        $checks[] = ['✗', "Assets: " . basename($file), 'File missing'];
        $allPassed = false;
    }
}

// Check 5: Helper functions
$helperFuncs = ['isPremiumUser', 'getUserSubscription', 'getAvailablePlans', 'requirePremium', 'getPremiumFeatures'];
foreach ($helperFuncs as $func) {
    if (function_exists($func)) {
        $checks[] = ['✓', "Helper: $func()", 'Function available'];
    } else {
        $checks[] = ['✗', "Helper: $func()", 'Function not found'];
        $allPassed = false;
    }
}

// Check 6: Test premium check functions
// Check 6: Test premium check functions
try {
    if (isset($_SESSION['user_id'])) {
        isPremiumUser($_SESSION['user_id']);
        getUserSubscription($_SESSION['user_id']);
        getAvailablePlans();
        $checks[] = ['✓', 'Premium Functions', 'All functions execute without errors'];
    } else {
        $checks[] = ['⚠', 'Premium Functions', 'Not logged in - skipping execution test'];
    }
} catch (Throwable $e) {   // 🔥 IMPORTANT CHANGE
    $checks[] = ['✗', 'Premium Functions', $e->getMessage()];
    $allPassed = false;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Payment System - Setup Verification</title>
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            margin: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2D5BFF 0%, #7C4DFF 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        
        .header p {
            margin: 8px 0 0;
            opacity: 0.95;
            font-size: 14px;
        }
        
        .status {
            padding: 40px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 24px;
            font-size: 16px;
        }
        
        .status-badge.pass {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .status-badge.fail {
            background: #FEE2E2;
            color: #7F1D1D;
        }
        
        .checks {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .check-item {
            padding: 16px;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
        
        .check-icon {
            font-size: 20px;
            min-width: 24px;
            text-align: center;
        }
        
        .check-content {
            flex: 1;
        }
        
        .check-title {
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 4px;
        }
        
        .check-desc {
            font-size: 13px;
            color: #6B7280;
        }
        
        .footer {
            padding: 24px 40px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            font-size: 13px;
            color: #6B7280;
        }
        
        .footer a {
            color: #2D5BFF;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👑 Premium Payment System</h1>
            <p>Setup Verification Report</p>
        </div>
        
        <div class="status">
            <div class="status-badge <?= $allPassed ? 'pass' : 'fail' ?>">
                <?= $allPassed ? '✓ All Checks Passed' : '✗ Some Checks Failed' ?>
            </div>
            
            <ul class="checks">
                <?php foreach ($checks as $check): ?>
                    <li class="check-item">
                        <div class="check-icon"><?= htmlspecialchars($check[0]) ?></div>
                        <div class="check-content">
                            <div class="check-title"><?= htmlspecialchars($check[1]) ?></div>
                            <div class="check-desc"><?= htmlspecialchars($check[2]) ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="footer">
            <strong>Next Steps:</strong><br>
            1. Go to <a href="dashboard.php">Dashboard</a><br>
            2. Click a locked premium card (AccessArena or InfoVault)<br>
            3. Fill dummy payment form and click "Pay Now"<br>
            4. Verify success state appears<br>
            5. Delete this verification file when done
        </div>
    </div>
</body>
</html>
