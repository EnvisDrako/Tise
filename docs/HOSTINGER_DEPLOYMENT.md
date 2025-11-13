# Hostinger Deployment Guide

## Integrated Web Portal & CRM System - Hostinger Setup

### Table of Contents
1. [Prerequisites](#prerequisites)
2. [Hostinger Account Setup](#hostinger-account-setup)
3. [File Upload and Structure](#file-upload-and-structure)
4. [Database Configuration](#database-configuration)
5. [Domain and SSL Setup](#domain-and-ssl-setup)
6. [Configuration Files](#configuration-files)
7. [File Permissions](#file-permissions)
8. [Testing the Installation](#testing-the-installation)
9. [Troubleshooting](#troubleshooting)
10. [Maintenance and Updates](#maintenance-and-updates)

---

## Prerequisites

### Hostinger Plan Requirements
- **Minimum Plan**: Premium Shared Hosting or higher
- **Recommended**: Business Shared Hosting or VPS
- **Requirements**:
  - PHP 7.4+ (8.0+ recommended)
  - MySQL 8.0+ or MariaDB 10.5+
  - At least 2GB storage (5GB+ recommended)
  - SSL certificate support
  - Cron jobs support (for automated tasks)

### What You'll Need
- Hostinger hosting account
- Domain name (optional, can use subdomain)
- FTP client (FileZilla recommended) or use Hostinger File Manager
- Database management access (phpMyAdmin)
- Your project files (downloaded/cloned from repository)

---

## Hostinger Account Setup

### 1. Access Hostinger Control Panel
1. Login to your Hostinger account at https://www.hostinger.com
2. Go to **Hosting** → **Manage** for your hosting plan
3. Access the **hPanel** (Hostinger Control Panel)

### 2. PHP Configuration
1. In hPanel, go to **Advanced** → **PHP Configuration**
2. Select **PHP 8.0** or higher
3. Enable the following extensions:
   - `pdo_mysql`
   - `mysqli`
   - `json`
   - `mbstring`
   - `fileinfo`
   - `gd`
   - `curl`
   - `zip`
   - `openssl`

### 3. PHP Settings Optimization
In **PHP Configuration** → **PHP Options**, set:
```ini
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 20M
upload_max_filesize = 10M
max_file_uploads = 20
```

---

## File Upload and Structure

### Option 1: Using Hostinger File Manager (Recommended)

#### 1. Access File Manager
1. In hPanel, go to **Files** → **File Manager**
2. Navigate to `public_html` directory
3. Create a new folder called `crm` (or your preferred name)

#### 2. Upload Project Files
1. **Compress your project**: Create a ZIP file of all project files
2. **Upload ZIP**: Use File Manager's upload feature to upload the ZIP
3. **Extract**: Right-click the ZIP file and select "Extract"
4. **Move files**: Move all extracted files to the root of your `crm` folder

### Option 2: Using FTP Client (FileZilla)

#### 1. Get FTP Credentials
1. In hPanel, go to **Files** → **FTP Accounts**
2. Note down:
   - **FTP Server**: Usually `ftp.your-domain.com`
   - **Username**: Your hosting username
   - **Password**: Your hosting password
   - **Port**: 21 (or 22 for SFTP)

#### 2. Upload Files
1. **Connect**: Use FileZilla to connect to your FTP server
2. **Navigate**: Go to `public_html/crm` (create `crm` folder if needed)
3. **Upload**: Upload all project files to this directory

### Recommended Directory Structure
```
public_html/
└── crm/                          # Your project folder
    ├── public/                   # Web-accessible files
    │   ├── index.php
    │   ├── login.php
    │   ├── admin/
    │   ├── institution/
    │   ├── enquiry/
    │   ├── api/
    │   ├── css/
    │   ├── js/
    │   └── uploads/              # File upload directory
    ├── includes/                 # Backend files (protected)
    ├── database/                 # Database scripts
    ├── docs/                     # Documentation
    └── logs/                     # Log files (create this)
```

---

## Database Configuration

### 1. Create MySQL Database
1. In hPanel, go to **Databases** → **MySQL Databases**
2. Click **Create New Database**
3. **Database Name**: `u123456789_crm` (Hostinger format)
4. **Username**: Create a new user (e.g., `u123456789_crmuser`)
5. **Password**: Generate a strong password
6. **Privileges**: Grant all privileges to the user

### 2. Import Database Schema
1. Go to **Databases** → **phpMyAdmin**
2. Select your newly created database
3. Click **Import** tab
4. **Choose File**: Upload `database/setup.sql`
5. **Format**: SQL
6. Click **Go** to import

### 3. Verify Database Import
1. Check that all tables were created (should see ~15+ tables)
2. Verify the default admin user exists:
   ```sql
   SELECT * FROM users WHERE role = 'admin';
   ```

---

## Domain and SSL Setup

### 1. Domain Configuration
#### Option A: Main Domain
- If using your main domain, files go in `public_html/`
- Access: `https://yourdomain.com`

#### Option B: Subdomain
1. In hPanel, go to **Domains** → **Subdomains**
2. Create subdomain: `crm.yourdomain.com`
3. Point to `/public_html/crm/public`
4. Access: `https://crm.yourdomain.com`

#### Option C: Subdirectory
- Files in `public_html/crm/`
- Access: `https://yourdomain.com/crm`

### 2. SSL Certificate
1. In hPanel, go to **Security** → **SSL/TLS**
2. **Enable SSL** for your domain/subdomain
3. **Force HTTPS**: Enable redirect from HTTP to HTTPS
4. Wait 10-15 minutes for SSL to activate

---

## Configuration Files

### 1. Create Configuration File
Create `includes/config.php` with your Hostinger database details:

```php
<?php
/**
 * Hostinger Production Configuration
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

// Database configuration (Replace with your Hostinger database details)
define('DB_HOST', 'localhost');  // Usually localhost for Hostinger
define('DB_NAME', 'u123456789_crm');  // Your database name
define('DB_USER', 'u123456789_crmuser');  // Your database username
define('DB_PASS', 'your_strong_password');  // Your database password
define('DB_CHARSET', 'utf8mb4');

// System configuration
define('DEBUG_MODE', false);  // IMPORTANT: Set to false for production
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/');

// Security configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// File upload configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');

// Site configuration
define('BASE_URL', 'https://yourdomain.com/crm'); // Update with your URL
define('SITE_NAME', 'Your Institution CRM');

// JWT Secret for API (Generate a random 64-character string)
define('JWT_SECRET', 'your-super-secure-jwt-secret-key-64-characters-long-change-this');

// Email configuration (Optional - configure with your email provider)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'CRM System');

// Timezone (Set to your timezone)
date_default_timezone_set('America/New_York');

// Production security headers
if (!DEBUG_MODE) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
?>
```

### 2. Create .htaccess File
Create `public/.htaccess` for security and URL rewriting:

```apache
# Security and Performance Configuration for Hostinger

# Prevent access to sensitive files
<Files "*.php~">
    Deny from all
</Files>

<Files "*.log">
    Deny from all
</Files>

<Files "config.php">
    Deny from all
</Files>

# Protect includes directory
RewriteEngine On
RewriteRule ^includes/ - [F,L]
RewriteRule ^logs/ - [F,L]
RewriteRule ^database/ - [F,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';"
</IfModule>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/icon "access plus 1 year"
    ExpiresByType text/plain "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
</IfModule>

# File upload security
<Directory "uploads">
    php_flag engine off
    AddType text/plain .php .php3 .phtml .pht .jsp .asp .sh .cgi
    <Files "*.php">
        Deny from all
    </Files>
</Directory>

# Prevent directory browsing
Options -Indexes

# Custom error pages (optional)
ErrorDocument 404 /crm/404.html
ErrorDocument 500 /crm/500.html
```

---

## File Permissions

### 1. Set Correct Permissions
Using File Manager or FTP client, set these permissions:

```
public/                     755
public/uploads/             755 (writable)
public/uploads/enquiry_documents/  755
public/uploads/student_photos/     755
public/uploads/marklist_files/     755
public/uploads/certificates/       755
logs/                       755 (writable)
includes/                   755
includes/config.php         644
public/.htaccess           644
```

### 2. Create Required Directories
Ensure these directories exist and are writable:
- `public/uploads/enquiry_documents/`
- `public/uploads/student_photos/`
- `public/uploads/marklist_files/`
- `public/uploads/certificates/`
- `logs/`

---

## Testing the Installation

### 1. Basic Connectivity Test
1. **Visit your site**: Go to `https://yourdomain.com/crm`
2. **Check homepage**: Should load without errors
3. **Test login**: Go to `/login.php`
4. **Database test**: Login with admin credentials

### 2. Default Login Credentials
- **Username**: `admin`
- **Password**: `admin123`
- **⚠️ IMPORTANT**: Change this password immediately after first login!

### 3. Functionality Tests
1. **Admin Portal**: Test user creation and management
2. **File Upload**: Try uploading a test document
3. **Database**: Check if data is being saved
4. **API**: Test API endpoints at `/api/`

### 4. Performance Test
1. **Page Load Speed**: Should load within 3-5 seconds
2. **Database Queries**: Check for any slow queries
3. **File Operations**: Test upload/download functionality

---

## Troubleshooting

### Common Hostinger Issues

#### 1. Database Connection Errors
**Error**: "Database connection failed"
**Solutions**:
```php
// In config.php, try these variations:
define('DB_HOST', 'localhost');
// OR
define('DB_HOST', '127.0.0.1');
// OR (check your Hostinger database details)
define('DB_HOST', 'mysql.hostinger.com');
```

#### 2. File Permission Issues
**Error**: "Permission denied" for uploads
**Solutions**:
1. Set upload directories to 755 or 777
2. Check if PHP has write permissions
3. Contact Hostinger support if needed

#### 3. PHP Memory Limit
**Error**: "Fatal error: Allowed memory size exhausted"
**Solutions**:
1. Increase memory limit in hPanel → PHP Configuration
2. Add to `.htaccess`:
   ```apache
   php_value memory_limit 256M
   ```

#### 4. SSL Certificate Issues
**Error**: "Not secure" warning
**Solutions**:
1. Wait 15-30 minutes after enabling SSL
2. Clear browser cache
3. Check SSL status in hPanel
4. Contact Hostinger support

#### 5. File Upload Size Limits
**Error**: Files not uploading
**Solutions**:
1. Check PHP settings in hPanel
2. Increase `upload_max_filesize` and `post_max_size`
3. Add to `.htaccess`:
   ```apache
   php_value upload_max_filesize 10M
   php_value post_max_size 20M
   ```

### Debug Mode
For troubleshooting, temporarily enable debug mode:
```php
// In config.php
define('DEBUG_MODE', true);
```
**Remember to disable it after fixing issues!**

---

## Maintenance and Updates

### 1. Regular Backups
#### Database Backup
1. Go to **Databases** → **phpMyAdmin**
2. Select your database
3. Click **Export**
4. Choose **Quick** export method
5. Download the SQL file

#### File Backup
1. Use **File Manager** to create ZIP of your files
2. Download the ZIP file
3. Store backups securely

### 2. Automated Backups (Recommended)
Set up automated backups in hPanel:
1. Go to **Files** → **Backups**
2. Enable automatic backups
3. Choose backup frequency (daily/weekly)

### 3. Cron Jobs for Maintenance
1. In hPanel, go to **Advanced** → **Cron Jobs**
2. Add these maintenance tasks:

```bash
# Daily cleanup (runs at 2 AM)
0 2 * * * /usr/bin/php /home/username/public_html/crm/maintenance/daily_cleanup.php

# Weekly database optimization (runs Sunday 3 AM)
0 3 * * 0 /usr/bin/php /home/username/public_html/crm/maintenance/weekly_maintenance.php
```

### 4. Security Updates
1. **Regular Updates**: Keep system files updated
2. **Password Changes**: Change admin passwords regularly
3. **Monitor Logs**: Check error logs for suspicious activity
4. **SSL Renewal**: Hostinger auto-renews, but monitor status

### 5. Performance Monitoring
1. **Page Speed**: Use Google PageSpeed Insights
2. **Uptime**: Monitor site availability
3. **Database**: Check for slow queries
4. **Storage**: Monitor disk usage in hPanel

---

## Production Checklist

### Before Going Live
- [ ] Database imported successfully
- [ ] Configuration file created with correct credentials
- [ ] Debug mode disabled (`DEBUG_MODE = false`)
- [ ] Default admin password changed
- [ ] SSL certificate active and working
- [ ] File permissions set correctly
- [ ] All upload directories created and writable
- [ ] .htaccess file configured
- [ ] Error pages created (404, 500)
- [ ] Backup system configured
- [ ] Email configuration tested (if using)
- [ ] All functionality tested
- [ ] Performance optimized

### Security Hardening
- [ ] Strong database passwords
- [ ] JWT secret key changed
- [ ] File upload restrictions in place
- [ ] Directory browsing disabled
- [ ] Sensitive files protected
- [ ] Security headers configured
- [ ] Regular backup schedule
- [ ] Monitoring and logging enabled

---

## Support and Resources

### Hostinger Resources
- **Knowledge Base**: https://support.hostinger.com
- **Live Chat**: Available 24/7 in hPanel
- **Community Forum**: https://community.hostinger.com
- **Video Tutorials**: https://www.hostinger.com/tutorials

### System Support
- **Documentation**: Check `/docs/` folder
- **API Documentation**: `/api/README.md`
- **Troubleshooting**: `/docs/MAINTENANCE_GUIDE.md`

### Emergency Contacts
- **Hostinger Support**: Available 24/7 via live chat
- **System Issues**: Check error logs in `/logs/` directory
- **Database Issues**: Use phpMyAdmin for direct database access

---

## Quick Reference

### Important URLs
- **Main Site**: `https://yourdomain.com/crm`
- **Admin Login**: `https://yourdomain.com/crm/login.php`
- **API Docs**: `https://yourdomain.com/crm/api/`
- **phpMyAdmin**: Access via hPanel → Databases

### Default Credentials
- **Admin**: `admin` / `admin123` (CHANGE IMMEDIATELY)

### Key Files
- **Config**: `includes/config.php`
- **Security**: `public/.htaccess`
- **Database**: `database/setup.sql`
- **Logs**: `logs/` directory

### Hostinger Limits (Shared Hosting)
- **Storage**: Varies by plan (2GB - Unlimited)
- **Bandwidth**: Usually unlimited
- **Databases**: 100-300 depending on plan
- **Email Accounts**: 100-Unlimited
- **Subdomains**: 100-Unlimited

---

**🎉 Congratulations!** Your Integrated Web Portal & CRM system should now be running successfully on Hostinger. 

For additional help, refer to the comprehensive documentation in the `/docs/` folder or contact Hostinger support for hosting-related issues.