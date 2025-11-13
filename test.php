<?php
/**
 * Simple Test File
 * Quick test to see if PHP is working
 */

echo "<h1>🧪 PHP Test</h1>";
echo "<p>✅ PHP is working!</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

// Test if we can access the public directory
if (file_exists('public/index.php')) {
    echo "<p>✅ Public directory found</p>";
    echo "<p><a href='public/'>Go to Public Directory</a></p>";
} else {
    echo "<p>❌ Public directory not found</p>";
}

// Test if config exists
if (file_exists('includes/config.php')) {
    echo "<p>✅ Configuration file exists</p>";
} else {
    echo "<p>❌ Configuration file missing</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Install XAMPP for complete PHP environment</li>";
echo "<li>Move project to C:\\xampp\\htdocs\\integrated-crm\\</li>";
echo "<li>Access via http://localhost/integrated-crm/</li>";
echo "</ol>";
?>