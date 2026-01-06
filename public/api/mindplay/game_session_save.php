<?php
/**
 * MindPlay - Games API: Save Game Session
 *
 * Saves a game session and updates user statistics.
 * Supports all 5 games:
 * - sudoku: Completion time, difficulty
 * - xo: Win/loss/draw tracking
 * - memory_match: Attempts, completion time
 * - quick_math: Correct answers, accuracy
 * - word_unscramble: Words solved, time taken
 *
 * BUSINESS RULES:
 * - Games can be played unlimited times per day
 * - Each session is recorded individually
 * - Statistics are automatically updated after each session
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
// 2. INPUT VALIDATION
// =====================================================
$input = json_decode(file_get_contents('php://input'), true);

$game_type = trim($input['game_type'] ?? '');
$score = isset($input['score']) ? (int)$input['score'] : 0;
$duration = isset($input['duration']) ? (int)$input['duration'] : 0;
$metadata = $input['metadata'] ?? [];
$session_date = $input['session_date'] ?? date('Y-m-d');

// Validate game type
$valid_games = ['sudoku', 'xo', 'memory_match', 'quick_math', 'word_unscramble'];
if (!in_array($game_type, $valid_games)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid game type. Allowed: ' . implode(', ', $valid_games)
    ]);
    exit;
}

// =====================================================
// 3. SAVE GAME SESSION
// =====================================================
try {
    // Start transaction for atomic update
    $pdo->beginTransaction();

    // Insert game session
    $sessionStmt = $pdo->prepare(
        "INSERT INTO game_sessions (user_id, game_type, session_date, score, duration, metadata)
         VALUES (:user_id, :game_type, :session_date, :score, :duration, :metadata)"
    );

    $sessionStmt->execute([
        ':user_id' => $user_id,
        ':game_type' => $game_type,
        ':session_date' => $session_date,
        ':score' => $score,
        ':duration' => $duration,
        ':metadata' => json_encode($metadata)
    ]);

    $session_id = $pdo->lastInsertId();

    // =====================================================
    // 4. UPDATE GAME STATISTICS
    // =====================================================

    // Check if stats record exists
    $statsCheckStmt = $pdo->prepare(
        "SELECT * FROM game_statistics
         WHERE user_id = :user_id AND game_type = :game_type"
    );
    $statsCheckStmt->execute([
        ':user_id' => $user_id,
        ':game_type' => $game_type
    ]);
    $existingStats = $statsCheckStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingStats) {
        // Update existing stats
        updateGameStatistics($pdo, $user_id, $game_type, $score, $duration, $metadata, $existingStats);
    } else {
        // Create new stats record
        createGameStatistics($pdo, $user_id, $game_type, $score, $duration, $metadata);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Game session saved successfully',
        'data' => [
            'session_id' => $session_id,
            'game_type' => $game_type,
            'score' => $score,
            'duration' => $duration
        ]
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Update existing game statistics
 */
