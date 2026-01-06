<?php
/**
 * MindPlay - Games API: Get Statistics
 *
 * Retrieves game statistics for the current user.
 * Can filter by specific game type or return all games.
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

// =====================================================
// 3. FETCH STATISTICS
// =====================================================
try {
    if ($game_type) {
        // Get stats for specific game
        $stmt = $pdo->prepare(
            "SELECT * FROM game_statistics
             WHERE user_id = :user_id AND game_type = :game_type"
        );
        $stmt->execute([
            ':user_id' => $user_id,
            ':game_type' => $game_type
        ]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stats) {
            $stats['additional_stats'] = json_decode($stats['additional_stats'] ?? '{}', true);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Statistics retrieved successfully',
            'data' => [
                'game_type' => $game_type,
                'stats' => $stats
            ]
        ]);
    } else {
        // Get stats for all games
        $stmt = $pdo->prepare(
            "SELECT * FROM game_statistics
             WHERE user_id = :user_id
             ORDER BY last_played DESC"
        );
        $stmt->execute([':user_id' => $user_id]);
        $allStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parse JSON fields
        foreach ($allStats as &$stat) {
            $stat['additional_stats'] = json_decode($stat['additional_stats'] ?? '{}', true);
        }
        unset($stat);

        echo json_encode([
            'success' => true,
            'message' => 'Statistics retrieved successfully',
            'data' => [
                'stats' => $allStats,
                'count' => count($allStats)
            ]
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
