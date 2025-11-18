<?php
/**
 * Local Development Configuration
 * Copy this to config.php for local development
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

// Auto detect base URL
$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
if ($serverName === 'localhost' || php_sapi_name() === 'cli') {
    define('BASE_URL', '/integrated-crm/public');
    define('SITE_URL', 'http://localhost/integrated-crm/public');
} else {
    define('BASE_URL', ''); // live server root
    define('SITE_URL', 'https://' . $serverName);
}

// Database configuration for local development
define('DB_HOST', 'localhost');
define('DB_NAME', 'integrated_crm');
define('DB_USER', 'root');  // Default XAMPP MySQL user
define('DB_PASS', '');      // Default XAMPP MySQL password (empty)
define('DB_CHARSET', 'utf8mb4');

// System configuration
define('DEBUG_MODE', true);  // Enable debug mode for development
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/');

// Security configuration (relaxed for local development)
define('SESSION_TIMEOUT', 7200); // 2 hours for development
define('MAX_LOGIN_ATTEMPTS', 10); // More lenient for development
define('LOCKOUT_DURATION', 300);  // 5 minutes
define('LOGIN_LOCKOUT_TIME', 300); // 5 minutes
define('PASSWORD_MIN_LENGTH', 6); // Minimum password length

// Pagination configuration
define('DEFAULT_PAGE_SIZE', 25); // Default items per page
define('MAX_PAGE_SIZE', 100); // Maximum items per page

// File upload configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Email configuration (optional for local development)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@localhost');
define('SMTP_FROM_NAME', 'CRM System');

// Site configuration
define('SITE_NAME', 'Taset Academy');

// JWT Secret for API (change this for production)
define('JWT_SECRET', 'your-local-development-jwt-secret-key-change-in-production');

// Timezone
date_default_timezone_set('America/New_York'); // Change to your timezone

// Development helpers
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
?>