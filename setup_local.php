<?php
/**
 * Local Development Setup Script
 * Run this script to set up the system for local development
 */

// Define system access
define('SYSTEM_ACCESS', true);

echo "<h1>Integrated CRM - Local Setup</h1>";
echo "<p>Setting up the system for local development...</p>";

// Check if config file exists
if (!file_exists('includes/config.php')) {
    // Copy local config
    if (copy('includes/config.local.php', 'includes/config.php')) {
        echo "<p>✅ Configuration file created</p>";
    } else {
        echo "<p>❌ Failed to create configuration file</p>";
        exit;
    }
} else {
    echo "<p>ℹ️ Configuration file already exists</p>";
}

// Include configuration
require_once 'includes/config.php';

// Test database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Database server connection successful</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if ($stmt->rowCount() == 0) {
        // Create database
        $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>✅ Database '" . DB_NAME . "' created</p>";
    } else {
        echo "<p>ℹ️ Database '" . DB_NAME . "' already exists</p>";
    }
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) == 0) {
        echo "<p>📋 No tables found. You need to import the database schema.</p>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
        echo "<li>Select the '" . DB_NAME . "' database</li>";
        echo "<li>Click 'Import' tab</li>";
        echo "<li>Choose file: <code>database/setup.sql</code></li>";
        echo "<li>Click 'Go' to import</li>";
        echo "</ol>";
    } else {
        echo "<p>✅ Database tables found: " . count($tables) . " tables</p>";
        
        // Check for admin user
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount > 0) {
            echo "<p>✅ Admin user exists</p>";
        } else {
            echo "<p>⚠️ No admin user found</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Make sure XAMPP MySQL is running!</strong></p>";
}

// Check directory permissions
$directories = [
    'public/uploads' => 'File uploads',
    'logs' => 'System logs',
    'public/uploads/enquiry_documents' => 'Enquiry documents',
    'public/uploads/student_photos' => 'Student photos',
    'public/uploads/marklist_files' => 'Marklist files',
    'public/uploads/certificates' => 'Certificates'
];

echo "<h3>Directory Setup</h3>";
foreach ($directories as $dir => $description) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✅ Created directory: $dir ($description)</p>";
        } else {
            echo "<p>❌ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p>ℹ️ Directory exists: $dir</p>";
    }
    
    if (is_writable($dir)) {
        echo "<p>✅ Directory is writable: $dir</p>";
    } else {
        echo "<p>⚠️ Directory is not writable: $dir</p>";
    }
}

// Check PHP extensions
echo "<h3>PHP Extensions Check</h3>";
$required_extensions = [
    'pdo' => 'PDO Database',
    'pdo_mysql' => 'MySQL PDO Driver',
    'json' => 'JSON Support',
    'mbstring' => 'Multibyte String',
    'fileinfo' => 'File Information',
    'gd' => 'Image Processing',
    'curl' => 'HTTP Client'
];

foreach ($required_extensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "<p>✅ $description ($ext)</p>";
    } else {
        echo "<p>❌ Missing: $description ($ext)</p>";
    }
}

echo "<h3>System Information</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";

echo "<h3>Access URLs</h3>";
$base_url = 'http://localhost/integrated-crm';
echo "<ul>";
echo "<li><a href='$base_url' target='_blank'>Public Website</a></li>";
echo "<li><a href='$base_url/login.php' target='_blank'>Login Page</a></li>";
echo "<li><a href='$base_url/admin/' target='_blank'>Admin Portal</a></li>";
echo "<li><a href='$base_url/institution/' target='_blank'>Faculty Portal</a></li>";
echo "<li><a href='$base_url/enquirer-portal/' target='_blank'>Enquirer Portal</a></li>";
echo "<li><a href='$base_url/api/' target='_blank'>API Documentation</a></li>";
echo "</ul>";

echo "<h3>Default Login Credentials</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Role</th><th>Username</th><th>Password</th></tr>";
echo "<tr><td>Admin</td><td>admin</td><td>admin123</td></tr>";
echo "</table>";
echo "<p><strong>⚠️ Change these passwords after first login!</strong></p>";

echo "<hr>";
echo "<p><strong>Setup Status:</strong> ";
if (file_exists('includes/config.php') && count($tables ?? []) > 0) {
    echo "✅ <span style='color: green;'>Ready to use!</span></p>";
    echo "<p><a href='$base_url' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Launch Application</a></p>";
} else {
    echo "⚠️ <span style='color: orange;'>Setup incomplete - follow the steps above</span></p>";
}
?>