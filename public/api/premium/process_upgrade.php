<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/premium_check.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    if (isPremiumUser($user_id)) {
        echo json_encode(['success' => false, 'error' => 'Already premium']);
        exit;
    }

    // Get Pro Plan
    $stmt = $pdo->prepare("SELECT id, price FROM subscription_plans WHERE name = 'Pro Plan' LIMIT 1");
    $stmt->execute();
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        throw new Exception('Premium plan not found');
    }

    // Create subscription
    $stmt = $pdo->prepare("
        INSERT INTO user_subscriptions 
        (user_id, plan_id, status, start_date, end_date)
        VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
    ");
    $stmt->execute([$user_id, $plan['id']]);
    $subscription_id = $pdo->lastInsertId();

    // Create payment record
    $stmt = $pdo->prepare("
        INSERT INTO payment_orders 
        (user_id, subscription_id, amount, currency, order_id, payment_id, status)
        VALUES (?, ?, ?, 'INR', ?, ?, 'success')
    ");
    $stmt->execute([
        $user_id,
        $subscription_id,
        $plan['price'],
        'ORD-' . uniqid(),
        'PAY-' . uniqid()
    ]);

    // Mark user premium
    $stmt = $pdo->prepare("UPDATE users SET is_premium = 1 WHERE id = ?");
    $stmt->execute([$user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Premium activated successfully',
        'redirect' => 'dashboard.php'
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
