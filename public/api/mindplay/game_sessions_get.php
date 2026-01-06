<?php
/**
 * MindPlay - Games API: Get Session History
 *
 * Retrieves game session history for the current user.
 * Supports filtering by game type, date range, and limit.
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
$game_type = $_GET['game_type'] ?? null;
$session_date = $_GET['session_date'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

// =====================================================
// 3. BUILD QUERY
// =====================================================
try {
    $sql = "SELECT id, game_type, session_date, session_time, score, duration, metadata
            FROM game_sessions
            WHERE user_id = :user_id";
    $params = [':user_id' => $user_id];

    // Filter by game type
    if ($game_type) {
        $sql .= " AND game_type = :game_type";
        $params[':game_type'] = $game_type;
    }

    // Filter by specific date
    if ($session_date) {
        $sql .= " AND session_date = :session_date";
        $params[':session_date'] = $session_date;
    }
    // Filter by date range
    elseif ($start_date && $end_date) {
        $sql .= " AND session_date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    $sql .= " ORDER BY session_time DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Parse JSON metadata
    foreach ($sessions as &$session) {
        $session['metadata'] = json_decode($session['metadata'] ?? '{}', true);
    }
    unset($session);

    // =====================================================
    // 4. CALCULATE TODAY'S PLAY COUNT
    // =====================================================
    $today = date('Y-m-d');
    $todayCount = 0;

    foreach ($sessions as $session) {
        if ($session['session_date'] === $today) {
            $todayCount++;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Game sessions retrieved successfully',
        'data' => [
            'sessions' => $sessions,
            'count' => count($sessions),
            'today_count' => $todayCount,
            'today_date' => $today
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
