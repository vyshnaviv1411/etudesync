<?php
/**
 * Centralized Date Validation for FocusFlow
 * Prevents creation/scheduling of tasks for past dates
 */

/**
 * Check if a date is in the past (before today)
 *
 * @param string|null $date - Date to check in Y-m-d format
 * @return bool - True if date is in the past, false otherwise
 */
function isPastDate($date) {
    if (empty($date)) {
        return false; // Null dates are allowed (optional)
    }

    $today = date('Y-m-d');
    return $date < $today;
}

/**
 * Validate date and return error message if invalid
 *
 * @param string|null $date - Date to validate in Y-m-d format
 * @param string $fieldName - Name of field for error message
 * @return string|null - Error message if invalid, null if valid
 */
function validateDate($date, $fieldName = 'date') {
    if (empty($date)) {
        return null; // Optional dates are allowed
    }

    if (isPastDate($date)) {
        return "Cannot create tasks for past {$fieldName}. Please choose today or a future date.";
    }

    return null;
}

/**
 * Validate date and exit with JSON error if invalid
 * Use this function in API endpoints for quick validation
 *
 * @param string|null $date - Date to validate
 * @param string $fieldName - Name of field for error message
 * @return void - Exits script if validation fails
 */
function validateDateOrExit($date, $fieldName = 'date') {
    $error = validateDate($date, $fieldName);

    if ($error) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $error
        ]);
        exit;
    }
}
