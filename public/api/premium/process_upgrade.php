<?php
/**
 * Process Premium Upgrade
 * public/api/premium/process_upgrade.php
 * 
 * Handles the full payment flow:
 * 1. Initiate payment (create subscription record)
 * 2. Confirm payment (mark user as premium)
 */

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json');

// Verify user is logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/premium_check.php';

$user_id = $_SESSION['user_id'];
$card_name = $_POST['cardName'] ?? '';
$card_number = $_POST['cardNumber'] ?? '';
$card_expiry = $_POST['cardExpiry'] ?? '';
$card_cvv = $_POST['cardCVV'] ?? '';

// Basic validation
if (empty($card_name) || empty($card_number) || empty($card_expiry) || empty($card_cvv)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing payment information']);
    exit;
}

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Check if user already premium
    if (isPremiumUser($user_id)) {
        echo json_encode(['success' => false, 'error' => 'User is already premium']);
        exit;
    }

    // Step 1: Get the Pro Plan ID
    $stmt = $db->prepare("SELECT id FROM subscription_plans WHERE name = 'Pro Plan' LIMIT 1");
    $stmt->execute();
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    $plan_id = $plan['id'] ?? 1;

    // Step 2: Create payment order
    $order_id = 'ORD-' . strtoupper(bin2hex(random_bytes(8)));
    $payment_id = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));

    $stmt = $db->prepare("
        INSERT INTO payment_orders (
            user_id, amount, currency, order_id, payment_id,
            status, payment_method, created_at
        ) VALUES (?, '399', 'INR', ?, ?, 'pending', 'dummy_card', NOW())
    ");
    $stmt->execute([
        $user_id,
        $order_id,
        $payment_id
    ]);

    // Step 3: Create user subscription (activate subscription for 1 month)
    $stmt = $db->prepare("
        INSERT INTO user_subscriptions (
            user_id, plan_id, status, start_date, end_date, renewal_date, created_at
        ) VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), DATE_ADD(NOW(), INTERVAL 1 MONTH), NOW())
    ");
    $stmt->execute([$user_id, $plan_id]);
    
    $subscription_id = $db->lastInsertId();

    // Step 4: Update payment order with subscription ID and mark complete
    $stmt = $db->prepare("
        UPDATE payment_orders 
        SET subscription_id = ?, status = 'success', completed_at = NOW() 
        WHERE payment_id = ?
    ");
    $stmt->execute([$subscription_id, $payment_id]);

    // Step 5: Update user premium status
    $stmt = $db->prepare("UPDATE users SET is_premium = 1 WHERE id = ?");
    $stmt->execute([$user_id]);

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Premium activated successfully',
        'subscription_id' => $subscription_id,
        'order_id' => $order_id,
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
