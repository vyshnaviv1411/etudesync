<?php
/**
 * MindPlay - Mood Tracker API: Get Mood
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/db.php';

// AUTH CHECK
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// PARAMETERS
$mood_date  = $_GET['mood_date'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date   = $_GET['end_date'] ?? null;
$limit      = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

try {
    $sql = "SELECT id, mood_date, mood_value, created_at
            FROM mood_tracker
            WHERE user_id = :user_id";
    $params = [':user_id' => $user_id];

    if ($mood_date) {
        $sql .= " AND mood_date = :mood_date";
        $params[':mood_date'] = $mood_date;
    } elseif ($start_date && $end_date) {
        $sql .= " AND mood_date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date']   = $end_date;
    }

    $sql .= " ORDER BY mood_date DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $moods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $today = date('Y-m-d');
    $todayMoodSet = false;
    $todayMoodValue = null;

    foreach ($moods as $mood) {
        if ($mood['mood_date'] === $today) {
            $todayMoodSet = true;
            $todayMoodValue = $mood['mood_value'];
            break;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'moods' => $moods,
            'today_mood_set' => $todayMoodSet,
            'today_mood_value' => $todayMoodValue,
            'today_date' => $today
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