function updateGameStatistics($pdo, $user_id, $game_type, $score, $duration, $metadata, $existing) {
    $total_plays = $existing['total_plays'] + 1;
    $best_score = $existing['best_score'];
    $total_wins = $existing['total_wins'];
    $total_losses = $existing['total_losses'];
    $total_draws = $existing['total_draws'];
    $current_streak = $existing['current_streak'];
    $best_streak = $existing['best_streak'];
    $additional_stats = json_decode($existing['additional_stats'] ?? '{}', true);

    // Game-specific logic
    switch ($game_type) {
        case 'sudoku':
            // Best score = fastest completion time (lower is better, 0 = incomplete)
            if ($metadata['completed'] ?? false) {
                if ($best_score == 0 || $score < $best_score) {
                    $best_score = $score;
                }
                // Track completions by difficulty
                $difficulty = $metadata['difficulty'] ?? 'medium';
                $key = $difficulty . '_completions';
                $additional_stats[$key] = ($additional_stats[$key] ?? 0) + 1;
            }
            break;

        case 'xo':
            // Score: 1=win, 0=loss, -1=draw
            if ($score == 1) {
                $total_wins++;
                $current_streak++;
                if ($current_streak > $best_streak) {
                    $best_streak = $current_streak;
                }
            } elseif ($score == 0) {
                $total_losses++;
                $current_streak = 0;
            } else {
                $total_draws++;
            }
            $best_score = $best_streak; // Best score = best win streak
            break;

        case 'memory_match':
            // Best score = fewest attempts (lower is better)
            if ($best_score == 0 || $score < $best_score) {
                $best_score = $score;
            }
            // Track average attempts
            $additional_stats['total_attempts'] = ($additional_stats['total_attempts'] ?? 0) + $score;
            $additional_stats['avg_attempts'] = round($additional_stats['total_attempts'] / $total_plays);
            // Track fastest time
            if (!isset($additional_stats['fastest_time']) || $duration < $additional_stats['fastest_time']) {
                $additional_stats['fastest_time'] = $duration;
            }
            break;

        case 'quick_math':
            // Best score = highest accuracy percentage
            $accuracy = $metadata['accuracy'] ?? 0;
            if ($accuracy > $best_score) {
                $best_score = $accuracy;
            }
            // Track totals
            $additional_stats['total_correct'] = ($additional_stats['total_correct'] ?? 0) + ($metadata['correct'] ?? 0);
            $additional_stats['total_questions'] = ($additional_stats['total_questions'] ?? 0) + ($metadata['total_questions'] ?? 0);
            if ($additional_stats['total_questions'] > 0) {
                $additional_stats['avg_accuracy'] = round(($additional_stats['total_correct'] / $additional_stats['total_questions']) * 100);
            }
            break;

        case 'word_unscramble':
            // Best score = most words solved in one session
            if ($score > $best_score) {
                $best_score = $score;
            }
            // Track totals
            $additional_stats['total_words_solved'] = ($additional_stats['total_words_solved'] ?? 0) + $score;
            $additional_stats['total_time'] = ($additional_stats['total_time'] ?? 0) + $duration;
            if ($score > 0) {
                $additional_stats['avg_time_per_word'] = round($additional_stats['total_time'] / $additional_stats['total_words_solved']);
            }
            break;
    }

    // Update stats in database
    $updateStmt = $pdo->prepare(
        "UPDATE game_statistics
         SET total_plays = :total_plays,
             best_score = :best_score,
             total_wins = :total_wins,
             total_losses = :total_losses,
             total_draws = :total_draws,
             current_streak = :current_streak,
             best_streak = :best_streak,
             additional_stats = :additional_stats,
             last_played = CURRENT_TIMESTAMP
         WHERE user_id = :user_id AND game_type = :game_type"
    );

    $updateStmt->execute([
        ':total_plays' => $total_plays,
        ':best_score' => $best_score,
        ':total_wins' => $total_wins,
        ':total_losses' => $total_losses,
        ':total_draws' => $total_draws,
        ':current_streak' => $current_streak,
        ':best_streak' => $best_streak,
        ':additional_stats' => json_encode($additional_stats),
        ':user_id' => $user_id,
        ':game_type' => $game_type
    ]);
}

/**
 * Create new game statistics record
 */
function createGameStatistics($pdo, $user_id, $game_type, $score, $duration, $metadata) {
    $total_plays = 1;
    $best_score = $score;
    $total_wins = 0;
    $total_losses = 0;
    $total_draws = 0;
    $current_streak = 0;
    $best_streak = 0;
    $additional_stats = [];

    // Game-specific initialization
    switch ($game_type) {
        case 'sudoku':
            if ($metadata['completed'] ?? false) {
                $difficulty = $metadata['difficulty'] ?? 'medium';
                $additional_stats[$difficulty . '_completions'] = 1;
            } else {
                $best_score = 0;
            }
            break;

        case 'xo':
            if ($score == 1) {
                $total_wins = 1;
                $current_streak = 1;
                $best_streak = 1;
            } elseif ($score == 0) {
                $total_losses = 1;
            } else {
                $total_draws = 1;
            }
            $best_score = $best_streak;
            break;

        case 'memory_match':
            $additional_stats['total_attempts'] = $score;
            $additional_stats['avg_attempts'] = $score;
            $additional_stats['fastest_time'] = $duration;
            break;

        case 'quick_math':
            $best_score = $metadata['accuracy'] ?? 0;
            $additional_stats['total_correct'] = $metadata['correct'] ?? 0;
            $additional_stats['total_questions'] = $metadata['total_questions'] ?? 0;
            $additional_stats['avg_accuracy'] = $metadata['accuracy'] ?? 0;
            break;

        case 'word_unscramble':
            $additional_stats['total_words_solved'] = $score;
            $additional_stats['total_time'] = $duration;
            if ($score > 0) {
                $additional_stats['avg_time_per_word'] = round($duration / $score);
            }
            break;
    }

    // Insert new stats record
    $insertStmt = $pdo->prepare(
        "INSERT INTO game_statistics
         (user_id, game_type, total_plays, best_score, total_wins, total_losses, total_draws,
          current_streak, best_streak, additional_stats)
         VALUES
         (:user_id, :game_type, :total_plays, :best_score, :total_wins, :total_losses, :total_draws,
          :current_streak, :best_streak, :additional_stats)"
    );

    $insertStmt->execute([
        ':user_id' => $user_id,
        ':game_type' => $game_type,
        ':total_plays' => $total_plays,
        ':best_score' => $best_score,
        ':total_wins' => $total_wins,
        ':total_losses' => $total_losses,
        ':total_draws' => $total_draws,
        ':current_streak' => $current_streak,
        ':best_streak' => $best_streak,
        ':additional_stats' => json_encode($additional_stats)
    ]);
}
