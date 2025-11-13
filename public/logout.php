<?php
/**
 * Logout Handler
 * Securely logs out users and destroys session
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Start session
session_start();

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Log the logout event if user was logged in
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();
    $username = getCurrentUserName();
    
    logEvent("User logout: ID {$userId}, Username: {$username}, Role: {$userRole}, IP: {$_SERVER['REMOTE_ADDR']}", 'INFO');
}

// Destroy session completely
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Start new session for success message
session_start();
$_SESSION['logout_success'] = true;

// Redirect to login
header('Location: ' . BASE_URL . '/login');
exit;
?>