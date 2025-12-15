<?php
session_start();
require_once __DIR__ . '/../../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Sessions today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, COALESCE(SUM(duration_minutes), 0) as minutes
        FROM pomodoro_sessions
        WHERE user_id = :user_id
        AND DATE(completed_at) = CURDATE()
        AND completed = 1
    ");
    $stmt->execute([':user_id' => $user_id]);
    $today = $stmt->fetch();

    // Calculate streak (consecutive days with at least 1 session)
    $stmt = $pdo->prepare("
        SELECT DATE(completed_at) as session_date
        FROM pomodoro_sessions
        WHERE user_id = :user_id AND completed = 1
        GROUP BY DATE(completed_at)
        ORDER BY session_date DESC
        LIMIT 30
    ");
    $stmt->execute([':user_id' => $user_id]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $streak = 0;
    $currentDate = date('Y-m-d');

    foreach ($dates as $date) {
        if ($date === $currentDate) {
            $streak++;
            $currentDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
        } else {
            break;
        }
    }

    // Last 7 days data for chart
    $stmt = $pdo->prepare("
        SELECT DATE(completed_at) as date, COUNT(*) as sessions
        FROM pomodoro_sessions
        WHERE user_id = :user_id
        AND completed = 1
        AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(completed_at)
        ORDER BY date ASC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $weekData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total sessions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM pomodoro_sessions
        WHERE user_id = :user_id AND completed = 1
    ");
    $stmt->execute([':user_id' => $user_id]);
    $total = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'sessions_today' => (int)$today['count'],
        'minutes_today' => (int)$today['minutes'],
        'streak' => $streak,
        'total_sessions' => (int)$total['total'],
        'week_data' => $weekData
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
