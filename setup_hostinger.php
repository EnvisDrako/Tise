<?php
/**
 * Hostinger Deployment Setup Script
 * Run this script after uploading files to Hostinger to verify setup
 */

// Basic security check
$allowed_ips = ['127.0.0.1', '::1']; // Add your IP if needed
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

echo "<!DOCTYPE html>";
echo "<html><head><title>Hostinger CRM Setup</title>";
echo "<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
.success { color: green; }
.warning { color: orange; }
.error { color: red; }
.info { color: blue; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.button { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
.code { background: #f4f4f4; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
</style></head><body>";

echo "<h1>🚀 Hostinger CRM Setup Verification</h1>";
echo "<p>This script helps verify your Hostinger deployment setup.</p>";

// Security warning
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<strong>⚠️ Security Notice:</strong> Delete this file after setup is complete!";
echo "</div>";

// Check if config file exists
echo "<h2>📋 Configuration Check</h2>";

if (!file_exists('includes/config.php')) {
    echo "<p class='warning'>⚠️ Configuration file not found!</p>";
    echo "<p><strong>Action Required:</strong></p>";
    echo "<ol>";
    echo "<li>Copy <code>includes/config.hostinger.php</code> to <code>includes/config.php</code></li>";
    echo "<li>Edit <code>includes/config.php</code> with your Hostinger database details</li>";
    echo "<li>Update the following values:</li>";
    echo "</ol>";
    
    echo "<div class='code'>";
    echo "define('DB_NAME', 'u123456789_crm');  // Your database name<br>";
    echo "define('DB_USER', 'u123456789_crmuser');  // Your database user<br>";
    echo "define('DB_PASS', 'your_password');  // Your database password<br>";
    echo "define('BASE_URL', 'https://yourdomain.com/crm');  // Your site URL<br>";
    echo "define('JWT_SECRET', 'random-64-char-string');  // Generate random string<br>";
    echo "</div>";
    
} else {
    echo "<p class='success'>✅ Configuration file exists</p>";
    
    // Test configuration
    define('SYSTEM_ACCESS', true);
    require_once 'includes/config.php';
    
    echo "<h3>Configuration Values</h3>";
    echo "<table>";
    echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
    
    // Check database settings
    echo "<tr><td>Database Host</td><td>" . DB_HOST . "</td><td>";
    echo (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1') ? "<span class='success'>✅ Good</span>" : "<span class='warning'>⚠️ Check</span>";
    echo "</td></tr>";
    
    echo "<tr><td>Database Name</td><td>" . DB_NAME . "</td><td>";
    echo (strpos(DB_NAME, 'u') === 0) ? "<span class='success'>✅ Hostinger format</span>" : "<span class='warning'>⚠️ Check format</span>";
    echo "</td></tr>";
    
    echo "<tr><td>Debug Mode</td><td>" . (DEBUG_MODE ? 'Enabled' : 'Disabled') . "</td><td>";
    echo (!DEBUG_MODE) ? "<span class='success'>✅ Production ready</span>" : "<span class='error'>❌ Disable for production</span>";
    echo "</td></tr>";
    
    echo "<tr><td>Base URL</td><td>" . BASE_URL . "</td><td>";
    echo (strpos(BASE_URL, 'https://') === 0) ? "<span class='success'>✅ HTTPS</span>" : "<span class='warning'>⚠️ Use HTTPS</span>";
    echo "</td></tr>";
    
    echo "<tr><td>JWT Secret</td><td>" . (strlen(JWT_SECRET) > 32 ? 'Set (' . strlen(JWT_SECRET) . ' chars)' : 'Too short') . "</td><td>";
    echo (strlen(JWT_SECRET) >= 32) ? "<span class='success'>✅ Good</span>" : "<span class='error'>❌ Too short</span>";
    echo "</td></tr>";
    
    echo "</table>";
}

// Database connection test
echo "<h2>🗄️ Database Connection</h2>";

if (file_exists('includes/config.php')) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='success'>✅ Database connection successful</p>";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Database Tables</h3>";
        if (count($tables) > 0) {
            echo "<p class='success'>✅ Found " . count($tables) . " tables</p>";
            
            $expected_tables = ['users', 'institutions', 'students', 'enquiries', 'marklists', 'certificate_requests', 'call_logs'];
            $missing_tables = array_diff($expected_tables, $tables);
            
            if (empty($missing_tables)) {
                echo "<p class='success'>✅ All required tables present</p>";
                
                // Check admin user
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                $adminCount = $stmt->fetchColumn();
                
                if ($adminCount > 0) {
                    echo "<p class='success'>✅ Admin user exists</p>";
                } else {
                    echo "<p class='error'>❌ No admin user found</p>";
                }
                
            } else {
                echo "<p class='error'>❌ Missing tables: " . implode(', ', $missing_tables) . "</p>";
            }
            
            echo "<details><summary>View all tables</summary><ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul></details>";
            
        } else {
            echo "<p class='error'>❌ No tables found - database schema not imported</p>";
            echo "<p><strong>Action Required:</strong></p>";
            echo "<ol>";
            echo "<li>Go to <a href='https://hpanel.hostinger.com' target='_blank'>Hostinger hPanel</a></li>";
            echo "<li>Navigate to Databases → phpMyAdmin</li>";
            echo "<li>Select your database: <strong>" . DB_NAME . "</strong></li>";
            echo "<li>Click 'Import' tab</li>";
            echo "<li>Upload file: <code>database/setup.sql</code></li>";
            echo "<li>Click 'Go' to import</li>";
            echo "</ol>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
        echo "<p><strong>Common Solutions:</strong></p>";
        echo "<ul>";
        echo "<li>Verify database credentials in <code>includes/config.php</code></li>";
        echo "<li>Check database name format (should start with 'u' for Hostinger)</li>";
        echo "<li>Ensure database user has proper privileges</li>";
        echo "<li>Try DB_HOST as '127.0.0.1' instead of 'localhost'</li>";
        echo "</ul>";
    }
} else {
    echo "<p class='warning'>⚠️ Cannot test - configuration file missing</p>";
}

// PHP Environment Check
echo "<h2>🐘 PHP Environment</h2>";

echo "<table>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td><td>";
echo (version_compare(PHP_VERSION, '7.4.0', '>=')) ? "<span class='success'>✅ Good</span>" : "<span class='error'>❌ Upgrade needed</span>";
echo "</td></tr>";

$memory_limit = ini_get('memory_limit');
echo "<tr><td>Memory Limit</td><td>$memory_limit</td><td>";
echo (intval($memory_limit) >= 128) ? "<span class='success'>✅ Good</span>" : "<span class='warning'>⚠️ Low</span>";
echo "</td></tr>";

$upload_max = ini_get('upload_max_filesize');
echo "<tr><td>Upload Max Size</td><td>$upload_max</td><td>";
echo (intval($upload_max) >= 10) ? "<span class='success'>✅ Good</span>" : "<span class='warning'>⚠️ Increase</span>";
echo "</td></tr>";

$post_max = ini_get('post_max_size');
echo "<tr><td>Post Max Size</td><td>$post_max</td><td>";
echo (intval($post_max) >= 20) ? "<span class='success'>✅ Good</span>" : "<span class='warning'>⚠️ Increase</span>";
echo "</td></tr>";

echo "</table>";

// Required PHP Extensions
echo "<h3>Required PHP Extensions</h3>";
$required_extensions = [
    'pdo' => 'PDO Database',
    'pdo_mysql' => 'MySQL PDO Driver',
    'json' => 'JSON Support',
    'mbstring' => 'Multibyte String',
    'fileinfo' => 'File Information',
    'gd' => 'Image Processing',
    'curl' => 'HTTP Client',
    'openssl' => 'SSL Support',
    'zip' => 'ZIP Archive'
];

echo "<table>";
echo "<tr><th>Extension</th><th>Description</th><th>Status</th></tr>";
foreach ($required_extensions as $ext => $desc) {
    echo "<tr><td>$ext</td><td>$desc</td><td>";
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ Loaded</span>";
    } else {
        echo "<span class='error'>❌ Missing</span>";
    }
    echo "</td></tr>";
}
echo "</table>";

// Directory Structure Check
echo "<h2>📁 Directory Structure</h2>";

$directories = [
    'public' => 'Web accessible files',
    'public/uploads' => 'File uploads',
    'public/uploads/enquiry_documents' => 'Enquiry documents',
    'public/uploads/student_photos' => 'Student photos',
    'public/uploads/marklist_files' => 'Marklist files',
    'public/uploads/certificates' => 'Generated certificates',
    'includes' => 'Backend files',
    'database' => 'Database scripts',
    'logs' => 'System logs'
];

echo "<table>";
echo "<tr><th>Directory</th><th>Description</th><th>Exists</th><th>Writable</th></tr>";

foreach ($directories as $dir => $desc) {
    echo "<tr><td>$dir</td><td>$desc</td><td>";
    
    if (is_dir($dir)) {
        echo "<span class='success'>✅ Yes</span>";
    } else {
        echo "<span class='error'>❌ No</span>";
        // Try to create directory
        if (mkdir($dir, 0755, true)) {
            echo " <span class='info'>(Created)</span>";
        }
    }
    
    echo "</td><td>";
    
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<span class='success'>✅ Yes</span>";
        } else {
            echo "<span class='warning'>⚠️ No</span>";
        }
    } else {
        echo "<span class='info'>N/A</span>";
    }
    
    echo "</td></tr>";
}
echo "</table>";

