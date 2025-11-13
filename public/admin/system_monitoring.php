<?php
/**
 * System Monitoring Dashboard
 * Provides real-time system health monitoring and error tracking
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../includes/ErrorHandler.php';
require_once '../../includes/auth_check_admin.php';

// Initialize error handler
initializeErrorHandler();

// Get monitoring data
$errorStats = ErrorHandler::getInstance()->getErrorStatistics(7);
$systemHealth = getSystemHealthMetrics();
$performanceMetrics = getPerformanceMetrics();
$securityAlerts = getSecurityAlerts();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'refresh_stats':
            echo json_encode([
                'error_stats' => ErrorHandler::getInstance()->getErrorStatistics(7),
                'system_health' => getSystemHealthMetrics(),
                'performance' => getPerformanceMetrics()
            ]);
            exit;
            
        case 'clear_logs':
            if (clearSystemLogs()) {
                echo json_encode(['success' => true, 'message' => 'Logs cleared successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to clear logs']);
            }
            exit;
            
        case 'download_logs':
            downloadSystemLogs($_GET['type'] ?? 'all');
            exit;
    }
}

/**
 * Get system health metrics
 */
function getSystemHealthMetrics() {
    $metrics = [];
    
    // Database connectivity
    try {
        $pdo = getDB();
        $pdo->query('SELECT 1');
        $metrics['database'] = ['status' => 'healthy', 'message' => 'Connected'];
    } catch (Exception $e) {
        $metrics['database'] = ['status' => 'error', 'message' => 'Connection failed'];
    }
    
    // Disk space
    $totalSpace = disk_total_space('.');
    $freeSpace = disk_free_space('.');
    $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
    
    $metrics['disk_space'] = [
        'status' => $usedPercent > 90 ? 'warning' : ($usedPercent > 95 ? 'error' : 'healthy'),
        'used_percent' => round($usedPercent, 1),
        'free_space' => formatFileSize($freeSpace),
        'total_space' => formatFileSize($totalSpace)
    ];
    
    // Memory usage
    $memoryUsage = memory_get_usage(true);
    $memoryLimit = ini_get('memory_limit');
    $memoryLimitBytes = convertToBytes($memoryLimit);
    $memoryPercent = ($memoryUsage / $memoryLimitBytes) * 100;
    
    $metrics['memory'] = [
        'status' => $memoryPercent > 80 ? 'warning' : ($memoryPercent > 90 ? 'error' : 'healthy'),
        'used_percent' => round($memoryPercent, 1),
        'used_memory' => formatFileSize($memoryUsage),
        'memory_limit' => $memoryLimit
    ];
    
    // Log file sizes
    $logPath = ERROR_LOG_PATH;
    $logFiles = ['system.log', 'database_errors.log', 'security/security.log'];
    $totalLogSize = 0;
    
    foreach ($logFiles as $file) {
        $filePath = $logPath . $file;
        if (file_exists($filePath)) {
            $totalLogSize += filesize($filePath);
        }
    }
    
    $metrics['log_files'] = [
        'status' => $totalLogSize > 50 * 1024 * 1024 ? 'warning' : 'healthy', // 50MB threshold
        'total_size' => formatFileSize($totalLogSize),
        'files_count' => count(glob($logPath . '*.log'))
    ];
    
    // Session storage
    $sessionPath = session_save_path() ?: sys_get_temp_dir();
    $sessionFiles = glob($sessionPath . '/sess_*');
    $activeSessions = count($sessionFiles);
    
    $metrics['sessions'] = [
        'status' => $activeSessions > 1000 ? 'warning' : 'healthy',
        'active_sessions' => $activeSessions,
        'session_path' => $sessionPath
    ];
    
    return $metrics;
}

/**
 * Get performance metrics
 */
