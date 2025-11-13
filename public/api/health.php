<?php
/**
 * System Health Check API Endpoint
 * Provides automated health check capabilities for monitoring systems
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Initialize monitoring
initializeSystemMonitoring();

// Get health check type
$checkType = $_GET['type'] ?? 'basic';
$format = $_GET['format'] ?? 'json';

try {
    switch ($checkType) {
        case 'basic':
            $result = performBasicHealthCheck();
            break;
            
        case 'detailed':
            $result = performDetailedHealthCheck();
            break;
            
        case 'database':
            $result = performDatabaseHealthCheck();
            break;
            
        case 'security':
            $result = performSecurityHealthCheck();
            break;
            
        case 'performance':
            $result = performPerformanceHealthCheck();
            break;
            
        default:
            $result = [
                'status' => 'error',
                'message' => 'Invalid health check type',
                'timestamp' => date('c')
            ];
    }
    
    // Add common metadata
    $result['timestamp'] = date('c');
    $result['server'] = $_SERVER['HTTP_HOST'] ?? 'unknown';
    $result['version'] = '1.0.0';
    
    // Output result
    if ($format === 'xml') {
        outputXML($result);
    } else {
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Health check failed',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Internal server error',
        'timestamp' => date('c')
    ]);
}

/**
 * Perform basic health check
 */
function performBasicHealthCheck() {
    $checks = [];
    $overallStatus = 'healthy';
    
    // PHP version check
    $checks['php'] = [
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'healthy' : 'warning',
        'version' => PHP_VERSION,
        'message' => 'PHP version check'
    ];
    
    // Database connectivity
    try {
        $pdo = getDB();
        $pdo->query('SELECT 1');
        $checks['database'] = [
            'status' => 'healthy',
            'message' => 'Database connection successful'
        ];
    } catch (Exception $e) {
        $checks['database'] = [
            'status' => 'critical',
            'message' => 'Database connection failed'
        ];
        $overallStatus = 'critical';
    }
    
    // File system write test
    $testFile = sys_get_temp_dir() . '/health_check_' . time() . '.tmp';
    if (file_put_contents($testFile, 'test') !== false) {
        unlink($testFile);
        $checks['filesystem'] = [
            'status' => 'healthy',
            'message' => 'File system writable'
        ];
    } else {
        $checks['filesystem'] = [
            'status' => 'warning',
            'message' => 'File system write test failed'
        ];
        if ($overallStatus === 'healthy') {
            $overallStatus = 'warning';
        }
    }
    
    // Memory usage check
    $memoryUsage = memory_get_usage(true);
    $memoryLimit = convertToBytes(ini_get('memory_limit'));
    $memoryPercent = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;
    
    $memoryStatus = 'healthy';
    if ($memoryPercent > 90) {
        $memoryStatus = 'critical';
        $overallStatus = 'critical';
    } elseif ($memoryPercent > 80) {
        $memoryStatus = 'warning';
        if ($overallStatus === 'healthy') {
            $overallStatus = 'warning';
        }
    }
    
    $checks['memory'] = [
        'status' => $memoryStatus,
        'usage_percent' => round($memoryPercent, 2),
        'usage_bytes' => $memoryUsage,
        'limit_bytes' => $memoryLimit,
        'message' => 'Memory usage check'
    ];
    
    return [
        'status' => $overallStatus,
        'checks' => $checks,
        'summary' => generateHealthSummary($checks)
    ];
}

/**
 * Perform detailed health check
 */
function performDetailedHealthCheck() {
    $basic = performBasicHealthCheck();
    $detailed = $basic;
    
    // Disk space check
    $totalSpace = disk_total_space('.');
    $freeSpace = disk_free_space('.');
    $usedPercent = $totalSpace > 0 ? (($totalSpace - $freeSpace) / $totalSpace) * 100 : 0;
    
    $diskStatus = 'healthy';
    if ($usedPercent > 95) {
        $diskStatus = 'critical';
    } elseif ($usedPercent > 90) {
        $diskStatus = 'warning';
    }
    
    $detailed['checks']['disk_space'] = [
        'status' => $diskStatus,
        'used_percent' => round($usedPercent, 2),
        'free_bytes' => $freeSpace,
        'total_bytes' => $totalSpace,
        'message' => 'Disk space check'
    ];
    
    // Session storage check
    $sessionPath = session_save_path() ?: sys_get_temp_dir();
    $sessionWritable = is_writable($sessionPath);
    
    $detailed['checks']['sessions'] = [
        'status' => $sessionWritable ? 'healthy' : 'warning',
        'path' => $sessionPath,
        'writable' => $sessionWritable,
        'message' => 'Session storage check'
    ];
    
    // Log directory check
    $logPath = ERROR_LOG_PATH;
    $logWritable = is_writable($logPath);
    $logSize = 0;
    
    if (file_exists($logPath . 'system.log')) {
        $logSize = filesize($logPath . 'system.log');
    }
    
    $detailed['checks']['logging'] = [
        'status' => $logWritable ? 'healthy' : 'warning',
        'path' => $logPath,
        'writable' => $logWritable,
        'log_size' => $logSize,
        'message' => 'Logging system check'
    ];
    
    // Extension checks
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
    $extensionStatus = 'healthy';
    $extensions = [];
    
    foreach ($requiredExtensions as $ext) {
        $loaded = extension_loaded($ext);
        $extensions[$ext] = $loaded;
        
        if (!$loaded) {
            $extensionStatus = 'warning';
        }
    }
    
    $detailed['checks']['extensions'] = [
        'status' => $extensionStatus,
        'extensions' => $extensions,
        'message' => 'PHP extensions check'
    ];
    
    // Update overall status
    $detailed['status'] = calculateOverallStatus($detailed['checks']);
    $detailed['summary'] = generateHealthSummary($detailed['checks']);
    
    return $detailed;
}

