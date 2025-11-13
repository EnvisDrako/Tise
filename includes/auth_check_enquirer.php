<?php
/**
 * Enquirer Authorization Middleware
 * Ensures only enquirer users can access enquiry portal pages
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
 * Validate enquirer session and permissions
 */
function validateEnquirerAccess() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        logEvent("Unauthorized access attempt to enquirer area - not logged in, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        redirect('../login.php?error=login_required');
    }
    
    // Check if user has enquirer role
    if (!isEnquirer()) {
        $userId = getCurrentUserId();
        $userRole = getCurrentUserRole();
        logEvent("Unauthorized access attempt to enquirer area by user ID: {$userId} (Role: {$userRole}), IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
        
        // Redirect based on user's actual role
        switch ($userRole) {
            case 'admin':
                redirect('../admin/index.php');
                break;
            case 'faculty':
                redirect('../institution/index.php');
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
            logEvent("Access attempt by inactive enquirer user ID: {$userId}, IP: {$_SERVER['REMOTE_ADDR']}", 'WARNING');
            
            // Clear session and redirect to login
            session_destroy();
            redirect('../login.php?error=inactive');
        }
        
    } catch (Exception $e) {
        logEvent("Database error during enquirer auth check: " . $e->getMessage(), 'ERROR');
        redirect('../login.php?error=system');
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}

/**
 * Get enquirer navigation menu items
 */
function getEnquirerNavigation() {
    return [
        [
            'title' => 'Dashboard',
            'url' => 'index.php',
            'icon' => 'fas fa-tachometer-alt',
            'active' => basename($_SERVER['PHP_SELF']) === 'index.php'
        ],
        [
            'title' => 'My Enquiries',
            'url' => 'enquiries.php',
            'icon' => 'fas fa-envelope',
            'active' => basename($_SERVER['PHP_SELF']) === 'enquiries.php'
        ],
        [
            'title' => 'Update Status',
            'url' => 'update_status.php',
            'icon' => 'fas fa-edit',
            'active' => basename($_SERVER['PHP_SELF']) === 'update_status.php'
        ],
        [
            'title' => 'Call History',
            'url' => 'call_logs.php',
            'icon' => 'fas fa-phone',
            'active' => basename($_SERVER['PHP_SELF']) === 'call_logs.php'
        ]
    ];
}

/**
 * Get enquirer statistics
 */
function getEnquirerStats() {
    static $stats = null;
    
    if ($stats === null) {
        $userId = getCurrentUserId();
        try {
            // Get assigned enquiries count
            $totalQuery = "SELECT COUNT(*) as total FROM enquiries WHERE assigned_to_user_id = ?";
            $totalResult = fetchOne($totalQuery, [$userId]);
            
            // Get enquiries by status
            $statusQuery = "SELECT status, COUNT(*) as count 
                           FROM enquiries 
                           WHERE assigned_to_user_id = ? 
                           GROUP BY status";
            $statusResults = fetchAll($statusQuery, [$userId]);
            
            // Get today's calls count
            $callsQuery = "SELECT COUNT(*) as calls 
                          FROM call_logs cl 
                          JOIN enquiries e ON cl.enquiry_id = e.id 
                          WHERE e.assigned_to_user_id = ? 
                          AND DATE(cl.call_timestamp) = CURDATE()";
            $callsResult = fetchOne($callsQuery, [$userId]);
            
            $stats = [
                'total_enquiries' => $totalResult['total'] ?? 0,
                'todays_calls' => $callsResult['calls'] ?? 0,
                'by_status' => []
            ];
            
            foreach ($statusResults as $status) {
                $stats['by_status'][$status['status']] = $status['count'];
            }
            
        } catch (Exception $e) {
            logEvent("Error fetching enquirer stats: " . $e->getMessage(), 'ERROR');
            $stats = [
                'total_enquiries' => 0,
                'todays_calls' => 0,
                'by_status' => []
            ];
        }
    }
    
    return $stats;
}

/**
 * Generate enquirer page header
 */
function renderEnquirerHeader($pageTitle = 'Enquirer Portal') {
    $userName = getCurrentUserName();
    $navigation = getEnquirerNavigation();
    
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-info">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-phone"></i> Enquirer Portal
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
 * Generate enquirer page footer
 */
function renderEnquirerFooter() {
    echo '    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/portal.js"></script>
</body>
</html>';
}

// Perform authorization check
validateEnquirerAccess();
?>