<?php
/**
 * Hostinger Production Configuration Template
 * Copy this to config.php and update with your Hostinger database details
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

// Database configuration - UPDATE THESE WITH YOUR HOSTINGER DATABASE DETAILS
define('DB_HOST', 'localhost');  // Usually localhost for Hostinger
define('DB_NAME', 'u123456789_crm');  // Replace with your database name from hPanel
define('DB_USER', 'u123456789_crmuser');  // Replace with your database username
define('DB_PASS', 'YOUR_STRONG_PASSWORD_HERE');  // Replace with your database password
define('DB_CHARSET', 'utf8mb4');

// System configuration
define('DEBUG_MODE', false);  // IMPORTANT: Keep false for production
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/');

// Security configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// File upload configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Site configuration - UPDATE WITH YOUR DOMAIN
define('BASE_URL', 'https://yourdomain.com/crm'); // Update with your actual URL
define('SITE_NAME', 'Your Institution CRM System');

// JWT Secret for API - GENERATE A RANDOM 64-CHARACTER STRING
define('JWT_SECRET', 'CHANGE-THIS-TO-A-RANDOM-64-CHARACTER-STRING-FOR-SECURITY');

// Email configuration (Optional - for notifications)
// Update with your domain email settings
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');  // Create this email in hPanel
define('SMTP_PASSWORD', 'your_email_password');     // Email password
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'CRM System');

// Timezone - SET TO YOUR TIMEZONE
date_default_timezone_set('America/New_York'); // Change to your timezone

// Hostinger-specific optimizations
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

// Production security headers
if (!DEBUG_MODE) {
    // Security headers for production
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Disable error display in production
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
} else {
    // Development error reporting (only if DEBUG_MODE is true)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// Hostinger-specific database connection optimization
define('DB_PERSISTENT', false); // Set to true if you want persistent connections
define('DB_TIMEOUT', 30); // Connection timeout in seconds

// File upload security settings
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);
define('SCAN_UPLOADS', true); // Enable virus scanning if available

// Cache settings (for better performance)
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 3600); // 1 hour

// API rate limiting (requests per hour per IP)
define('API_RATE_LIMIT', 1000);

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Only over HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Additional Hostinger-specific settings
if (function_exists('opcache_reset')) {
    // OPcache is available on Hostinger
    define('OPCACHE_ENABLED', true);
} else {
    define('OPCACHE_ENABLED', false);
}

// Backup and maintenance settings
define('AUTO_BACKUP', true);
define('BACKUP_RETENTION_DAYS', 30);
define('MAINTENANCE_MODE', false); // Set to true to enable maintenance mode

// Logging levels
define('LOG_LEVEL_ERROR', 1);
define('LOG_LEVEL_WARNING', 2);
define('LOG_LEVEL_INFO', 3);
define('LOG_LEVEL_DEBUG', 4);
define('CURRENT_LOG_LEVEL', DEBUG_MODE ? LOG_LEVEL_DEBUG : LOG_LEVEL_ERROR);

/**
 * Custom error handler for production
 */
if (!DEBUG_MODE) {
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error_message = date('Y-m-d H:i:s') . " - Error: $message in $file on line $line\n";
        error_log($error_message, 3, ERROR_LOG_PATH . 'php_errors.log');
        
        // Don't show errors to users in production
        return true;
    });
}

/**
 * Database connection helper with Hostinger optimizations
 */
function getHostingerDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => DB_PERSISTENT,
                PDO::ATTR_TIMEOUT => DB_TIMEOUT,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log the error
            error_log("Database connection failed: " . $e->getMessage(), 3, ERROR_LOG_PATH . 'database_errors.log');
            
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Maintenance mode check
 */
if (MAINTENANCE_MODE && !DEBUG_MODE) {
    // Check if current user is admin (you can customize this logic)
    $is_admin = false;
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $is_admin = true;
    }
    
    if (!$is_admin) {
        http_response_code(503);
        include __DIR__ . '/../public/maintenance.html';
        exit;
    }
}

/**
 * Security function to generate CSRF tokens
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Security function to verify CSRF tokens
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Hostinger-specific file upload validation
 */
function validateHostingerUpload($file) {
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large'];
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES);
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    // Additional security checks
    if (SCAN_UPLOADS) {
        // Basic malware detection (you can enhance this)
        $file_content = file_get_contents($file['tmp_name']);
        $malware_patterns = ['<?php', '<script', 'eval(', 'base64_decode'];
        
        foreach ($malware_patterns as $pattern) {
            if (stripos($file_content, $pattern) !== false) {
                return ['success' => false, 'error' => 'Potentially malicious file detected'];
            }
        }
    }
    
    return ['success' => true];
}

// Initialize session with security settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>