// Security Check
echo "<h2>🔒 Security Check</h2>";

// Check .htaccess
if (file_exists('public/.htaccess')) {
    echo "<p class='success'>✅ .htaccess file exists</p>";
} else {
    echo "<p class='warning'>⚠️ .htaccess file missing</p>";
    echo "<p>Create <code>public/.htaccess</code> for security and performance.</p>";
}

// Check sensitive files protection
$sensitive_files = ['includes/config.php', 'database/setup.sql'];
foreach ($sensitive_files as $file) {
    if (file_exists($file)) {
        echo "<p class='info'>ℹ️ Sensitive file exists: $file</p>";
    }
}

// SSL Check
$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
echo "<p>" . ($is_https ? "<span class='success'>✅ HTTPS enabled</span>" : "<span class='warning'>⚠️ HTTPS not detected</span>") . "</p>";

// Final Status
echo "<h2>🎯 Setup Status</h2>";

$config_ok = file_exists('includes/config.php');
$db_ok = false;
$tables_ok = false;

if ($config_ok) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db_ok = true;
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $tables_ok = count($tables) > 10;
        
    } catch (Exception $e) {
        $db_ok = false;
    }
}

if ($config_ok && $db_ok && $tables_ok) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>🎉 Setup Complete!</h3>";
    echo "<p>Your CRM system is ready to use.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Delete this setup file</strong> for security</li>";
    echo "<li>Visit your site: <a href='" . (defined('BASE_URL') ? BASE_URL : '/') . "' target='_blank'>" . (defined('BASE_URL') ? BASE_URL : 'Your Site') . "</a></li>";
    echo "<li>Login with: <strong>admin</strong> / <strong>admin123</strong></li>";
    echo "<li><strong>Change the admin password immediately!</strong></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>⚠️ Setup Incomplete</h3>";
    echo "<p>Please complete the following steps:</p>";
    echo "<ul>";
    if (!$config_ok) echo "<li>Create and configure <code>includes/config.php</code></li>";
    if (!$db_ok) echo "<li>Fix database connection issues</li>";
    if (!$tables_ok) echo "<li>Import database schema via phpMyAdmin</li>";
    echo "</ul>";
    echo "</div>";
}

// System Information
echo "<h2>ℹ️ System Information</h2>";
echo "<table>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Current Path</td><td>" . __DIR__ . "</td></tr>";
echo "<tr><td>Server Time</td><td>" . date('Y-m-d H:i:s T') . "</td></tr>";
echo "<tr><td>User Agent</td><td>" . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<p><small>Generated at " . date('Y-m-d H:i:s') . " | <strong>Remember to delete this file after setup!</strong></small></p>";

echo "</body></html>";
?>