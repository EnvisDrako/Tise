<?php
/**
 * Admin Authorization Middleware
 * Ensures only admin users can access admin portal pages
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
 * Validate admin session and permissions
 */
function validateAdminAccess() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        logEvent("Unauthorized access attempt to admin area - not logged in, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        redirect('../login.php?error=login_required');
    }
    
    // Check if user has admin role
    if (!isAdmin()) {
        $userId = getCurrentUserId();
        $userRole = getCurrentUserRole();
        logEvent("Unauthorized access attempt to admin area by user ID: {$userId} (Role: {$userRole}), IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        
        // Redirect based on user's actual role
        switch ($userRole) {
            case 'faculty':
                redirect('../institution/index.php');
                break;
            case 'enquirer':
                redirect('../enquiry/index.php');
                break;
            default:
                redirect('../login.php?error=unauthorized');
        }
    }
    
    // Verify user account is still active
    try {
        $userId = getCurrentUserId();
        $query = "SELECT is_active FROM users WHERE id = ? LIMIT 1";
        $user = fetchOne($query, [$userId]);
        
        if (!$user || !$user['is_active']) {
            logEvent("Access attempt by inactive admin user ID: {$userId}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
            
            // Clear session and redirect to login
            session_destroy();
            redirect('../login.php?error=inactive');
        }
        
    } catch (Exception $e) {
        logEvent("Database error during admin auth check: " . $e->getMessage(), 'ERROR');
        redirect('../login.php?error=system');
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}

/**
 * Get admin navigation menu items
 */
function getAdminNavigation() {
    return [
        [
            'title' => 'Dashboard',
            'url' => 'index.php',
            'icon' => 'fas fa-tachometer-alt',
            'active' => basename($_SERVER['PHP_SELF']) === 'index.php'
        ],
        [
            'title' => 'User Management',
            'url' => url('admin/users.php'),
            'icon' => 'fas fa-users',
            'active' => basename($_SERVER['PHP_SELF']) === 'users.php'
        ],
        [
            'title' => 'Institutions',
            'url' => url('admin/institutions.php'),
            'icon' => 'fas fa-university',
            'active' => basename($_SERVER['PHP_SELF']) === 'institutions.php'
        ],
        [
            'title' => 'Enquiries',
            'url' => url('admin/enquiries.php'),
            'icon' => 'fas fa-envelope',
            'active' => basename($_SERVER['PHP_SELF']) === 'enquiries.php',
            'submenu' => [
                ['title' => 'View All', 'url' => url('admin/enquiries.php')],
                ['title' => 'Add Enquiry', 'url' => url('admin/add_enquiry.php')]
            ]
        ],
        [
            'title' => 'Students',
            'url' => url('admin/students.php'),
            'icon' => 'fas fa-graduation-cap',
            'active' => basename($_SERVER['PHP_SELF']) === 'students.php',
            'submenu' => [
                ['title' => 'View All', 'url' => url('admin/students.php')],
                ['title' => 'Add Student', 'url' => url('admin/add_student.php')]
            ]
        ],
        [
            'title' => 'Certificates',
            'url' => url('admin/certificates.php'),
            'icon' => 'fas fa-certificate',
            'active' => basename($_SERVER['PHP_SELF']) === 'certificates.php'
        ],
        [
            'title' => 'Website Management',
            'url' => url('admin/website/notices.php'),
            'icon' => 'fas fa-globe',
            'active' => strpos($_SERVER['PHP_SELF'], 'website/') !== false,
            'submenu' => [
                ['title' => 'Notices', 'url' => url('admin/website/notices.php')],
                ['title' => 'Downloads', 'url' => url('admin/website/downloads.php')],
                ['title' => 'Placements', 'url' => url('admin/website/placements.php')]
            ]
        ]
    ];
}

/**
 * Generate admin page header
 */
function renderAdminHeader($pageTitle = 'Admin Portal') {
    $userName = getCurrentUserName();
    $navigation = getAdminNavigation();
    
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="' . url('admin/') . '">
                <i class="fas fa-shield-alt"></i> Admin Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">';
    
    foreach ($navigation as $item) {
        $activeClass = $item['active'] ? ' active' : '';
        
        // Check if item has submenu
        if (isset($item['submenu']) && !empty($item['submenu'])) {
            echo '<li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle' . $activeClass . '" href="#" id="nav' . str_replace(' ', '', $item['title']) . '" role="button" data-bs-toggle="dropdown">
                        <i class="' . $item['icon'] . '"></i> ' . $item['title'] . '
                    </a>
                    <ul class="dropdown-menu">';
            
            foreach ($item['submenu'] as $subitem) {
                echo '<li><a class="dropdown-item" href="' . $subitem['url'] . '">' . $subitem['title'] . '</a></li>';
            }
            
            echo '    </ul>
                  </li>';
        } else {
            echo '<li class="nav-item">
                    <a class="nav-link' . $activeClass . '" href="' . $item['url'] . '">
                        <i class="' . $item['icon'] . '"></i> ' . $item['title'] . '
                    </a>
                  </li>';
        }
    }
    
    echo '      </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> ' . htmlspecialchars($userName) . '
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="' . url('/') . '"><i class="fas fa-home"></i> Public Site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="' . url('logout') . '"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">';
}

/**
 * Generate admin page footer
 */
function renderAdminFooter() {
    echo '    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/portal.js"></script>
</body>
</html>';
}

// Perform authorization check
validateAdminAccess();
?>