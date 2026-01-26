<?php
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT id, name, description, price, billing_cycle, features
        FROM subscription_plans
        WHERE is_active = 1
        ORDER BY price ASC
    ");

    echo json_encode([
        'success' => true,
        'plans' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
