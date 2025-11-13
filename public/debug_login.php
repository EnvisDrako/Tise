<?php
/**
 * Debug Login - Check what's happening
 */
define('SYSTEM_ACCESS', true);
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db_connect.php';

echo "<h1>Login Debug</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } pre { background: #f5f5f5; padding: 10px; }</style>";

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
try {
    $db = getDB();
    echo "<p style='color: green;'>✅ Connected to database</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check if users table exists
echo "<h2>2. Users Table</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Users table exists with " . $result['count'] . " users</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Users table error: " . $e->getMessage() . "</p>";
    echo "<p>You need to run the database setup first!</p>";
    exit;
}

// Test 3: Check for admin user
echo "<h2>3. Admin User</h2>";
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<p style='color: green;'>✅ Admin user found</p>";
        echo "<pre>";
        print_r([
            'id' => $admin['id'],
            'username' => $admin['username'],
            'role' => $admin['role'],
            'full_name' => $admin['full_name'],
            'is_active' => $admin['is_active'],
            'password_hash' => substr($admin['password_hash'], 0, 20) . '...'
        ]);
        echo "</pre>";
        
        // Test password
        $testPassword = 'admin123';
        if (password_verify($testPassword, $admin['password_hash'])) {
            echo "<p style='color: green;'>✅ Password 'admin123' is correct!</p>";
        } else {
            echo "<p style='color: red;'>❌ Password 'admin123' does NOT match</p>";
            echo "<p>Generating new hash...</p>";
            $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
            echo "<p>Run this SQL to fix:</p>";
            echo "<pre>UPDATE users SET password_hash = '$newHash' WHERE username = 'admin';</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ Admin user not found</p>";
        echo "<p>Creating admin user...</p>";
        
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, role, full_name, email, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $hash, 'admin', 'System Administrator', 'admin@system.com', 1]);
        
        echo "<p style='color: green;'>✅ Admin user created!</p>";
        echo "<p>Username: admin</p>";
        echo "<p>Password: admin123</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 4: Test login process
echo "<h2>4. Test Login Process</h2>";
if (isset($admin) && $admin) {
    echo "<form method='POST' action='login.php'>";
    echo "<input type='hidden' name='username' value='admin'>";
    echo "<input type='hidden' name='password' value='admin123'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;'>Test Login</button>";
    echo "</form>";
    echo "<p>Or manually go to: <a href='login.php'>Login Page</a></p>";
}

// Test 5: Check session
echo "<h2>5. Session Info</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "\n";
if (!empty($_SESSION)) {
    echo "Session Data:\n";
    print_r($_SESSION);
} else {
    echo "Session is empty\n";
}
echo "</pre>";

// Test 6: Check constants
echo "<h2>6. Configuration</h2>";
echo "<pre>";
echo "BASE_URL: " . BASE_URL . "\n";
echo "SITE_URL: " . SITE_URL . "\n";
echo "SESSION_TIMEOUT: " . SESSION_TIMEOUT . "\n";
echo "MAX_LOGIN_ATTEMPTS: " . MAX_LOGIN_ATTEMPTS . "\n";
echo "</pre>";
?>
