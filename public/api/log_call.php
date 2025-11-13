<?php
/**
 * Mobile API Call Logging Endpoint
 * Accepts call outcome and duration data from mobile app
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../includes/jwt_helper.php';

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logAPIRequest('/api/log_call', null, $_SERVER['REQUEST_METHOD'], false);
    sendAPIError('Method not allowed', 405);
}

try {
    // Validate API request and get user data
    $userData = validateAPIRequest();
    
    if (!$userData) {
        logAPIRequest('/api/log_call', null, 'POST', false);
        sendAPIError('Invalid or expired token', 401, 'INVALID_TOKEN');
    }
    
    // Check if user is enquirer
    if ($userData['role'] !== 'enquirer') {
        logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
        sendAPIError('Access denied. Enquirer role required.', 403, 'ACCESS_DENIED');
    }
    
    // Check rate limiting
    if (!checkAPIRateLimit($userData['user_id'])) {
        logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
        sendAPIError('Rate limit exceeded', 429, 'RATE_LIMITED');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
        sendAPIError('Invalid JSON input', 400, 'INVALID_JSON');
    }
    
    // Validate required fields
    $requiredFields = ['enquiry_id', 'call_outcome'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
            sendAPIError("Missing required field: {$field}", 400, 'MISSING_FIELD');
        }
    }
    
    $enquiryId = (int)$input['enquiry_id'];
    $callOutcome = sanitizeInput($input['call_outcome']);
    $callDuration = isset($input['call_duration']) ? (int)$input['call_duration'] : 0;
    $notes = isset($input['notes']) ? sanitizeInput($input['notes']) : '';
    $followUpRequired = isset($input['follow_up_required']) ? (bool)$input['follow_up_required'] : false;
    $callTimestamp = isset($input['call_timestamp']) ? $input['call_timestamp'] : null;
    
    // Validate call outcome
    $validOutcomes = ['answered', 'no_answer', 'busy', 'invalid', 'voicemail'];
    if (!in_array($callOutcome, $validOutcomes)) {
        logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
        sendAPIError('Invalid call outcome. Must be one of: ' . implode(', ', $validOutcomes), 400, 'INVALID_OUTCOME');
    }
    
    // Validate call duration (max 2 hours = 7200 seconds)
    if ($callDuration < 0 || $callDuration > 7200) {
        logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
        sendAPIError('Invalid call duration. Must be between 0 and 7200 seconds.', 400, 'INVALID_DURATION');
    }
    
    // Validate call timestamp if provided
    if ($callTimestamp) {
        $timestamp = strtotime($callTimestamp);
        if (!$timestamp || $timestamp > time() || $timestamp < (time() - 86400)) {
            // Must be within last 24 hours and not in future
            logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
            sendAPIError('Invalid call timestamp. Must be within last 24 hours.', 400, 'INVALID_TIMESTAMP');
        }
    }
    
    // Verify enquiry exists and is assigned to this user
    $enquiry = fetchOne(
        "SELECT id, full_name, phone, status FROM enquiries WHERE id = ? AND assigned_to_user_id = ?",
        [$enquiryId, $userData['user_id']]
    );
    
    if (!$enquiry) {
        logAPIRequest('/api/log_call', $userData['user_id'], 'POST', false);
        sendAPIError('Enquiry not found or not assigned to you', 404, 'ENQUIRY_NOT_FOUND');
    }
    
    // Begin database transaction
    $pdo = getDB();
    $pdo->beginTransaction();
    
    try {
        // Create call_logs table if it doesn't exist
        $createCallLogsTable = "
            CREATE TABLE IF NOT EXISTS call_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                enquiry_id INT NOT NULL,
                caller_user_id INT NOT NULL,
                call_duration INT DEFAULT 0,
                call_outcome ENUM('answered', 'no_answer', 'busy', 'invalid', 'voicemail') NOT NULL,
                follow_up_required BOOLEAN DEFAULT FALSE,
                notes TEXT,
                call_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (enquiry_id) REFERENCES enquiries(id),
                FOREIGN KEY (caller_user_id) REFERENCES users(id),
                INDEX idx_caller_timestamp (caller_user_id, call_timestamp),
                INDEX idx_enquiry_outcome (enquiry_id, call_outcome)
            )
        ";
        $pdo->exec($createCallLogsTable);
        
        // Insert call log
        $insertCallQuery = "
            INSERT INTO call_logs (enquiry_id, caller_user_id, call_duration, call_outcome, follow_up_required, notes, call_timestamp)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        
        $callTimestampForDB = $callTimestamp ? date('Y-m-d H:i:s', strtotime($callTimestamp)) : date('Y-m-d H:i:s');
        
        executeQuery($insertCallQuery, [
            $enquiryId,
            $userData['user_id'],
            $callDuration,
            $callOutcome,
            $followUpRequired,
            $notes,
            $callTimestampForDB
        ]);
        
        $callLogId = $pdo->lastInsertId();
        
        // Update enquiry status based on call outcome
        $newStatus = null;
        switch ($callOutcome) {
            case 'answered':
                if ($enquiry['status'] === 'new' || $enquiry['status'] === 'assigned') {
                    $newStatus = 'contacted';
                }
                break;
            case 'no_answer':
            case 'busy':
            case 'voicemail':
                // Keep current status but might set follow-up
                if ($enquiry['status'] === 'new') {
                    $newStatus = 'assigned';
                }
                break;
            case 'invalid':
                $newStatus = 'not_interested';
                break;
        }
        
        // Update enquiry status if needed
        if ($newStatus && $newStatus !== $enquiry['status']) {
            executeQuery("UPDATE enquiries SET status = ? WHERE id = ?", [$newStatus, $enquiryId]);
            
            // Log status change in history
            addToHistory($enquiryId, $userData['user_id'], 'status_update', [
                'old_status' => $enquiry['status'],
                'new_status' => $newStatus,
                'reason' => 'Call outcome: ' . $callOutcome,
                'call_log_id' => $callLogId
            ]);
        }
        
        // Add call to enquiry history
        addToHistory($enquiryId, $userData['user_id'], 'call_logged', [
            'outcome' => $callOutcome,
            'duration' => $callDuration,
            'notes' => $notes,
            'follow_up_required' => $followUpRequired,
            'call_log_id' => $callLogId
        ]);
        
        // If follow-up is required, schedule it
        if ($followUpRequired) {
            // Schedule follow-up for next business day
            $followUpDate = date('Y-m-d H:i:s', strtotime('+1 weekday 09:00:00'));
            scheduleFollowup($enquiryId, $userData['user_id'], $followUpDate, 'Follow-up required from call on ' . date('Y-m-d H:i'));
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Get updated enquiry data
        $updatedEnquiry = fetchOne(
            "SELECT id, full_name, phone, status FROM enquiries WHERE id = ?",
            [$enquiryId]
        );
        
        // Log successful request
        logAPIRequest('/api/log_call', $userData['user_id'], 'POST', true);
        logEvent("Call logged successfully by user {$userData['user_id']} for enquiry {$enquiryId}: {$callOutcome}", 'INFO');
        
        // Return success response
        sendAPIResponse([
            'call_log_id' => $callLogId,
            'enquiry' => [
                'id' => $updatedEnquiry['id'],
                'name' => $updatedEnquiry['full_name'],
                'phone' => $updatedEnquiry['phone'],
                'status' => $updatedEnquiry['status']
            ],
            'call_details' => [
                'outcome' => $callOutcome,
                'duration' => $callDuration,
                'follow_up_required' => $followUpRequired,
                'timestamp' => $callTimestampForDB
            ],
            'status_updated' => $newStatus !== null,
            'new_status' => $newStatus ?: $enquiry['status']
        ], true, 'Call logged successfully');
        
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Log the error
    logEvent("API log_call error: " . $e->getMessage(), 'ERROR');
    logAPIRequest('/api/log_call', $userData['user_id'] ?? null, 'POST', false);
    
    // Return generic error
    sendAPIError('Internal server error', 500, 'SERVER_ERROR');
}
?>