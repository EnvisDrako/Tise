<?php
/**
 * Login Processing Logic
 * Handles authentication, password verification, and role-based redirection
 * This file is included by login.php and should not be accessed directly
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

// Ensure this file is only included from login.php
if (!isset($username) || !isset($password)) {
    die('Invalid access');
}

/**
 * Check for login attempts and implement rate limiting
 */
function checkLoginAttempts($username) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    $lockout_time = LOGIN_LOCKOUT_TIME;
    
    // Initialize session arrays if not exists
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    if (!isset($_SESSION['login_lockout'])) {
        $_SESSION['login_lockout'] = [];
    }
    
    // Check if IP is currently locked out
    $lockout_key = $ip_address . '_' . $username;
    if (isset($_SESSION['login_lockout'][$lockout_key])) {
        $lockout_until = $_SESSION['login_lockout'][$lockout_key];
        if ($current_time < $lockout_until) {
            return false; // Still locked out
        } else {
            // Lockout expired, remove it
            unset($_SESSION['login_lockout'][$lockout_key]);
            unset($_SESSION['login_attempts'][$lockout_key]);
        }
    }
    
    return true; // Not locked out
}

/**
 * Record failed login attempt
 */
function recordFailedAttempt($username) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    $lockout_key = $ip_address . '_' . $username;
    
    // Initialize if not exists
    if (!isset($_SESSION['login_attempts'][$lockout_key])) {
        $_SESSION['login_attempts'][$lockout_key] = [];
    }
    
    // Add current attempt
    $_SESSION['login_attempts'][$lockout_key][] = $current_time;
    
    // Remove attempts older than lockout time
    $_SESSION['login_attempts'][$lockout_key] = array_filter(
        $_SESSION['login_attempts'][$lockout_key],
        function($timestamp) use ($current_time) {
            return ($current_time - $timestamp) < LOGIN_LOCKOUT_TIME;
        }
    );
    
    // Check if max attempts reached
    if (count($_SESSION['login_attempts'][$lockout_key]) >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['login_lockout'][$lockout_key] = $current_time + LOGIN_LOCKOUT_TIME;
        logEvent("Account locked for user: {$username}, IP: {$ip_address}", 'WARNING');
    }
}

/**
 * Clear login attempts on successful login
 */
function clearLoginAttempts($username) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $lockout_key = $ip_address . '_' . $username;
    
    unset($_SESSION['login_attempts'][$lockout_key]);
    unset($_SESSION['login_lockout'][$lockout_key]);
}

/**
 * Create secure session for authenticated user
 */
function createUserSession($user) {
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['institution_id'] = $user['institution_id'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['session_token'] = generateRandomString(32);
    
    // Set session cookie parameters for security
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

/**
 * Get redirect URL based on user role
 */
function getRedirectUrl($role) {
    switch ($role) {
        case 'admin':
            return BASE_URL . '/admin/';
        case 'faculty':
            return BASE_URL . '/institution/';
        case 'enquirer':
            return BASE_URL . '/enquiry/';
        default:
            return BASE_URL . '/login.php?error=invalid';
    }
}

// Main authentication logic
try {
    // Check if user is locked out
    if (!checkLoginAttempts($username)) {
        logEvent("Login attempt from locked account: {$username}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        header('Location: ' . BASE_URL . '/login.php?error=locked');
        exit;
    }
    
    // Query user from database
    $query = "SELECT id, username, password_hash, role, full_name, email, 
                     institution_id, is_active, created_at 
              FROM users 
              WHERE username = ? 
              LIMIT 1";
    
    $user = fetchOne($query, [$username]);
    
    if (!$user) {
        // User not found
        recordFailedAttempt($username);
        logEvent("Login attempt with invalid username: {$username}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        header('Location: ' . BASE_URL . '/login.php?error=invalid');
        exit;
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password_hash'])) {
        // Invalid password
        recordFailedAttempt($username);
        logEvent("Login attempt with invalid password for user: {$username}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        header('Location: ' . BASE_URL . '/login.php?error=invalid');
        exit;
    }
    
    // Check if account is active
    if (!$user['is_active']) {
        recordFailedAttempt($username);
        logEvent("Login attempt with inactive account: {$username}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        header('Location: ' . BASE_URL . '/login.php?error=inactive');
        exit;
    }
    
    // Authentication successful
    clearLoginAttempts($username);
    createUserSession($user);
    
    // Log successful login
    logEvent("Successful login for user: {$username} (Role: {$user['role']}), IP: {$_SERVER['REMOTE_ADDR']}", 'INFO');
    
    // Update last login time
    $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
    executeQuery($updateQuery, [$user['id']]);
    
    // Redirect to appropriate portal
    $redirectUrl = getRedirectUrl($user['role']);
    header('Location: ' . $redirectUrl);
    exit;
    
} catch (Exception $e) {
    // Log the error
    logEvent("Login process error: " . $e->getMessage(), 'ERROR');
    
    // Redirect with generic error
    header('Location: ' . BASE_URL . '/login.php?error=system');
    exit;
}
?>