<?php
/**
 * MindPlay - Journal API: Get Entry
 *
 * Retrieves journal entries for the current user.
 * Supports:
 * - Single date lookup (entry_date parameter)
 * - Date range lookup (start_date and end_date parameters)
 * - All entries (no parameters)
 *
 * Returns locked status based on date and submission state.
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
$entry_date = $_GET['entry_date'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

// =====================================================
// 3. BUILD QUERY BASED ON PARAMETERS
// =====================================================
try {
    $sql = "SELECT id, entry_date, content, theme_color, is_submitted, created_at, updated_at
            FROM journal_entries
            WHERE user_id = :user_id";
    $params = [':user_id' => $user_id];

    // Single date lookup
    if ($entry_date) {
        $sql .= " AND entry_date = :entry_date";
        $params[':entry_date'] = $entry_date;
    }
    // Date range lookup
    elseif ($start_date && $end_date) {
        $sql .= " AND entry_date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    $sql .= " ORDER BY entry_date DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // 4. CALCULATE LOCKED STATUS FOR EACH ENTRY
    // =====================================================
    $today = date('Y-m-d');

    foreach ($entries as &$entry) {
        // Entry is locked if:
        // 1. It's been submitted (is_submitted = 1), OR
        // 2. It's from a past day
        $isPast = $entry['entry_date'] < $today;
        $isSubmitted = $entry['is_submitted'] == 1;

        $entry['is_locked'] = $isPast || $isSubmitted;
        $entry['is_past'] = $isPast;
        $entry['is_today'] = $entry['entry_date'] === $today;
        $entry['is_editable'] = $entry['entry_date'] === $today && !$isSubmitted;
    }
    unset($entry); // Break reference

    // =====================================================
    // 5. CHECK TODAY'S ENTRY STATUS
    // =====================================================
    $todayEntry = null;
    $todayEntryExists = false;

    foreach ($entries as $entry) {
        if ($entry['entry_date'] === $today) {
            $todayEntry = $entry;
            $todayEntryExists = true;
            break;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Journal entries retrieved successfully',
        'data' => [
            'entries' => $entries,
            'count' => count($entries),
            'today_entry_exists' => $todayEntryExists,
            'today_entry' => $todayEntry,
            'today_date' => $today
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