/**
 * Perform database health check
 */
function performDatabaseHealthCheck() {
    $checks = [];
    $overallStatus = 'healthy';
    
    try {
        $pdo = getDB();
        
        // Connection test
        $start = microtime(true);
        $pdo->query('SELECT 1');
        $connectionTime = (microtime(true) - $start) * 1000;
        
        $connectionStatus = 'healthy';
        if ($connectionTime > 1000) {
            $connectionStatus = 'warning';
        } elseif ($connectionTime > 2000) {
            $connectionStatus = 'critical';
        }
        
        $checks['connection'] = [
            'status' => $connectionStatus,
            'response_time_ms' => round($connectionTime, 2),
            'message' => 'Database connection test'
        ];
        
        // Table existence check
        $requiredTables = ['users', 'institutions', 'students', 'enquiries'];
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            try {
                $pdo->query("SELECT 1 FROM {$table} LIMIT 1");
            } catch (Exception $e) {
                $missingTables[] = $table;
            }
        }
        
        $checks['tables'] = [
            'status' => empty($missingTables) ? 'healthy' : 'critical',
            'required_tables' => $requiredTables,
            'missing_tables' => $missingTables,
            'message' => 'Required tables check'
        ];
        
        if (!empty($missingTables)) {
            $overallStatus = 'critical';
        }
        
        // Database size check
        $stmt = $pdo->query("
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = '" . DB_NAME . "'
        ");
        $dbSize = $stmt->fetchColumn();
        
        $checks['size'] = [
            'status' => 'healthy',
            'size_mb' => $dbSize,
            'message' => 'Database size check'
        ];
        
        // Performance metrics
        $stmt = $pdo->query("SHOW STATUS LIKE 'Threads_connected'");
        $connections = $stmt->fetch();
        
        $stmt = $pdo->query("SHOW STATUS LIKE 'Queries'");
        $queries = $stmt->fetch();
        
        $checks['performance'] = [
            'status' => 'healthy',
            'connections' => $connections ? (int)$connections['Value'] : 0,
            'total_queries' => $queries ? (int)$queries['Value'] : 0,
            'message' => 'Database performance metrics'
        ];
        
    } catch (Exception $e) {
        $checks['connection'] = [
            'status' => 'critical',
            'error' => DEBUG_MODE ? $e->getMessage() : 'Connection failed',
            'message' => 'Database connection failed'
        ];
        $overallStatus = 'critical';
    }
    
    return [
        'status' => $overallStatus,
        'checks' => $checks,
        'summary' => generateHealthSummary($checks)
    ];
}

/**
 * Perform security health check
 */
function performSecurityHealthCheck() {
    $checks = [];
    $overallStatus = 'healthy';
    
    // HTTPS check
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    $checks['https'] = [
        'status' => $isHttps ? 'healthy' : 'warning',
        'enabled' => $isHttps,
        'message' => 'HTTPS encryption check'
    ];
    
    // Security headers check
    $securityHeaders = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block'
    ];
    
    $headerStatus = 'healthy';
    $headerResults = [];
    
    foreach ($securityHeaders as $header => $expectedValue) {
        $headerResults[$header] = [
            'present' => false,
            'value' => null
        ];
        
        // Note: We can't check response headers in this context
        // This would need to be done by an external monitoring system
    }
    
    $checks['security_headers'] = [
        'status' => $headerStatus,
        'headers' => $headerResults,
        'message' => 'Security headers check'
    ];
    
    // File permissions check
    $criticalPaths = [
        'includes/config.php',
        'includes/db_connect.php',
        '.htaccess'
    ];
    
    $permissionStatus = 'healthy';
    $permissionResults = [];
    
    foreach ($criticalPaths as $path) {
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath)) {
            $perms = fileperms($fullPath);
            $permissionResults[$path] = [
                'permissions' => substr(sprintf('%o', $perms), -4),
                'readable' => is_readable($fullPath),
                'writable' => is_writable($fullPath)
            ];
        }
    }
    
    $checks['file_permissions'] = [
        'status' => $permissionStatus,
        'files' => $permissionResults,
        'message' => 'File permissions check'
    ];
    
    // Recent security events
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notification_history 
            WHERE notification_type = 'security_breaches' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
        $securityEvents = $stmt->fetchColumn();
        
        $eventStatus = 'healthy';
        if ($securityEvents > 10) {
            $eventStatus = 'warning';
        } elseif ($securityEvents > 50) {
            $eventStatus = 'critical';
        }
        
        $checks['security_events'] = [
            'status' => $eventStatus,
            'events_24h' => $securityEvents,
            'message' => 'Recent security events'
        ];
        
    } catch (Exception $e) {
        $checks['security_events'] = [
            'status' => 'warning',
            'error' => 'Could not check security events',
            'message' => 'Security events check failed'
        ];
    }
    
    return [
        'status' => calculateOverallStatus($checks),
        'checks' => $checks,
        'summary' => generateHealthSummary($checks)
    ];
}

