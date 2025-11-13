<?php
/**
 * Diagnostic Page
 * Simple page to test if PHP and includes are working
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnostic Page</h1>";
echo "<p>This page helps diagnose setup issues.</p>";

// Test 1: Basic PHP
echo "<h2>✅ Test 1: PHP Working</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Test 2: File paths
echo "<h2>📁 Test 2: File Paths</h2>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>Parent Directory: " . dirname(__DIR__) . "</p>";

// Test 3: Check if includes directory exists
echo "<h2>📂 Test 3: Includes Directory</h2>";
$includes_path = '../includes';
if (is_dir($includes_path)) {
    echo "<p>✅ Includes directory found: $includes_path</p>";
    
    // Check for specific files
    $files_to_check = [
        'config.php',
        'public_header.php',
        'public_footer.php'
    ];
    
    foreach ($files_to_check as $file) {
        $file_path = $includes_path . '/' . $file;
        if (file_exists($file_path)) {
            echo "<p>✅ Found: $file</p>";
        } else {
            echo "<p>❌ Missing: $file</p>";
        }
    }
} else {
    echo "<p>❌ Includes directory not found: $includes_path</p>";
}

// Test 4: Try to define SYSTEM_ACCESS and include header
echo "<h2>🔧 Test 4: Include Test</h2>";
try {
    define('SYSTEM_ACCESS', true);
    echo "<p>✅ SYSTEM_ACCESS defined</p>";
    
    // Try to include header (but don't output it)
    ob_start();
    include '../includes/public_header.php';
    $header_content = ob_get_clean();
    
    if (strlen($header_content) > 0) {
        echo "<p>✅ Header file included successfully (" . strlen($header_content) . " bytes)</p>";
    } else {
        echo "<p>⚠️ Header file included but no content</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Include failed: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p>❌ Fatal error: " . $e->getMessage() . "</p>";
}

// Test 5: Database connection test
echo "<h2>🗄️ Test 5: Database Connection</h2>";
try {
    if (file_exists('../includes/config.php')) {
        include '../includes/config.php';
        echo "<p>✅ Config file loaded</p>";
        
        if (defined('DB_HOST')) {
            echo "<p>✅ Database constants defined</p>";
            echo "<p>DB Host: " . DB_HOST . "</p>";
            echo "<p>DB Name: " . DB_NAME . "</p>";
            
            // Try to connect
            try {
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                echo "<p>✅ Database connection successful</p>";
            } catch (PDOException $e) {
                echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>❌ Database constants not defined</p>";
        }
    } else {
        echo "<p>❌ Config file not found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Config error: " . $e->getMessage() . "</p>";
}

// Test 6: PHP Extensions
echo "<h2>🔌 Test 6: PHP Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'fileinfo', 'gd', 'curl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ $ext loaded</p>";
    } else {
        echo "<p>❌ $ext missing</p>";
    }
}

echo "<hr>";
echo "<h2>🎯 Next Steps</h2>";
echo "<p>If all tests pass, try accessing the main site:</p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
echo "<p><a href='../'>Go to Root</a></p>";

echo "<hr>";
echo "<p><small>Diagnostic completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>