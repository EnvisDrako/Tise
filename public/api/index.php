<?php
/**
 * Mobile API Documentation and Status
 * Provides information about available API endpoints
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/jwt_helper.php';

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

$apiInfo = [
    'api_name' => 'Integrated CRM Mobile API',
    'version' => '1.0.0',
    'description' => 'Mobile API for enquirer users to manage calls and leads',
    'base_url' => SITE_URL . '/api',
    'authentication' => 'JWT Bearer Token',
    'rate_limit' => API_RATE_LIMIT . ' requests per hour',
    'endpoints' => [
        'POST /login' => [
            'description' => 'Authenticate enquirer user and get JWT token',
            'parameters' => [
                'username' => 'string (required)',
                'password' => 'string (required)'
            ],
            'response' => [
                'token' => 'JWT token for authentication',
                'user' => 'User information',
                'expires_in' => 'Token expiry in seconds'
            ]
        ],
        'GET /get_numbers' => [
            'description' => 'Get assigned enquiry phone numbers',
            'authentication' => 'Bearer token required',
            'parameters' => [
                'page' => 'int (optional, default: 1)',
                'limit' => 'int (optional, default: 25, max: 50)',
                'status' => 'string (optional: new, assigned, contacted, interested, not_interested, enrolled)',
                'search' => 'string (optional: search in name, phone, course)'
            ],
            'response' => [
                'phone_numbers' => 'Array of enquiry records with phone numbers',
                'pagination' => 'Pagination information',
                'statistics' => 'Summary statistics',
                'filters_applied' => 'Applied filters'
            ]
        ],
        'POST /log_call' => [
            'description' => 'Log call outcome and duration',
            'authentication' => 'Bearer token required',
            'parameters' => [
                'enquiry_id' => 'int (required)',
                'call_outcome' => 'string (required: answered, no_answer, busy, invalid, voicemail)',
                'call_duration' => 'int (optional: duration in seconds)',
                'notes' => 'string (optional)',
                'follow_up_required' => 'boolean (optional)',
                'call_timestamp' => 'string (optional: ISO datetime, within last 24 hours)'
            ],
            'response' => [
                'call_log_id' => 'ID of created call log',
                'enquiry' => 'Updated enquiry information',
                'call_details' => 'Logged call details',
                'status_updated' => 'Whether enquiry status was updated',
                'new_status' => 'New enquiry status if updated'
            ]
        ]
    ],
    'error_codes' => [
        'INVALID_JSON' => 'Request body is not valid JSON',
        'MISSING_CREDENTIALS' => 'Username or password missing',
        'INVALID_CREDENTIALS' => 'Invalid username or password',
        'INVALID_TOKEN' => 'JWT token is invalid or expired',
        'ACCESS_DENIED' => 'User role not authorized for this endpoint',
        'RATE_LIMITED' => 'Too many requests, rate limit exceeded',
        'MISSING_FIELD' => 'Required field is missing',
        'INVALID_OUTCOME' => 'Call outcome is not valid',
        'INVALID_DURATION' => 'Call duration is out of valid range',
        'INVALID_TIMESTAMP' => 'Call timestamp is invalid or too old',
        'ENQUIRY_NOT_FOUND' => 'Enquiry not found or not assigned to user',
        'SERVER_ERROR' => 'Internal server error'
    ],
    'response_format' => [
        'success' => 'boolean indicating success/failure',
        'data' => 'response data object',
        'message' => 'human readable message',
        'timestamp' => 'ISO datetime of response'
    ],
    'status' => 'operational',
    'last_updated' => date('c')
];

echo json_encode($apiInfo, JSON_PRETTY_PRINT);
?>