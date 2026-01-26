<?php
require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/**
 * Check if a user has premium access
 */
function isPremiumUser($user_id) {
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT is_premium FROM users WHERE id = ? LIMIT 1"
    );
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user && (int)$user['is_premium'] === 1;
}

/**
 * Get user's active subscription
 */
function getUserSubscription($user_id) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT us.*, sp.name AS plan_name
        FROM user_subscriptions us
        JOIN subscription_plans sp ON us.plan_id = sp.id
        WHERE us.user_id = ?
          AND us.status = 'active'
          AND (us.end_date IS NULL OR us.end_date > NOW())
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get available plans
 */
function getAvailablePlans() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT *
        FROM subscription_plans
        WHERE is_active = 1
        ORDER BY price ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Page protection
 */
function requirePremium() {
    if (!isset($_SESSION['user_id']) || !isPremiumUser($_SESSION['user_id'])) {
        $_SESSION['premium_message'] = 'This is a premium feature.';
        header('Location: premium_access.php');
        exit;
    }
}

/**
 * API protection
 */
function requirePremiumAPI() {
    if (!isset($_SESSION['user_id']) || !isPremiumUser($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'premium_required' => true,
            'error' => 'Premium access required'
        ]);
        exit;
    }
}
