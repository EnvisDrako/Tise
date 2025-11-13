# System Maintenance and Troubleshooting Guide

## Integrated Web Portal & CRM System - Maintenance Guide

### Table of Contents
1. [System Overview](#system-overview)
2. [Regular Maintenance Tasks](#regular-maintenance-tasks)
3. [Monitoring and Alerts](#monitoring-and-alerts)
4. [Backup and Recovery](#backup-and-recovery)
5. [Performance Optimization](#performance-optimization)
6. [Security Maintenance](#security-maintenance)
7. [Troubleshooting Guide](#troubleshooting-guide)
8. [Log Analysis](#log-analysis)
9. [Database Maintenance](#database-maintenance)
10. [Emergency Procedures](#emergency-procedures)

---

## System Overview

### System Architecture
The Integrated Web Portal & CRM system consists of:
- **Web Server**: Apache/Nginx serving PHP application
- **Database**: MySQL/MariaDB for data storage
- **File Storage**: Local filesystem for uploads and documents
- **Session Management**: PHP sessions with database storage
- **API Layer**: RESTful API for mobile app integration

### Key Components
- **Public Website**: Information and enquiry submission
- **Admin Portal**: Complete system administration
- **Faculty Portal**: Institution-specific management
- **Enquirer Portal**: Lead management and calling
- **Mobile API**: Android app integration

### System Requirements
- **OS**: Linux (Ubuntu 20.04+) or Windows Server 2019+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.4+ (8.0+ recommended)
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: 4GB+ RAM for production
- **Storage**: 50GB+ for production use

---

## Regular Maintenance Tasks

### Daily Tasks

#### 1. System Health Check
```bash
#!/bin/bash
# Daily health check script

echo "=== Daily System Health Check - $(date) ==="

# Check web server status
systemctl status apache2 | grep "Active:"

# Check database status
systemctl status mysql | grep "Active:"

# Check disk space
df -h | grep -E "(/$|/var)"

# Check memory usage
free -h

# Check recent errors
tail -n 50 /var/log/apache2/error.log | grep "$(date '+%Y-%m-%d')"

echo "=== Health Check Complete ==="
```

#### 2. Log Review
- Review Apache error logs for critical issues
- Check application error logs for system errors
- Monitor database error logs for connection issues
- Review security logs for suspicious activities

#### 3. Backup Verification
```bash
# Verify latest backup exists and is recent
BACKUP_DIR="/backup/crm"
LATEST_BACKUP=$(ls -t $BACKUP_DIR/db_backup_*.sql | head -1)
BACKUP_AGE=$(find $BACKUP_DIR -name "db_backup_*.sql" -mtime -1 | wc -l)

if [ $BACKUP_AGE -eq 0 ]; then
    echo "WARNING: No recent database backup found!"
    # Send alert email
fi
```

### Weekly Tasks

#### 1. Database Optimization
```sql
-- Run weekly database maintenance
OPTIMIZE TABLE users, institutions, students, enquiries, marklists, certificate_requests, call_logs;

-- Update table statistics
ANALYZE TABLE users, institutions, students, enquiries, marklists, certificate_requests, call_logs;

-- Check for table corruption
CHECK TABLE users, institutions, students, enquiries, marklists, certificate_requests, call_logs;
```

#### 2. Log Rotation and Cleanup
```bash
#!/bin/bash
# Weekly log cleanup script

# Compress old Apache logs
find /var/log/apache2 -name "*.log" -mtime +7 -exec gzip {} \;

# Remove old compressed logs (older than 30 days)
find /var/log/apache2 -name "*.gz" -mtime +30 -delete

# Clean application logs
find /var/www/html/integrated-crm/logs -name "*.log" -mtime +14 -exec gzip {} \;
find /var/www/html/integrated-crm/logs -name "*.gz" -mtime +30 -delete

# Clean PHP session files
find /var/lib/php/sessions -name "sess_*" -mtime +1 -delete
```

#### 3. Security Updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Check for security updates
sudo unattended-upgrades --dry-run

# Update PHP packages if needed
sudo apt update && sudo apt upgrade php*
```

### Monthly Tasks

#### 1. Performance Analysis
- Review system performance metrics
- Analyze slow query logs
- Check database index usage
- Review file storage usage and cleanup old files

#### 2. User Account Audit
```sql
-- Review inactive users
SELECT username, full_name, last_login, created_at 
FROM users 
WHERE last_login < DATE_SUB(NOW(), INTERVAL 90 DAY) 
   OR last_login IS NULL;

-- Check for duplicate accounts
SELECT email, COUNT(*) as count 
FROM users 
GROUP BY email 
HAVING count > 1;

-- Review admin accounts
SELECT username, full_name, created_at, last_login 
FROM users 
WHERE role = 'admin';
```

#### 3. Data Archival
```bash
#!/bin/bash
# Monthly data archival script

# Archive old enquiries (older than 1 year, status = enrolled/not_interested)
mysql -u crm_user -p integrated_crm << EOF
INSERT INTO enquiries_archive 
SELECT * FROM enquiries 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR) 
  AND status IN ('enrolled', 'not_interested');

DELETE FROM enquiries 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR) 
  AND status IN ('enrolled', 'not_interested');
EOF

# Archive old call logs (older than 2 years)
mysql -u crm_user -p integrated_crm << EOF
INSERT INTO call_logs_archive 
SELECT * FROM call_logs 
WHERE call_timestamp < DATE_SUB(NOW(), INTERVAL 2 YEAR);

DELETE FROM call_logs 
WHERE call_timestamp < DATE_SUB(NOW(), INTERVAL 2 YEAR);
EOF
```

---

## Monitoring and Alerts

### System Monitoring Setup

#### 1. Server Monitoring Script
```bash
#!/bin/bash
# System monitoring script - run every 5 minutes via cron

ALERT_EMAIL="admin@your-domain.com"
THRESHOLD_CPU=80
THRESHOLD_MEMORY=85
THRESHOLD_DISK=90

# Check CPU usage
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
if (( $(echo "$CPU_USAGE > $THRESHOLD_CPU" | bc -l) )); then
    echo "HIGH CPU USAGE: $CPU_USAGE%" | mail -s "Server Alert: High CPU" $ALERT_EMAIL
fi

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100.0)}')
if [ $MEMORY_USAGE -gt $THRESHOLD_MEMORY ]; then
    echo "HIGH MEMORY USAGE: $MEMORY_USAGE%" | mail -s "Server Alert: High Memory" $ALERT_EMAIL
fi

# Check disk usage
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | cut -d'%' -f1)
if [ $DISK_USAGE -gt $THRESHOLD_DISK ]; then
    echo "HIGH DISK USAGE: $DISK_USAGE%" | mail -s "Server Alert: High Disk Usage" $ALERT_EMAIL
fi

# Check web server
if ! systemctl is-active --quiet apache2; then
    echo "Apache web server is down!" | mail -s "Server Alert: Apache Down" $ALERT_EMAIL
fi

# Check database
if ! systemctl is-active --quiet mysql; then
    echo "MySQL database server is down!" | mail -s "Server Alert: MySQL Down" $ALERT_EMAIL
fi
```

#### 2. Application Monitoring
```php
<?php
// Application health check endpoint: /api/health.php

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => []
];

// Database connectivity check
try {
    require_once '../includes/config.php';
    require_once '../includes/db_connect.php';
    
    $db = getDB();
    $stmt = $db->query('SELECT 1');
    $health['checks']['database'] = 'healthy';
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = 'failed: ' . $e->getMessage();
}

// File system check
$uploadDir = '../public/uploads';
if (is_writable($uploadDir)) {
    $health['checks']['file_system'] = 'healthy';
} else {
    $health['status'] = 'unhealthy';
    $health['checks']['file_system'] = 'upload directory not writable';
}

// Memory usage check
$memoryUsage = memory_get_usage(true);
$memoryLimit = ini_get('memory_limit');
$health['checks']['memory'] = [
    'usage' => $memoryUsage,
    'limit' => $memoryLimit,
    'status' => 'healthy'
];

// Response time check
$startTime = microtime(true);
// Simulate some work
usleep(1000);
$responseTime = (microtime(true) - $startTime) * 1000;
$health['checks']['response_time'] = round($responseTime, 2) . 'ms';

echo json_encode($health, JSON_PRETTY_PRINT);
?>
```

#### 3. Cron Job Setup
```bash
# Add to crontab (crontab -e)

# System monitoring every 5 minutes
*/5 * * * * /path/to/system_monitor.sh

# Daily health check at 6 AM
0 6 * * * /path/to/daily_health_check.sh

# Weekly maintenance on Sunday at 2 AM
0 2 * * 0 /path/to/weekly_maintenance.sh

# Monthly archival on 1st of month at 3 AM
0 3 1 * * /path/to/monthly_archival.sh

# Database backup every 6 hours
0 */6 * * * /path/to/backup_database.sh
```

---

## Backup and Recovery

### Database Backup Strategy

#### 1. Automated Database Backup
```bash
#!/bin/bash
# Database backup script

BACKUP_DIR="/backup/crm"
DB_NAME="integrated_crm"
DB_USER="crm_user"
DB_PASS="your_password"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/db_backup_$DATE.sql"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -u $DB_USER -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    $DB_NAME > $BACKUP_FILE

# Compress backup
gzip $BACKUP_FILE

# Remove backups older than 30 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete

# Verify backup was created
if [ -f "$BACKUP_FILE.gz" ]; then
    echo "Database backup completed successfully: $BACKUP_FILE.gz"
else
    echo "ERROR: Database backup failed!" | mail -s "Backup Alert: Database Backup Failed" admin@your-domain.com
fi
```

#### 2. File System Backup
```bash
#!/bin/bash
# File system backup script

BACKUP_DIR="/backup/crm"
APP_DIR="/var/www/html/integrated-crm"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/files_backup_$DATE.tar.gz"

# Create backup of uploads and configuration
tar -czf $BACKUP_FILE \
    --exclude="$APP_DIR/logs/*.log" \
    --exclude="$APP_DIR/tests" \
    $APP_DIR/public/uploads \
    $APP_DIR/includes/config.php

# Remove old file backups (keep 7 days)
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +7 -delete

echo "File system backup completed: $BACKUP_FILE"
```

### Recovery Procedures

#### 1. Database Recovery
```bash
#!/bin/bash
# Database recovery script

BACKUP_FILE="$1"
DB_NAME="integrated_crm"
DB_USER="crm_user"
DB_PASS="your_password"

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    exit 1
fi

# Stop web server to prevent new connections
systemctl stop apache2

# Create recovery database
mysql -u root -p << EOF
DROP DATABASE IF EXISTS ${DB_NAME}_recovery;
CREATE DATABASE ${DB_NAME}_recovery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON ${DB_NAME}_recovery.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

# Restore from backup
if [[ $BACKUP_FILE == *.gz ]]; then
    gunzip -c $BACKUP_FILE | mysql -u $DB_USER -p$DB_PASS ${DB_NAME}_recovery
else
    mysql -u $DB_USER -p$DB_PASS ${DB_NAME}_recovery < $BACKUP_FILE
fi

# Verify recovery
TABLES=$(mysql -u $DB_USER -p$DB_PASS ${DB_NAME}_recovery -e "SHOW TABLES;" | wc -l)
if [ $TABLES -gt 1 ]; then
    echo "Recovery successful. $TABLES tables restored."
    echo "To activate recovery database, run:"
    echo "mysql -u root -p -e 'RENAME TABLE ${DB_NAME} TO ${DB_NAME}_old, ${DB_NAME}_recovery TO ${DB_NAME};'"
else
    echo "Recovery failed. Please check backup file."
fi

# Restart web server
systemctl start apache2
```

#### 2. File Recovery
```bash
#!/bin/bash
# File recovery script

BACKUP_FILE="$1"
RESTORE_DIR="/var/www/html/integrated-crm"

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file.tar.gz>"
    exit 1
fi

# Create temporary restore directory
TEMP_DIR="/tmp/crm_restore_$(date +%s)"
mkdir -p $TEMP_DIR

# Extract backup
tar -xzf $BACKUP_FILE -C $TEMP_DIR

# Stop web server
systemctl stop apache2

# Backup current files
mv $RESTORE_DIR/public/uploads $RESTORE_DIR/public/uploads_backup_$(date +%s)

# Restore files
cp -r $TEMP_DIR/var/www/html/integrated-crm/public/uploads $RESTORE_DIR/public/

# Set proper permissions
chown -R www-data:www-data $RESTORE_DIR/public/uploads
chmod -R 755 $RESTORE_DIR/public/uploads

# Cleanup
rm -rf $TEMP_DIR

# Start web server
systemctl start apache2

echo "File recovery completed successfully"
```

---

## Performance Optimization

### Database Optimization

#### 1. Index Analysis
```sql
-- Check for missing indexes
SELECT 
    table_name,
    column_name,
    cardinality,
    sub_part,
    packed,
    null_field,
    index_type
FROM information_schema.statistics 
WHERE table_schema = 'integrated_crm'
ORDER BY table_name, seq_in_index;

-- Identify slow queries
SELECT 
    query_time,
    lock_time,
    rows_sent,
    rows_examined,
    sql_text
FROM mysql.slow_log 
WHERE start_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY query_time DESC
LIMIT 10;
```

#### 2. Query Optimization
```sql
-- Add indexes for common queries
CREATE INDEX idx_enquiries_status_assigned ON enquiries(status, assigned_to_user_id);
CREATE INDEX idx_students_institution_course ON students(institution_id, course);
CREATE INDEX idx_call_logs_enquiry_timestamp ON call_logs(enquiry_id, call_timestamp);
CREATE INDEX idx_certificate_requests_status ON certificate_requests(status, requested_at);

-- Optimize frequently used queries
-- Example: Enquiry filtering query optimization
EXPLAIN SELECT * FROM enquiries 
WHERE status = 'assigned' 
  AND assigned_to_user_id = 123 
  AND created_at >= '2024-01-01'
ORDER BY created_at DESC;
```

#### 3. Database Configuration Tuning
```ini
# MySQL configuration optimizations (/etc/mysql/mysql.conf.d/mysqld.cnf)

[mysqld]
# Memory settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_log_buffer_size = 64M

# Query cache
query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 2M

# Connection settings
max_connections = 200
max_connect_errors = 1000000

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Performance schema
performance_schema = ON
```

### Web Server Optimization

#### 1. Apache Configuration
```apache
# Apache performance tuning (/etc/apache2/apache2.conf)

# Enable compression
LoadModule deflate_module modules/mod_deflate.so
<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
    SetEnvIfNoCase Request_URI \
        \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
</Location>

# Enable caching
LoadModule expires_module modules/mod_expires.so
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
</IfModule>

# Worker configuration
<IfModule mpm_prefork_module>
    StartServers 8
    MinSpareServers 5
    MaxSpareServers 20
    ServerLimit 256
    MaxRequestWorkers 256
    MaxConnectionsPerChild 10000
</IfModule>
```

#### 2. PHP Optimization
```ini
# PHP performance settings (/etc/php/8.0/apache2/php.ini)

# Memory and execution
memory_limit = 256M
max_execution_time = 60
max_input_time = 60

# File uploads
upload_max_filesize = 10M
post_max_size = 20M
max_file_uploads = 20

# OPcache settings
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
opcache.fast_shutdown = 1

# Session settings
session.gc_maxlifetime = 3600
session.gc_probability = 1
session.gc_divisor = 100
```

---

## Security Maintenance

### Security Monitoring

#### 1. Failed Login Monitoring
```bash
#!/bin/bash
# Monitor failed login attempts

LOG_FILE="/var/www/html/integrated-crm/logs/security.log"
ALERT_THRESHOLD=10
TIME_WINDOW=60  # minutes

# Count failed logins in last hour
FAILED_LOGINS=$(grep "Failed login attempt" $LOG_FILE | \
    awk -v window=$TIME_WINDOW '
    BEGIN { 
        cmd = "date -d \"" window " minutes ago\" +%s"
        cmd | getline threshold
        close(cmd)
    }
    {
        cmd = "date -d \"" $1 " " $2 "\" +%s"
        cmd | getline timestamp
        close(cmd)
        if (timestamp > threshold) count++
    }
    END { print count+0 }')

if [ $FAILED_LOGINS -gt $ALERT_THRESHOLD ]; then
    echo "SECURITY ALERT: $FAILED_LOGINS failed login attempts in last $TIME_WINDOW minutes" | \
        mail -s "Security Alert: Multiple Failed Logins" admin@your-domain.com
fi
```

#### 2. File Integrity Monitoring
```bash
#!/bin/bash
# File integrity monitoring

CHECKSUM_FILE="/var/log/crm_checksums.txt"
APP_DIR="/var/www/html/integrated-crm"

# Generate checksums for critical files
find $APP_DIR -name "*.php" -type f -exec md5sum {} \; > /tmp/current_checksums.txt

# Compare with previous checksums
if [ -f $CHECKSUM_FILE ]; then
    CHANGES=$(diff $CHECKSUM_FILE /tmp/current_checksums.txt)
    if [ ! -z "$CHANGES" ]; then
        echo "File integrity check failed. Changes detected:" > /tmp/integrity_alert.txt
        echo "$CHANGES" >> /tmp/integrity_alert.txt
        mail -s "Security Alert: File Integrity Check Failed" admin@your-domain.com < /tmp/integrity_alert.txt
    fi
fi

# Update checksum file
cp /tmp/current_checksums.txt $CHECKSUM_FILE
```

### Security Updates

#### 1. Automated Security Updates
```bash
#!/bin/bash
# Automated security update script

# Update package lists
apt update

# Install security updates only
unattended-upgrades

# Check for PHP security updates
PHP_UPDATES=$(apt list --upgradable 2>/dev/null | grep php | wc -l)
if [ $PHP_UPDATES -gt 0 ]; then
    echo "PHP security updates available: $PHP_UPDATES packages" | \
        mail -s "Security Update: PHP Updates Available" admin@your-domain.com
fi

# Restart services if needed
if [ -f /var/run/reboot-required ]; then
    echo "System reboot required for security updates" | \
        mail -s "Security Update: Reboot Required" admin@your-domain.com
fi
```

#### 2. SSL Certificate Monitoring
```bash
#!/bin/bash
# SSL certificate expiration monitoring

DOMAIN="your-domain.com"
ALERT_DAYS=30

# Check certificate expiration
EXPIRY_DATE=$(echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null | \
    openssl x509 -noout -dates | grep notAfter | cut -d= -f2)

EXPIRY_TIMESTAMP=$(date -d "$EXPIRY_DATE" +%s)
CURRENT_TIMESTAMP=$(date +%s)
DAYS_UNTIL_EXPIRY=$(( ($EXPIRY_TIMESTAMP - $CURRENT_TIMESTAMP) / 86400 ))

if [ $DAYS_UNTIL_EXPIRY -lt $ALERT_DAYS ]; then
    echo "SSL certificate for $DOMAIN expires in $DAYS_UNTIL_EXPIRY days ($EXPIRY_DATE)" | \
        mail -s "Security Alert: SSL Certificate Expiring Soon" admin@your-domain.com
fi
```

---

## Troubleshooting Guide

### Common Issues and Solutions

#### 1. Database Connection Issues

**Symptoms:**
- "Database connection failed" errors
- Slow page loading
- Timeout errors

**Diagnosis:**
```bash
# Check MySQL service status
systemctl status mysql

# Check MySQL error log
tail -f /var/log/mysql/error.log

# Test database connection
mysql -u crm_user -p integrated_crm -e "SELECT 1;"

# Check active connections
mysql -u root -p -e "SHOW PROCESSLIST;"

# Check database size
mysql -u root -p -e "
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'integrated_crm';"
```

**Solutions:**
```bash
# Restart MySQL service
systemctl restart mysql

# Optimize database tables
mysql -u crm_user -p integrated_crm -e "OPTIMIZE TABLE users, enquiries, students;"

# Check and repair tables
mysql -u crm_user -p integrated_crm -e "REPAIR TABLE users, enquiries, students;"

# Increase connection limits (in /etc/mysql/mysql.conf.d/mysqld.cnf)
# max_connections = 200
# wait_timeout = 600
```

#### 2. File Upload Issues

**Symptoms:**
- File upload failures
- "Permission denied" errors
- Large files not uploading

**Diagnosis:**
```bash
# Check upload directory permissions
ls -la /var/www/html/integrated-crm/public/uploads/

# Check disk space
df -h /var/www/html/integrated-crm/

# Check PHP upload settings
php -i | grep -E "(upload_max_filesize|post_max_size|max_file_uploads)"

# Check Apache error log for upload errors
grep -i "upload" /var/log/apache2/error.log
```

**Solutions:**
```bash
# Fix permissions
chown -R www-data:www-data /var/www/html/integrated-crm/public/uploads/
chmod -R 755 /var/www/html/integrated-crm/public/uploads/

# Increase PHP upload limits (in php.ini)
# upload_max_filesize = 10M
# post_max_size = 20M
# max_file_uploads = 20

# Restart Apache
systemctl restart apache2
```

#### 3. Performance Issues

**Symptoms:**
- Slow page loading
- High server load
- Timeout errors

**Diagnosis:**
```bash
# Check server resources
top
htop
iotop

# Check Apache processes
ps aux | grep apache2

# Check slow queries
mysql -u root -p -e "
SELECT query_time, lock_time, rows_sent, rows_examined, sql_text 
FROM mysql.slow_log 
WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR) 
ORDER BY query_time DESC LIMIT 10;"

# Check Apache access log for slow requests
awk '$NF > 5000 { print $0 }' /var/log/apache2/access.log
```

**Solutions:**
```bash
# Enable PHP OPcache
# Add to php.ini: opcache.enable=1

# Optimize database
mysql -u crm_user -p integrated_crm -e "OPTIMIZE TABLE users, enquiries, students;"

# Add database indexes for slow queries
mysql -u crm_user -p integrated_crm -e "
CREATE INDEX idx_enquiries_status ON enquiries(status);
CREATE INDEX idx_students_institution ON students(institution_id);"

# Increase Apache worker limits
# Edit /etc/apache2/mods-available/mpm_prefork.conf
```

#### 4. Session Issues

**Symptoms:**
- Users getting logged out frequently
- "Session expired" errors
- Login loops

**Diagnosis:**
```bash
# Check session directory
ls -la /var/lib/php/sessions/

# Check session configuration
php -i | grep -E "(session\.|gc_)"

# Check session files
find /var/lib/php/sessions -name "sess_*" -mtime -1 | wc -l
```

**Solutions:**
```bash
# Fix session directory permissions
chown -R www-data:www-data /var/lib/php/sessions/
chmod 1733 /var/lib/php/sessions/

# Increase session timeout (in php.ini)
# session.gc_maxlifetime = 3600
# session.cookie_lifetime = 0

# Clean old session files
find /var/lib/php/sessions -name "sess_*" -mtime +1 -delete
```

---

## Log Analysis

### Log File Locations
- **Apache Access Log**: `/var/log/apache2/access.log`
- **Apache Error Log**: `/var/log/apache2/error.log`
- **MySQL Error Log**: `/var/log/mysql/error.log`
- **MySQL Slow Query Log**: `/var/log/mysql/slow.log`
- **Application Logs**: `/var/www/html/integrated-crm/logs/`
- **PHP Error Log**: `/var/log/php_errors.log`

### Log Analysis Scripts

#### 1. Apache Log Analysis
```bash
#!/bin/bash
# Apache log analysis script

ACCESS_LOG="/var/log/apache2/access.log"
ERROR_LOG="/var/log/apache2/error.log"

echo "=== Apache Log Analysis - $(date) ==="

# Top 10 IP addresses
echo "Top 10 IP addresses:"
awk '{print $1}' $ACCESS_LOG | sort | uniq -c | sort -nr | head -10

# Top 10 requested pages
echo -e "\nTop 10 requested pages:"
awk '{print $7}' $ACCESS_LOG | sort | uniq -c | sort -nr | head -10

# HTTP status codes
echo -e "\nHTTP status codes:"
awk '{print $9}' $ACCESS_LOG | sort | uniq -c | sort -nr

# Recent errors (last 100 lines)
echo -e "\nRecent errors:"
tail -100 $ERROR_LOG | grep "$(date '+%Y-%m-%d')"

# Slow requests (>5 seconds)
echo -e "\nSlow requests (>5 seconds):"
awk '$NF > 5000000 { print $0 }' $ACCESS_LOG | tail -10
```

#### 2. Database Log Analysis
```bash
#!/bin/bash
# Database log analysis script

MYSQL_ERROR_LOG="/var/log/mysql/error.log"
MYSQL_SLOW_LOG="/var/log/mysql/slow.log"

echo "=== Database Log Analysis - $(date) ==="

# Recent MySQL errors
echo "Recent MySQL errors:"
grep "$(date '+%Y-%m-%d')" $MYSQL_ERROR_LOG | tail -10

# Connection errors
echo -e "\nConnection errors:"
grep -i "connection" $MYSQL_ERROR_LOG | tail -5

# Slow queries summary
echo -e "\nSlow queries (if enabled):"
if [ -f $MYSQL_SLOW_LOG ]; then
    tail -20 $MYSQL_SLOW_LOG
else
    echo "Slow query log not enabled"
fi
```

#### 3. Application Log Analysis
```bash
#!/bin/bash
# Application log analysis script

APP_LOG_DIR="/var/www/html/integrated-crm/logs"

echo "=== Application Log Analysis - $(date) ==="

# Recent application errors
echo "Recent application errors:"
find $APP_LOG_DIR -name "*.log" -exec grep "ERROR" {} \; | tail -10

# Login attempts
echo -e "\nRecent login attempts:"
find $APP_LOG_DIR -name "*.log" -exec grep "login" {} \; | tail -10

# File upload errors
echo -e "\nFile upload errors:"
find $APP_LOG_DIR -name "*.log" -exec grep -i "upload" {} \; | tail -5

# Database errors
echo -e "\nDatabase errors:"
find $APP_LOG_DIR -name "*database*.log" -exec tail -5 {} \;
```

---

## Database Maintenance

### Regular Database Tasks

#### 1. Database Health Check
```sql
-- Check database size and growth
SELECT 
    table_schema AS 'Database',
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    table_rows AS 'Rows'
FROM information_schema.TABLES 
WHERE table_schema = 'integrated_crm'
ORDER BY (data_length + index_length) DESC;

-- Check for table corruption
CHECK TABLE users, institutions, students, enquiries, marklists, certificate_requests, call_logs;

-- Analyze table statistics
ANALYZE TABLE users, institutions, students, enquiries, marklists, certificate_requests, call_logs;
```

#### 2. Index Maintenance
```sql
-- Check index usage
SELECT 
    t.table_name,
    t.table_rows,
    s.index_name,
    s.column_name,
    s.cardinality
FROM information_schema.tables t
LEFT JOIN information_schema.statistics s ON t.table_name = s.table_name
WHERE t.table_schema = 'integrated_crm'
  AND t.table_type = 'BASE TABLE'
ORDER BY t.table_name, s.seq_in_index;

-- Find unused indexes
SELECT 
    object_schema,
    object_name,
    index_name
FROM performance_schema.table_io_waits_summary_by_index_usage 
WHERE index_name IS NOT NULL
  AND count_star = 0
  AND object_schema = 'integrated_crm'
ORDER BY object_schema, object_name;
```

#### 3. Data Cleanup
```sql
-- Clean up old session data
DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Clean up old temporary data
DELETE FROM temp_uploads WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Archive old enquiries
INSERT INTO enquiries_archive 
SELECT * FROM enquiries 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR) 
  AND status IN ('enrolled', 'not_interested');

-- Update statistics after cleanup
ANALYZE TABLE enquiries, sessions;
```

---

## Emergency Procedures

### System Down Procedures

#### 1. Web Server Down
```bash
#!/bin/bash
# Emergency web server recovery

echo "=== Web Server Emergency Recovery ==="

# Check if Apache is running
if ! systemctl is-active --quiet apache2; then
    echo "Apache is down. Attempting restart..."
    
    # Try to start Apache
    systemctl start apache2
    
    # Check if it started successfully
    if systemctl is-active --quiet apache2; then
        echo "Apache restarted successfully"
    else
        echo "Apache failed to start. Checking configuration..."
        
        # Test Apache configuration
        apache2ctl configtest
        
        # Check error log
        tail -20 /var/log/apache2/error.log
        
        # Try to fix common issues
        # Check disk space
        df -h
        
        # Check permissions
        ls -la /var/www/html/integrated-crm/
        
        echo "Manual intervention required. Check logs and configuration."
    fi
else
    echo "Apache is running normally"
fi
```

#### 2. Database Down
```bash
#!/bin/bash
# Emergency database recovery

echo "=== Database Emergency Recovery ==="

# Check if MySQL is running
if ! systemctl is-active --quiet mysql; then
    echo "MySQL is down. Attempting restart..."
    
    # Try to start MySQL
    systemctl start mysql
    
    # Check if it started successfully
    if systemctl is-active --quiet mysql; then
        echo "MySQL restarted successfully"
        
        # Test database connection
        mysql -u crm_user -p -e "SELECT 1;" integrated_crm
        
    else
        echo "MySQL failed to start. Checking for issues..."
        
        # Check MySQL error log
        tail -20 /var/log/mysql/error.log
        
        # Check disk space
        df -h /var/lib/mysql
        
        # Check MySQL process
        ps aux | grep mysql
        
        echo "Manual intervention required. Check MySQL error log."
    fi
else
    echo "MySQL is running normally"
fi
```

#### 3. Full System Recovery
```bash
#!/bin/bash
# Full system emergency recovery

echo "=== Full System Emergency Recovery ==="

# Stop all services
systemctl stop apache2
systemctl stop mysql

# Check system resources
echo "System resources:"
free -h
df -h

# Check for critical errors
echo "Recent critical errors:"
journalctl -p err -n 20

# Start services in order
echo "Starting MySQL..."
systemctl start mysql
sleep 5

if systemctl is-active --quiet mysql; then
    echo "MySQL started successfully"
    
    echo "Starting Apache..."
    systemctl start apache2
    sleep 3
    
    if systemctl is-active --quiet apache2; then
        echo "Apache started successfully"
        echo "System recovery completed"
        
        # Test application
        curl -s -o /dev/null -w "%{http_code}" http://localhost/
        
    else
        echo "Apache failed to start"
    fi
else
    echo "MySQL failed to start - system recovery failed"
fi
```

### Contact Information

#### Emergency Contacts
- **System Administrator**: admin@your-domain.com / +1-XXX-XXX-XXXX
- **Database Administrator**: dba@your-domain.com / +1-XXX-XXX-XXXX
- **Hosting Provider**: support@hosting-provider.com / +1-XXX-XXX-XXXX
- **Emergency Escalation**: emergency@your-domain.com / +1-XXX-XXX-XXXX

#### Escalation Procedures
1. **Level 1**: System administrator attempts resolution (0-30 minutes)
2. **Level 2**: Database administrator involved (30-60 minutes)
3. **Level 3**: Hosting provider contacted (60+ minutes)
4. **Level 4**: Emergency escalation for critical business impact

---

**End of Maintenance Guide**

This comprehensive maintenance guide covers all aspects of system administration, monitoring, and troubleshooting for the Integrated Web Portal & CRM system. Regular adherence to these procedures will ensure optimal system performance and reliability.