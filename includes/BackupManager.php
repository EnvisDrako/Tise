<?php
/**
 * Automated Database Backup and Recovery System
 * Provides comprehensive backup, restore, and monitoring capabilities
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

class BackupManager {
    private static $instance = null;
    private $backupPath;
    private $db;
    private $config;
    
    private function __construct() {
        $this->backupPath = __DIR__ . '/../backups/';
        $this->db = getDB();
        $this->config = $this->loadBackupConfig();
        $this->createBackupDirectory();
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
     * Create backup directory if it doesn't exist
     */
    private function createBackupDirectory() {
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        
        // Create subdirectories
        $subdirs = ['daily', 'weekly', 'monthly', 'manual'];
        foreach ($subdirs as $dir) {
            $path = $this->backupPath . $dir . '/';
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        // Create .htaccess to protect backup files
        $htaccessContent = "Order Deny,Allow\nDeny from all";
        file_put_contents($this->backupPath . '.htaccess', $htaccessContent);
    }
    
    /**
     * Load backup configuration
     */
    private function loadBackupConfig() {
        try {
            // Create backup config table if it doesn't exist
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS backup_config (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    config_key VARCHAR(50) NOT NULL UNIQUE,
                    config_value TEXT NOT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Insert default configuration
            $this->db->exec("
                INSERT IGNORE INTO backup_config (config_key, config_value) VALUES
                ('auto_backup_enabled', '1'),
                ('daily_backup_time', '02:00'),
                ('weekly_backup_day', '0'),
                ('monthly_backup_date', '1'),
                ('retention_daily', '7'),
                ('retention_weekly', '4'),
                ('retention_monthly', '12'),
                ('compression_enabled', '1'),
                ('backup_tables', 'all'),
                ('notification_email', '" . ADMIN_EMAIL . "')
            ");
            
            // Load configuration
            $stmt = $this->db->query("SELECT config_key, config_value FROM backup_config WHERE is_active = 1");
            $config = [];
            while ($row = $stmt->fetch()) {
                $config[$row['config_key']] = $row['config_value'];
            }
            
            return $config;
            
        } catch (Exception $e) {
            logError("Failed to load backup config: " . $e->getMessage());
            return $this->getDefaultConfig();
        }
    }
    
    /**
     * Get default backup configuration
     */
    private function getDefaultConfig() {
        return [
            'auto_backup_enabled' => '1',
            'daily_backup_time' => '02:00',
            'weekly_backup_day' => '0',
            'monthly_backup_date' => '1',
            'retention_daily' => '7',
            'retention_weekly' => '4',
            'retention_monthly' => '12',
            'compression_enabled' => '1',
            'backup_tables' => 'all',
            'notification_email' => ADMIN_EMAIL
        ];
    }
    
    /**
     * Create database backup
     */
    public function createBackup($type = 'manual', $tables = null) {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$type}_{$timestamp}.sql";
            $filepath = $this->backupPath . $type . '/' . $filename;
            
            // Get tables to backup
            if ($tables === null) {
                $tables = $this->getTablesToBackup();
            }
            
            // Create backup
            $backupContent = $this->generateBackupSQL($tables);
            
            // Write to file
            file_put_contents($filepath, $backupContent);
            
            // Compress if enabled
            if ($this->config['compression_enabled'] === '1') {
                $compressedFile = $this->compressBackup($filepath);
                if ($compressedFile) {
                    unlink($filepath); // Remove uncompressed file
                    $filepath = $compressedFile;
                    $filename = basename($compressedFile);
                }
            }
            
            // Record backup in database
            $backupId = $this->recordBackup($type, $filename, $filepath, filesize($filepath));
            
            // Clean old backups
            $this->cleanOldBackups($type);
            
            // Send notification if configured
            if ($this->config['notification_email'] && $type !== 'manual') {
                $this->sendBackupNotification($type, $filename, filesize($filepath), true);
            }
            
            logUserAction('database_backup_created', [
                'type' => $type,
                'filename' => $filename,
                'size' => filesize($filepath)
            ]);
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath)
            ];
            
        } catch (Exception $e) {
            logError("Backup creation failed: " . $e->getMessage());
            
            // Send failure notification
            if ($this->config['notification_email']) {
                $this->sendBackupNotification($type, '', 0, false, $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get tables to backup
     */
    private function getTablesToBackup() {
        if ($this->config['backup_tables'] === 'all') {
            $stmt = $this->db->query("SHOW TABLES");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            return json_decode($this->config['backup_tables'], true) ?: [];
        }
    }
    
    /**
     * Generate backup SQL
     */
    private function generateBackupSQL($tables) {
        $sql = "-- Database Backup\n";
        $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . DB_NAME . "\n\n";
        
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET AUTOCOMMIT = 0;\n";
        $sql .= "START TRANSACTION;\n\n";
        
        foreach ($tables as $table) {
            $sql .= $this->getTableBackupSQL($table);
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sql .= "COMMIT;\n";
        
        return $sql;
    }
    
    /**
     * Get backup SQL for a specific table
     */
    private function getTableBackupSQL($table) {
        $sql = "-- Table structure for table `{$table}`\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // Get table structure
        $stmt = $this->db->query("SHOW CREATE TABLE `{$table}`");
        $row = $stmt->fetch();
        $sql .= $row['Create Table'] . ";\n\n";
        
        // Get table data
        $sql .= "-- Dumping data for table `{$table}`\n";
        $stmt = $this->db->query("SELECT * FROM `{$table}`");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns = array_keys($row);
            $values = array_values($row);
            
            // Escape values
            $escapedValues = array_map(function($value) {
                if ($value === null) {
                    return 'NULL';
                } else {
                    return "'" . addslashes($value) . "'";
                }
            }, $values);
            
            $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
        }
        
        $sql .= "\n";
        return $sql;
    }
    
    /**
     * Compress backup file
     */
    private function compressBackup($filepath) {
        if (!function_exists('gzopen')) {
            return false;
        }
        
        $compressedFile = $filepath . '.gz';
        
        $input = fopen($filepath, 'rb');
        $output = gzopen($compressedFile, 'wb9');
        
        if ($input && $output) {
            while (!feof($input)) {
                gzwrite($output, fread($input, 8192));
            }
            
            fclose($input);
            gzclose($output);
            
            return $compressedFile;
        }
        
        return false;
    }
    
    /**
     * Record backup in database
     */
    private function recordBackup($type, $filename, $filepath, $size) {
        try {
            // Create backup history table if it doesn't exist
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS backup_history (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    backup_type ENUM('manual', 'daily', 'weekly', 'monthly') NOT NULL,
                    filename VARCHAR(255) NOT NULL,
                    filepath VARCHAR(500) NOT NULL,
                    file_size BIGINT NOT NULL,
                    status ENUM('completed', 'failed', 'corrupted') DEFAULT 'completed',
                    created_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id)
                )
            ");
            
            $stmt = $this->db->prepare("
                INSERT INTO backup_history (backup_type, filename, filepath, file_size, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $type,
                $filename,
                $filepath,
                $size,
                getCurrentUserId()
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            logError("Failed to record backup: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clean old backups based on retention policy
     */
    private function cleanOldBackups($type) {
        try {
            $retentionKey = "retention_{$type}";
            $retentionDays = (int)($this->config[$retentionKey] ?? 7);
            
            // Get old backup files
            $stmt = $this->db->prepare("
                SELECT id, filepath FROM backup_history 
                WHERE backup_type = ? 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY created_at ASC
            ");
            $stmt->execute([$type, $retentionDays]);
            
            while ($backup = $stmt->fetch()) {
                // Delete file
                if (file_exists($backup['filepath'])) {
                    unlink($backup['filepath']);
                }
                
                // Delete record
                $deleteStmt = $this->db->prepare("DELETE FROM backup_history WHERE id = ?");
                $deleteStmt->execute([$backup['id']]);
            }
            
        } catch (Exception $e) {
            logError("Failed to clean old backups: " . $e->getMessage());
        }
    }
    
    /**
     * Restore database from backup
     */
    public function restoreBackup($backupId) {
        try {
            // Get backup details
            $stmt = $this->db->prepare("SELECT * FROM backup_history WHERE id = ?");
            $stmt->execute([$backupId]);
            $backup = $stmt->fetch();
            
            if (!$backup) {
                throw new Exception("Backup not found");
            }
            
            if (!file_exists($backup['filepath'])) {
                throw new Exception("Backup file not found");
            }
            
            // Read backup content
            $backupContent = $this->readBackupFile($backup['filepath']);
            
            // Create restore point before restoring
            $restorePoint = $this->createBackup('manual');
            
            // Execute restore
            $this->executeRestore($backupContent);
            
            logUserAction('database_restored', [
                'backup_id' => $backupId,
                'backup_filename' => $backup['filename'],
                'restore_point_id' => $restorePoint['backup_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'message' => 'Database restored successfully',
                'restore_point_id' => $restorePoint['backup_id'] ?? null
            ];
            
        } catch (Exception $e) {
            logError("Database restore failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Read backup file content
     */
    private function readBackupFile($filepath) {
        if (substr($filepath, -3) === '.gz') {
            // Compressed file
            return gzfile($filepath);
        } else {
            // Regular file
            return file($filepath);
        }
    }
    
    /**
     * Execute database restore
     */
    private function executeRestore($sqlLines) {
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        $query = '';
        foreach ($sqlLines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || substr($line, 0, 2) === '--' || substr($line, 0, 2) === '/*') {
                continue;
            }
            
            $query .= $line;
            
            // Execute query when semicolon is found
            if (substr($line, -1) === ';') {
                $this->db->exec($query);
                $query = '';
            }
        }
        
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    /**
     * Send backup notification
     */
    private function sendBackupNotification($type, $filename, $size, $success, $error = '') {
        $subject = $success ? "Backup Completed - " . SITE_NAME : "Backup Failed - " . SITE_NAME;
        
        $message = $success ? 
            "Database backup completed successfully.\n\n" .
            "Type: " . ucfirst($type) . "\n" .
            "Filename: {$filename}\n" .
            "Size: " . formatFileSize($size) . "\n" .
            "Time: " . date('Y-m-d H:i:s') :
            
            "Database backup failed.\n\n" .
            "Type: " . ucfirst($type) . "\n" .
            "Error: {$error}\n" .
            "Time: " . date('Y-m-d H:i:s');
        
        $headers = "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">";
        
        @mail($this->config['notification_email'], $subject, $message, $headers);
    }
    
    /**
     * Get backup history
     */
    public function getBackupHistory($limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT bh.*, u.full_name as created_by_name
                FROM backup_history bh
                LEFT JOIN users u ON bh.created_by = u.id
                ORDER BY bh.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logError("Failed to get backup history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get backup statistics
     */
    public function getBackupStatistics() {
        try {
            $stats = [];
            
            // Total backups
            $stmt = $this->db->query("SELECT COUNT(*) FROM backup_history");
            $stats['total_backups'] = $stmt->fetchColumn();
            
            // Backup by type
            $stmt = $this->db->query("
                SELECT backup_type, COUNT(*) as count 
                FROM backup_history 
                GROUP BY backup_type
            ");
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total backup size
            $stmt = $this->db->query("SELECT SUM(file_size) FROM backup_history");
            $stats['total_size'] = $stmt->fetchColumn() ?: 0;
            
            // Last backup
            $stmt = $this->db->query("
                SELECT backup_type, created_at 
                FROM backup_history 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $lastBackup = $stmt->fetch();
            $stats['last_backup'] = $lastBackup ?: null;
            
            // Backup frequency (last 30 days)
            $stmt = $this->db->query("
                SELECT DATE(created_at) as backup_date, COUNT(*) as count
                FROM backup_history 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY backup_date DESC
            ");
            $stats['frequency'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            logError("Failed to get backup statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verify backup integrity
     */
    public function verifyBackup($backupId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM backup_history WHERE id = ?");
            $stmt->execute([$backupId]);
            $backup = $stmt->fetch();
            
            if (!$backup) {
                return ['valid' => false, 'error' => 'Backup not found'];
            }
            
            if (!file_exists($backup['filepath'])) {
                return ['valid' => false, 'error' => 'Backup file not found'];
            }
            
            // Check file size
            $actualSize = filesize($backup['filepath']);
            if ($actualSize !== (int)$backup['file_size']) {
                return ['valid' => false, 'error' => 'File size mismatch'];
            }
            
            // Try to read backup content
            $content = $this->readBackupFile($backup['filepath']);
            if (empty($content)) {
                return ['valid' => false, 'error' => 'Cannot read backup file'];
            }
            
            // Basic SQL validation
            $hasCreateTable = false;
            foreach ($content as $line) {
                if (stripos($line, 'CREATE TABLE') !== false) {
                    $hasCreateTable = true;
                    break;
                }
            }
            
            if (!$hasCreateTable) {
                return ['valid' => false, 'error' => 'Invalid backup format'];
            }
            
            return ['valid' => true, 'message' => 'Backup is valid'];
            
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Schedule automatic backups
     */
    public function scheduleBackups() {
        if ($this->config['auto_backup_enabled'] !== '1') {
            return false;
        }
        
        $now = new DateTime();
        $currentHour = $now->format('H:i');
        $currentDay = $now->format('w'); // 0 = Sunday
        $currentDate = $now->format('j');
        
        // Check if it's time for daily backup
        if ($currentHour === $this->config['daily_backup_time']) {
            $this->createBackup('daily');
        }
        
        // Check if it's time for weekly backup
        if ($currentDay === $this->config['weekly_backup_day'] && 
            $currentHour === $this->config['daily_backup_time']) {
            $this->createBackup('weekly');
        }
        
        // Check if it's time for monthly backup
        if ($currentDate === $this->config['monthly_backup_date'] && 
            $currentHour === $this->config['daily_backup_time']) {
            $this->createBackup('monthly');
        }
        
        return true;
    }
    
    /**
     * Update backup configuration
     */
    public function updateConfig($config) {
        try {
            foreach ($config as $key => $value) {
                $stmt = $this->db->prepare("
                    UPDATE backup_config 
                    SET config_value = ?, updated_at = NOW() 
                    WHERE config_key = ?
                ");
                $stmt->execute([$value, $key]);
            }
            
            // Reload configuration
            $this->config = $this->loadBackupConfig();
            
            return true;
            
        } catch (Exception $e) {
            logError("Failed to update backup config: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Global backup functions
 */
function createDatabaseBackup($type = 'manual') {
    return BackupManager::getInstance()->createBackup($type);
}

function restoreDatabaseBackup($backupId) {
    return BackupManager::getInstance()->restoreBackup($backupId);
}

function scheduleBackups() {
    return BackupManager::getInstance()->scheduleBackups();
}
?>