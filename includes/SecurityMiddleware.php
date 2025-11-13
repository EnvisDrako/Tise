<?php
/**
 * Security Middleware
 * Provides comprehensive security checks for all requests
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

class SecurityMiddleware {
    private static $instance = null;
    private $rateLimiter;
    private $ipWhitelist = [];
    private $ipBlacklist = [];
    
    private function __construct() {
        $this->rateLimiter = new RateLimiter();
        $this->loadSecurityConfig();
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
     * Load security configuration
     */
    private function loadSecurityConfig() {
        // Load IP whitelist/blacklist from database or config
        try {
            $pdo = getDB();
            
            // Create security config table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS security_config (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    config_key VARCHAR(50) NOT NULL UNIQUE,
                    config_value TEXT NOT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Load IP whitelist
            $stmt = $pdo->prepare("SELECT config_value FROM security_config WHERE config_key = 'ip_whitelist' AND is_active = 1");
            $stmt->execute();
            $whitelist = $stmt->fetchColumn();
            if ($whitelist) {
                $this->ipWhitelist = json_decode($whitelist, true) ?: [];
            }
            
            // Load IP blacklist
            $stmt = $pdo->prepare("SELECT config_value FROM security_config WHERE config_key = 'ip_blacklist' AND is_active = 1");
            $stmt->execute();
            $blacklist = $stmt->fetchColumn();
            if ($blacklist) {
                $this->ipBlacklist = json_decode($blacklist, true) ?: [];
            }
            
        } catch (Exception $e) {
            logError("Failed to load security config: " . $e->getMessage());
        }
    }
    
    /**
     * Apply security checks to request
     */
    public function applySecurityChecks($options = []) {
        $clientIP = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        
        // Check IP blacklist
        if ($this->isBlacklistedIP($clientIP)) {
            $this->blockRequest('IP address is blacklisted', [
                'ip' => $clientIP,
                'reason' => 'blacklisted_ip'
            ]);
        }
        
        // Check for suspicious patterns
        if ($this->detectSuspiciousActivity($requestUri, $userAgent)) {
            $this->blockRequest('Suspicious activity detected', [
                'ip' => $clientIP,
                'user_agent' => $userAgent,
                'request_uri' => $requestUri,
                'reason' => 'suspicious_activity'
            ]);
        }
        
        // Rate limiting
        if (!$this->rateLimiter->checkLimit($clientIP, $options['rate_limit'] ?? 'default')) {
            $this->blockRequest('Rate limit exceeded', [
                'ip' => $clientIP,
                'reason' => 'rate_limit_exceeded'
            ]);
        }
        
        // SQL injection detection
        if ($this->detectSQLInjection()) {
            $this->blockRequest('SQL injection attempt detected', [
                'ip' => $clientIP,
                'request_data' => $this->sanitizeLogData($_REQUEST),
                'reason' => 'sql_injection_attempt'
            ]);
        }
        
        // XSS detection
        if ($this->detectXSSAttempt()) {
            $this->blockRequest('XSS attempt detected', [
                'ip' => $clientIP,
                'request_data' => $this->sanitizeLogData($_REQUEST),
                'reason' => 'xss_attempt'
            ]);
        }
        
        // CSRF protection for POST requests
        if ($requestMethod === 'POST' && ($options['csrf_protection'] ?? true)) {
            if (!CSRFProtection::validateRequest()) {
                $this->blockRequest('CSRF token validation failed', [
                    'ip' => $clientIP,
                    'reason' => 'csrf_validation_failed'
                ]);
            }
        }
        
        // Check for file upload attacks
        if (!empty($_FILES)) {
            $this->validateFileUploads();
        }
        
        // Log successful security check
        $this->logSecurityEvent('security_check_passed', [
            'ip' => $clientIP,
            'user_agent' => substr($userAgent, 0, 200),
            'request_uri' => $requestUri,
            'request_method' => $requestMethod
        ]);
        
        return true;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    /**
     * Check if IP is blacklisted
     */
    private function isBlacklistedIP($ip) {
        return in_array($ip, $this->ipBlacklist);
    }
    
    /**
     * Detect suspicious activity patterns
     */
    private function detectSuspiciousActivity($requestUri, $userAgent) {
        // Suspicious user agents
        $suspiciousAgents = [
            'sqlmap', 'nikto', 'nmap', 'masscan', 'zap', 'burp',
            'wget', 'curl', 'python-requests', 'bot', 'crawler'
        ];
        
        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }
        
        // Suspicious URI patterns
        $suspiciousPatterns = [
            '/\.\./i',                    // Directory traversal
            '/union.*select/i',           // SQL injection
            '/<script/i',                 // XSS
            '/javascript:/i',             // XSS
            '/vbscript:/i',              // XSS
            '/onload=/i',                // XSS
            '/onerror=/i',               // XSS
            '/eval\(/i',                 // Code injection
            '/base64_decode/i',          // Code injection
            '/system\(/i',               // Command injection
            '/exec\(/i',                 // Command injection
            '/passthru\(/i',             // Command injection
            '/shell_exec/i',             // Command injection
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $requestUri)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect SQL injection attempts
     */
    private function detectSQLInjection() {
        $inputs = array_merge($_GET, $_POST, $_COOKIE);
        
        $sqlPatterns = [
            '/union.*select/i',
            '/select.*from/i',
            '/insert.*into/i',
            '/update.*set/i',
            '/delete.*from/i',
            '/drop.*table/i',
            '/create.*table/i',
            '/alter.*table/i',
            '/exec\(/i',
            '/execute\(/i',
            '/sp_/i',
            '/xp_/i',
            '/0x[0-9a-f]+/i',
            '/\bor\b.*\b1\b.*=.*\b1\b/i',
            '/\band\b.*\b1\b.*=.*\b1\b/i',
            '/\'.*or.*\'/i',
            '/\".*or.*\"/i',
            '/\bor\b.*\bsleep\b/i',
            '/\bunion\b.*\ball\b.*\bselect\b/i'
        ];
        
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Detect XSS attempts
     */
    private function detectXSSAttempt() {
        $inputs = array_merge($_GET, $_POST, $_COOKIE);
        
        $xssPatterns = [
            '/<script[^>]*>/i',
            '/<\/script>/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/onmouseover\s*=/i',
            '/onfocus\s*=/i',
            '/onblur\s*=/i',
            '/onchange\s*=/i',
            '/onsubmit\s*=/i',
            '/eval\s*\(/i',
            '/expression\s*\(/i',
            '/alert\s*\(/i',
            '/confirm\s*\(/i',
            '/prompt\s*\(/i'
        ];
        
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Validate file uploads for security
     */
    private function validateFileUploads() {
        foreach ($_FILES as $fieldName => $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Check file size
                if ($file['size'] > MAX_FILE_SIZE) {
                    $this->blockRequest('File size exceeds limit', [
                        'field' => $fieldName,
                        'size' => $file['size'],
                        'max_size' => MAX_FILE_SIZE,
                        'reason' => 'file_size_exceeded'
                    ]);
                }
                
                // Check file extension
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, ALLOWED_FILE_TYPES)) {
                    $this->blockRequest('File type not allowed', [
                        'field' => $fieldName,
                        'filename' => $file['name'],
                        'extension' => $extension,
                        'reason' => 'invalid_file_type'
                    ]);
                }
                
                // Check for executable files
                $dangerousExtensions = [
                    'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 
                    'jsp', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js'
                ];
                
                if (in_array($extension, $dangerousExtensions)) {
                    $this->blockRequest('Dangerous file type detected', [
                        'field' => $fieldName,
                        'filename' => $file['name'],
                        'extension' => $extension,
                        'reason' => 'dangerous_file_type'
                    ]);
                }
            }
        }
    }
    
    /**
     * Block request and log security event
     */
    private function blockRequest($reason, $context = []) {
        // Log security event
        logSecurityEvent($reason, $context);
        
        // Send security alert if configured
        if (class_exists('NotificationManager')) {
            NotificationManager::getInstance()->sendSecurityAlert([
                'type' => 'Request Blocked',
                'message' => $reason,
                'ip_address' => $context['ip'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'context' => $context
            ]);
        }
        
        // Return 403 Forbidden
        http_response_code(403);
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        echo json_encode([
            'error' => 'Access denied',
            'message' => 'Your request has been blocked for security reasons.',
            'code' => 403
        ]);
        
        exit;
    }
    
    /**
     * Log security event
     */
    private function logSecurityEvent($event, $context = []) {
        logError($event, 'SECURITY_BREACH', $context);
    }
    
    /**
     * Sanitize data for logging
     */
    private function sanitizeLogData($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Truncate long values and remove sensitive data
                if (strlen($value) > 200) {
                    $value = substr($value, 0, 200) . '...';
                }
                
                // Remove potential passwords
                if (stripos($key, 'password') !== false || 
                    stripos($key, 'pass') !== false ||
                    stripos($key, 'pwd') !== false) {
                    $value = '[REDACTED]';
                }
                
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeLogData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Add IP to blacklist
     */
    public function blacklistIP($ip, $reason = '') {
        try {
            $pdo = getDB();
            
            // Add to blacklist array
            if (!in_array($ip, $this->ipBlacklist)) {
                $this->ipBlacklist[] = $ip;
            }
            
            // Update database
            $stmt = $pdo->prepare("
                INSERT INTO security_config (config_key, config_value) 
                VALUES ('ip_blacklist', ?) 
                ON DUPLICATE KEY UPDATE config_value = ?, updated_at = NOW()
            ");
            
            $blacklistJson = json_encode($this->ipBlacklist);
            $stmt->execute([$blacklistJson, $blacklistJson]);
            
            // Log the action
            logSecurityEvent("IP blacklisted: {$ip}", [
                'ip' => $ip,
                'reason' => $reason,
                'action' => 'ip_blacklisted'
            ]);
            
            return true;
            
        } catch (Exception $e) {
            logError("Failed to blacklist IP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove IP from blacklist
     */
    public function removeFromBlacklist($ip) {
        try {
            $pdo = getDB();
            
            // Remove from blacklist array
            $this->ipBlacklist = array_filter($this->ipBlacklist, function($blacklistedIP) use ($ip) {
                return $blacklistedIP !== $ip;
            });
            
            // Update database
            $stmt = $pdo->prepare("
                UPDATE security_config 
                SET config_value = ?, updated_at = NOW() 
                WHERE config_key = 'ip_blacklist'
            ");
            
            $stmt->execute([json_encode(array_values($this->ipBlacklist))]);
            
            // Log the action
            logSecurityEvent("IP removed from blacklist: {$ip}", [
                'ip' => $ip,
                'action' => 'ip_unblacklisted'
            ]);
            
            return true;
            
        } catch (Exception $e) {
            logError("Failed to remove IP from blacklist: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Rate Limiter Class
 */
class RateLimiter {
    private $limits = [
        'default' => ['requests' => 100, 'window' => 3600], // 100 requests per hour
        'login' => ['requests' => 5, 'window' => 900],      // 5 login attempts per 15 minutes
        'api' => ['requests' => 1000, 'window' => 3600],    // 1000 API calls per hour
        'upload' => ['requests' => 10, 'window' => 3600]    // 10 uploads per hour
    ];
    
    /**
     * Check rate limit for IP and action
     */
    public function checkLimit($ip, $action = 'default') {
        if (!isset($this->limits[$action])) {
            $action = 'default';
        }
        
        $limit = $this->limits[$action];
        $key = "rate_limit_{$action}_{$ip}";
        
        try {
            $pdo = getDB();
            
            // Create rate limit table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS rate_limits (
                    id VARCHAR(100) PRIMARY KEY,
                    requests INT DEFAULT 1,
                    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Clean old entries
            $pdo->prepare("DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL ? SECOND)")
                ->execute([$limit['window']]);
            
            // Get current count
            $stmt = $pdo->prepare("SELECT requests, window_start FROM rate_limits WHERE id = ?");
            $stmt->execute([$key]);
            $current = $stmt->fetch();
            
            if (!$current) {
                // First request in window
                $pdo->prepare("INSERT INTO rate_limits (id, requests) VALUES (?, 1)")
                    ->execute([$key]);
                return true;
            }
            
            // Check if window has expired
            $windowStart = strtotime($current['window_start']);
            if ((time() - $windowStart) > $limit['window']) {
                // Reset window
                $pdo->prepare("UPDATE rate_limits SET requests = 1, window_start = NOW() WHERE id = ?")
                    ->execute([$key]);
                return true;
            }
            
            // Check if limit exceeded
            if ($current['requests'] >= $limit['requests']) {
                // Log rate limit exceeded
                logSecurityEvent("Rate limit exceeded", [
                    'ip' => $ip,
                    'action' => $action,
                    'requests' => $current['requests'],
                    'limit' => $limit['requests'],
                    'window' => $limit['window']
                ]);
                
                return false;
            }
            
            // Increment counter
            $pdo->prepare("UPDATE rate_limits SET requests = requests + 1 WHERE id = ?")
                ->execute([$key]);
            
            return true;
            
        } catch (Exception $e) {
            logError("Rate limiter error: " . $e->getMessage());
            return true; // Allow request on error
        }
    }
    
    /**
     * Get remaining requests for IP and action
     */
    public function getRemainingRequests($ip, $action = 'default') {
        if (!isset($this->limits[$action])) {
            $action = 'default';
        }
        
        $limit = $this->limits[$action];
        $key = "rate_limit_{$action}_{$ip}";
        
        try {
            $pdo = getDB();
            
            $stmt = $pdo->prepare("SELECT requests FROM rate_limits WHERE id = ?");
            $stmt->execute([$key]);
            $current = $stmt->fetchColumn();
            
            return max(0, $limit['requests'] - ($current ?: 0));
            
        } catch (Exception $e) {
            return $limit['requests'];
        }
    }
}

/**
 * Global security helper functions
 */
function applySecurityChecks($options = []) {
    return SecurityMiddleware::getInstance()->applySecurityChecks($options);
}

function blacklistIP($ip, $reason = '') {
    return SecurityMiddleware::getInstance()->blacklistIP($ip, $reason);
}

function checkRateLimit($action = 'default') {
    $ip = SecurityMiddleware::getInstance()->getClientIP();
    $rateLimiter = new RateLimiter();
    return $rateLimiter->checkLimit($ip, $action);
}
?>