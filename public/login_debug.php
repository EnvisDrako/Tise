<?php
/**
 * Login Debug - Step by step login process
 */
define('SYSTEM_ACCESS', true);
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db_connect.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Login Process Debug</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<div class='step'>";
    echo "<h2>Step 1: Form Submission</h2>";
    echo "<p class='success'>✅ Form submitted</p>";
    echo "<p>Username: <code>" . htmlspecialchars($username) . "</code></p>";
    echo "<p>Password: <code>" . str_repeat('*', strlen($password)) . "</code></p>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h2>Step 2: Database Query</h2>";
    try {
        $query = "SELECT id, username, password_hash, role, full_name, email, 
                         institution_id, is_active 
                  FROM users 
                  WHERE username = ? 
                  LIMIT 1";
        
        $user = fetchOne($query, [$username]);
        
        if ($user) {
            echo "<p class='success'>✅ User found in database</p>";
            echo "<pre>";
            print_r([
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
                'is_active' => $user['is_active'],
                'institution_id' => $user['institution_id']
            ]);
            echo "</pre>";
        } else {
            echo "<p class='error'>❌ User not found</p>";
            echo "<p>The username '<code>" . htmlspecialchars($username) . "</code>' does not exist in the database.</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    if (isset($user) && $user) {
        echo "<div class='step'>";
        echo "<h2>Step 3: Password Verification</h2>";
        
        $passwordMatch = password_verify($password, $user['password_hash']);
        
        if ($passwordMatch) {
            echo "<p class='success'>✅ Password is correct!</p>";
        } else {
            echo "<p class='error'>❌ Password is incorrect</p>";
            echo "<p>Testing with 'admin123':</p>";
            $testMatch = password_verify('admin123', $user['password_hash']);
            echo "<p>" . ($testMatch ? "✅ 'admin123' works" : "❌ 'admin123' doesn't work") . "</p>";
            
            // Show what the hash should be
            $correctHash = password_hash($password, PASSWORD_DEFAULT);
            echo "<p>If you want to use password '<code>" . htmlspecialchars($password) . "</code>', run this SQL:</p>";
            echo "<pre>UPDATE users SET password_hash = '$correctHash' WHERE username = '" . htmlspecialchars($username) . "';</pre>";
        }
        echo "</div>";
        
        if ($passwordMatch) {
            echo "<div class='step'>";
            echo "<h2>Step 4: Account Status</h2>";
            if ($user['is_active']) {
                echo "<p class='success'>✅ Account is active</p>";
            } else {
                echo "<p class='error'>❌ Account is inactive</p>";
            }
            echo "</div>";
            
            if ($user['is_active']) {
                echo "<div class='step'>";
                echo "<h2>Step 5: Create Session</h2>";
                
                // Create session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['institution_id'] = $user['institution_id'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['session_token'] = bin2hex(random_bytes(16));
                
                echo "<p class='success'>✅ Session created</p>";
                echo "<pre>";
                print_r($_SESSION);
                echo "</pre>";
                echo "</div>";
                
                echo "<div class='step'>";
                echo "<h2>Step 6: Determine Redirect</h2>";
                
                $redirectUrl = '';
                switch ($user['role']) {
                    case 'admin':
                        $redirectUrl = BASE_URL . '/admin/';
                        break;
                    case 'faculty':
                        $redirectUrl = BASE_URL . '/institution/';
                        break;
                    case 'enquirer':
                        $redirectUrl = BASE_URL . '/enquiry/';
                        break;
                }
                
                echo "<p class='success'>✅ Redirect URL determined</p>";
                echo "<p>Role: <code>" . $user['role'] . "</code></p>";
                echo "<p>Redirect to: <code>" . $redirectUrl . "</code></p>";
                echo "<p><a href='$redirectUrl' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
                echo "</div>";
                
                echo "<div class='step'>";
                echo "<h2>✅ Login Successful!</h2>";
                echo "<p>You can now access your dashboard.</p>";
                echo "<p><strong>Note:</strong> The actual login page will redirect automatically.</p>";
                echo "</div>";
            }
        }
    }
} else {
    // Show form
    ?>
    <form method="POST" style="max-width: 400px; margin: 20px 0;">
        <h2>Test Login</h2>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Username:</label>
            <input type="text" name="username" value="admin" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Password:</label>
            <input type="password" name="password" value="admin123" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Debug Login Process
        </button>
    </form>
    
    <p><a href="login.php">Go to actual login page</a></p>
    <?php
}
?>
    </div>
</body>
</html>
