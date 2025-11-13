<?php
/**
 * Local Development Configuration
 * Copy this to config.php for local development
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
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

// Local development settings
define('BASE_URL', 'http://localhost/integrated-crm');
define('SITE_NAME', 'Integrated CRM - Local Dev');

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