/**
 * Perform performance health check
 */
function performPerformanceHealthCheck() {
    $checks = [];
    
    // Response time test
    $start = microtime(true);
    
    // Simulate some work
    for ($i = 0; $i < 1000; $i++) {
        $dummy = md5($i);
    }
    
    $responseTime = (microtime(true) - $start) * 1000;
    
    $responseStatus = 'healthy';
    if ($responseTime > 100) {
        $responseStatus = 'warning';
    } elseif ($responseTime > 500) {
        $responseStatus = 'critical';
    }
    
    $checks['response_time'] = [
        'status' => $responseStatus,
        'time_ms' => round($responseTime, 2),
        'message' => 'Response time test'
    ];
    
    // Memory usage
    $memoryUsage = memory_get_usage(true);
    $memoryPeak = memory_get_peak_usage(true);
    $memoryLimit = convertToBytes(ini_get('memory_limit'));
    
    $memoryPercent = $memoryLimit > 0 ? ($memoryPeak / $memoryLimit) * 100 : 0;
    
    $memoryStatus = 'healthy';
    if ($memoryPercent > 90) {
        $memoryStatus = 'critical';
    } elseif ($memoryPercent > 80) {
        $memoryStatus = 'warning';
    }
    
    $checks['memory_performance'] = [
        'status' => $memoryStatus,
        'current_usage' => $memoryUsage,
        'peak_usage' => $memoryPeak,
        'limit' => $memoryLimit,
        'usage_percent' => round($memoryPercent, 2),
        'message' => 'Memory performance check'
    ];
    
    // Database performance
    try {
        $pdo = getDB();
        
        $start = microtime(true);
        $pdo->query('SELECT COUNT(*) FROM users');
        $dbTime = (microtime(true) - $start) * 1000;
        
        $dbStatus = 'healthy';
        if ($dbTime > 100) {
            $dbStatus = 'warning';
        } elseif ($dbTime > 500) {
            $dbStatus = 'critical';
        }
        
        $checks['database_performance'] = [
            'status' => $dbStatus,
            'query_time_ms' => round($dbTime, 2),
            'message' => 'Database performance test'
        ];
        
    } catch (Exception $e) {
        $checks['database_performance'] = [
            'status' => 'critical',
            'error' => 'Database query failed',
            'message' => 'Database performance test failed'
        ];
    }
    
    return [
        'status' => calculateOverallStatus($checks),
        'checks' => $checks,
        'summary' => generateHealthSummary($checks)
    ];
}

/**
 * Calculate overall status from individual checks
 */
function calculateOverallStatus($checks) {
    $hasCritical = false;
    $hasWarning = false;
    
    foreach ($checks as $check) {
        if ($check['status'] === 'critical') {
            $hasCritical = true;
        } elseif ($check['status'] === 'warning') {
            $hasWarning = true;
        }
    }
    
    if ($hasCritical) {
        return 'critical';
    } elseif ($hasWarning) {
        return 'warning';
    } else {
        return 'healthy';
    }
}

/**
 * Generate health summary
 */
function generateHealthSummary($checks) {
    $total = count($checks);
    $healthy = 0;
    $warning = 0;
    $critical = 0;
    
    foreach ($checks as $check) {
        switch ($check['status']) {
            case 'healthy':
                $healthy++;
                break;
            case 'warning':
                $warning++;
                break;
            case 'critical':
                $critical++;
                break;
        }
    }
    
    return [
        'total_checks' => $total,
        'healthy' => $healthy,
        'warning' => $warning,
        'critical' => $critical,
        'health_percentage' => $total > 0 ? round(($healthy / $total) * 100, 2) : 0
    ];
}

/**
 * Convert memory limit to bytes
 */
function convertToBytes($value) {
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
 * Output XML format
 */
function outputXML($data) {
    header('Content-Type: application/xml');
    
    $xml = new SimpleXMLElement('<health_check/>');
    arrayToXML($data, $xml);
    
    echo $xml->asXML();
}

/**
 * Convert array to XML
 */
function arrayToXML($array, &$xml) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $subnode = $xml->addChild($key);
            arrayToXML($value, $subnode);
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
}
?>