function getPerformanceMetrics() {
    global $pdo;
    
    $metrics = [];
    
    // Database performance
    try {
        $start = microtime(true);
        $pdo->query('SELECT COUNT(*) FROM users');
        $dbResponseTime = (microtime(true) - $start) * 1000;
        
        $metrics['database_response'] = [
            'status' => $dbResponseTime > 1000 ? 'warning' : ($dbResponseTime > 2000 ? 'error' : 'healthy'),
            'response_time' => round($dbResponseTime, 2) . 'ms'
        ];
    } catch (Exception $e) {
        $metrics['database_response'] = [
            'status' => 'error',
            'response_time' => 'N/A'
        ];
    }
    
    // PHP performance
    $metrics['php_version'] = [
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'healthy' : 'warning',
        'version' => PHP_VERSION
    ];
    
    // Server load (if available)
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        $metrics['server_load'] = [
            'status' => $load[0] > 2.0 ? 'warning' : ($load[0] > 4.0 ? 'error' : 'healthy'),
            'load_1min' => round($load[0], 2),
            'load_5min' => round($load[1], 2),
            'load_15min' => round($load[2], 2)
        ];
    }
    
    return $metrics;
}

/**
 * Get security alerts
 */
function getSecurityAlerts() {
    $alerts = [];
    $logPath = ERROR_LOG_PATH . 'security/security.log';
    
    if (file_exists($logPath)) {
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $recentLines = array_slice($lines, -10);
        
        foreach ($recentLines as $line) {
            if (preg_match('/\[(.*?)\] \[(.*?)\] (.*)/', $line, $matches)) {
                $alerts[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'message' => $matches[3]
                ];
            }
        }
    }
    
    return array_reverse($alerts);
}

/**
 * Convert memory limit string to bytes
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
 * Clear system logs
 */
function clearSystemLogs() {
    try {
        $logPath = ERROR_LOG_PATH;
        $logFiles = [
            'system.log',
            'database_errors.log',
            'php_errors.log',
            'security/security.log',
            'performance/performance.log',
            'audit/audit.log'
        ];
        
        foreach ($logFiles as $file) {
            $filePath = $logPath . $file;
            if (file_exists($filePath)) {
                // Backup before clearing
                $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
                copy($filePath, $backupPath);
                
                // Clear the file
                file_put_contents($filePath, '');
            }
        }
        
        logUserAction('system_logs_cleared', ['admin_id' => getCurrentUserId()]);
        return true;
        
    } catch (Exception $e) {
        logError("Failed to clear system logs: " . $e->getMessage());
        return false;
    }
}

/**
 * Download system logs
 */
function downloadSystemLogs($type = 'all') {
    $logPath = ERROR_LOG_PATH;
    $timestamp = date('Y-m-d_H-i-s');
    
    if ($type === 'all') {
        // Create ZIP archive of all logs
        $zipFile = sys_get_temp_dir() . "/system_logs_{$timestamp}.zip";
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $logFiles = glob($logPath . '*.log');
            $logFiles = array_merge($logFiles, glob($logPath . '*/*.log'));
            
            foreach ($logFiles as $file) {
                $relativePath = str_replace($logPath, '', $file);
                $zip->addFile($file, $relativePath);
            }
            
            $zip->close();
            
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="system_logs_' . $timestamp . '.zip"');
            header('Content-Length: ' . filesize($zipFile));
            
            readfile($zipFile);
            unlink($zipFile);
        }
    } else {
        // Download specific log file
        $logFile = $logPath . $type . '.log';
        
        if (file_exists($logFile)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $type . '_' . $timestamp . '.log"');
            header('Content-Length: ' . filesize($logFile));
            
            readfile($logFile);
        }
    }
    
    logUserAction('system_logs_downloaded', [
        'admin_id' => getCurrentUserId(),
        'log_type' => $type
    ]);
}

