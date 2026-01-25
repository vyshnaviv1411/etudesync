<?php
/**
 * MindPlay - Mood Tracker API: Save Mood
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

// READ JSON INPUT
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit;
}

$mood_value = trim($input['mood_value'] ?? '');
$mood_date  = $input['mood_date'] ?? date('Y-m-d');

$valid_moods = [
    'happy','sad','neutral','excited',
    'anxious','calm','energetic','tired'
];

if (!in_array($mood_value, $valid_moods)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid mood value'
    ]);
    exit;
}

if ($mood_date > date('Y-m-d')) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot set mood for future dates'
    ]);
    exit;
}

try {
    // CHECK DAILY LOCK
    $check = $pdo->prepare(
        "SELECT id FROM mood_tracker
         WHERE user_id = :user_id AND mood_date = :mood_date"
    );
    $check->execute([
        ':user_id' => $user_id,
        ':mood_date' => $mood_date
    ]);

    if ($check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Mood already set for today',
            'locked' => true
        ]);
        exit;
    }

    // INSERT
    $insert = $pdo->prepare(
        "INSERT INTO mood_tracker (user_id, mood_date, mood_value)
         VALUES (:user_id, :mood_date, :mood_value)"
    );
    $insert->execute([
        ':user_id' => $user_id,
        ':mood_date' => $mood_date,
        ':mood_value' => $mood_value
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Mood saved successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
