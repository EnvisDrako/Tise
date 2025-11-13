<?php
/**
 * Comprehensive Error Handling and Logging System
 * Provides centralized error logging, monitoring, and user-friendly error messages
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

class ErrorHandler {
    private static $instance = null;
    private $logPath;
    private $errorCounts = [];
    private $criticalErrors = [];
    
    private function __construct() {
        $this->logPath = ERROR_LOG_PATH;
        $this->initializeErrorHandling();
        $this->createLogDirectories();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize PHP error handling
     */
    private function initializeErrorHandling() {
        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
        
        // Configure error reporting
        if (DEBUG_MODE) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            ini_set('display_errors', 0);
        }
        
        ini_set('log_errors', 1);
        ini_set('error_log', $this->logPath . 'php_errors.log');
    }
    
    /**
     * Create log directories if they don't exist
     */
    private function createLogDirectories() {
        $directories = [
            $this->logPath,
            $this->logPath . 'security/',
            $this->logPath . 'performance/',
            $this->logPath . 'audit/'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = $this->getErrorType($severity);
        $context = [
            'file' => $file,
            'line' => $line,
            'severity' => $severity,
            'type' => $errorType
        ];
        
        $this->logError($message, 'PHP_ERROR', $context);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handleException($exception) {
        $context = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'code' => $exception->getCode()
        ];
        
        $this->logError($exception->getMessage(), 'EXCEPTION', $context);
        
        if (!DEBUG_MODE) {
            $this->displayUserFriendlyError();
        }
    }
    
    /**
     * Handle fatal errors
     */
    public function handleFatalError() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $context = [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $this->getErrorType($error['type'])
            ];
            
            $this->logError($error['message'], 'FATAL_ERROR', $context);
            
            if (!DEBUG_MODE) {
                $this->displayUserFriendlyError();
            }
        }
    }
    
    /**
     * Log error with context and user tracking
     */
    public function logError($message, $level = 'ERROR', $context = []) {
        if (!LOG_ERRORS) {
            return false;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $userId = $this->getCurrentUserId();
        $userRole = $this->getCurrentUserRole();
        $ipAddress = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'Unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'user_id' => $userId,
            'user_role' => $userRole,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'request_uri' => $requestUri,
            'request_method' => $requestMethod,
            'context' => $context,
            'session_id' => session_id(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        // Log to main error log
        $this->writeToLog('system.log', $logEntry);
        
        // Log to specific category if applicable
        $this->categorizeAndLog($level, $logEntry);
        
        // Track error counts for monitoring
        $this->trackErrorCount($level);
        
        // Handle critical errors
        if (in_array($level, ['FATAL_ERROR', 'CRITICAL', 'SECURITY_BREACH'])) {
            $this->handleCriticalError($logEntry);
        }
        
        return true;
    }
    
    /**
     * Write log entry to file
     */
    private function writeToLog($filename, $logEntry) {
        $logFile = $this->logPath . $filename;
        $formattedEntry = $this->formatLogEntry($logEntry);
        
        // Rotate log if it gets too large (10MB)
        if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
            $this->rotateLog($logFile);
        }
        
        file_put_contents($logFile, $formattedEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Format log entry for readability
     */
    private function formatLogEntry($logEntry) {
        $formatted = "[{$logEntry['timestamp']}] [{$logEntry['level']}]";
        
        if ($logEntry['user_id']) {
            $formatted .= " [User:{$logEntry['user_id']}:{$logEntry['user_role']}]";
        }
        
        $formatted .= " [IP:{$logEntry['ip_address']}]";
        $formatted .= " {$logEntry['message']}";
        
        if (!empty($logEntry['context'])) {
            $formatted .= " | Context: " . json_encode($logEntry['context']);
        }
        
        return $formatted;
    }
    
    /**
     * Categorize and log to specific files
     */
    private function categorizeAndLog($level, $logEntry) {
        switch ($level) {
            case 'SECURITY_BREACH':
            case 'UNAUTHORIZED_ACCESS':
            case 'SUSPICIOUS_ACTIVITY':
                $this->writeToLog('security/security.log', $logEntry);
                break;
                
            case 'PERFORMANCE_WARNING':
            case 'SLOW_QUERY':
                $this->writeToLog('performance/performance.log', $logEntry);
                break;
                
            case 'USER_ACTION':
            case 'LOGIN':
            case 'LOGOUT':
            case 'DATA_CHANGE':
                $this->writeToLog('audit/audit.log', $logEntry);
                break;
                
            case 'DATABASE_ERROR':
                $this->writeToLog('database_errors.log', $logEntry);
                break;
        }
    }
    
    /**
     * Track error counts for monitoring
     */
    private function trackErrorCount($level) {
        $today = date('Y-m-d');
        
        if (!isset($this->errorCounts[$today])) {
            $this->errorCounts[$today] = [];
        }
        
        if (!isset($this->errorCounts[$today][$level])) {
            $this->errorCounts[$today][$level] = 0;
        }
        
        $this->errorCounts[$today][$level]++;
        
        // Save to file for persistence
        $this->saveErrorCounts();
    }
    
    /**
     * Save error counts to file
     */
    private function saveErrorCounts() {
        $countsFile = $this->logPath . 'error_counts.json';
        file_put_contents($countsFile, json_encode($this->errorCounts), LOCK_EX);
    }
    
    /**
     * Load error counts from file
     */
    private function loadErrorCounts() {
        $countsFile = $this->logPath . 'error_counts.json';
        
        if (file_exists($countsFile)) {
            $this->errorCounts = json_decode(file_get_contents($countsFile), true) ?: [];
        }
    }
    
    /**
     * Handle critical errors
     */
    private function handleCriticalError($logEntry) {
        $this->criticalErrors[] = $logEntry;
        
        // Send immediate notification for critical errors
        if (defined('ADMIN_EMAIL') && ADMIN_EMAIL) {
            $this->sendCriticalErrorNotification($logEntry);
        }
        
        // Send through notification manager if available
        if (class_exists('NotificationManager')) {
            NotificationManager::getInstance()->sendCriticalErrorAlert($logEntry);
        }
        
        // Log to critical errors file
        $this->writeToLog('critical_errors.log', $logEntry);
    }
    
    /**
     * Send critical error notification
     */
    private function sendCriticalErrorNotification($logEntry) {
        $subject = "Critical Error Alert - " . SITE_NAME;
        $message = "A critical error has occurred:\n\n";
        $message .= "Time: {$logEntry['timestamp']}\n";
        $message .= "Level: {$logEntry['level']}\n";
        $message .= "Message: {$logEntry['message']}\n";
        $message .= "User: {$logEntry['user_id']} ({$logEntry['user_role']})\n";
        $message .= "IP: {$logEntry['ip_address']}\n";
        $message .= "URI: {$logEntry['request_uri']}\n";
        
        if (!empty($logEntry['context'])) {
            $message .= "Context: " . json_encode($logEntry['context'], JSON_PRETTY_PRINT) . "\n";
        }
        
        $headers = "From: system@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        @mail(ADMIN_EMAIL, $subject, $message, $headers);
    }
    
    /**
     * Rotate log file when it gets too large
     */
    private function rotateLog($logFile) {
        $rotatedFile = $logFile . '.' . date('Y-m-d-H-i-s');
        rename($logFile, $rotatedFile);
        
        // Compress old log file
        if (function_exists('gzopen')) {
            $this->compressLogFile($rotatedFile);
        }
        
        // Clean up old rotated logs (keep last 30 days)
        $this->cleanupOldLogs(dirname($logFile));
    }
    
    /**
     * Compress log file
     */
    private function compressLogFile($logFile) {
        $compressedFile = $logFile . '.gz';
        
        $input = fopen($logFile, 'rb');
        $output = gzopen($compressedFile, 'wb9');
        
        if ($input && $output) {
            while (!feof($input)) {
                gzwrite($output, fread($input, 8192));
            }
            
            fclose($input);
            gzclose($output);
            
            // Remove original file after compression
            unlink($logFile);
        }
    }
    
    /**
     * Clean up old log files
     */
    private function cleanupOldLogs($logDir) {
        $files = glob($logDir . '/*.log.*');
        $cutoffTime = time() - (30 * 24 * 60 * 60); // 30 days
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
    
    /**
     * Display user-friendly error message
     */
    private function displayUserFriendlyError() {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error - ' . SITE_NAME . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .error-container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-icon { font-size: 48px; color: #e74c3c; text-align: center; margin-bottom: 20px; }
        .error-title { font-size: 24px; color: #2c3e50; text-align: center; margin-bottom: 15px; }
        .error-message { color: #7f8c8d; text-align: center; line-height: 1.6; margin-bottom: 25px; }
        .error-actions { text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; margin: 0 5px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Oops! Something went wrong</h1>
        <p class="error-message">
            We\'re experiencing a temporary issue. Our team has been notified and is working to resolve this problem.
            Please try again in a few minutes.
        </p>
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn">Go Back</a>
            <a href="/" class="btn">Home Page</a>
        </div>
    </div>
</body>
</html>';
        exit;
    }
    
    /**
     * Get error statistics for monitoring dashboard
     */
    public function getErrorStatistics($days = 7) {
        $this->loadErrorCounts();
        
        $stats = [
            'total_errors' => 0,
            'critical_errors' => 0,
            'daily_breakdown' => [],
            'error_types' => [],
            'recent_errors' => []
        ];
        
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        foreach ($this->errorCounts as $date => $counts) {
            if ($date >= $startDate) {
                $dayTotal = array_sum($counts);
                $stats['total_errors'] += $dayTotal;
                $stats['daily_breakdown'][$date] = $dayTotal;
                
                foreach ($counts as $type => $count) {
                    if (!isset($stats['error_types'][$type])) {
                        $stats['error_types'][$type] = 0;
                    }
                    $stats['error_types'][$type] += $count;
                    
                    if (in_array($type, ['FATAL_ERROR', 'CRITICAL', 'SECURITY_BREACH'])) {
                        $stats['critical_errors'] += $count;
                    }
                }
            }
        }
        
        // Get recent errors from log file
        $stats['recent_errors'] = $this->getRecentErrors(20);
        
        return $stats;
    }
    
    /**
     * Get recent errors from log file
     */
    private function getRecentErrors($limit = 20) {
        $logFile = $this->logPath . 'system.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $recentLines = array_slice($lines, -$limit);
        
        $errors = [];
        foreach ($recentLines as $line) {
            if (preg_match('/\[(.*?)\] \[(.*?)\] (.*)/', $line, $matches)) {
                $errors[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'message' => $matches[3]
                ];
            }
        }
        
        return array_reverse($errors);
    }
    
    /**
     * Get current user ID safely
     */
    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role safely
     */
    private function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Get error type name from severity level
     */
    private function getErrorType($severity) {
        $types = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];
        
        return $types[$severity] ?? 'Unknown Error';
    }
}

/**
 * Global error logging function
 */
function logError($message, $level = 'ERROR', $context = []) {
    return ErrorHandler::getInstance()->logError($message, $level, $context);
}

/**
 * Log security events
 */
function logSecurityEvent($message, $context = []) {
    return ErrorHandler::getInstance()->logError($message, 'SECURITY_BREACH', $context);
}

/**
 * Log user actions for audit trail
 */
function logUserAction($action, $details = []) {
    $context = array_merge($details, [
        'action' => $action,
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_role' => $_SESSION['user_role'] ?? null
    ]);
    
    return ErrorHandler::getInstance()->logError("User action: {$action}", 'USER_ACTION', $context);
}

/**
 * Log performance issues
 */
function logPerformanceIssue($message, $context = []) {
    return ErrorHandler::getInstance()->logError($message, 'PERFORMANCE_WARNING', $context);
}

/**
 * Initialize error handler
 */
function initializeErrorHandler() {
    ErrorHandler::getInstance();
}
?>