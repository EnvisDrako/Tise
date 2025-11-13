<?php
/**
 * Mobile API Phone Numbers Endpoint
 * Returns assigned enquiry phone numbers for authenticated enquirer users
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
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    logAPIRequest('/api/get_numbers', null, $_SERVER['REQUEST_METHOD'], false);
    sendAPIError('Method not allowed', 405);
}

try {
    // Validate API request and get user data
    $userData = validateAPIRequest();
    
    if (!$userData) {
        logAPIRequest('/api/get_numbers', null, 'GET', false);
        sendAPIError('Invalid or expired token', 401, 'INVALID_TOKEN');
    }
    
    // Check if user is enquirer
    if ($userData['role'] !== 'enquirer') {
        logAPIRequest('/api/get_numbers', $userData['user_id'], 'GET', false);
        sendAPIError('Access denied. Enquirer role required.', 403, 'ACCESS_DENIED');
    }
    
    // Check rate limiting
    if (!checkAPIRateLimit($userData['user_id'])) {
        logAPIRequest('/api/get_numbers', $userData['user_id'], 'GET', false);
        sendAPIError('Rate limit exceeded', 429, 'RATE_LIMITED');
    }
    
    // Get query parameters for filtering and pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 25))); // Max 50 records per request
    $offset = ($page - 1) * $limit;
    
    // Optional filters
    $status = $_GET['status'] ?? null;
    $priority = $_GET['priority'] ?? null;
    $search = $_GET['search'] ?? null;
    
    // Build query conditions
    $whereConditions = ["assigned_to_user_id = ?"];
    $params = [$userData['user_id']];
    
    // Status filter
    if ($status && in_array($status, ['new', 'assigned', 'contacted', 'interested', 'not_interested', 'enrolled'])) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    // Search filter
    if ($search) {
        $whereConditions[] = "(full_name LIKE ? OR phone LIKE ? OR course_interest LIKE ?)";
        $searchTerm = '%' . sanitizeInput($search) . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM enquiries WHERE {$whereClause}";
    $totalResult = fetchOne($countQuery, $params);
    $totalRecords = $totalResult['total'] ?? 0;
    
    // Get enquiries with phone numbers
    $query = "
        SELECT 
            id,
            full_name,
            phone,
            email,
            course_interest,
            status,
            created_at,
            CASE 
                WHEN status = 'new' THEN 'high'
                WHEN status = 'assigned' THEN 'high'
                WHEN status = 'interested' THEN 'medium'
                WHEN status = 'contacted' THEN 'medium'
                ELSE 'low'
            END as priority,
            CASE 
                WHEN status IN ('contacted', 'assigned') 
                AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY) THEN 1
                ELSE 0
            END as is_overdue,
            DATEDIFF(NOW(), created_at) as days_since_created
        FROM enquiries 
        WHERE {$whereClause}
        ORDER BY 
            CASE status 
                WHEN 'new' THEN 1 
                WHEN 'assigned' THEN 2 
                WHEN 'interested' THEN 3 
                WHEN 'contacted' THEN 4
                ELSE 5 
            END,
            created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $enquiries = fetchAll($query, $params);
    
    // Format the response data
    $phoneNumbers = [];
    foreach ($enquiries as $enquiry) {
        $phoneNumbers[] = [
            'enquiry_id' => (int)$enquiry['id'],
            'name' => $enquiry['full_name'],
            'phone' => $enquiry['phone'],
            'email' => $enquiry['email'],
            'course_interest' => $enquiry['course_interest'],
            'status' => $enquiry['status'],
            'priority' => $enquiry['priority'],
            'is_overdue' => (bool)$enquiry['is_overdue'],
            'days_since_created' => (int)$enquiry['days_since_created'],
            'created_at' => $enquiry['created_at']
        ];
    }
    
    // Calculate pagination info
    $totalPages = ceil($totalRecords / $limit);
    
    // Get summary statistics for the user
    $statsQuery = "
        SELECT 
            COUNT(*) as total_assigned,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
            SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted_count,
            SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) as interested_count,
            SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) as enrolled_count,
            SUM(CASE WHEN status IN ('contacted', 'assigned') 
                AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY) THEN 1 ELSE 0 END) as overdue_count
        FROM enquiries 
        WHERE assigned_to_user_id = ?
    ";
    
    $stats = fetchOne($statsQuery, [$userData['user_id']]);
    
    // Log successful request
    logAPIRequest('/api/get_numbers', $userData['user_id'], 'GET', true);
    
    // Return response with caching headers
    header('Cache-Control: private, max-age=300'); // Cache for 5 minutes
    header('ETag: "' . md5(serialize($phoneNumbers) . $page . $limit) . '"');
    
    sendAPIResponse([
        'phone_numbers' => $phoneNumbers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'per_page' => $limit,
            'has_next' => $page < $totalPages,
            'has_previous' => $page > 1
        ],
        'statistics' => [
            'total_assigned' => (int)($stats['total_assigned'] ?? 0),
            'new_count' => (int)($stats['new_count'] ?? 0),
            'contacted_count' => (int)($stats['contacted_count'] ?? 0),
            'interested_count' => (int)($stats['interested_count'] ?? 0),
            'enrolled_count' => (int)($stats['enrolled_count'] ?? 0),
            'overdue_count' => (int)($stats['overdue_count'] ?? 0)
        ],
        'filters_applied' => [
            'status' => $status,
            'search' => $search
        ]
    ], true, 'Phone numbers retrieved successfully');
    
} catch (Exception $e) {
    // Log the error
    logEvent("API get_numbers error: " . $e->getMessage(), 'ERROR');
    logAPIRequest('/api/get_numbers', $userData['user_id'] ?? null, 'GET', false);
    
    // Return generic error
    sendAPIError('Internal server error', 500, 'SERVER_ERROR');
}
?>