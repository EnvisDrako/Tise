<?php
/**
 * Error Checker - Check all portal pages for errors
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SYSTEM_ACCESS', true);
session_start();

// Create a fake admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['user_role'] = 'admin';
$_SESSION['full_name'] = 'Test Admin';
$_SESSION['email'] = 'admin@test.com';
$_SESSION['institution_id'] = 1;
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();
$_SESSION['session_token'] = 'test123';

echo "<h1>Portal Error Checker</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;}</style>";

$adminFiles = [
    'admin/index.php',
    'admin/users.php',
    'admin/institutions.php',
    'admin/enquiries.php',
    'admin/students.php',
    'admin/certificates.php'
];

foreach ($adminFiles as $file) {
    echo "<h2>Testing: $file</h2>";
    
    if (!file_exists($file)) {
        echo "<p class='error'>❌ File not found</p>";
        continue;
    }
    
    // Capture output and errors
    ob_start();
    $error = null;
    
    try {
        include $file;
        $output = ob_get_clean();
        
        if (strlen($output) > 0) {
            echo "<p class='success'>✅ File loads successfully (" . strlen($output) . " bytes)</p>";
        } else {
            echo "<p class='error'>⚠️ File loads but produces no output</p>";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } catch (Error $e) {
        ob_end_clean();
        echo "<p class='error'>❌ Fatal Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    echo "<hr>";
}

echo "<h2>Session Info</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
