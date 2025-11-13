# Installation and Configuration Guide

## Integrated Web Portal & CRM System

### Table of Contents
1. [System Requirements](#system-requirements)
2. [Pre-Installation Setup](#pre-installation-setup)
3. [Database Setup](#database-setup)
4. [Application Installation](#application-installation)
5. [Configuration](#configuration)
6. [Security Configuration](#security-configuration)
7. [Testing Installation](#testing-installation)
8. [Troubleshooting](#troubleshooting)

---

## System Requirements

### Server Requirements
- **Operating System**: Linux (Ubuntu 20.04+ recommended) or Windows Server 2019+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: Version 7.4 or higher (PHP 8.0+ recommended)
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Memory**: Minimum 2GB RAM (4GB+ recommended)
- **Storage**: Minimum 10GB free space (50GB+ recommended for production)

### PHP Extensions Required
```bash
# Required PHP extensions
php-mysql
php-pdo
php-json
php-mbstring
php-xml
php-curl
php-gd
php-zip
php-fileinfo
php-openssl
```

### Optional but Recommended
- **SSL Certificate** for HTTPS
- **Redis** or **Memcached** for caching
- **Elasticsearch** for advanced search (optional)

---

## Pre-Installation Setup

### 1. Update System Packages
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

### 2. Install Web Server (Apache)
```bash
# Ubuntu/Debian
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2

# Enable required modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
```

### 3. Install PHP and Extensions
```bash
# Ubuntu/Debian
sudo apt install php php-mysql php-pdo php-json php-mbstring php-xml php-curl php-gd php-zip php-fileinfo php-openssl -y

# Verify PHP installation
php -v
```

### 4. Install MySQL/MariaDB
```bash
# Ubuntu/Debian (MySQL)
sudo apt install mysql-server -y
sudo systemctl enable mysql
sudo systemctl start mysql

# Secure MySQL installation
sudo mysql_secure_installation
```

---

## Database Setup

### 1. Create Database and User
```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE integrated_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant privileges
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON integrated_crm.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

### 2. Import Database Schema
```bash
# Navigate to project directory
cd /var/www/html/integrated-crm

# Import database schema
mysql -u crm_user -p integrated_crm < database/setup.sql

# Verify tables were created
mysql -u crm_user -p integrated_crm -e "SHOW TABLES;"
```

### 3. Create Database Views (Optional)
```bash
# Import filter views for better performance
mysql -u crm_user -p integrated_crm < database/create_filter_views.sql
```

---

## Application Installation

### 1. Download and Extract Files
```bash
# Create web directory
sudo mkdir -p /var/www/html/integrated-crm

# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/integrated-crm

# Extract application files (assuming you have the source)
# Copy all files to /var/www/html/integrated-crm/
```

### 2. Set Directory Permissions
```bash
# Set proper permissions
sudo chmod -R 755 /var/www/html/integrated-crm
sudo chmod -R 775 /var/www/html/integrated-crm/public/uploads
sudo chmod -R 775 /var/www/html/integrated-crm/logs

# Create necessary directories
sudo mkdir -p /var/www/html/integrated-crm/public/uploads/{enquiry_documents,student_photos,marklist_files,certificates}
sudo mkdir -p /var/www/html/integrated-crm/logs
```

### 3. Configure Apache Virtual Host
```apache
# Create virtual host file
sudo nano /etc/apache2/sites-available/integrated-crm.conf

# Add the following configuration:
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/integrated-crm/public
    
    <Directory /var/www/html/integrated-crm/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/integrated-crm_error.log
    CustomLog ${APACHE_LOG_DIR}/integrated-crm_access.log combined
</VirtualHost>

# Enable the site
sudo a2ensite integrated-crm.conf
sudo systemctl reload apache2
```

---

## Configuration

### 1. Database Configuration
```php
# Edit includes/config.php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'integrated_crm');
define('DB_USER', 'crm_user');
define('DB_PASS', 'secure_password_here');
define('DB_CHARSET', 'utf8mb4');

// System configuration
define('SYSTEM_ACCESS', true);
define('DEBUG_MODE', false); // Set to true for development
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/');

// Security configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// File upload configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Email configuration (if using email notifications)
define('SMTP_HOST', 'your-smtp-server.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@domain.com');
define('SMTP_PASSWORD', 'your-email-password');
?>
```

### 2. Web Server Configuration
```apache
# Edit public/.htaccess
RewriteEngine On

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Prevent access to sensitive files
<Files "*.php~">
    Deny from all
</Files>

<Files "*.log">
    Deny from all
</Files>

# Clean URLs
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)/?$ $1.php [L,QSA]

# Protect includes directory
RewriteRule ^includes/ - [F,L]

# File upload security
<Directory "uploads">
    php_flag engine off
    AddType text/plain .php .php3 .phtml .pht
</Directory>
```

---

## Security Configuration

### 1. SSL/HTTPS Setup
```bash
# Install Certbot for Let's Encrypt (recommended)
sudo apt install certbot python3-certbot-apache -y

# Obtain SSL certificate
sudo certbot --apache -d your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. Firewall Configuration
```bash
# Configure UFW (Ubuntu Firewall)
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Apache Full'
sudo ufw status
```

### 3. PHP Security Settings
```ini
# Edit /etc/php/7.4/apache2/php.ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
max_execution_time = 30
max_input_time = 60
memory_limit = 256M
post_max_size = 20M
upload_max_filesize = 10M
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

---

## Testing Installation

### 1. Basic Functionality Test
```bash
# Test database connection
php -r "
require_once '/var/www/html/integrated-crm/includes/config.php';
require_once '/var/www/html/integrated-crm/includes/db_connect.php';
try {
    \$db = getDB();
    echo 'Database connection: SUCCESS\n';
} catch (Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . '\n';
}
"
```

### 2. Web Interface Test
1. Open browser and navigate to `https://your-domain.com`
2. Verify home page loads correctly
3. Test login functionality with default admin account
4. Check all portal redirections work properly

### 3. File Upload Test
1. Navigate to enquiry form
2. Try uploading a test document
3. Verify file is stored in correct directory
4. Check file permissions are correct

### 4. Run Integration Tests
```bash
# Run the integration test suite
cd /var/www/html/integrated-crm
php tests/SimpleIntegrationTest.php
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. Database Connection Failed
**Problem**: "Database connection failed" error
**Solutions**:
- Verify MySQL service is running: `sudo systemctl status mysql`
- Check database credentials in `includes/config.php`
- Ensure database user has proper privileges
- Test connection manually: `mysql -u crm_user -p integrated_crm`

#### 2. Permission Denied Errors
**Problem**: File upload or logging errors
**Solutions**:
```bash
# Fix ownership and permissions
sudo chown -R www-data:www-data /var/www/html/integrated-crm
sudo chmod -R 755 /var/www/html/integrated-crm
sudo chmod -R 775 /var/www/html/integrated-crm/public/uploads
sudo chmod -R 775 /var/www/html/integrated-crm/logs
```

#### 3. 500 Internal Server Error
**Problem**: Server error when accessing pages
**Solutions**:
- Check Apache error log: `sudo tail -f /var/log/apache2/error.log`
- Verify PHP syntax: `php -l /var/www/html/integrated-crm/public/index.php`
- Check .htaccess file syntax
- Ensure all required PHP extensions are installed

#### 4. Session Issues
**Problem**: Users getting logged out frequently
**Solutions**:
- Check session directory permissions
- Verify session configuration in php.ini
- Increase session timeout in config.php
- Check server time synchronization

#### 5. File Upload Issues
**Problem**: Files not uploading or upload errors
**Solutions**:
- Check PHP upload settings in php.ini
- Verify upload directory permissions
- Check available disk space
- Review file size limits

### Log Files to Check
- Apache Error Log: `/var/log/apache2/error.log`
- Apache Access Log: `/var/log/apache2/access.log`
- PHP Error Log: `/var/log/php_errors.log`
- Application Error Log: `/var/www/html/integrated-crm/logs/system.log`
- Database Error Log: `/var/www/html/integrated-crm/logs/database_errors.log`

### Performance Optimization
1. **Enable PHP OPcache**:
   ```ini
   # Add to php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=4000
   ```

2. **Configure MySQL for Performance**:
   ```ini
   # Add to /etc/mysql/mysql.conf.d/mysqld.cnf
   innodb_buffer_pool_size = 1G
   query_cache_type = 1
   query_cache_size = 64M
   ```

3. **Enable Apache Compression**:
   ```apache
   # Enable mod_deflate
   sudo a2enmod deflate
   
   # Add to virtual host
   <Location />
       SetOutputFilter DEFLATE
   </Location>
   ```

---

## Support and Maintenance

### Regular Maintenance Tasks
1. **Database Backup** (Daily):
   ```bash
   mysqldump -u crm_user -p integrated_crm > backup_$(date +%Y%m%d).sql
   ```

2. **Log Rotation** (Weekly):
   ```bash
   # Configure logrotate for application logs
   sudo nano /etc/logrotate.d/integrated-crm
   ```

3. **Security Updates** (Monthly):
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

4. **Performance Monitoring**:
   - Monitor disk space usage
   - Check database performance
   - Review error logs regularly

### Getting Help
- Check the troubleshooting section above
- Review log files for specific error messages
- Run the integration test suite to identify issues
- Contact system administrator for advanced issues

---

**Installation Complete!** 

Your Integrated Web Portal & CRM system should now be fully operational. Remember to:
- Change default passwords
- Configure regular backups
- Monitor system performance
- Keep software updated
- Review security logs regularly