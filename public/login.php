<?php
/**
 * Universal Login Interface
 * Provides secure login form for all user roles (admin, faculty, enquirer)
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Start session
session_start();

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $userRole = $_SESSION['user_role'] ?? '';
    switch ($userRole) {
        case 'admin':
            header('Location: ' . BASE_URL . '/admin/');
            exit;
        case 'faculty':
            header('Location: ' . BASE_URL . '/institution/');
            exit;
        case 'enquirer':
            header('Location: ' . BASE_URL . '/enquiry/');
            exit;
        default:
            // Invalid role, destroy session and continue to login
            session_destroy();
            session_start();
    }
}

// Initialize variables
$error_message = '';
$username = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        // Process login directly
        try {
            // Query user from database
            $query = "SELECT id, username, password_hash, role, full_name, email, 
                             institution_id, is_active 
                      FROM users 
                      WHERE username = ? 
                      LIMIT 1";
            
            $user = fetchOne($query, [$username]);
            
            if (!$user) {
                $error_message = 'Invalid username or password.';
            } elseif (!verifyPassword($password, $user['password_hash'])) {
                $error_message = 'Invalid username or password.';
            } elseif (!$user['is_active']) {
                $error_message = 'Your account is inactive. Please contact administrator.';
            } else {
                // Login successful - create session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['institution_id'] = $user['institution_id'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['session_token'] = generateRandomString(32);
                
                // Update last login
                try {
                    $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
                    executeQuery($updateQuery, [$user['id']]);
                } catch (Exception $e) {
                    // Ignore update error, continue with login
                }
                
                // Determine redirect URL
                $redirectUrl = BASE_URL . '/admin/';
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
                
                // Use JavaScript redirect as backup
                echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . $redirectUrl . '"></head><body>';
                echo '<script>window.location.href="' . $redirectUrl . '";</script>';
                echo '<p>Redirecting... <a href="' . $redirectUrl . '">Click here if not redirected</a></p>';
                echo '</body></html>';
                exit;
            }
        } catch (Exception $e) {
            $error_message = 'System error. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Initialize success message
$success_message = '';

// Handle logout success message
if (isset($_SESSION['logout_success']) && $_SESSION['logout_success'] === true) {
    $success_message = 'You have been successfully logged out.';
    unset($_SESSION['logout_success']); // Clear the message
}

// Get any error message from URL parameters (from login_process.php)
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid':
            $error_message = 'Invalid username or password.';
            break;
        case 'inactive':
            $error_message = 'Your account is inactive. Please contact administrator.';
            break;
        case 'locked':
            $error_message = 'Account temporarily locked due to multiple failed attempts. Please try again later.';
            break;
        case 'system':
            $error_message = 'System error occurred. Please try again later.';
            break;
        case 'login_required':
            $error_message = 'Please login to access that area.';
            break;
        case 'unauthorized':
            $error_message = 'You are not authorized to access that area.';
            break;
        case 'no_institution':
            $error_message = 'Your account is not associated with an institution. Please contact administrator.';
            break;
        default:
            $error_message = 'Login failed. Please try again.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1rem;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-to-home a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .back-to-home a:hover {
            color: #764ba2;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            z-index: 10;
        }
        
        .password-field {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2><i class="fas fa-user-shield"></i> Login</h2>
            <p>Access your portal dashboard</p>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/login" id="loginForm">
            <div class="form-floating">
                <input type="text" 
                       class="form-control" 
                       id="username" 
                       name="username" 
                       placeholder="Username"
                       value="<?php echo htmlspecialchars($username); ?>"
                       required
                       autocomplete="username">
                <label for="username"><i class="fas fa-user"></i> Username</label>
            </div>
            
            <div class="form-floating password-field">
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Password"
                       required
                       autocomplete="current-password">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                </button>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </form>
        
        <div class="back-to-home">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please enter both username and password.');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long.');
                return false;
            }
            
            if (password.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
                e.preventDefault();
                alert('Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long.');
                return false;
            }
        });
        
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>