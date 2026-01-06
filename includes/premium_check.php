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
 * Check if a user has active premium subscription
 * @param int $user_id
 * @return bool
 */
function isPremiumUser($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as count FROM user_subscriptions 
        WHERE user_id = ? AND status = "active" AND end_date > NOW()
        LIMIT 1
    ');
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    return (int) $result['count'] > 0;
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
 * Redirects to dashboard if not premium
 */
function requirePremium() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    if (!isPremiumUser($_SESSION['user_id'])) {
        $_SESSION['error'] = 'This feature requires premium access. Please upgrade.';
        header('Location: dashboard.php');
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
