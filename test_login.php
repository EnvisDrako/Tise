<?php
/**
 * Test Login Script
 * Tests if admin user exists and password verification works
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db_connect.php';

echo "<h1>Login Test</h1>";

// Test database connection
try {
    $db = getDB();
    echo "<p>✅ Database connected</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check if admin user exists
$username = 'admin';
$password = 'admin123';

$query = "SELECT id, username, password_hash, role, full_name, email, is_active 
          FROM users 
          WHERE username = ? 
          LIMIT 1";

try {
    $user = fetchOne($query, [$username]);
    
    if ($user) {
        echo "<p>✅ Admin user found</p>";
        echo "<pre>";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Full Name: " . $user['full_name'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
        echo "</pre>";
        
        // Test password verification
        if (verifyPassword($password, $user['password_hash'])) {
            echo "<p>✅ Password verification successful</p>";
            echo "<p><strong>Login should work with:</strong></p>";
            echo "<ul>";
            echo "<li>Username: <code>admin</code></li>";
            echo "<li>Password: <code>admin123</code></li>";
            echo "</ul>";
        } else {
            echo "<p>❌ Password verification failed</p>";
            echo "<p>The password hash in database doesn't match 'admin123'</p>";
            
            // Generate new hash
            $newHash = hashPassword($password);
            echo "<p>To fix, run this SQL:</p>";
            echo "<pre>UPDATE users SET password_hash = '$newHash' WHERE username = 'admin';</pre>";
        }
    } else {
        echo "<p>❌ Admin user not found in database</p>";
        echo "<p>Please run the database setup script first.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='public/login.php'>Go to Login Page</a></p>";
?>
