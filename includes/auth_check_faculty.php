<?php
/**
 * Faculty Authorization Middleware
 * Ensures only faculty users can access institution portal pages
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db_connect.php';

// Initialize global PDO connection for functions that use it
global $pdo;
$pdo = getDB();

/**
 * Validate faculty session and permissions
 */
function validateFacultyAccess() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        logEvent("Unauthorized access attempt to faculty area - not logged in, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        redirect('../login.php?error=login_required');
    }
    
    // Check if user has faculty role
    if (!isFaculty()) {
        $userId = getCurrentUserId();
        $userRole = getCurrentUserRole();
        logEvent("Unauthorized access attempt to faculty area by user ID: {$userId} (Role: {$userRole}), IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        
        // Redirect based on user's actual role
        switch ($userRole) {
            case 'admin':
                redirect('../admin/index.php');
                break;
            case 'enquirer':
                redirect('../enquiry/index.php');
                break;
            default:
                redirect('../login.php?error=unauthorized');
        }
    }
    
    // Verify user account is still active and has institution assigned
    try {
        $userId = getCurrentUserId();
        $query = "SELECT is_active, institution_id FROM users WHERE id = ? LIMIT 1";
        $user = fetchOne($query, [$userId]);
        
        if (!$user || !$user['is_active']) {
            logEvent("Access attempt by inactive faculty user ID: {$userId}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
            
            // Clear session and redirect to login
            session_destroy();
            redirect('../login.php?error=inactive');
        }
        
        if (empty($user['institution_id'])) {
            logEvent("Access attempt by faculty user without institution: {$userId}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
            redirect('../login.php?error=no_institution');
        }
        
        // Update session with current institution_id if needed
        $_SESSION['institution_id'] = $user['institution_id'];
        
    } catch (Exception $e) {
        logEvent("Database error during faculty auth check: " . $e->getMessage(), 'ERROR');
        redirect('../login.php?error=system');
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}

/**
 * Get faculty navigation menu items
 */
function getFacultyNavigation() {
    return [
        [
            'title' => 'Dashboard',
            'url' => 'index.php',
            'icon' => 'fas fa-tachometer-alt',
            'active' => basename($_SERVER['PHP_SELF']) === 'index.php'
        ],
        [
            'title' => 'Register Student',
            'url' => 'register_student.php',
            'icon' => 'fas fa-user-plus',
            'active' => basename($_SERVER['PHP_SELF']) === 'register_student.php'
        ],
        [
            'title' => 'Students',
            'url' => 'students.php',
            'icon' => 'fas fa-graduation-cap',
            'active' => basename($_SERVER['PHP_SELF']) === 'students.php'
        ],
        [
            'title' => 'Marklists',
            'url' => 'marklists.php',
            'icon' => 'fas fa-list-alt',
            'active' => basename($_SERVER['PHP_SELF']) === 'marklists.php'
        ],
        [
            'title' => 'Certificates',
            'url' => 'certificates.php',
            'icon' => 'fas fa-certificate',
            'active' => basename($_SERVER['PHP_SELF']) === 'certificates.php'
        ]
    ];
}

/**
 * Get current user's institution details
 */
function getCurrentInstitution() {
    static $institution = null;
    
    if ($institution === null) {
        $institutionId = getCurrentUserInstitutionId();
        if ($institutionId) {
            try {
                $query = "SELECT * FROM institutions WHERE id = ? AND is_active = 1 LIMIT 1";
                $institution = fetchOne($query, [$institutionId]);
            } catch (Exception $e) {
                logEvent("Error fetching institution details: " . $e->getMessage(), 'ERROR');
                $institution = false;
            }
        }
    }
    
    return $institution;
}

/**
 * Generate faculty page header
 */
function renderFacultyHeader($pageTitle = 'Faculty Portal') {
    $userName = getCurrentUserName();
    $institution = getCurrentInstitution();
    $institutionName = $institution ? $institution['name'] : 'Unknown Institution';
    $navigation = getFacultyNavigation();
    
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($pageTitle) . ' - ' . SITE_NAME . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/portal.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-university"></i> Faculty Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">';
    
    foreach ($navigation as $item) {
        $activeClass = $item['active'] ? ' active' : '';
        echo '<li class="nav-item">
                <a class="nav-link' . $activeClass . '" href="' . $item['url'] . '">
                    <i class="' . $item['icon'] . '"></i> ' . $item['title'] . '
                </a>
              </li>';
    }
    
    echo '      </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="fas fa-building"></i> ' . htmlspecialchars($institutionName) . '
                        </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> ' . htmlspecialchars($userName) . '
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../index.php"><i class="fas fa-home"></i> Public Site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">';
}

/**
 * Generate faculty page footer
 */
function renderFacultyFooter() {
    echo '    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/portal.js"></script>
</body>
</html>';
}

// Perform authorization check
validateFacultyAccess();
?>