<?php
/**
 * System Monitoring and Performance Tracking
 * Provides comprehensive system health monitoring and performance metrics
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

class SystemMonitor {
    private static $instance = null;
    private $db;
    private $startTime;
    private $metrics = [];
    
    private function __construct() {
        $this->db = getDB();
        $this->startTime = microtime(true);
        $this->createMonitoringTables();
        $this->startPerformanceTracking();
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
     * Create monitoring tables
     */
    private function createMonitoringTables() {
        try {
            // Performance metrics table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS performance_metrics (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    metric_type VARCHAR(50) NOT NULL,
                    metric_name VARCHAR(100) NOT NULL,
                    metric_value DECIMAL(10,4) NOT NULL,
                    unit VARCHAR(20) NOT NULL,
                    request_uri VARCHAR(500),
                    user_id INT NULL,
                    ip_address VARCHAR(45),
                    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_metric_type (metric_type),
                    INDEX idx_recorded_at (recorded_at),
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            ");
            
            // System health table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS system_health (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    cpu_usage DECIMAL(5,2),
                    memory_usage DECIMAL(5,2),
                    disk_usage DECIMAL(5,2),
                    database_connections INT,
                    active_sessions INT,
                    error_count INT DEFAULT 0,
                    response_time DECIMAL(8,4),
                    uptime_seconds BIGINT,
                    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_recorded_at (recorded_at)
                )
            ");
            
            // Alert thresholds table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS monitoring_alerts (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    alert_type VARCHAR(50) NOT NULL,
                    threshold_value DECIMAL(10,4) NOT NULL,
                    comparison_operator ENUM('>', '<', '>=', '<=', '=', '!=') DEFAULT '>',
                    is_enabled BOOLEAN DEFAULT TRUE,
                    notification_email VARCHAR(255),
                    last_triggered TIMESTAMP NULL,
                    trigger_count INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Insert default alert thresholds
            $this->db->exec("
                INSERT IGNORE INTO monitoring_alerts (alert_type, threshold_value, comparison_operator, notification_email) VALUES
                ('response_time', 2000, '>', '" . ADMIN_EMAIL . "'),
                ('memory_usage', 80, '>', '" . ADMIN_EMAIL . "'),
                ('disk_usage', 90, '>', '" . ADMIN_EMAIL . "'),
                ('error_rate', 10, '>', '" . ADMIN_EMAIL . "'),
                ('database_connections', 100, '>', '" . ADMIN_EMAIL . "')
            ");
            
        } catch (Exception $e) {
            logError("Failed to create monitoring tables: " . $e->getMessage());
        }
    }
    
    /**
     * Start performance tracking for current request
     */
    private function startPerformanceTracking() {
        $this->metrics['request_start'] = microtime(true);
        $this->metrics['memory_start'] = memory_get_usage(true);
        $this->metrics['queries_start'] = $this->getQueryCount();
    }
    
    /**
     * Record performance metric
     */
    public function recordMetric($type, $name, $value, $unit = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO performance_metrics (metric_type, metric_name, metric_value, unit, request_uri, user_id, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $type,
                $name,
                $value,
                $unit,
                $_SERVER['REQUEST_URI'] ?? '',
                getCurrentUserId(),
                $this->getClientIP()
            ]);
            
        } catch (Exception $e) {
            error_log("Failed to record metric: " . $e->getMessage());
        }
    }
    
    /**
     * Record system health snapshot
     */
    public function recordSystemHealth() {
        try {
            $health = $this->collectSystemHealth();
            
            $stmt = $this->db->prepare("
                INSERT INTO system_health (cpu_usage, memory_usage, disk_usage, database_connections, 
                                         active_sessions, error_count, response_time, uptime_seconds)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $health['cpu_usage'],
                $health['memory_usage'],
                $health['disk_usage'],
                $health['database_connections'],
                $health['active_sessions'],
                $health['error_count'],
                $health['response_time'],
                $health['uptime_seconds']
            ]);
            
            // Check for alerts
            $this->checkAlerts($health);
            
        } catch (Exception $e) {
            logError("Failed to record system health: " . $e->getMessage());
        }
    }
    
    /**
     * Collect current system health metrics
     */
    private function collectSystemHealth() {
        $health = [];
        
        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
        $health['memory_usage'] = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;
        
        // Disk usage
        $totalSpace = disk_total_space('.');
        $freeSpace = disk_free_space('.');
        $health['disk_usage'] = $totalSpace > 0 ? (($totalSpace - $freeSpace) / $totalSpace) * 100 : 0;
        
        // CPU usage (if available)
        $health['cpu_usage'] = $this->getCPUUsage();
        
        // Database connections
        $health['database_connections'] = $this->getDatabaseConnections();
        
        // Active sessions
        $health['active_sessions'] = $this->getActiveSessions();
        
        // Error count (last hour)
        $health['error_count'] = $this->getRecentErrorCount();
        
        // Average response time (last hour)
        $health['response_time'] = $this->getAverageResponseTime();
        
        // System uptime
        $health['uptime_seconds'] = $this->getSystemUptime();
        
        return $health;
    }
    
    /**
     * Get CPU usage percentage
     */
    private function getCPUUsage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] * 100; // Convert to percentage (approximate)
        }
        
        return null;
    }
    
    /**
     * Get database connection count
     */
    private function getDatabaseConnections() {
        try {
            $stmt = $this->db->query("SHOW STATUS LIKE 'Threads_connected'");
            $result = $stmt->fetch();
            return $result ? (int)$result['Value'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get active sessions count
     */
    private function getActiveSessions() {
        $sessionPath = session_save_path() ?: sys_get_temp_dir();
        $sessionFiles = glob($sessionPath . '/sess_*');
        return count($sessionFiles);
    }
    
    /**
     * Get recent error count
     */
    private function getRecentErrorCount() {
        try {
            $errorHandler = ErrorHandler::getInstance();
            $stats = $errorHandler->getErrorStatistics(1); // Last 24 hours
            return $stats['total_errors'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get average response time
     */
    private function getAverageResponseTime() {
        try {
            $stmt = $this->db->prepare("
                SELECT AVG(metric_value) 
                FROM performance_metrics 
                WHERE metric_type = 'response_time' 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute();
            return $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get system uptime
     */
    private function getSystemUptime() {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            return (float)explode(' ', $uptime)[0];
        }
        
        return null;
    }
    
    /**
     * Get query count (approximate)
     */
    private function getQueryCount() {
        try {
            $stmt = $this->db->query("SHOW STATUS LIKE 'Queries'");
            $result = $stmt->fetch();
            return $result ? (int)$result['Value'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Check alerts against thresholds
     */
    private function checkAlerts($health) {
        try {
            $stmt = $this->db->query("SELECT * FROM monitoring_alerts WHERE is_enabled = 1");
            
            while ($alert = $stmt->fetch()) {
                $value = $this->getHealthValue($health, $alert['alert_type']);
                
                if ($value !== null && $this->compareValues($value, $alert['threshold_value'], $alert['comparison_operator'])) {
                    $this->triggerAlert($alert, $value);
                }
            }
            
        } catch (Exception $e) {
            logError("Failed to check alerts: " . $e->getMessage());
        }
    }
    
    /**
     * Get health value by type
     */
    private function getHealthValue($health, $type) {
        $mapping = [
            'memory_usage' => $health['memory_usage'],
            'disk_usage' => $health['disk_usage'],
            'cpu_usage' => $health['cpu_usage'],
            'database_connections' => $health['database_connections'],
            'error_count' => $health['error_count'],
            'response_time' => $health['response_time']
        ];
        
        return $mapping[$type] ?? null;
    }
    
    /**
     * Compare values based on operator
     */
    private function compareValues($value1, $value2, $operator) {
        switch ($operator) {
            case '>': return $value1 > $value2;
            case '<': return $value1 < $value2;
            case '>=': return $value1 >= $value2;
            case '<=': return $value1 <= $value2;
            case '=': return $value1 == $value2;
            case '!=': return $value1 != $value2;
            default: return false;
        }
    }
    
    /**
     * Trigger alert
     */
    private function triggerAlert($alert, $currentValue) {
        try {
            // Check if alert was recently triggered (within last hour)
            $lastTriggered = strtotime($alert['last_triggered']);
            if ($lastTriggered && (time() - $lastTriggered) < 3600) {
                return; // Don't spam alerts
            }
            
            // Update alert record
            $stmt = $this->db->prepare("
                UPDATE monitoring_alerts 
                SET last_triggered = NOW(), trigger_count = trigger_count + 1 
                WHERE id = ?
            ");
            $stmt->execute([$alert['id']]);
            
            // Send notification
            $this->sendAlertNotification($alert, $currentValue);
            
            // Log alert
            logError("System alert triggered: {$alert['alert_type']}", 'PERFORMANCE_WARNING', [
                'alert_type' => $alert['alert_type'],
                'threshold' => $alert['threshold_value'],
                'current_value' => $currentValue,
                'operator' => $alert['comparison_operator']
            ]);
            
        } catch (Exception $e) {
            logError("Failed to trigger alert: " . $e->getMessage());
        }
    }
    
    /**
     * Send alert notification
     */
    private function sendAlertNotification($alert, $currentValue) {
        if (empty($alert['notification_email'])) {
            return;
        }
        
        $subject = "System Alert: {$alert['alert_type']} - " . SITE_NAME;
        
        $message = "System monitoring alert triggered:\n\n";
        $message .= "Alert Type: {$alert['alert_type']}\n";
        $message .= "Current Value: {$currentValue}\n";
        $message .= "Threshold: {$alert['comparison_operator']} {$alert['threshold_value']}\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "Please check the system monitoring dashboard for more details.";
        
        $headers = "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">";
        
        @mail($alert['notification_email'], $subject, $message, $headers);
    }
    
    /**
     * Track page load performance
     */
    public function trackPageLoad() {
        $endTime = microtime(true);
        $responseTime = ($endTime - $this->metrics['request_start']) * 1000; // Convert to milliseconds
        
        $memoryUsed = memory_get_usage(true) - $this->metrics['memory_start'];
        $queriesExecuted = $this->getQueryCount() - $this->metrics['queries_start'];
        
        // Record metrics
        $this->recordMetric('response_time', 'page_load', $responseTime, 'ms');
        $this->recordMetric('memory', 'page_memory', $memoryUsed, 'bytes');
        $this->recordMetric('database', 'queries_executed', $queriesExecuted, 'count');
        
        // Log slow pages
        if ($responseTime > 2000) { // 2 seconds threshold
            logPerformanceIssue("Slow page load detected", [
                'response_time' => $responseTime,
                'memory_used' => $memoryUsed,
                'queries' => $queriesExecuted,
                'uri' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
        }
    }
    
    /**
     * Get performance statistics
     */
    public function getPerformanceStats($hours = 24) {
        try {
            $stats = [];
            
            // Average response time
            $stmt = $this->db->prepare("
                SELECT AVG(metric_value) as avg_response_time
                FROM performance_metrics 
                WHERE metric_type = 'response_time' 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ");
            $stmt->execute([$hours]);
            $stats['avg_response_time'] = $stmt->fetchColumn() ?: 0;
            
            // Peak response time
            $stmt = $this->db->prepare("
                SELECT MAX(metric_value) as peak_response_time
                FROM performance_metrics 
                WHERE metric_type = 'response_time' 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ");
            $stmt->execute([$hours]);
            $stats['peak_response_time'] = $stmt->fetchColumn() ?: 0;
            
            // Total requests
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_requests
                FROM performance_metrics 
                WHERE metric_type = 'response_time' 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ");
            $stmt->execute([$hours]);
            $stats['total_requests'] = $stmt->fetchColumn() ?: 0;
            
            // Slow requests (>2 seconds)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as slow_requests
                FROM performance_metrics 
                WHERE metric_type = 'response_time' 
                AND metric_value > 2000
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ");
            $stmt->execute([$hours]);
            $stats['slow_requests'] = $stmt->fetchColumn() ?: 0;
            
            // Average memory usage
            $stmt = $this->db->prepare("
                SELECT AVG(metric_value) as avg_memory
                FROM performance_metrics 
                WHERE metric_type = 'memory' 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ");
            $stmt->execute([$hours]);
            $stats['avg_memory'] = $stmt->fetchColumn() ?: 0;
            
            // Database query statistics
            $stmt = $this->db->prepare("
                SELECT AVG(metric_value) as avg_queries, MAX(metric_value) as max_queries
                FROM performance_metrics 
                WHERE metric_type = 'database' 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            ");
            $stmt->execute([$hours]);
            $queryStats = $stmt->fetch();
            $stats['avg_queries'] = $queryStats['avg_queries'] ?: 0;
            $stats['max_queries'] = $queryStats['max_queries'] ?: 0;
            
            return $stats;
            
        } catch (Exception $e) {
            logError("Failed to get performance stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system health history
     */
    public function getHealthHistory($hours = 24) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM system_health 
                WHERE recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY recorded_at DESC
            ");
            $stmt->execute([$hours]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logError("Failed to get health history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean old monitoring data
     */
    public function cleanOldData($days = 30) {
        try {
            // Clean performance metrics
            $stmt = $this->db->prepare("
                DELETE FROM performance_metrics 
                WHERE recorded_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            
            // Clean system health data
            $stmt = $this->db->prepare("
                DELETE FROM system_health 
                WHERE recorded_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([$days]);
            
            logUserAction('monitoring_data_cleaned', ['days_retained' => $days]);
            
        } catch (Exception $e) {
            logError("Failed to clean monitoring data: " . $e->getMessage());
        }
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes($value) {
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
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
     * Destructor - track page load when request ends
     */
    public function __destruct() {
        if (isset($this->metrics['request_start'])) {
            $this->trackPageLoad();
        }
    }
}

/**
 * Global monitoring functions
 */
function recordPerformanceMetric($type, $name, $value, $unit = '') {
    return SystemMonitor::getInstance()->recordMetric($type, $name, $value, $unit);
}

function recordSystemHealth() {
    return SystemMonitor::getInstance()->recordSystemHealth();
}

function initializeSystemMonitoring() {
    return SystemMonitor::getInstance();
}
?>