<?php
/**
 * Security Settings Management
 * Admin interface for managing security configurations
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check_admin.php';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF()) {
        $message = 'Security token validation failed. Please try again.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_rate_limits':
                if (updateRateLimits($_POST)) {
                    $message = 'Rate limits updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update rate limits.';
                    $messageType = 'error';
                }
                break;
                
            case 'manage_ip_blacklist':
                if (manageIPBlacklist($_POST)) {
                    $message = 'IP blacklist updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update IP blacklist.';
                    $messageType = 'error';
                }
                break;
                
            case 'update_security_settings':
                if (updateSecuritySettings($_POST)) {
                    $message = 'Security settings updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update security settings.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get current settings
$rateLimits = getRateLimitSettings();
$ipBlacklist = getIPBlacklist();
$securitySettings = getSecuritySettings();
$securityStats = getSecurityStatistics();

/**
 * Update rate limit settings
 */
function updateRateLimits($data) {
    try {
        $pdo = getDB();
        
        $limits = [
            'default_requests' => (int)$data['default_requests'],
            'default_window' => (int)$data['default_window'],
            'login_requests' => (int)$data['login_requests'],
            'login_window' => (int)$data['login_window'],
            'api_requests' => (int)$data['api_requests'],
            'api_window' => (int)$data['api_window'],
            'upload_requests' => (int)$data['upload_requests'],
            'upload_window' => (int)$data['upload_window']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO security_config (config_key, config_value) 
            VALUES ('rate_limits', ?) 
            ON DUPLICATE KEY UPDATE config_value = ?, updated_at = NOW()
        ");
        
        $limitsJson = json_encode($limits);
        $stmt->execute([$limitsJson, $limitsJson]);
        
        logUserAction('rate_limits_updated', $limits);
        return true;
        
    } catch (Exception $e) {
        logError("Failed to update rate limits: " . $e->getMessage());
        return false;
    }
}

/**
 * Manage IP blacklist
 */
function manageIPBlacklist($data) {
    try {
        $action = $data['ip_action'] ?? '';
        $ip = trim($data['ip_address'] ?? '');
        $reason = trim($data['reason'] ?? '');
        
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        
        $security = SecurityMiddleware::getInstance();
        
        if ($action === 'add') {
            return $security->blacklistIP($ip, $reason);
        } elseif ($action === 'remove') {
            return $security->removeFromBlacklist($ip);
        }
        
        return false;
        
    } catch (Exception $e) {
        logError("Failed to manage IP blacklist: " . $e->getMessage());
        return false;
    }
}

/**
 * Update security settings
 */
function updateSecuritySettings($data) {
    try {
        $pdo = getDB();
        
        $settings = [
            'csrf_protection' => isset($data['csrf_protection']) ? 1 : 0,
            'xss_protection' => isset($data['xss_protection']) ? 1 : 0,
            'sql_injection_protection' => isset($data['sql_injection_protection']) ? 1 : 0,
            'file_upload_scanning' => isset($data['file_upload_scanning']) ? 1 : 0,
            'suspicious_activity_detection' => isset($data['suspicious_activity_detection']) ? 1 : 0,
            'auto_blacklist_enabled' => isset($data['auto_blacklist_enabled']) ? 1 : 0,
            'auto_blacklist_threshold' => (int)$data['auto_blacklist_threshold'],
            'security_notifications' => isset($data['security_notifications']) ? 1 : 0,
            'notification_email' => trim($data['notification_email'] ?? '')
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO security_config (config_key, config_value) 
            VALUES ('security_settings', ?) 
            ON DUPLICATE KEY UPDATE config_value = ?, updated_at = NOW()
        ");
        
        $settingsJson = json_encode($settings);
        $stmt->execute([$settingsJson, $settingsJson]);
        
        logUserAction('security_settings_updated', $settings);
        return true;
        
    } catch (Exception $e) {
        logError("Failed to update security settings: " . $e->getMessage());
        return false;
    }
}

/**
 * Get rate limit settings
 */
function getRateLimitSettings() {
    try {
        $pdo = getDB();
        
        $stmt = $pdo->prepare("SELECT config_value FROM security_config WHERE config_key = 'rate_limits'");
        $stmt->execute();
        $result = $stmt->fetchColumn();
        
        if ($result) {
            return json_decode($result, true);
        }
        
        // Default values
        return [
            'default_requests' => 100,
            'default_window' => 3600,
            'login_requests' => 5,
            'login_window' => 900,
            'api_requests' => 1000,
            'api_window' => 3600,
            'upload_requests' => 10,
            'upload_window' => 3600
        ];
        
    } catch (Exception $e) {
        logError("Failed to get rate limit settings: " . $e->getMessage());
        return [];
    }
}

/**
 * Get IP blacklist
 */
function getIPBlacklist() {
    try {
        $pdo = getDB();
        
        $stmt = $pdo->prepare("SELECT config_value FROM security_config WHERE config_key = 'ip_blacklist'");
        $stmt->execute();
        $result = $stmt->fetchColumn();
        
        return $result ? json_decode($result, true) : [];
        
    } catch (Exception $e) {
        logError("Failed to get IP blacklist: " . $e->getMessage());
        return [];
    }
}

/**
 * Get security settings
 */
function getSecuritySettings() {
    try {
        $pdo = getDB();
        
        $stmt = $pdo->prepare("SELECT config_value FROM security_config WHERE config_key = 'security_settings'");
        $stmt->execute();
        $result = $stmt->fetchColumn();
        
        if ($result) {
            return json_decode($result, true);
        }
        
        // Default values
        return [
            'csrf_protection' => 1,
            'xss_protection' => 1,
            'sql_injection_protection' => 1,
            'file_upload_scanning' => 1,
            'suspicious_activity_detection' => 1,
            'auto_blacklist_enabled' => 0,
            'auto_blacklist_threshold' => 10,
            'security_notifications' => 1,
            'notification_email' => ADMIN_EMAIL
        ];
        
    } catch (Exception $e) {
        logError("Failed to get security settings: " . $e->getMessage());
        return [];
    }
}

/**
 * Get security statistics
 */
function getSecurityStatistics() {
    try {
        $pdo = getDB();
        
        // Get blocked requests in last 24 hours
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notification_history 
            WHERE notification_type = 'security_breaches' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
        $blockedRequests = $stmt->fetchColumn();
        
        // Get rate limit violations
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM rate_limits 
            WHERE last_request >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
        $rateLimitViolations = $stmt->fetchColumn();
        
        // Get blacklisted IPs count
        $blacklist = getIPBlacklist();
        $blacklistedIPs = count($blacklist);
        
        return [
            'blocked_requests_24h' => $blockedRequests,
            'rate_limit_violations_24h' => $rateLimitViolations,
            'blacklisted_ips' => $blacklistedIPs,
            'last_security_scan' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        logError("Failed to get security statistics: " . $e->getMessage());
        return [
            'blocked_requests_24h' => 0,
            'rate_limit_violations_24h' => 0,
            'blacklisted_ips' => 0,
            'last_security_scan' => 'Unknown'
        ];
    }
}

// Page title
$pageTitle = 'Security Settings';
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
        .security-dashboard {
            padding: 20px;
        }
        
        .security-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .stat-card.warning {
            border-left-color: #f39c12;
        }
        
        .stat-card.danger {
            border-left-color: #e74c3c;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .settings-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .ip-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .ip-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .ip-item:last-child {
            border-bottom: none;
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
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-small {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d5f4e6;
            color: #27ae60;
            border: 1px solid #27ae60;
        }
        
        .alert-error {
            background: #fadbd8;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        @media (max-width: 768px) {
            .security-stats {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-shield-alt"></i> Security Settings</h1>
            </div>
            
            <div class="security-dashboard">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Security Statistics -->
                <div class="security-stats">
                    <div class="stat-card <?php echo $securityStats['blocked_requests_24h'] > 0 ? 'warning' : ''; ?>">
                        <div class="stat-value"><?php echo $securityStats['blocked_requests_24h']; ?></div>
                        <div class="stat-label">Blocked Requests (24h)</div>
                    </div>
                    
                    <div class="stat-card <?php echo $securityStats['rate_limit_violations_24h'] > 0 ? 'warning' : ''; ?>">
                        <div class="stat-value"><?php echo $securityStats['rate_limit_violations_24h']; ?></div>
                        <div class="stat-label">Rate Limit Violations (24h)</div>
                    </div>
                    
                    <div class="stat-card <?php echo $securityStats['blacklisted_ips'] > 0 ? 'danger' : ''; ?>">
                        <div class="stat-value"><?php echo $securityStats['blacklisted_ips']; ?></div>
                        <div class="stat-label">Blacklisted IPs</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><i class="fas fa-check-circle" style="color: #27ae60;"></i></div>
                        <div class="stat-label">Last Scan: <?php echo date('H:i'); ?></div>
                    </div>
                </div>
                
                <!-- Rate Limit Settings -->
                <div class="settings-section">
                    <h2 class="section-title">Rate Limit Settings</h2>
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update_rate_limits">
                        
                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <label>Default Requests per Hour</label>
                                    <input type="number" name="default_requests" value="<?php echo $rateLimits['default_requests']; ?>" min="1" max="10000">
                                </div>
                                
                                <div class="form-group">
                                    <label>Login Attempts per 15 minutes</label>
                                    <input type="number" name="login_requests" value="<?php echo $rateLimits['login_requests']; ?>" min="1" max="100">
                                </div>
                            </div>
                            
                            <div>
                                <div class="form-group">
                                    <label>API Requests per Hour</label>
                                    <input type="number" name="api_requests" value="<?php echo $rateLimits['api_requests']; ?>" min="1" max="50000">
                                </div>
                                
                                <div class="form-group">
                                    <label>File Uploads per Hour</label>
                                    <input type="number" name="upload_requests" value="<?php echo $rateLimits['upload_requests']; ?>" min="1" max="1000">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Rate Limits
                        </button>
                    </form>
                </div>
                
                <!-- IP Blacklist Management -->
                <div class="settings-section">
                    <h2 class="section-title">IP Blacklist Management</h2>
                    
                    <form method="POST" style="margin-bottom: 20px;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="manage_ip_blacklist">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>IP Address</label>
                                <input type="text" name="ip_address" placeholder="192.168.1.100" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Reason</label>
                                <input type="text" name="reason" placeholder="Suspicious activity">
                            </div>
                        </div>
                        
                        <div style="margin-top: 15px;">
                            <button type="submit" name="ip_action" value="add" class="btn btn-danger">
                                <i class="fas fa-ban"></i> Add to Blacklist
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!empty($ipBlacklist)): ?>
                        <div class="ip-list">
                            <?php foreach ($ipBlacklist as $ip): ?>
                                <div class="ip-item">
                                    <span><?php echo htmlspecialchars($ip); ?></span>
                                    <form method="POST" style="display: inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="manage_ip_blacklist">
                                        <input type="hidden" name="ip_address" value="<?php echo htmlspecialchars($ip); ?>">
                                        <button type="submit" name="ip_action" value="remove" class="btn btn-success btn-small">
                                            <i class="fas fa-check"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No IPs currently blacklisted.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Security Protection Settings -->
                <div class="settings-section">
                    <h2 class="section-title">Security Protection Settings</h2>
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update_security_settings">
                        
                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="csrf_protection" id="csrf_protection" 
                                               <?php echo $securitySettings['csrf_protection'] ? 'checked' : ''; ?>>
                                        <label for="csrf_protection">CSRF Protection</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="xss_protection" id="xss_protection" 
                                               <?php echo $securitySettings['xss_protection'] ? 'checked' : ''; ?>>
                                        <label for="xss_protection">XSS Protection</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="sql_injection_protection" id="sql_injection_protection" 
                                               <?php echo $securitySettings['sql_injection_protection'] ? 'checked' : ''; ?>>
                                        <label for="sql_injection_protection">SQL Injection Protection</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="file_upload_scanning" id="file_upload_scanning" 
                                               <?php echo $securitySettings['file_upload_scanning'] ? 'checked' : ''; ?>>
                                        <label for="file_upload_scanning">File Upload Scanning</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="suspicious_activity_detection" id="suspicious_activity_detection" 
                                               <?php echo $securitySettings['suspicious_activity_detection'] ? 'checked' : ''; ?>>
                                        <label for="suspicious_activity_detection">Suspicious Activity Detection</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="security_notifications" id="security_notifications" 
                                               <?php echo $securitySettings['security_notifications'] ? 'checked' : ''; ?>>
                                        <label for="security_notifications">Security Notifications</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Notification Email</label>
                            <input type="email" name="notification_email" 
                                   value="<?php echo htmlspecialchars($securitySettings['notification_email']); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Security Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>