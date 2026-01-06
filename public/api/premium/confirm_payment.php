<?php
/**
 * API: Confirm Dummy Payment
 * public/api/premium/confirm_payment.php
 * 
 * Simulates payment success and activates premium access.
 * In production, this would verify with actual payment gateway.
 */

session_start();
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

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
    $order_id = $data['order_id'] ?? null;
    $payment_id = $data['payment_id'] ?? null;

    if (!$order_id || !$payment_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing payment details']);
        exit;
    }

    // Verify payment order exists and belongs to user
    $stmt = $pdo->prepare('
        SELECT id, subscription_id, status FROM payment_orders 
        WHERE user_id = ? AND order_id = ? AND payment_id = ?
        LIMIT 1
    ');
    $stmt->execute([$user_id, $order_id, $payment_id]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment order not found']);
        exit;
    }

    if ($order['status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Payment already processed']);
        exit;
    }

    // Update payment status to success
    $stmt = $pdo->prepare('
        UPDATE payment_orders 
        SET status = "success", completed_at = NOW()
        WHERE id = ?
    ');
    $stmt->execute([$order['id']]);

    // Verify subscription was activated
    $stmt = $pdo->prepare('
        SELECT id, status FROM user_subscriptions WHERE id = ?
    ');
    $stmt->execute([$order['subscription_id']]);
    $subscription = $stmt->fetch();

    if (!$subscription || $subscription['status'] !== 'active') {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to activate subscription']);
        exit;
    }

    // Update user's is_premium flag
    $stmt = $pdo->prepare('UPDATE users SET is_premium = 1 WHERE id = ?');
    $stmt->execute([$user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Payment successful! Premium activated.',
        'subscription_id' => $order['subscription_id'],
        'order_id' => $order_id,
        'payment_id' => $payment_id
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    error_log('Payment confirmation error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    error_log('Confirmation error: ' . $e->getMessage());
}
