<?php
/**
 * API: Get Available Plans
 * public/api/premium/get_plans.php
 * 
 * Returns all available subscription plans
 */

require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->prepare('
        SELECT id, name, description, price, billing_cycle, features 
        FROM subscription_plans 
        WHERE is_active = 1
        ORDER BY price ASC
    ');
    $stmt->execute();
    $plans = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'plans' => $plans
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    error_log('Get plans error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    error_log('Server error: ' . $e->getMessage());
}
