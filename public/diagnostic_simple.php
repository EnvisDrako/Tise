<?php
// Simple diagnostic page
echo "<h1>Diagnostic Page</h1>";

echo "<h2>1. PHP is working</h2>";
echo "<p>✅ If you see this, PHP is working!</p>";

echo "<h2>2. Server Info</h2>";
echo "<pre>";
echo "SERVER_NAME: " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'NOT SET') . "\n";
echo "DOCUMENT_ROOT: " . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'NOT SET') . "\n";
echo "REQUEST_URI: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'NOT SET') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "</pre>";

echo "<h2>3. Testing SYSTEM_ACCESS</h2>";
define('SYSTEM_ACCESS', true);
echo "<p>✅ SYSTEM_ACCESS defined</p>";

echo "<h2>4. Testing config.php</h2>";
try {
    require_once __DIR__ . '/../includes/config.php';
    echo "<p>✅ config.php loaded successfully</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";
    echo "<p>SITE_URL: " . SITE_URL . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error loading config.php: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Testing functions.php</h2>";
try {
    require_once __DIR__ . '/../includes/functions.php';
    echo "<p>✅ functions.php loaded successfully</p>";
    
    if (function_exists('asset')) {
        echo "<p>✅ asset() function exists</p>";
        echo "<p>asset('css/style.css') = " . asset('css/style.css') . "</p>";
    } else {
        echo "<p>❌ asset() function NOT found</p>";
    }
    
    if (function_exists('url')) {
        echo "<p>✅ url() function exists</p>";
        echo "<p>url('about') = " . url('about') . "</p>";
    } else {
        echo "<p>❌ url() function NOT found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error loading functions.php: " . $e->getMessage() . "</p>";
}

echo "<h2>6. Testing public_header.php</h2>";
try {
    $page_title = "Test";
    include __DIR__ . '/../includes/public_header.php';
    echo "<p>✅ public_header.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p>❌ Error loading public_header.php: " . $e->getMessage() . "</p>";
}

echo "<h2>All Tests Complete!</h2>";
echo "<p><a href='index.php'>Try Main Page</a></p>";
?>
