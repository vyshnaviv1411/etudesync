<?php
/**
 * Premium Access Middleware
 * includes/premium_check.php
 *
 * Provides functions to check and manage premium access.
 * Include in any page/API that requires premium features.
 */

require_once __DIR__ . '/db.php';

/**
 * PREMIUM WHITELIST - Only these emails have premium access
 * Centralized premium user management
 */
define('PREMIUM_WHITELIST', [
    'siddhitripathi11.16@gmail.com'
]);

/**
 * Check if an email has premium access
 * @param string $email
 * @return bool
 */
function isEmailPremium($email) {
    return in_array(strtolower(trim($email)), array_map('strtolower', PREMIUM_WHITELIST));
}

/**
 * Check if a user has active premium access (by user ID)
 * @param int $user_id
 * @return bool
 */
function isPremiumUser($user_id) {
    global $pdo;

    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    return isEmailPremium($user['email']);
}

/**
 * Get user's active subscription details
 * @param int $user_id
 * @return array|null
 */
function getUserSubscription($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT us.id, us.plan_id, us.status, us.start_date, us.end_date, us.renewal_date,
               sp.name as plan_name, sp.price, sp.billing_cycle
        FROM user_subscriptions us
        JOIN subscription_plans sp ON us.plan_id = sp.id
        WHERE us.user_id = ? AND us.status = "active" AND us.end_date > NOW()
        LIMIT 1
    ');
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Get available subscription plans
 * @return array
 */
function getAvailablePlans() {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT id, name, description, price, billing_cycle, features 
        FROM subscription_plans 
        WHERE is_active = 1
        ORDER BY price ASC
    ');
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Require premium access (protect page)
 * Redirects to premium access page if not premium
 */
function requirePremium() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    if (!isPremiumUser($_SESSION['user_id'])) {
        $_SESSION['premium_message'] = 'This is a premium feature. Please upgrade to access.';
        header('Location: premium_access.php');
        exit;
    }
}

/**
 * Require premium access for API endpoints
 * Returns JSON error if not premium
 */
function requirePremiumAPI() {
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => 'Not logged in', 'premium_required' => true]);
        exit;
    }

    if (!isPremiumUser($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => 'This feature requires premium access', 'premium_required' => true]);
        exit;
    }
}

/**
 * Get premium-only features
 * @return array
 */
function getPremiumFeatures() {
    return [
        'quizforge' => [
            'name' => 'QuizForge',
            'description' => 'Create and attempt unlimited quizzes with advanced analytics',
            'icon' => 'icon-quizforge.png',
            'link' => 'quizforge.php'
        ],
        'infovault' => [
            'name' => 'InfoVault',
            'description' => 'Premium note storage and intelligent organization',
            'icon' => 'icon-infovault.png',
            'link' => 'infovault.php'
        ]
    ];
}
