<?php
/**
 * MindPlay - Journal API: Save/Update Entry
 *
 * Saves or updates a journal entry for a specific date.
 * BUSINESS RULES:
 * - One entry per user per day
 * - Entry is editable throughout the day until submitted
 * - After submission (is_submitted = 1), entry is locked
 * - Past days are automatically locked (read-only)
 * - Auto-save: is_submitted = 0 (still editable)
 * - Submit: is_submitted = 1 (locked permanently)
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
// 2. INPUT VALIDATION
// =====================================================
$input = json_decode(file_get_contents('php://input'), true);

$content = $input['content'] ?? '';
$entry_date = $input['entry_date'] ?? date('Y-m-d');
$theme_color = trim($input['theme_color'] ?? 'default');
$is_submitted = isset($input['is_submitted']) ? (int)$input['is_submitted'] : 0;

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry_date)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid date format. Use YYYY-MM-DD'
    ]);
    exit;
}
$today = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))
            ->format('Y-m-d');

/* Cannot create future entries */
if ($entry_date > $today) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot create journal entries for future dates'
    ]);
    exit;
}

/* Past days are locked */
if ($entry_date < $today) {
    echo json_encode([
        'success' => false,
        'message' => 'Past journal entries are locked and cannot be edited',
        'data' => [
            'entry_date' => $entry_date,
            'locked' => true
        ]
    ]);
    exit;
}

// =====================================================
// 3. CHECK IF ENTRY EXISTS
// =====================================================
try {
    $checkStmt = $pdo->prepare(
        "SELECT id, content, theme_color, is_submitted, created_at
         FROM journal_entries
         WHERE user_id = :user_id AND entry_date = :entry_date"
    );
    $checkStmt->execute([
        ':user_id' => $user_id,
        ':entry_date' => $entry_date
    ]);

    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // =====================================================
    // 4A. UPDATE EXISTING ENTRY
    // =====================================================
    if ($existing) {
        // BUSINESS RULE: Cannot edit submitted entries
        if ($existing['is_submitted'] == 1) {
            echo json_encode([
                'success' => false,
                'message' => 'This journal entry has been submitted and is locked',
                'data' => [
                    'entry_id' => $existing['id'],
                    'is_submitted' => true,
                    'locked' => true
                ]
            ]);
            exit;
        }

        // Update entry
        $updateStmt = $pdo->prepare(
            "UPDATE journal_entries
             SET content = :content,
                 theme_color = :theme_color,
                 is_submitted = :is_submitted,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );

        $updateStmt->execute([
            ':content' => $content,
            ':theme_color' => $theme_color,
            ':is_submitted' => $is_submitted,
            ':id' => $existing['id']
        ]);

        echo json_encode([
            'success' => true,
            'message' => $is_submitted ? 'Journal entry submitted successfully' : 'Journal entry auto-saved',
            'data' => [
                'id' => $existing['id'],
                'entry_date' => $entry_date,
                'is_submitted' => $is_submitted == 1,
                'locked' => $is_submitted == 1,
                'action' => 'updated'
            ]
        ]);
    }
    // =====================================================
    // 4B. CREATE NEW ENTRY
    // =====================================================
    else {
        $insertStmt = $pdo->prepare(
            "INSERT INTO journal_entries (user_id, entry_date, content, theme_color, is_submitted)
             VALUES (:user_id, :entry_date, :content, :theme_color, :is_submitted)"
        );

        $insertStmt->execute([
            ':user_id' => $user_id,
            ':entry_date' => $entry_date,
            ':content' => $content,
            ':theme_color' => $theme_color,
            ':is_submitted' => $is_submitted
        ]);

        $entry_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => $is_submitted ? 'Journal entry submitted successfully' : 'Journal entry created',
            'data' => [
                'id' => $entry_id,
                'entry_date' => $entry_date,
                'is_submitted' => $is_submitted == 1,
                'locked' => $is_submitted == 1,
                'action' => 'created'
            ]
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
