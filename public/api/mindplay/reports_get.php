<?php
/**
 * MindPlay - Reports API: Get Insights
 *
 * Aggregates data from all MindPlay features to provide insights:
 * - Mood trends over time
 * - Journal consistency (streak, total entries)
 * - Game-wise insights (best scores, play frequency)
 * - Overall well-being score
 *
 * Supports date range filtering for time-based reports.
 */

session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');


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
// Default: last 30 days
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime("-{$days} days"));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// =====================================================
// 3. AGGREGATE DATA FROM ALL FEATURES
// =====================================================
try {
    // =====================================================
    // 3A. MOOD TRENDS
    // =====================================================
    $moodStmt = $pdo->prepare(
        "SELECT mood_date, mood_value
         FROM mood_tracker
         WHERE user_id = :user_id
           AND mood_date BETWEEN :start_date AND :end_date
         ORDER BY mood_date ASC"
    );
    $moodStmt->execute([
        ':user_id' => $user_id,
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    $moodData = $moodStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate mood distribution
    $moodDistribution = [];
    $moodTimeline = [];
    foreach ($moodData as $mood) {
        $moodDistribution[$mood['mood_value']] = ($moodDistribution[$mood['mood_value']] ?? 0) + 1;
        $moodTimeline[] = [
            'date' => $mood['mood_date'],
            'mood' => $mood['mood_value']
        ];
    }
// =====================================================
// 3B. JOURNAL CONSISTENCY (FINAL – NO STREAK)
// =====================================================
date_default_timezone_set('Asia/Kolkata');

// Count how many UNIQUE days the user has written a journal
$journalStmt = $pdo->prepare(
    "SELECT COUNT(DISTINCT entry_date) AS total_days
     FROM journal_entries
     WHERE user_id = :user_id
       AND is_submitted = 1
       AND entry_date <= CURDATE()"
);

$journalStmt->execute([
    ':user_id' => $user_id
]);

$journalResult = $journalStmt->fetch(PDO::FETCH_ASSOC);

// ✅ This is the correct value you want
$totalJournalDays = (int) ($journalResult['total_days'] ?? 0);


    // =====================================================
    // 3C. GAME INSIGHTS
    // =====================================================
    $gameStatsStmt = $pdo->prepare(
        "SELECT * FROM game_statistics
         WHERE user_id = :user_id
         ORDER BY total_plays DESC"
    );
    $gameStatsStmt->execute([':user_id' => $user_id]);
    $gameStats = $gameStatsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Parse JSON and format game insights
    $gameInsights = [];
    $totalGamesPlayed = 0;
    foreach ($gameStats as $stat) {
        $additional = json_decode($stat['additional_stats'] ?? '{}', true);

        $gameInsights[] = [
            'game_type' => $stat['game_type'],
            'total_plays' => $stat['total_plays'],
            'best_score' => $stat['best_score'],
            'total_wins' => $stat['total_wins'],
            'total_losses' => $stat['total_losses'],
            'total_draws' => $stat['total_draws'],
            'best_streak' => $stat['best_streak'],
            'last_played' => $stat['last_played'],
            'additional_stats' => $additional
        ];

        $totalGamesPlayed += $stat['total_plays'];
    }

    // Get recent game sessions (last 7 days) for activity chart
    $recentSessionsStmt = $pdo->prepare(
        "SELECT session_date, game_type, COUNT(*) as count
         FROM game_sessions
         WHERE user_id = :user_id
           AND session_date BETWEEN :start_date AND :end_date
         GROUP BY session_date, game_type
         ORDER BY session_date ASC"
    );
    $recentSessionsStmt->execute([
        ':user_id' => $user_id,
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    $recentGameActivity = $recentSessionsStmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // 3D. OVERALL WELL-BEING SCORE (0-100)
    // =====================================================
    // Simple algorithm:
    // - Mood consistency: 30 points (based on entries in range)
    // - Journal consistency: 30 points (based on streak)
    // - Game activity: 40 points (based on regular play)
// =====================================================
// 3D. OVERALL WELL-BEING SCORE (FIXED)
// =====================================================

// Mood score (max 30)
$moodScore = min(30, (count($moodData) / $days) * 30);

// Journal score based on DAYS WRITTEN (max 30)
$journalScore = min(30, ($totalJournalDays / $days) * 30);

// Game score (max 40)
$gameScore = min(40, ($totalGamesPlayed / ($days * 2)) * 40);

$wellBeingScore = round($moodScore + $journalScore + $gameScore);


    // =====================================================
    // 4. RETURN COMPREHENSIVE REPORT
    // =====================================================
    echo json_encode([
        'success' => true,
        'message' => 'Reports generated successfully',
        'data' => [
            'date_range' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'days' => $days
            ],
            'mood_insights' => [
                'total_entries' => count($moodData),
                'mood_distribution' => $moodDistribution,
                'mood_timeline' => $moodTimeline
            ],
          'journal_insights' => [
                 'days_written' => $totalJournalDays
            ],

            'game_insights' => [
                'total_games_played' => $totalGamesPlayed,
                'games' => $gameInsights,
                'recent_activity' => $recentGameActivity
            ],
            'well_being_score' => [
                'overall_score' => $wellBeingScore,
                'mood_score' => round($moodScore),
                'journal_score' => round($journalScore),
                'game_score' => round($gameScore)
            ]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
