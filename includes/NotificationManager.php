<?php
/**
 * Notification Manager for Critical Error Alerts
 * Handles email notifications and alert management
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

class NotificationManager {
    private static $instance = null;
    private $db;
    private $emailQueue = [];
    
    private function __construct() {
        $this->db = getDB();
        $this->createNotificationTables();
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
     * Create notification tables if they don't exist
     */
    private function createNotificationTables() {
        try {
            // Notification settings table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS notification_settings (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    notification_type VARCHAR(50) NOT NULL,
                    is_enabled BOOLEAN DEFAULT TRUE,
                    email_address VARCHAR(255),
                    threshold_value INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    UNIQUE KEY unique_user_type (user_id, notification_type)
                )
            ");
            
            // Notification history table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS notification_history (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    notification_type VARCHAR(50) NOT NULL,
                    recipient_email VARCHAR(255) NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
                    error_message TEXT,
                    sent_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Alert thresholds table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS alert_thresholds (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    alert_type VARCHAR(50) NOT NULL UNIQUE,
                    threshold_value INT NOT NULL,
                    time_window INT DEFAULT 3600,
                    is_enabled BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Insert default thresholds
            $this->db->exec("
                INSERT IGNORE INTO alert_thresholds (alert_type, threshold_value, time_window) VALUES
                ('critical_errors', 5, 3600),
                ('failed_logins', 10, 1800),
                ('database_errors', 3, 1800),
                ('security_breaches', 1, 300),
                ('performance_issues', 10, 3600)
            ");
            
        } catch (PDOException $e) {
            error_log("Failed to create notification tables: " . $e->getMessage());
        }
    }
    
    /**
     * Send critical error notification
     */
    public function sendCriticalErrorAlert($errorData) {
        if (!$this->shouldSendAlert('critical_errors')) {
            return false;
        }
        
        $recipients = $this->getNotificationRecipients('critical_errors');
        
        if (empty($recipients)) {
            // Fallback to admin email
            $recipients = [ADMIN_EMAIL];
        }
        
        $subject = "🚨 Critical Error Alert - " . SITE_NAME;
        $message = $this->formatCriticalErrorMessage($errorData);
        
        foreach ($recipients as $email) {
            $this->queueNotification('critical_errors', $email, $subject, $message);
        }
        
        $this->processEmailQueue();
        $this->recordAlertSent('critical_errors');
        
        return true;
    }
    
    /**
     * Send security breach notification
     */
    public function sendSecurityAlert($securityData) {
        if (!$this->shouldSendAlert('security_breaches')) {
            return false;
        }
        
        $recipients = $this->getNotificationRecipients('security_breaches');
        
        if (empty($recipients)) {
            $recipients = [ADMIN_EMAIL];
        }
        
        $subject = "🔒 Security Alert - " . SITE_NAME;
        $message = $this->formatSecurityMessage($securityData);
        
        foreach ($recipients as $email) {
            $this->queueNotification('security_breaches', $email, $subject, $message);
        }
        
        $this->processEmailQueue();
        $this->recordAlertSent('security_breaches');
        
        return true;
    }
    
    /**
     * Send performance warning
     */
    public function sendPerformanceAlert($performanceData) {
        if (!$this->shouldSendAlert('performance_issues')) {
            return false;
        }
        
        $recipients = $this->getNotificationRecipients('performance_issues');
        
        if (empty($recipients)) {
            return false; // Performance alerts are optional
        }
        
        $subject = "⚡ Performance Warning - " . SITE_NAME;
        $message = $this->formatPerformanceMessage($performanceData);
        
        foreach ($recipients as $email) {
            $this->queueNotification('performance_issues', $email, $subject, $message);
        }
        
        $this->processEmailQueue();
        $this->recordAlertSent('performance_issues');
        
        return true;
    }
    
    /**
     * Check if alert should be sent based on thresholds
     */
    private function shouldSendAlert($alertType) {
        try {
            $stmt = $this->db->prepare("
                SELECT threshold_value, time_window, is_enabled 
                FROM alert_thresholds 
                WHERE alert_type = ?
            ");
            $stmt->execute([$alertType]);
            $threshold = $stmt->fetch();
            
            if (!$threshold || !$threshold['is_enabled']) {
                return false;
            }
            
            // Check if we've already sent too many alerts recently
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as alert_count
                FROM notification_history 
                WHERE notification_type = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
                AND status = 'sent'
            ");
            $stmt->execute([$alertType, $threshold['time_window']]);
            $recentAlerts = $stmt->fetchColumn();
            
            return $recentAlerts < $threshold['threshold_value'];
            
        } catch (PDOException $e) {
            error_log("Error checking alert threshold: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification recipients for alert type
     */
    private function getNotificationRecipients($alertType) {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT ns.email_address
                FROM notification_settings ns
                JOIN users u ON ns.user_id = u.id
                WHERE ns.notification_type = ? 
                AND ns.is_enabled = 1 
                AND u.is_active = 1
                AND ns.email_address IS NOT NULL
            ");
            $stmt->execute([$alertType]);
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            error_log("Error getting notification recipients: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Queue notification for sending
     */
    private function queueNotification($type, $email, $subject, $message) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notification_history (notification_type, recipient_email, subject, message)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$type, $email, $subject, $message]);
            
            $this->emailQueue[] = [
                'id' => $this->db->lastInsertId(),
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ];
            
        } catch (PDOException $e) {
            error_log("Error queueing notification: " . $e->getMessage());
        }
    }
    
    /**
     * Process email queue
     */
    private function processEmailQueue() {
        foreach ($this->emailQueue as $email) {
            $success = $this->sendEmail($email['email'], $email['subject'], $email['message']);
            
            try {
                $stmt = $this->db->prepare("
                    UPDATE notification_history 
                    SET status = ?, sent_at = NOW(), error_message = ?
                    WHERE id = ?
                ");
                
                if ($success) {
                    $stmt->execute(['sent', null, $email['id']]);
                } else {
                    $stmt->execute(['failed', 'Email sending failed', $email['id']]);
                }
                
            } catch (PDOException $e) {
                error_log("Error updating notification status: " . $e->getMessage());
            }
        }
        
        $this->emailQueue = [];
    }
    
    /**
     * Send email using PHP mail function
     */
    private function sendEmail($to, $subject, $message) {
        $headers = [
            'From: ' . SITE_NAME . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
            'Reply-To: ' . ADMIN_EMAIL,
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion(),
            'X-Priority: 1', // High priority for alerts
            'Importance: High'
        ];
        
        $htmlMessage = $this->wrapMessageInHTML($message, $subject);
        
        return @mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
    }
    
    /**
     * Wrap message in HTML template
     */
    private function wrapMessageInHTML($message, $subject) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$subject}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #e74c3c; color: white; padding: 15px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .alert-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 4px; }
                .timestamp { font-weight: bold; color: #e74c3c; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>{$subject}</h2>
                </div>
                <div class='content'>
                    <div class='alert-box'>
                        {$message}
                    </div>
                    <p><strong>Timestamp:</strong> <span class='timestamp'>" . date('Y-m-d H:i:s') . "</span></p>
                </div>
                <div class='footer'>
                    <p>This is an automated alert from " . SITE_NAME . "</p>
                    <p>Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Format critical error message
     */
    private function formatCriticalErrorMessage($errorData) {
        $message = "<h3>Critical Error Details:</h3>";
        $message .= "<p><strong>Error Message:</strong> " . htmlspecialchars($errorData['message']) . "</p>";
        $message .= "<p><strong>Level:</strong> " . htmlspecialchars($errorData['level']) . "</p>";
        
        if (!empty($errorData['user_id'])) {
            $message .= "<p><strong>User:</strong> {$errorData['user_id']} ({$errorData['user_role']})</p>";
        }
        
        $message .= "<p><strong>IP Address:</strong> " . htmlspecialchars($errorData['ip_address']) . "</p>";
        $message .= "<p><strong>Request URI:</strong> " . htmlspecialchars($errorData['request_uri']) . "</p>";
        
        if (!empty($errorData['context'])) {
            $message .= "<h4>Context:</h4>";
            $message .= "<pre>" . htmlspecialchars(json_encode($errorData['context'], JSON_PRETTY_PRINT)) . "</pre>";
        }
        
        $message .= "<p><em>Please investigate this issue immediately.</em></p>";
        
        return $message;
    }
    
    /**
     * Format security message
     */
    private function formatSecurityMessage($securityData) {
        $message = "<h3>Security Alert Details:</h3>";
        $message .= "<p><strong>Alert Type:</strong> " . htmlspecialchars($securityData['type']) . "</p>";
        $message .= "<p><strong>Description:</strong> " . htmlspecialchars($securityData['message']) . "</p>";
        $message .= "<p><strong>IP Address:</strong> " . htmlspecialchars($securityData['ip_address']) . "</p>";
        
        if (!empty($securityData['user_id'])) {
            $message .= "<p><strong>User Involved:</strong> {$securityData['user_id']}</p>";
        }
        
        $message .= "<p><strong>Request URI:</strong> " . htmlspecialchars($securityData['request_uri']) . "</p>";
        $message .= "<p><strong>User Agent:</strong> " . htmlspecialchars($securityData['user_agent']) . "</p>";
        
        $message .= "<p><em>Immediate security review recommended.</em></p>";
        
        return $message;
    }
    
    /**
     * Format performance message
     */
    private function formatPerformanceMessage($performanceData) {
        $message = "<h3>Performance Warning:</h3>";
        $message .= "<p><strong>Issue:</strong> " . htmlspecialchars($performanceData['issue']) . "</p>";
        $message .= "<p><strong>Metric:</strong> " . htmlspecialchars($performanceData['metric']) . "</p>";
        $message .= "<p><strong>Current Value:</strong> " . htmlspecialchars($performanceData['value']) . "</p>";
        $message .= "<p><strong>Threshold:</strong> " . htmlspecialchars($performanceData['threshold']) . "</p>";
        
        if (!empty($performanceData['details'])) {
            $message .= "<h4>Additional Details:</h4>";
            $message .= "<p>" . htmlspecialchars($performanceData['details']) . "</p>";
        }
        
        $message .= "<p><em>Performance optimization may be required.</em></p>";
        
        return $message;
    }
    
    /**
     * Record that an alert was sent
     */
    private function recordAlertSent($alertType) {
        try {
            // This could be used for rate limiting or statistics
            $logFile = ERROR_LOG_PATH . 'alert_history.log';
            $logEntry = date('Y-m-d H:i:s') . " - Alert sent: {$alertType}\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            error_log("Error recording alert: " . $e->getMessage());
        }
    }
    
    /**
     * Configure notification settings for user
     */
    public function configureNotifications($userId, $settings) {
        try {
            foreach ($settings as $type => $config) {
                $stmt = $this->db->prepare("
                    INSERT INTO notification_settings (user_id, notification_type, is_enabled, email_address, threshold_value)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    is_enabled = VALUES(is_enabled),
                    email_address = VALUES(email_address),
                    threshold_value = VALUES(threshold_value),
                    updated_at = NOW()
                ");
                
                $stmt->execute([
                    $userId,
                    $type,
                    $config['enabled'] ? 1 : 0,
                    $config['email'] ?? null,
                    $config['threshold'] ?? 0
                ]);
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error configuring notifications: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStats($days = 7) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    notification_type,
                    status,
                    COUNT(*) as count
                FROM notification_history 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY notification_type, status
            ");
            $stmt->execute([$days]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Global notification functions
 */
function sendCriticalErrorAlert($errorData) {
    return NotificationManager::getInstance()->sendCriticalErrorAlert($errorData);
}

function sendSecurityAlert($securityData) {
    return NotificationManager::getInstance()->sendSecurityAlert($securityData);
}

function sendPerformanceAlert($performanceData) {
    return NotificationManager::getInstance()->sendPerformanceAlert($performanceData);
}
?>