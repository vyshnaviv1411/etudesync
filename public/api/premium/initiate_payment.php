<?php
/**
 * API: Dummy Payment Gateway
 * public/api/premium/initiate_payment.php
 * 
 * Initiates a dummy payment for premium subscription.
 * This endpoint creates a payment order and returns mock payment details.
 */

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $plan_name = $data['plan'] ?? 'Pro Plan';

    // Get plan details
    $stmt = $pdo->prepare('SELECT id, price FROM subscription_plans WHERE name = ? AND is_active = 1');
    $stmt->execute([$plan_name]);
    $plan = $stmt->fetch();

    if (!$plan) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Plan not found']);
        exit;
    }

    // Check if user already has active premium
    $stmt = $pdo->prepare('
        SELECT id FROM user_subscriptions 
        WHERE user_id = ? AND status = "active" 
        LIMIT 1
    ');
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'You already have an active premium subscription']);
        exit;
    }

    // Generate dummy payment IDs
    $order_id = 'ORD-' . uniqid() . '-' . time();
    $payment_id = 'PAY-' . uniqid() . '-' . time();

    // Create subscription record
    $stmt = $pdo->prepare('
        INSERT INTO user_subscriptions (user_id, plan_id, status, start_date, end_date)
        VALUES (?, ?, "active", NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
    ');
    $stmt->execute([$user_id, $plan['id']]);
    $subscription_id = $pdo->lastInsertId();

    // Create payment order
    $stmt = $pdo->prepare('
        INSERT INTO payment_orders 
        (user_id, subscription_id, amount, order_id, payment_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, "pending", NOW())
    ');
    $stmt->execute([$user_id, $subscription_id, $plan['price'], $order_id, $payment_id]);
    $order_record = $pdo->lastInsertId();

    // Return payment details for frontend simulation
    echo json_encode([
        'success' => true,
        'payment' => [
            'order_id' => $order_id,
            'payment_id' => $payment_id,
            'amount' => (float) $plan['price'],
            'plan' => $plan_name,
            'currency' => 'USD',
            'subscription_id' => $subscription_id
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    error_log('Payment initiation error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    error_log('Payment error: ' . $e->getMessage());
}
