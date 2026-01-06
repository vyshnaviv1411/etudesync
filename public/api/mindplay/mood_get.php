<?php
/**
 * MindPlay - Mood Tracker API: Get Mood
 *
 * Retrieves mood entries for the current user.
 * Supports:
 * - Single date lookup (mood_date parameter)
 * - Date range lookup (start_date and end_date parameters)
 * - All moods (no parameters - returns all user moods)
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/db.php';

// =====================================================
// 1. AUTHENTICATION CHECK
// =====================================================
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// =====================================================
// 2. PARSE QUERY PARAMETERS
// =====================================================
$mood_date = $_GET['mood_date'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

// =====================================================
// 3. BUILD QUERY BASED ON PARAMETERS
// =====================================================
try {
    $sql = "SELECT id, mood_date, mood_value, created_at
            FROM mood_tracker
            WHERE user_id = :user_id";
    $params = [':user_id' => $user_id];

    // Single date lookup
    if ($mood_date) {
        $sql .= " AND mood_date = :mood_date";
        $params[':mood_date'] = $mood_date;
    }
    // Date range lookup
    elseif ($start_date && $end_date) {
        $sql .= " AND mood_date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    $sql .= " ORDER BY mood_date DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    // Bind limit as integer
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $moods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // 4. CHECK IF TODAY'S MOOD IS SET
    // =====================================================
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
        'message' => 'Moods retrieved successfully',
        'data' => [
            'moods' => $moods,
            'count' => count($moods),
            'today_mood_set' => $todayMoodSet,
            'today_mood_value' => $todayMoodValue,
            'today_date' => $today
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