// Page title
$pageTitle = 'System Monitoring';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="../../css/portal.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .monitoring-dashboard {
            padding: 20px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .metric-card.healthy {
            border-left-color: #27ae60;
        }
        
        .metric-card.warning {
            border-left-color: #f39c12;
        }
        
        .metric-card.error {
            border-left-color: #e74c3c;
        }
        
        .metric-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .metric-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .metric-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-healthy {
            background: #d5f4e6;
            color: #27ae60;
        }
        
        .status-warning {
            background: #fef9e7;
            color: #f39c12;
        }
        
        .status-error {
            background: #fadbd8;
            color: #e74c3c;
        }
        
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .metric-details {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .error-log-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .log-entry {
            padding: 10px;
            border-left: 3px solid #3498db;
            margin-bottom: 10px;
            background: #f8f9fa;
            font-family: monospace;
            font-size: 13px;
        }
        
        .log-entry.error {
            border-left-color: #e74c3c;
        }
        
        .log-entry.warning {
            border-left-color: #f39c12;
        }
        
        .log-timestamp {
            color: #7f8c8d;
            font-weight: 600;
        }
        
        .log-level {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            margin: 0 5px;
        }
        
        .level-error {
            background: #e74c3c;
            color: white;
        }
        
        .level-warning {
            background: #f39c12;
            color: white;
        }
        
        .level-info {
            background: #3498db;
            color: white;
        }
        
        .actions-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .auto-refresh {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        
        .refresh-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #27ae60;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .chart-container {
            height: 200px;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-heartbeat"></i> System Monitoring</h1>
                <div class="auto-refresh">
                    <span>Auto-refresh</span>
                    <div class="refresh-indicator"></div>
                </div>
            </div>
            
            <div class="monitoring-dashboard">
                <div class="actions-bar">
                    <button class="btn btn-primary" onclick="refreshStats()">
                        <i class="fas fa-sync-alt"></i> Refresh Now
                    </button>
                    <a href="?action=download_logs&type=all" class="btn btn-success">
                        <i class="fas fa-download"></i> Download All Logs
                    </a>
                    <button class="btn btn-warning" onclick="clearLogs()">
                        <i class="fas fa-trash"></i> Clear Logs
                    </button>
                    <a href="error_reports.php" class="btn btn-primary">
                        <i class="fas fa-chart-line"></i> Error Reports
                    </a>
                </div>
                
                <!-- System Health Metrics -->
                <div class="metrics-grid">
                    <!-- Database Health -->
                    <div class="metric-card <?php echo $systemHealth['database']['status']; ?>">
                        <div class="metric-header">
                            <div class="metric-title">
                                <i class="fas fa-database"></i> Database
                            </div>
                            <span class="metric-status status-<?php echo $systemHealth['database']['status']; ?>">
                                <?php echo $systemHealth['database']['status']; ?>
                            </span>
                        </div>
                        <div class="metric-details">
                            <?php echo $systemHealth['database']['message']; ?>
                        </div>
                    </div>
                    
                    <!-- Disk Space -->
                    <div class="metric-card <?php echo $systemHealth['disk_space']['status']; ?>">
                        <div class="metric-header">
                            <div class="metric-title">
                                <i class="fas fa-hdd"></i> Disk Space
                            </div>
                            <span class="metric-status status-<?php echo $systemHealth['disk_space']['status']; ?>">
                                <?php echo $systemHealth['disk_space']['status']; ?>
                            </span>
                        </div>
                        <div class="metric-value"><?php echo $systemHealth['disk_space']['used_percent']; ?>%</div>
                        <div class="metric-details">
                            Free: <?php echo $systemHealth['disk_space']['free_space']; ?> / 
                            Total: <?php echo $systemHealth['disk_space']['total_space']; ?>
                        </div>
                    </div>
                    
                    <!-- Memory Usage -->
                    <div class="metric-card <?php echo $systemHealth['memory']['status']; ?>">
                        <div class="metric-header">
                            <div class="metric-title">
                                <i class="fas fa-memory"></i> Memory
                            </div>
                            <span class="metric-status status-<?php echo $systemHealth['memory']['status']; ?>">
                                <?php echo $systemHealth['memory']['status']; ?>
                            </span>
                        </div>
                        <div class="metric-value"><?php echo $systemHealth['memory']['used_percent']; ?>%</div>
                        <div class="metric-details">
                            Used: <?php echo $systemHealth['memory']['used_memory']; ?> / 
                            Limit: <?php echo $systemHealth['memory']['memory_limit']; ?>
                        </div>
                    </div>
                    
                    <!-- Error Statistics -->
                    <div class="metric-card <?php echo $errorStats['critical_errors'] > 0 ? 'error' : 'healthy'; ?>">
                        <div class="metric-header">
                            <div class="metric-title">
                                <i class="fas fa-exclamation-triangle"></i> Errors (7 days)
                            </div>
                            <span class="metric-status status-<?php echo $errorStats['critical_errors'] > 0 ? 'error' : 'healthy'; ?>">
                                <?php echo $errorStats['critical_errors'] > 0 ? 'critical' : 'healthy'; ?>
                            </span>
                        </div>
                        <div class="metric-value"><?php echo $errorStats['total_errors']; ?></div>
                        <div class="metric-details">
                            Critical: <?php echo $errorStats['critical_errors']; ?>
                        </div>
                    </div>
                    
                    <!-- Active Sessions -->
                    <div class="metric-card <?php echo $systemHealth['sessions']['status']; ?>">
                        <div class="metric-header">
                            <div class="metric-title">
                                <i class="fas fa-users"></i> Active Sessions
                            </div>
                            <span class="metric-status status-<?php echo $systemHealth['sessions']['status']; ?>">
                                <?php echo $systemHealth['sessions']['status']; ?>
                            </span>
                        </div>
                        <div class="metric-value"><?php echo $systemHealth['sessions']['active_sessions']; ?></div>
                        <div class="metric-details">
                            Session storage: <?php echo basename($systemHealth['sessions']['session_path']); ?>
                        </div>
                    </div>
                    
                    <!-- Log Files -->
                    <div class="metric-card <?php echo $systemHealth['log_files']['status']; ?>">
                        <div class="metric-header">
                            <div class="metric-title">
                                <i class="fas fa-file-alt"></i> Log Files
                            </div>
                            <span class="metric-status status-<?php echo $systemHealth['log_files']['status']; ?>">
                                <?php echo $systemHealth['log_files']['status']; ?>
                            </span>
                        </div>
                        <div class="metric-value"><?php echo $systemHealth['log_files']['total_size']; ?></div>
                        <div class="metric-details">
                            Files: <?php echo $systemHealth['log_files']['files_count']; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Errors -->
                <div class="error-log-section">
                    <h3><i class="fas fa-list"></i> Recent Errors</h3>
                    <div id="recent-errors">
                        <?php if (empty($errorStats['recent_errors'])): ?>
                            <p class="text-muted">No recent errors found.</p>
                        <?php else: ?>
                            <?php foreach ($errorStats['recent_errors'] as $error): ?>
                                <div class="log-entry <?php echo strtolower($error['level']); ?>">
                                    <span class="log-timestamp"><?php echo $error['timestamp']; ?></span>
                                    <span class="log-level level-<?php echo strtolower($error['level']); ?>">
                                        <?php echo $error['level']; ?>
                                    </span>
                                    <span class="log-message"><?php echo htmlspecialchars($error['message']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Security Alerts -->
                <?php if (!empty($securityAlerts)): ?>
                <div class="error-log-section">
                    <h3><i class="fas fa-shield-alt"></i> Security Alerts</h3>
                    <div id="security-alerts">
                        <?php foreach ($securityAlerts as $alert): ?>
                            <div class="log-entry error">
                                <span class="log-timestamp"><?php echo $alert['timestamp']; ?></span>
                                <span class="log-level level-error">SECURITY</span>
                                <span class="log-message"><?php echo htmlspecialchars($alert['message']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        setInterval(refreshStats, 30000);
        
        function refreshStats() {
            fetch('?action=refresh_stats')
                .then(response => response.json())
                .then(data => {
                    // Update metrics would go here
                    console.log('Stats refreshed', data);
                    location.reload(); // Simple refresh for now
                })
                .catch(error => {
                    console.error('Error refreshing stats:', error);
                });
        }
        
        function clearLogs() {
            if (confirm('Are you sure you want to clear all system logs? This action cannot be undone.')) {
                fetch('?action=clear_logs')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Logs cleared successfully');
                            location.reload();
                        } else {
                            alert('Failed to clear logs: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error clearing logs:', error);
                        alert('Error clearing logs');
                    });
            }
        }
    </script>
</body>
</html>