<?php
/**
 * MindPlay - Mood Tracker API: Save Mood
 *
 * Saves a mood entry for the current user for a specific date.
 * BUSINESS RULE: Only one mood entry allowed per user per day.
 * If an entry exists for today, it cannot be changed (daily lock).
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

// Mood value (required)
$mood_value = trim($input['mood_value'] ?? '');
// Date (optional - defaults to today)
$mood_date = $input['mood_date'] ?? date('Y-m-d');

// Validate mood value
$valid_moods = ['happy', 'sad', 'neutral', 'excited', 'anxious', 'calm', 'energetic', 'tired'];
if (empty($mood_value) || !in_array($mood_value, $valid_moods)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid mood value. Allowed: ' . implode(', ', $valid_moods)
    ]);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $mood_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid date format. Use YYYY-MM-DD'
    ]);
    exit;
}

// BUSINESS RULE: Cannot set mood for future dates
if ($mood_date > date('Y-m-d')) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot set mood for future dates'
    ]);
    exit;
}

// =====================================================
// 3. CHECK IF MOOD ALREADY EXISTS (DAILY LOCK)
// =====================================================
try {
    $checkStmt = $pdo->prepare(
        "SELECT id, mood_value, created_at
         FROM mood_tracker
         WHERE user_id = :user_id AND mood_date = :mood_date"
    );
    $checkStmt->execute([
        ':user_id' => $user_id,
        ':mood_date' => $mood_date
    ]);

    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // DAILY LOCK: Mood already set for this date - cannot change
        echo json_encode([
            'success' => false,
            'message' => 'Mood already set for this date. You can only set mood once per day.',
            'data' => [
                'existing_mood' => $existing['mood_value'],
                'locked' => true,
                'created_at' => $existing['created_at']
            ]
        ]);
        exit;
    }

    // =====================================================
    // 4. INSERT NEW MOOD ENTRY
    // =====================================================
    $insertStmt = $pdo->prepare(
        "INSERT INTO mood_tracker (user_id, mood_date, mood_value)
         VALUES (:user_id, :mood_date, :mood_value)"
    );

    $insertStmt->execute([
        ':user_id' => $user_id,
        ':mood_date' => $mood_date,
        ':mood_value' => $mood_value
    ]);

    $mood_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Mood saved successfully',
        'data' => [
            'id' => $mood_id,
            'mood_value' => $mood_value,
            'mood_date' => $mood_date,
            'locked' => true
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
