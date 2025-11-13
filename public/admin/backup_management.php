<?php
/**
 * Backup Management Interface
 * Admin interface for managing database backups and system recovery
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../includes/BackupManager.php';
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
            case 'create_backup':
                $result = BackupManager::getInstance()->createBackup('manual');
                if ($result['success']) {
                    $message = 'Backup created successfully: ' . $result['filename'];
                    $messageType = 'success';
                } else {
                    $message = 'Backup failed: ' . $result['error'];
                    $messageType = 'error';
                }
                break;
                
            case 'restore_backup':
                $backupId = (int)$_POST['backup_id'];
                $result = BackupManager::getInstance()->restoreBackup($backupId);
                if ($result['success']) {
                    $message = 'Database restored successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Restore failed: ' . $result['error'];
                    $messageType = 'error';
                }
                break;
                
            case 'update_config':
                $config = [
                    'auto_backup_enabled' => isset($_POST['auto_backup_enabled']) ? '1' : '0',
                    'daily_backup_time' => $_POST['daily_backup_time'],
                    'retention_daily' => (int)$_POST['retention_daily'],
                    'retention_weekly' => (int)$_POST['retention_weekly'],
                    'retention_monthly' => (int)$_POST['retention_monthly'],
                    'compression_enabled' => isset($_POST['compression_enabled']) ? '1' : '0',
                    'notification_email' => $_POST['notification_email']
                ];
                
                if (BackupManager::getInstance()->updateConfig($config)) {
                    $message = 'Backup configuration updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update backup configuration.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'verify_backup':
            $backupId = (int)$_GET['backup_id'];
            $result = BackupManager::getInstance()->verifyBackup($backupId);
            echo json_encode($result);
            exit;
            
        case 'download_backup':
            $backupId = (int)$_GET['backup_id'];
            downloadBackup($backupId);
            exit;
    }
}

// Get backup data
$backupManager = BackupManager::getInstance();
$backupHistory = $backupManager->getBackupHistory(50);
$backupStats = $backupManager->getBackupStatistics();
$backupConfig = getBackupConfig();

/**
 * Get backup configuration
 */
function getBackupConfig() {
    try {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT config_key, config_value FROM backup_config WHERE is_active = 1");
        
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['config_key']] = $row['config_value'];
        }
        
        return $config;
        
    } catch (Exception $e) {
        return [
            'auto_backup_enabled' => '1',
            'daily_backup_time' => '02:00',
            'retention_daily' => '7',
            'retention_weekly' => '4',
            'retention_monthly' => '12',
            'compression_enabled' => '1',
            'notification_email' => ADMIN_EMAIL
        ];
    }
}

/**
 * Download backup file
 */
