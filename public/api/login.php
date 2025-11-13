<?php
/**
 * Mobile API Login Endpoint
 * Authenticates enquirer users and returns JWT token
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
    logAPIRequest('/api/login', null, $_SERVER['REQUEST_METHOD'], false);
    sendAPIError('Method not allowed', 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        logAPIRequest('/api/login', null, 'POST', false);
        sendAPIError('Invalid JSON input', 400, 'INVALID_JSON');
    }
    
    // Validate required fields
    if (empty($input['username']) || empty($input['password'])) {
        logAPIRequest('/api/login', null, 'POST', false);
        sendAPIError('Username and password are required', 400, 'MISSING_CREDENTIALS');
    }
    
    $username = sanitizeInput($input['username']);
    $password = $input['password'];
    
    // Rate limiting check (basic IP-based)
    $ip = $_SERVER['REMOTE_ADDR'];
    $rateLimitKey = "login_attempts_{$ip}";
    $rateLimitFile = ERROR_LOG_PATH . "{$rateLimitKey}_" . date('Y-m-d-H') . ".cache";
    
    if (file_exists($rateLimitFile)) {
        $attempts = (int)file_get_contents($rateLimitFile);
        if ($attempts >= 10) { // 10 attempts per hour per IP
            logAPIRequest('/api/login', null, 'POST', false);
            sendAPIError('Too many login attempts. Please try again later.', 429, 'RATE_LIMITED');
        }
        file_put_contents($rateLimitFile, $attempts + 1);
    } else {
        file_put_contents($rateLimitFile, 1);
    }
    
    // Query user from database - only allow enquirer role for mobile API
    $query = "SELECT id, username, password_hash, role, full_name, email, 
                     institution_id, is_active, created_at 
              FROM users 
              WHERE username = ? AND role = 'enquirer' AND is_active = 1
              LIMIT 1";
    
    $user = fetchOne($query, [$username]);
    
    if (!$user) {
        logAPIRequest('/api/login', null, 'POST', false);
        sendAPIError('Invalid credentials', 401, 'INVALID_CREDENTIALS');
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password_hash'])) {
        logAPIRequest('/api/login', null, 'POST', false);
        sendAPIError('Invalid credentials', 401, 'INVALID_CREDENTIALS');
    }
    
    // Generate JWT token
    $payload = [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'full_name' => $user['full_name'],
        'institution_id' => $user['institution_id']
    ];
    
    $token = generateJWT($payload);
    
    // Update last login
    executeQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
    
    // Log successful login
    logAPIRequest('/api/login', $user['id'], 'POST', true);
    logEvent("API login successful for user: {$username} (ID: {$user['id']}), IP: {$ip}", 'INFO');
    
    // Return success response
    sendAPIResponse([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ],
        'expires_in' => JWT_EXPIRY
    ], true, 'Login successful');
    
} catch (Exception $e) {
    // Log the error
    logEvent("API login error: " . $e->getMessage(), 'ERROR');
    logAPIRequest('/api/login', null, 'POST', false);
    
    // Return generic error
    sendAPIError('Internal server error', 500, 'SERVER_ERROR');
}
?>