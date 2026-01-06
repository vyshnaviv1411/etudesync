<?php
// public/api/assessarena/leaderboard.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../includes/premium_check.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']);
    exit;
}

// CRITICAL: Require premium access
requirePremiumAPI();

require_once __DIR__ . '/../db.php';

$quiz_code = strtoupper(trim($_GET['quiz_code'] ?? ''));

try {
    if ($quiz_code) {
        // Leaderboard for specific quiz
        $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE code = :code");
        $stmt->execute([':code' => $quiz_code]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            echo json_encode(['ok' => false, 'msg' => 'Quiz not found']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT
                u.user_name,
                MAX(a.score) as best_score,
                a.total_questions,
                MIN(a.duration_seconds) as fastest_time,
                COUNT(a.id) as attempts_count
            FROM attempts a
            JOIN users u ON a.user_id = u.user_id
            WHERE a.quiz_id = :quiz_id
            GROUP BY a.user_id
            ORDER BY best_score DESC, fastest_time ASC
            LIMIT 50
        ");
        $stmt->execute([':quiz_id' => $quiz['id']]);
        $leaderboard = $stmt->fetchAll();

        // Add percentage
        foreach ($leaderboard as &$entry) {
            $entry['percentage'] = round(($entry['best_score'] / $entry['total_questions']) * 100, 2);
            $entry['fastest_time_formatted'] = gmdate('i:s', $entry['fastest_time']);
        }

        echo json_encode([
            'ok' => true,
            'quiz_code' => $quiz_code,
            'leaderboard' => $leaderboard
        ]);
    } else {
        // Global stats
        $user_id = (int)$_SESSION['user_id'];

        // User's best quizzes
        $stmt = $pdo->prepare("
            SELECT
                q.title,
                q.code,
                MAX(a.score) as best_score,
                a.total_questions,
                COUNT(a.id) as attempts_count
            FROM attempts a
            JOIN quizzes q ON a.quiz_id = q.id
            WHERE a.user_id = :user_id
            GROUP BY a.quiz_id
            ORDER BY best_score DESC
            LIMIT 10
        ");
        $stmt->execute([':user_id' => $user_id]);
        $user_stats = $stmt->fetchAll();

        foreach ($user_stats as &$stat) {
            $stat['percentage'] = round(($stat['best_score'] / $stat['total_questions']) * 100, 2);
        }

        echo json_encode([
            'ok' => true,
            'user_stats' => $user_stats
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