function downloadBackup($backupId) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM backup_history WHERE id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch();
        
        if (!$backup || !file_exists($backup['filepath'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Backup file not found']);
            return;
        }
        
        $filename = $backup['filename'];
        $filepath = $backup['filepath'];
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        
        logUserAction('backup_downloaded', [
            'backup_id' => $backupId,
            'filename' => $filename
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Download failed']);
    }
}

// Page title
$pageTitle = 'Backup Management';
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
        .backup-dashboard {
            padding: 20px;
        }
        
        .backup-stats {
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
        
        .backup-section {
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
        
        .backup-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .backup-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .backup-table th,
        .backup-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .backup-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .backup-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-completed {
            background: #d5f4e6;
            color: #27ae60;
        }
        
        .status-failed {
            background: #fadbd8;
            color: #e74c3c;
        }
        
        .backup-type {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .type-manual {
            background: #3498db;
            color: white;
        }
        
        .type-daily {
            background: #27ae60;
            color: white;
        }
        
        .type-weekly {
            background: #f39c12;
            color: white;
        }
        
        .type-monthly {
            background: #9b59b6;
            color: white;
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
        
        .btn-small {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .btn:hover {
            opacity: 0.9;
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
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: #3498db;
            transition: width 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .backup-stats {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .backup-actions {
                flex-direction: column;
            }
            
            .backup-table {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-database"></i> Backup Management</h1>
            </div>
            
            <div class="backup-dashboard">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Backup Statistics -->
                <div class="backup-stats">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $backupStats['total_backups']; ?></div>
                        <div class="stat-label">Total Backups</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo formatFileSize($backupStats['total_size']); ?></div>
                        <div class="stat-label">Total Size</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php echo $backupStats['last_backup'] ? date('M j, H:i', strtotime($backupStats['last_backup']['created_at'])) : 'Never'; ?>
                        </div>
                        <div class="stat-label">Last Backup</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value">
                            <i class="fas fa-check-circle" style="color: #27ae60;"></i>
                        </div>
                        <div class="stat-label">System Status</div>
                    </div>
                </div>
                
                <!-- Backup Actions -->
                <div class="backup-section">
                    <h2 class="section-title">Backup Actions</h2>
                    
                    <div class="backup-actions">
                        <form method="POST" style="display: inline;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="create_backup">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Manual Backup
                            </button>
                        </form>
                        
                        <a href="?action=download_backup&backup_id=latest" class="btn btn-success">
                            <i class="fas fa-download"></i> Download Latest
                        </a>
                        
                        <a href="../system_monitoring.php" class="btn btn-warning">
                            <i class="fas fa-chart-line"></i> System Health
                        </a>
                    </div>
                </div>
                
                <!-- Backup Configuration -->
                <div class="backup-section">
                    <h2 class="section-title">Backup Configuration</h2>
                    
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update_config">
                        
                        <div class="form-grid">
                            <div>
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="auto_backup_enabled" id="auto_backup_enabled" 
                                               <?php echo $backupConfig['auto_backup_enabled'] === '1' ? 'checked' : ''; ?>>
                                        <label for="auto_backup_enabled">Enable Automatic Backups</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Daily Backup Time</label>
                                    <input type="time" name="daily_backup_time" 
                                           value="<?php echo htmlspecialchars($backupConfig['daily_backup_time']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="compression_enabled" id="compression_enabled" 
                                               <?php echo $backupConfig['compression_enabled'] === '1' ? 'checked' : ''; ?>>
                                        <label for="compression_enabled">Enable Compression</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="form-group">
                                    <label>Daily Retention (days)</label>
                                    <input type="number" name="retention_daily" min="1" max="30" 
                                           value="<?php echo htmlspecialchars($backupConfig['retention_daily']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Weekly Retention (weeks)</label>
                                    <input type="number" name="retention_weekly" min="1" max="12" 
                                           value="<?php echo htmlspecialchars($backupConfig['retention_weekly']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Monthly Retention (months)</label>
                                    <input type="number" name="retention_monthly" min="1" max="24" 
                                           value="<?php echo htmlspecialchars($backupConfig['retention_monthly']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Notification Email</label>
                            <input type="email" name="notification_email" 
                                   value="<?php echo htmlspecialchars($backupConfig['notification_email']); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Configuration
                        </button>
                    </form>
                </div>
                
                <!-- Backup History -->
                <div class="backup-section">
                    <h2 class="section-title">Backup History</h2>
                    
                    <?php if (empty($backupHistory)): ?>
                        <p class="text-muted">No backups found.</p>
                    <?php else: ?>
                        <table class="backup-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backupHistory as $backup): ?>
                                    <tr>
                                        <td>
                                            <span class="backup-type type-<?php echo $backup['backup_type']; ?>">
                                                <?php echo ucfirst($backup['backup_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                                        <td><?php echo formatFileSize($backup['file_size']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $backup['status']; ?>">
                                                <?php echo ucfirst($backup['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y H:i', strtotime($backup['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($backup['created_by_name'] ?: 'System'); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <a href="?action=download_backup&backup_id=<?php echo $backup['id']; ?>" 
                                                   class="btn btn-primary btn-small">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                
                                                <button onclick="verifyBackup(<?php echo $backup['id']; ?>)" 
                                                        class="btn btn-success btn-small">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                
                                                <button onclick="restoreBackup(<?php echo $backup['id']; ?>)" 
                                                        class="btn btn-warning btn-small">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function verifyBackup(backupId) {
            fetch(`?action=verify_backup&backup_id=${backupId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        alert('Backup verification successful: ' + data.message);
                    } else {
                        alert('Backup verification failed: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Verification request failed');
                });
        }
        
        function restoreBackup(backupId) {
            if (confirm('Are you sure you want to restore this backup? This will overwrite the current database and cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="restore_backup">
                    <input type="hidden" name="backup_id" value="${backupId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>