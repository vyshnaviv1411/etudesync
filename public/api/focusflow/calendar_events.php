<?php
/**
 * Calendar Events API
 * Fetches todos with due dates for calendar display
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get month and year from query params
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Calculate date range for the month
$start_date = sprintf('%04d-%02d-01', $year, $month);
$last_day = date('t', strtotime($start_date));
$end_date = sprintf('%04d-%02d-%02d', $year, $month, $last_day);

try {
    // Fetch todos with due dates in the specified month
    $stmt = $pdo->prepare(
        "SELECT id, title, due_date, priority, status
         FROM todos
         WHERE user_id = :user_id
           AND due_date BETWEEN :start_date AND :end_date
         ORDER BY due_date ASC, priority DESC"
    );

    $stmt->execute([
        ':user_id' => $user_id,
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);

    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group events by date
    $events_by_date = [];
    foreach ($todos as $todo) {
        $date = $todo['due_date'];
        if (!isset($events_by_date[$date])) {
            $events_by_date[$date] = [];
        }

        $events_by_date[$date][] = [
            'id' => $todo['id'],
            'title' => $todo['title'],
            'priority' => $todo['priority'],
            'status' => $todo['status'],
            'type' => 'todo'
        ];
    }

    echo json_encode([
        'success' => true,
        'events' => $events_by_date,
        'month' => $month,
        'year' => $year
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
