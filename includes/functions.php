<?php
/**
 * Common utility functions for the Integrated Web Portal & CRM
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

// Include security and monitoring components (commented out temporarily for debugging)
// require_once __DIR__ . '/ErrorHandler.php';
// require_once __DIR__ . '/InputValidator.php';
// require_once __DIR__ . '/SecurityMiddleware.php';
// require_once __DIR__ . '/NotificationManager.php';
// require_once __DIR__ . '/BackupManager.php';
// require_once __DIR__ . '/SystemMonitor.php';

// Initialize error handler and monitoring
// initializeErrorHandler();
// initializeSystemMonitoring();

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 */
function isValidPhone($phone) {
    return preg_match('/^[0-9+\-\s()]{10,15}$/', $phone);
}

/**
 * Generate secure password hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get institution-specific student statistics
 */
function getInstitutionStudentStats($institutionId) {
    global $pdo;
    
    try {
        // Total students
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM students WHERE institution_id = ?");
        $stmt->execute([$institutionId]);
        $totalStudents = $stmt->fetchColumn();
        
        // Exam eligible students
        $stmt = $pdo->prepare("SELECT COUNT(*) as eligible FROM students WHERE institution_id = ? AND is_exam_eligible = 1");
        $stmt->execute([$institutionId]);
        $examEligible = $stmt->fetchColumn();
        
        // Total marklists
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT m.id) as total 
            FROM marklists m 
            JOIN students s ON m.student_id = s.id 
            WHERE s.institution_id = ?
        ");
        $stmt->execute([$institutionId]);
        $totalMarklists = $stmt->fetchColumn();
        
        // Certificate requests
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM certificate_requests WHERE institution_id = ?");
        $stmt->execute([$institutionId]);
        $totalCertificates = $stmt->fetchColumn();
        
        return [
            'total_students' => $totalStudents,
            'exam_eligible' => $examEligible,
            'total_marklists' => $totalMarklists,
            'total_certificates' => $totalCertificates
        ];
    } catch (PDOException $e) {
        logError("Error getting institution stats: " . $e->getMessage());
        return [
            'total_students' => 0,
            'exam_eligible' => 0,
            'total_marklists' => 0,
            'total_certificates' => 0
        ];
    }
}

/**
 * Get recent activities for institution
 */
function getInstitutionRecentActivities($institutionId, $limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                'student' as type,
                s.full_name as name,
                s.created_at as date,
                'Student registered' as activity
            FROM students s 
            WHERE s.institution_id = ?
            
            UNION ALL
            
            SELECT 
                'certificate' as type,
                CONCAT(s.full_name, ' - ', cr.request_type) as name,
                cr.requested_at as date,
                CONCAT('Certificate request ', cr.status) as activity
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            WHERE cr.institution_id = ?
            
            ORDER BY date DESC
            LIMIT ?
        ");
        $stmt->execute([$institutionId, $institutionId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logError("Error getting recent activities: " . $e->getMessage());
        return [];
    }
}

/**
 * Get course-wise student distribution for institution
 */
function getInstitutionCourseDistribution($institutionId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                course,
                COUNT(*) as student_count,
                SUM(CASE WHEN is_exam_eligible = 1 THEN 1 ELSE 0 END) as eligible_count
            FROM students 
            WHERE institution_id = ? AND course IS NOT NULL
            GROUP BY course
            ORDER BY student_count DESC
        ");
        $stmt->execute([$institutionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logError("Error getting course distribution: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate random string for tokens
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Log system events
 */
function logEvent($message, $level = 'INFO') {
    if (LOG_ERRORS) {
        $logFile = ERROR_LOG_PATH . 'system.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Get file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file type is allowed
 */
function isAllowedFileType($filename) {
    $extension = getFileExtension($filename);
    return in_array($extension, ALLOWED_FILE_TYPES);
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $extension = getFileExtension($originalName);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $timestamp = time();
    $random = generateRandomString(8);
    return sanitizeInput($basename) . "_{$timestamp}_{$random}.{$extension}";
}

/**
 * Redirect to URL
 */
function redirect($url) {
    // If URL doesn't start with http, prepend BASE_URL
    if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
        $url = url($url);
    }
    header("Location: {$url}");
    exit();
}

/**
 * Check if user is logged in and session is valid
 */
function isLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return isLoggedIn() ? $_SESSION['user_role'] : null;
}

/**
 * Get current user's full name
 */
function getCurrentUserName() {
    return isLoggedIn() ? $_SESSION['full_name'] : null;
}

/**
 * Get current user's institution ID
 */
function getCurrentUserInstitutionId() {
    return isLoggedIn() ? $_SESSION['institution_id'] : null;
}

/**
 * Check if current user has specific role
 */
function hasRole($role) {
    return getCurrentUserRole() === $role;
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if current user is faculty
 */
function isFaculty() {
    return hasRole('faculty');
}

/**
 * Check if current user is enquirer
 */
function isEnquirer() {
    return hasRole('enquirer');
}

/**
 * Require login - redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Require specific role - redirect if user doesn't have required role
 */
function requireRole($requiredRole) {
    requireLogin();
    
    if (!hasRole($requiredRole)) {
        logEvent("Unauthorized access attempt by user: " . getCurrentUserId() . 
                " to role: {$requiredRole}", 'WARNING');
        redirect('login.php?error=unauthorized');
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Pagination helper
 */
function getPaginationData($totalRecords, $currentPage = 1, $pageSize = null) {
    $pageSize = $pageSize ?? DEFAULT_PAGE_SIZE;
    $pageSize = min($pageSize, MAX_PAGE_SIZE);
    
    $totalPages = ceil($totalRecords / $pageSize);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $pageSize;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'page_size' => $pageSize,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Create breadcrumb navigation
 */
function createBreadcrumb($items) {
    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($items as $index => $item) {
        $isLast = ($index === count($items) - 1);
        
        if ($isLast) {
            $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . 
                          sanitizeInput($item['title']) . '</li>';
        } else {
            $breadcrumb .= '<li class="breadcrumb-item"><a href="' . 
                          sanitizeInput($item['url']) . '">' . 
                          sanitizeInput($item['title']) . '</a></li>';
        }
    }
    
    $breadcrumb .= '</ol></nav>';
    return $breadcrumb;
}

/**
 * Get system-wide statistics for admin dashboard
 */
function getSystemStatistics($filters = []) {
    try {
        $stats = [];
        
        // Total users count with role breakdown
        $userStats = fetchOne("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN role = 'faculty' THEN 1 ELSE 0 END) as faculty_count,
                SUM(CASE WHEN role = 'enquirer' THEN 1 ELSE 0 END) as enquirer_count,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users
            FROM users
        ");
        $stats['users'] = $userStats ?: [
            'total_users' => 0, 'admin_count' => 0, 'faculty_count' => 0, 
            'enquirer_count' => 0, 'active_users' => 0, 'inactive_users' => 0
        ];
        
        // Total institutions count
        $institutionStats = fetchOne("
            SELECT 
                COUNT(*) as total_institutions,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_institutions,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_institutions
            FROM institutions
        ");
        $stats['institutions'] = $institutionStats ?: [
            'total_institutions' => 0, 'active_institutions' => 0, 'inactive_institutions' => 0
        ];
        
        // Enquiries statistics with status breakdown
        $enquiryStats = fetchOne("
            SELECT 
                COUNT(*) as total_enquiries,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_enquiries,
                SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_enquiries,
                SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted_enquiries,
                SUM(CASE WHEN status = 'interested' THEN 1 ELSE 0 END) as interested_enquiries,
                SUM(CASE WHEN status = 'not_interested' THEN 1 ELSE 0 END) as not_interested_enquiries,
                SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) as enrolled_enquiries,
                SUM(CASE WHEN assigned_to_user_id IS NULL THEN 1 ELSE 0 END) as unassigned_enquiries,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_enquiries,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_enquiries
            FROM enquiries
        ");
        $stats['enquiries'] = $enquiryStats ?: [
            'total_enquiries' => 0, 'new_enquiries' => 0, 'assigned_enquiries' => 0,
            'contacted_enquiries' => 0, 'interested_enquiries' => 0, 'not_interested_enquiries' => 0,
            'enrolled_enquiries' => 0, 'unassigned_enquiries' => 0, 'today_enquiries' => 0, 'week_enquiries' => 0
        ];
        
        // Students statistics
        $studentStats = fetchOne("
            SELECT 
                COUNT(*) as total_students,
                SUM(CASE WHEN is_exam_eligible = 1 THEN 1 ELSE 0 END) as exam_eligible_students,
                SUM(CASE WHEN is_exam_eligible = 0 THEN 1 ELSE 0 END) as not_eligible_students,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_registrations,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_registrations
            FROM students
        ");
        $stats['students'] = $studentStats ?: [
            'total_students' => 0, 'exam_eligible_students' => 0, 'not_eligible_students' => 0,
            'today_registrations' => 0, 'week_registrations' => 0
        ];
        
        // Certificate requests statistics
        $certificateStats = fetchOne("
            SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests,
                SUM(CASE WHEN DATE(requested_at) = CURDATE() THEN 1 ELSE 0 END) as today_requests
            FROM certificate_requests
        ");
        $stats['certificates'] = $certificateStats ?: [
            'total_requests' => 0, 'pending_requests' => 0, 'approved_requests' => 0,
            'rejected_requests' => 0, 'today_requests' => 0
        ];
        
        // Recent activity
        $recentEnquiries = fetchAll("
            SELECT full_name, course_interest, created_at 
            FROM enquiries 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stats['recent_enquiries'] = $recentEnquiries ?: [];
        
        $recentStudents = fetchAll("
            SELECT s.full_name, s.course, i.name as institution_name, s.created_at
            FROM students s
            LEFT JOIN institutions i ON s.institution_id = i.id
            ORDER BY s.created_at DESC 
            LIMIT 5
        ");
        $stats['recent_students'] = $recentStudents ?: [];
        
        return $stats;
        
    } catch (Exception $e) {
        logEvent("Error fetching system statistics: " . $e->getMessage(), 'ERROR');
        return [
            'users' => ['total_users' => 0, 'admin_count' => 0, 'faculty_count' => 0, 'enquirer_count' => 0, 'active_users' => 0, 'inactive_users' => 0],
            'institutions' => ['total_institutions' => 0, 'active_institutions' => 0, 'inactive_institutions' => 0],
            'enquiries' => ['total_enquiries' => 0, 'new_enquiries' => 0, 'assigned_enquiries' => 0, 'contacted_enquiries' => 0, 'interested_enquiries' => 0, 'not_interested_enquiries' => 0, 'enrolled_enquiries' => 0, 'unassigned_enquiries' => 0, 'today_enquiries' => 0, 'week_enquiries' => 0],
            'students' => ['total_students' => 0, 'exam_eligible_students' => 0, 'not_eligible_students' => 0, 'today_registrations' => 0, 'week_registrations' => 0],
            'certificates' => ['total_requests' => 0, 'pending_requests' => 0, 'approved_requests' => 0, 'rejected_requests' => 0, 'today_requests' => 0],
            'recent_enquiries' => [],
            'recent_students' => []
        ];
    }
}

/**
 * Get filtered statistics based on date range and other filters
 */
function getFilteredStatistics($filters = []) {
    $whereConditions = [];
    $params = [];
    
    // Date range filter
    if (!empty($filters['date_from'])) {
        $whereConditions[] = "created_at >= ?";
        $params[] = $filters['date_from'] . ' 00:00:00';
    }
    
    if (!empty($filters['date_to'])) {
        $whereConditions[] = "created_at <= ?";
        $params[] = $filters['date_to'] . ' 23:59:59';
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    try {
        $stats = [];
        
        // Filtered enquiries
        $enquiryQuery = "SELECT COUNT(*) as count FROM enquiries {$whereClause}";
        $stats['filtered_enquiries'] = fetchOne($enquiryQuery, $params)['count'] ?? 0;
        
        // Filtered students
        $studentQuery = "SELECT COUNT(*) as count FROM students {$whereClause}";
        $stats['filtered_students'] = fetchOne($studentQuery, $params)['count'] ?? 0;
        
        return $stats;
        
    } catch (Exception $e) {
        logEvent("Error fetching filtered statistics: " . $e->getMessage(), 'ERROR');
        return ['filtered_enquiries' => 0, 'filtered_students' => 0];
    }
}

/**
 * Get enhanced enquirer statistics with performance metrics
 */
function getEnquirerStatsWithPerformance($userId) {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total assigned enquiries
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM enquiries WHERE assigned_to_user_id = ?");
        $stmt->execute([$userId]);
        $stats['total_enquiries'] = $stmt->fetchColumn();
        
        // High priority count (assuming we add priority field)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as high_priority 
            FROM enquiries 
            WHERE assigned_to_user_id = ? 
            AND (status = 'new' OR status = 'follow_up' OR created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR))
        ");
        $stmt->execute([$userId]);
        $stats['high_priority'] = $stmt->fetchColumn();
        
        // Status breakdown
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) as count 
            FROM enquiries 
            WHERE assigned_to_user_id = ? 
            GROUP BY status
        ");
        $stmt->execute([$userId]);
        $statusResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats['by_status'] = [];
        foreach ($statusResults as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }
        
        // Conversion rate (interested + enrolled / total)
        $interested = ($stats['by_status']['interested'] ?? 0) + ($stats['by_status']['enrolled'] ?? 0);
        $stats['conversion_rate'] = $stats['total_enquiries'] > 0 ? 
            round(($interested / $stats['total_enquiries']) * 100, 1) : 0;
        
        // Follow-ups due (contacted but no recent activity)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as follow_ups 
            FROM enquiries 
            WHERE assigned_to_user_id = ? 
            AND status IN ('contacted', 'follow_up')
            AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)
        ");
        $stmt->execute([$userId]);
        $stats['follow_ups_due'] = $stmt->fetchColumn();
        
        // Average response time (hours)
        $stats['response_time'] = 2.5; // Placeholder - would need call_logs table
        
        // Enrolled this month
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as enrolled 
            FROM enquiries 
            WHERE assigned_to_user_id = ? 
            AND status = 'enrolled'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$userId]);
        $stats['enrolled_count'] = $stmt->fetchColumn();
        
        // Get unique courses for filter dropdown
        $stmt = $pdo->prepare("
            SELECT DISTINCT course_interest 
            FROM enquiries 
            WHERE assigned_to_user_id = ? 
            AND course_interest IS NOT NULL 
            ORDER BY course_interest
        ");
        $stmt->execute([$userId]);
        $stats['courses'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $stats;
        
    } catch (PDOException $e) {
        logEvent("Error getting enquirer stats: " . $e->getMessage(), 'ERROR');
        return [
            'total_enquiries' => 0,
            'high_priority' => 0,
            'by_status' => [],
            'conversion_rate' => 0,
            'follow_ups_due' => 0,
            'response_time' => 0,
            'enrolled_count' => 0,
            'courses' => []
        ];
    }
}

/**
 * Get call statistics for enquirer
 */
function getCallStatistics($userId) {
    global $pdo;
    
    try {
        $stats = [];
        
        // Today's calls (placeholder - would use call_logs table)
        $stats['today_calls'] = 12;
        
        // Weekly calls
        $stats['weekly_calls'] = 45;
        
        // Average call duration in minutes
        $stats['avg_call_duration'] = 8;
        
        // Success rate percentage
        $stats['success_rate'] = 75;
        
        return $stats;
        
    } catch (Exception $e) {
        logEvent("Error getting call statistics: " . $e->getMessage(), 'ERROR');
        return [
            'today_calls' => 0,
            'weekly_calls' => 0,
            'avg_call_duration' => 0,
            'success_rate' => 0
        ];
    }
}

/**
 * Get priority enquiries for dashboard
 */
function getPriorityEnquiries($userId, $limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT *,
                CASE 
                    WHEN status = 'new' THEN 'high'
                    WHEN status = 'follow_up' THEN 'high'
                    WHEN status = 'interested' THEN 'medium'
                    ELSE 'low'
                END as priority,
                CASE 
                    WHEN status IN ('contacted', 'follow_up') 
                    AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY) THEN 1
                    ELSE 0
                END as is_overdue,
                created_at as last_contact
            FROM enquiries 
            WHERE assigned_to_user_id = ?
            ORDER BY 
                CASE status 
                    WHEN 'new' THEN 1 
                    WHEN 'follow_up' THEN 2 
                    WHEN 'interested' THEN 3 
                    ELSE 4 
                END,
                created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error getting priority enquiries: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Get filtered enquiries for AJAX requests
 */
function getFilteredEnquiries($userId, $filters = []) {
    global $pdo;
    
    try {
        // Ensure filters is an array
        if (!is_array($filters)) {
            $filters = [];
        }
        
        $whereConditions = ["assigned_to_user_id = ?"];
        $params = [$userId];
        
        // Status filter
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        // Course filter
        if (!empty($filters['course'])) {
            $whereConditions[] = "course_interest = ?";
            $params[] = $filters['course'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR course_interest LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $stmt = $pdo->prepare("
            SELECT *,
                CASE 
                    WHEN status = 'new' THEN 'high'
                    WHEN status = 'follow_up' THEN 'high'
                    WHEN status = 'interested' THEN 'medium'
                    ELSE 'low'
                END as priority,
                CASE 
                    WHEN status IN ('contacted', 'follow_up') 
                    AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY) THEN 1
                    ELSE 0
                END as is_overdue,
                created_at as last_contact
            FROM enquiries 
            {$whereClause}
            ORDER BY 
                CASE status 
                    WHEN 'new' THEN 1 
                    WHEN 'follow_up' THEN 2 
                    WHEN 'interested' THEN 3 
                    ELSE 4 
                END,
                created_at DESC
            LIMIT 50
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error getting filtered enquiries: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Get priority row class for styling
 */
function getPriorityRowClass($enquiry) {
    $classes = [];
    
    if ($enquiry['priority'] === 'high') {
        $classes[] = 'priority-high-row';
    }
    
    if ($enquiry['is_overdue']) {
        $classes[] = 'overdue-row';
    }
    
    return implode(' ', $classes);
}

/**
 * Check if enquiry is overdue for follow-up
 */
function isOverdue($enquiry) {
    return $enquiry['is_overdue'] == 1;
}

/**
 * Get enquiry details for status update
 */
function getEnquiryDetails($enquiryId, $userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM enquiries 
            WHERE id = ? AND assigned_to_user_id = ?
        ");
        $stmt->execute([$enquiryId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error getting enquiry details: " . $e->getMessage(), 'ERROR');
        return null;
    }
}

/**
 * Get all assigned enquiries for dropdown
 */
function getAssignedEnquiries($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, full_name, phone, status, created_at
            FROM enquiries 
            WHERE assigned_to_user_id = ?
            ORDER BY 
                CASE status 
                    WHEN 'new' THEN 1 
                    WHEN 'follow_up' THEN 2 
                    WHEN 'contacted' THEN 3 
                    ELSE 4 
                END,
                created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error getting assigned enquiries: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Add entry to enquiry history
 */
function addToHistory($enquiryId, $userId, $actionType, $details = []) {
    global $pdo;
    
    try {
        // Create enquiry_history table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS enquiry_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                enquiry_id INT NOT NULL,
                user_id INT NOT NULL,
                action_type VARCHAR(50) NOT NULL,
                details JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (enquiry_id) REFERENCES enquiries(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ";
        $pdo->exec($createTable);
        
        $stmt = $pdo->prepare("
            INSERT INTO enquiry_history (enquiry_id, user_id, action_type, details)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$enquiryId, $userId, $actionType, json_encode($details)]);
        
        return true;
        
    } catch (PDOException $e) {
        logEvent("Error adding to history: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Get enquiry history with user details
 */
function getEnquiryHistory($enquiryId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT eh.*, u.full_name as user_name
            FROM enquiry_history eh
            JOIN users u ON eh.user_id = u.id
            WHERE eh.enquiry_id = ?
            ORDER BY eh.created_at DESC
        ");
        $stmt->execute([$enquiryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error getting enquiry history: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Schedule follow-up for enquiry
 */
function scheduleFollowup($enquiryId, $userId, $followupDateTime, $notes = '') {
    global $pdo;
    
    try {
        // Create followups table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS enquiry_followups (
                id INT PRIMARY KEY AUTO_INCREMENT,
                enquiry_id INT NOT NULL,
                user_id INT NOT NULL,
                followup_datetime DATETIME NOT NULL,
                notes TEXT,
                status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (enquiry_id) REFERENCES enquiries(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ";
        $pdo->exec($createTable);
        
        $stmt = $pdo->prepare("
            INSERT INTO enquiry_followups (enquiry_id, user_id, followup_datetime, notes)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$enquiryId, $userId, $followupDateTime, $notes]);
        
        // Add to history
        addToHistory($enquiryId, $userId, 'followup_scheduled', [
            'followup_datetime' => $followupDateTime,
            'notes' => $notes
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        logEvent("Error scheduling follow-up: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Get history icon based on action type
 */
function getHistoryIcon($actionType) {
    $icons = [
        'status_update' => 'fa-edit',
        'note_added' => 'fa-sticky-note',
        'followup_scheduled' => 'fa-calendar-plus',
        'call_logged' => 'fa-phone',
        'email_sent' => 'fa-envelope',
        'meeting_scheduled' => 'fa-calendar-check',
        'enquiry_created' => 'fa-plus-circle'
    ];
    
    return $icons[$actionType] ?? 'fa-info-circle';
}

/**
 * Get history title based on action type
 */
function getHistoryTitle($actionType) {
    $titles = [
        'status_update' => 'Status Updated',
        'note_added' => 'Note Added',
        'followup_scheduled' => 'Follow-up Scheduled',
        'call_logged' => 'Call Logged',
        'email_sent' => 'Email Sent',
        'meeting_scheduled' => 'Meeting Scheduled',
        'enquiry_created' => 'Enquiry Created'
    ];
    
    return $titles[$actionType] ?? 'Activity';
}

/**
 * Format history details for display
 */
function formatHistoryDetails($entry) {
    $details = json_decode($entry['details'], true);
    $output = '';
    
    switch ($entry['action_type']) {
        case 'status_update':
            $output .= '<div class="status-change">';
            $output .= '<span class="badge status-' . $details['old_status'] . '">' . ucfirst(str_replace('_', ' ', $details['old_status'])) . '</span>';
            $output .= ' <i class="fas fa-arrow-right mx-2"></i> ';
            $output .= '<span class="badge status-' . $details['new_status'] . '">' . ucfirst(str_replace('_', ' ', $details['new_status'])) . '</span>';
            $output .= '</div>';
            if (!empty($details['notes'])) {
                $output .= '<div class="mt-2"><strong>Notes:</strong> ' . htmlspecialchars($details['notes']) . '</div>';
            }
            break;
            
        case 'note_added':
            $output .= '<div class="note-content">';
            $output .= '<span class="badge bg-secondary">' . ucfirst($details['note_type']) . '</span>';
            $output .= '<div class="mt-2">' . htmlspecialchars($details['note']) . '</div>';
            $output .= '</div>';
            break;
            
        case 'followup_scheduled':
            $output .= '<div class="followup-info">';
            $output .= '<i class="fas fa-calendar"></i> ' . date('M j, Y g:i A', strtotime($details['followup_datetime']));
            if (!empty($details['notes'])) {
                $output .= '<div class="mt-2"><strong>Notes:</strong> ' . htmlspecialchars($details['notes']) . '</div>';
            }
            $output .= '</div>';
            break;
            
        default:
            $output .= '<div class="activity-details">';
            foreach ($details as $key => $value) {
                $output .= '<div><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value) . '</div>';
            }
            $output .= '</div>';
    }
    
    return $output;
}

/**
 * Get detailed call statistics for enquirer
 */
function getCallStatisticsDetailed($userId) {
    global $pdo;
    
    try {
        $stats = [];
        
        // Create call_logs table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS call_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                enquiry_id INT NOT NULL,
                caller_user_id INT NOT NULL,
                call_duration INT DEFAULT 0,
                call_outcome ENUM('answered', 'no_answer', 'busy', 'invalid', 'voicemail') NOT NULL,
                follow_up_required BOOLEAN DEFAULT FALSE,
                notes TEXT,
                call_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (enquiry_id) REFERENCES enquiries(id),
                FOREIGN KEY (caller_user_id) REFERENCES users(id)
            )
        ";
        $pdo->exec($createTable);
        
        // Total calls
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM call_logs WHERE caller_user_id = ?");
        $stmt->execute([$userId]);
        $stats['total_calls'] = $stmt->fetchColumn();
        
        // Today's calls
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM call_logs 
            WHERE caller_user_id = ? AND DATE(call_timestamp) = CURDATE()
        ");
        $stmt->execute([$userId]);
        $stats['today_calls'] = $stmt->fetchColumn();
        
        // Weekly calls
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM call_logs 
            WHERE caller_user_id = ? AND call_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([$userId]);
        $stats['weekly_calls'] = $stmt->fetchColumn();
        
        // Success rate (answered calls / total calls)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM call_logs 
            WHERE caller_user_id = ? AND call_outcome = 'answered'
        ");
        $stmt->execute([$userId]);
        $answeredCalls = $stmt->fetchColumn();
        $stats['success_rate'] = $stats['total_calls'] > 0 ? 
            round(($answeredCalls / $stats['total_calls']) * 100, 1) : 0;
        
        // Average duration
        $stmt = $pdo->prepare("
            SELECT AVG(call_duration) FROM call_logs 
            WHERE caller_user_id = ? AND call_outcome = 'answered'
        ");
        $stmt->execute([$userId]);
        $avgDuration = $stmt->fetchColumn();
        $stats['avg_duration'] = $avgDuration ? round($avgDuration / 60, 1) : 0;
        
        // Follow-ups required
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM call_logs 
            WHERE caller_user_id = ? AND follow_up_required = 1
        ");
        $stmt->execute([$userId]);
        $stats['follow_ups'] = $stmt->fetchColumn();
        
        return $stats;
        
    } catch (PDOException $e) {
        logEvent("Error getting detailed call statistics: " . $e->getMessage(), 'ERROR');
        return [
            'total_calls' => 0,
            'today_calls' => 0,
            'weekly_calls' => 0,
            'success_rate' => 0,
            'avg_duration' => 0,
            'follow_ups' => 0
        ];
    }
}

/**
 * Get filtered call logs
 */
function getFilteredCallLogs($userId, $filters = [], $limit = 50) {
    global $pdo;
    
    try {
        $whereConditions = ["cl.caller_user_id = ?"];
        $params = [$userId];
        
        // Outcome filter
        if (!empty($filters['outcome'])) {
            $whereConditions[] = "cl.call_outcome = ?";
            $params[] = $filters['outcome'];
        }
        
        // Duration filters
        if (!empty($filters['duration_min'])) {
            $whereConditions[] = "cl.call_duration >= ?";
            $params[] = $filters['duration_min'] * 60; // Convert to seconds
        }
        
        if (!empty($filters['duration_max'])) {
            $whereConditions[] = "cl.call_duration <= ?";
            $params[] = $filters['duration_max'] * 60; // Convert to seconds
        }
        
        // Date filters
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(cl.call_timestamp) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(cl.call_timestamp) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Follow-up filter
        if (isset($filters['follow_up']) && $filters['follow_up'] !== '') {
            $whereConditions[] = "cl.follow_up_required = ?";
            $params[] = (int)$filters['follow_up'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(e.full_name LIKE ? OR e.phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $pdo->prepare("
            SELECT cl.*, e.full_name as enquiry_name, e.phone
            FROM call_logs cl
            JOIN enquiries e ON cl.enquiry_id = e.id
            WHERE {$whereClause}
            ORDER BY cl.call_timestamp DESC
            LIMIT ?
        ");
        $params[] = $limit;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error getting filtered call logs: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Log a call manually
 */
function logCall($enquiryId, $userId, $outcome, $duration, $notes = '', $followUpRequired = false) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO call_logs (enquiry_id, caller_user_id, call_duration, call_outcome, follow_up_required, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$enquiryId, $userId, $duration * 60, $outcome, $followUpRequired, $notes]);
        
        $callId = $pdo->lastInsertId();
        
        // Add to enquiry history
        addToHistory($enquiryId, $userId, 'call_logged', [
            'outcome' => $outcome,
            'duration' => $duration,
            'notes' => $notes,
            'follow_up_required' => $followUpRequired
        ]);
        
        return $callId;
        
    } catch (PDOException $e) {
        logEvent("Error logging call: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Update enquiry status
 */
function updateEnquiryStatus($enquiryId, $status) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $enquiryId]);
        
    } catch (PDOException $e) {
        logEvent("Error updating enquiry status: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Export call report
 */
function exportCallReport($userId, $filters, $format) {
    $calls = getFilteredCallLogs($userId, $filters, 1000);
    
    switch ($format) {
        case 'csv':
            exportCallsCSV($calls);
            break;
        case 'excel':
            exportCallsExcel($calls);
            break;
        case 'pdf':
            exportCallsPDF($calls);
            break;
    }
}

/**
 * Export calls to CSV
 */
function exportCallsCSV($calls) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="call_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, [
        'Date', 'Time', 'Contact Name', 'Phone', 'Duration (min)', 
        'Outcome', 'Follow-up Required', 'Notes'
    ]);
    
    // CSV data
    foreach ($calls as $call) {
        fputcsv($output, [
            date('Y-m-d', strtotime($call['call_timestamp'])),
            date('H:i:s', strtotime($call['call_timestamp'])),
            $call['enquiry_name'],
            $call['phone'],
            round($call['call_duration'] / 60, 1),
            ucfirst(str_replace('_', ' ', $call['call_outcome'])),
            $call['follow_up_required'] ? 'Yes' : 'No',
            $call['notes']
        ]);
    }
    
    fclose($output);
}

/**
 * Export calls to Excel (simplified CSV with .xlsx extension)
 */
function exportCallsExcel($calls) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="call_report_' . date('Y-m-d') . '.xlsx"');
    
    // For simplicity, export as CSV with Excel headers
    exportCallsCSV($calls);
}

/**
 * Export calls to PDF (basic HTML to PDF)
 */
function exportCallsPDF($calls) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="call_report_' . date('Y-m-d') . '.pdf"');
    
    // For simplicity, create an HTML report
    // In production, you'd use a proper PDF library like TCPDF or FPDF
    echo '<html><head><title>Call Report</title></head><body>';
    echo '<h1>Call Report - ' . date('Y-m-d') . '</h1>';
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>Date</th><th>Contact</th><th>Duration</th><th>Outcome</th><th>Notes</th></tr>';
    
    foreach ($calls as $call) {
        echo '<tr>';
        echo '<td>' . date('Y-m-d H:i', strtotime($call['call_timestamp'])) . '</td>';
        echo '<td>' . htmlspecialchars($call['enquiry_name']) . '</td>';
        echo '<td>' . round($call['call_duration'] / 60, 1) . ' min</td>';
        echo '<td>' . ucfirst(str_replace('_', ' ', $call['call_outcome'])) . '</td>';
        echo '<td>' . htmlspecialchars($call['notes']) . '</td>';
        echo '</tr>';
    }
    
    echo '</table></body></html>';
}

/**
 * Get performance data for charts
 */
function getPerformanceData($userId, $period = 'week') {
    global $pdo;
    
    try {
        $data = [];
        
        // Get outcome distribution
        $stmt = $pdo->prepare("
            SELECT call_outcome, COUNT(*) as count
            FROM call_logs 
            WHERE caller_user_id = ?
            GROUP BY call_outcome
        ");
        $stmt->execute([$userId]);
        $data['outcomes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get daily activity for the period
        $days = $period === 'month' ? 30 : 7;
        $stmt = $pdo->prepare("
            SELECT DATE(call_timestamp) as call_date, COUNT(*) as call_count
            FROM call_logs 
            WHERE caller_user_id = ? 
            AND call_timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(call_timestamp)
            ORDER BY call_date
        ");
        $stmt->execute([$userId, $days]);
        $data['daily_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $data;
        
    } catch (PDOException $e) {
        logEvent("Error getting performance data: " . $e->getMessage(), 'ERROR');
        return ['outcomes' => [], 'daily_activity' => []];
    }
}

/**
 * Get duration badge class for styling
 */
function getDurationBadgeClass($duration) {
    if ($duration < 120) return 'duration-short'; // Less than 2 minutes
    if ($duration < 600) return 'duration-medium'; // Less than 10 minutes
    return 'duration-long'; // 10+ minutes
}

/**
 * Format duration for display
 */
function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    
    if ($minutes > 0) {
        return $minutes . 'm ' . $remainingSeconds . 's';
    } else {
        return $remainingSeconds . 's';
    }
}

/**
 * Legacy function for backward compatibility
 * Note: This function is defined in auth_check_enquirer.php
 */

/**
 * Export and Download Management Functions
 */

/**
 * Get export statistics
 */
function getExportStatistics() {
    global $pdo;
    
    try {
        // Total exports
        $stmt = $pdo->query("SELECT COUNT(*) FROM system_exports");
        $totalExports = $stmt->fetchColumn();
        
        // Today's exports
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_exports WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $todayExports = $stmt->fetchColumn();
        
        // Scheduled exports
        $stmt = $pdo->query("SELECT COUNT(*) FROM scheduled_exports WHERE is_active = 1");
        $scheduledExports = $stmt->fetchColumn();
        
        // Total file size
        $stmt = $pdo->query("SELECT COALESCE(SUM(file_size), 0) FROM system_exports WHERE status = 'completed'");
        $totalSize = $stmt->fetchColumn();
        
        return [
            'total_exports' => $totalExports,
            'today_exports' => $todayExports,
            'scheduled_exports' => $scheduledExports,
            'total_size' => $totalSize
        ];
        
    } catch (Exception $e) {
        error_log("Export statistics error: " . $e->getMessage());
        return [
            'total_exports' => 0,
            'today_exports' => 0,
            'scheduled_exports' => 0,
            'total_size' => 0
        ];
    }
}

/**
 * Get recent exports
 */
function getRecentExports($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, data_type, format, record_count, file_size, status, 
                   filename, file_path, created_at, completed_at
            FROM system_exports 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Recent exports error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get scheduled exports
 */
function getScheduledExports() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT id, data_type, format, frequency, next_run, email_address, 
                   compress, is_active, created_at
            FROM scheduled_exports 
            WHERE is_active = 1
            ORDER BY next_run ASC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Scheduled exports error: " . $e->getMessage());
        return [];
    }
}

/**
 * Start export process
 */
function startExportProcess($dataType, $format, $dateFrom, $dateTo, $filters, $options) {
    global $pdo;
    
    try {
        // Insert export record
        $stmt = $pdo->prepare("
            INSERT INTO system_exports (data_type, format, filters, date_from, date_to, 
                                      options, status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'processing', ?, NOW())
        ");
        
        $stmt->execute([
            $dataType,
            $format,
            json_encode($filters),
            $dateFrom ?: null,
            $dateTo ?: null,
            json_encode($options),
            getCurrentUserId()
        ]);
        
        $exportId = $pdo->lastInsertId();
        
        // Process export in background (simplified - in production use queue system)
        processExport($exportId);
        
        return $exportId;
        
    } catch (Exception $e) {
        error_log("Start export error: " . $e->getMessage());
        throw new Exception("Failed to start export process");
    }
}

/**
 * Process export (simplified version)
 */
function processExport($exportId) {
    global $pdo;
    
    try {
        // Get export details
        $stmt = $pdo->prepare("SELECT * FROM system_exports WHERE id = ?");
        $stmt->execute([$exportId]);
        $export = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$export) {
            throw new Exception("Export not found");
        }
        
        // Get data based on type
        $data = getExportData($export['data_type'], $export['filters'], $export['date_from'], $export['date_to']);
        
        // Create export manager
        $exportManager = new ExportManager($pdo);
        
        // Export data
        $result = $exportManager->exportData($data, $export['format'], [
            'filename' => $export['data_type'] . '_export_' . date('Y-m-d_H-i-s'),
            'include_headers' => json_decode($export['options'], true)['include_headers'] ?? true,
            'include_metadata' => json_decode($export['options'], true)['include_metadata'] ?? true,
            'compress' => json_decode($export['options'], true)['compress'] ?? false
        ]);
        
        if ($result['success']) {
            // Update export record
            $stmt = $pdo->prepare("
                UPDATE system_exports 
                SET status = 'completed', filename = ?, file_path = ?, file_size = ?, 
                    record_count = ?, completed_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $result['filename'],
                $result['filepath'],
                $result['size'],
                count($data),
                $exportId
            ]);
            
            // Send email if requested
            $options = json_decode($export['options'], true);
            if ($options['email_delivery'] && !empty($options['email_address'])) {
                sendExportEmail($exportId, $options['email_address'], $options['email_subject']);
            }
            
        } else {
            // Update as failed
            $stmt = $pdo->prepare("
                UPDATE system_exports 
                SET status = 'failed', error_message = ?, completed_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([$result['message'], $exportId]);
        }
        
    } catch (Exception $e) {
        error_log("Process export error: " . $e->getMessage());
        
        // Update as failed
        $stmt = $pdo->prepare("
            UPDATE system_exports 
            SET status = 'failed', error_message = ?, completed_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([$e->getMessage(), $exportId]);
    }
}

/**
 * Get export data based on type
 */
function getExportData($dataType, $filters, $dateFrom, $dateTo) {
    global $pdo;
    
    $filters = json_decode($filters, true) ?: [];
    $whereConditions = [];
    $params = [];
    
    // Add date filters
    if ($dateFrom) {
        $whereConditions[] = "created_at >= ?";
        $params[] = $dateFrom;
    }
    if ($dateTo) {
        $whereConditions[] = "created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    switch ($dataType) {
        case 'users':
            $query = "SELECT id, username, full_name, email, phone, role, 
                            institution_id, is_active, created_at FROM users";
            
            // Add role filter
            if (!empty($filters['role'])) {
                $whereConditions[] = "role = ?";
                $params[] = $filters['role'];
            }
            
            // Add active status filter
            if (isset($filters['is_active'])) {
                $whereConditions[] = "is_active = ?";
                $params[] = $filters['is_active'];
            }
            break;
            
        case 'institutions':
            $query = "SELECT id, name, code, address, phone, email, 
                            established_date, is_active, created_at FROM institutions";
            break;
            
        case 'students':
            $query = "SELECT s.id, s.student_id, s.full_name, s.email, s.phone, 
                            s.course, s.batch_year, s.is_exam_eligible, s.created_at,
                            i.name as institution_name
                     FROM students s 
                     LEFT JOIN institutions i ON s.institution_id = i.id";
            
            // Add institution filter
            if (!empty($filters['institution_id'])) {
                $whereConditions[] = "s.institution_id = ?";
                $params[] = $filters['institution_id'];
            }
            
            // Add exam eligibility filter
            if (isset($filters['is_exam_eligible'])) {
                $whereConditions[] = "s.is_exam_eligible = ?";
                $params[] = $filters['is_exam_eligible'];
            }
            break;
            
        case 'enquiries':
            $query = "SELECT e.id, e.full_name, e.email, e.phone, e.course_interest, 
                            e.status, e.created_at, u.full_name as assigned_to
                     FROM enquiries e 
                     LEFT JOIN users u ON e.assigned_to_user_id = u.id";
            
            // Add status filter
            if (!empty($filters['status'])) {
                $whereConditions[] = "e.status = ?";
                $params[] = $filters['status'];
            }
            
            // Add assignment filter
            if (!empty($filters['assigned'])) {
                if ($filters['assigned'] === 'assigned') {
                    $whereConditions[] = "e.assigned_to_user_id IS NOT NULL";
                } else {
                    $whereConditions[] = "e.assigned_to_user_id IS NULL";
                }
            }
            break;
            
        case 'certificates':
            $query = "SELECT cr.id, cr.request_type, cr.status, cr.requested_at, cr.processed_at,
                            s.full_name as student_name, s.student_id,
                            i.name as institution_name
                     FROM certificate_requests cr
                     JOIN students s ON cr.student_id = s.id
                     JOIN institutions i ON cr.institution_id = i.id";
            break;
            
        case 'call_logs':
            $query = "SELECT cl.id, cl.call_duration, cl.call_outcome, cl.follow_up_required,
                            cl.call_timestamp, e.full_name as enquiry_name, e.phone,
                            u.full_name as caller_name
                     FROM call_logs cl
                     JOIN enquiries e ON cl.enquiry_id = e.id
                     JOIN users u ON cl.caller_user_id = u.id";
            break;
            
        case 'marklists':
            $query = "SELECT m.id, m.subject, m.marks_obtained, m.total_marks, m.grade,
                            m.exam_date, s.full_name as student_name, s.student_id,
                            i.name as institution_name
                     FROM marklists m
                     JOIN students s ON m.student_id = s.id
                     JOIN institutions i ON s.institution_id = i.id";
            break;
            
        case 'all_data':
            // For complete system data, we'll export multiple sheets/files
            return getCompleteSystemData($dateFrom, $dateTo);
            
        default:
            throw new Exception("Unknown data type: " . $dataType);
    }
    
    // Add WHERE conditions
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $query .= " ORDER BY created_at DESC";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Get export data error: " . $e->getMessage());
        throw new Exception("Failed to retrieve export data");
    }
}

/**
 * Get complete system data for comprehensive export
 */
function getCompleteSystemData($dateFrom, $dateTo) {
    $data = [];
    
    $dataTypes = ['users', 'institutions', 'students', 'enquiries', 'certificates', 'call_logs', 'marklists'];
    
    foreach ($dataTypes as $type) {
        try {
            $data[$type] = getExportData($type, '{}', $dateFrom, $dateTo);
        } catch (Exception $e) {
            error_log("Error getting $type data: " . $e->getMessage());
            $data[$type] = [];
        }
    }
    
    return $data;
}

/**
 * Create scheduled export
 */
function createScheduledExport($dataType, $format, $frequency, $time, $email, $compress) {
    global $pdo;
    
    try {
        // Calculate next run time
        $nextRun = calculateNextRun($frequency, $time);
        
        $stmt = $pdo->prepare("
            INSERT INTO scheduled_exports (data_type, format, frequency, run_time, 
                                         email_address, compress, next_run, is_active, 
                                         created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())
        ");
        
        $stmt->execute([
            $dataType,
            $format,
            $frequency,
            $time,
            $email,
            $compress ? 1 : 0,
            $nextRun,
            getCurrentUserId()
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        error_log("Create scheduled export error: " . $e->getMessage());
        throw new Exception("Failed to create scheduled export");
    }
}

/**
 * Calculate next run time for scheduled export
 */
function calculateNextRun($frequency, $time) {
    $now = new DateTime();
    $runTime = DateTime::createFromFormat('H:i', $time);
    
    switch ($frequency) {
        case 'daily':
            $next = new DateTime('tomorrow ' . $time);
            break;
        case 'weekly':
            $next = new DateTime('next monday ' . $time);
            break;
        case 'monthly':
            $next = new DateTime('first day of next month ' . $time);
            break;
        default:
            throw new Exception("Invalid frequency: " . $frequency);
    }
    
    return $next->format('Y-m-d H:i:s');
}

/**
 * Send export email notification
 */
function sendExportEmail($exportId, $emailAddress, $subject) {
    global $pdo;
    
    try {
        // Get export details
        $stmt = $pdo->prepare("SELECT * FROM system_exports WHERE id = ?");
        $stmt->execute([$exportId]);
        $export = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$export) {
            throw new Exception("Export not found");
        }
        
        // Create download URL
        $downloadUrl = getBaseUrl() . "/admin/downloads.php?ajax=download&id=" . $exportId;
        
        // Email content
        $message = "
        <h2>Data Export Ready</h2>
        <p>Your requested data export is ready for download.</p>
        
        <h3>Export Details:</h3>
        <ul>
            <li><strong>Data Type:</strong> " . ucfirst($export['data_type']) . "</li>
            <li><strong>Format:</strong> " . strtoupper($export['format']) . "</li>
            <li><strong>Records:</strong> " . number_format($export['record_count']) . "</li>
            <li><strong>File Size:</strong> " . formatBytes($export['file_size']) . "</li>
            <li><strong>Generated:</strong> " . date('M j, Y g:i A', strtotime($export['completed_at'])) . "</li>
        </ul>
        
        <p><a href='{$downloadUrl}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Download Export</a></p>
        
        <p><small>This download link will expire in 24 hours for security reasons.</small></p>
        ";
        
        // Send email (simplified - in production use proper email library)
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: System <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
        
        mail($emailAddress, $subject, $message, $headers);
        
    } catch (Exception $e) {
        error_log("Send export email error: " . $e->getMessage());
    }
}

/**
 * Get export by ID
 */
function getExportById($exportId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM system_exports WHERE id = ?");
        $stmt->execute([$exportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Get export by ID error: " . $e->getMessage());
        return null;
    }
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Get data type icon
 */
function getDataTypeIcon($dataType) {
    $icons = [
        'users' => 'users',
        'institutions' => 'university',
        'students' => 'graduation-cap',
        'enquiries' => 'envelope',
        'certificates' => 'certificate',
        'call_logs' => 'phone',
        'marklists' => 'list-alt',
        'all_data' => 'database'
    ];
    
    return $icons[$dataType] ?? 'file';
}

/**
 * Get base URL
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

/**
 * Get institutions for dropdown
 */
function getInstitutions() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT id, name FROM institutions WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Get institutions error: " . $e->getMessage());
        return [];
    }
}

/**
 * URL Helper Functions for BASE_URL support
 */

/**
 * Get asset URL with BASE_URL prefix
 * @param string $path - Path to asset (e.g., '/css/style.css')
 * @return string - Full URL with BASE_URL prefix
 */
function asset($path) {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Get URL with BASE_URL prefix
 * @param string $path - Path (e.g., '/about' or 'about')
 * @return string - Full URL with BASE_URL prefix
 */
function url($path = '') {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

/**
 * Get current URL
 * @return string - Current full URL
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if current page matches given path
 * @param string $path - Path to check
 * @return bool
 */
function isCurrentPage($path) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $checkPath = BASE_URL . '/' . ltrim($path, '/');
    return $currentPath === $checkPath || $currentPath === $checkPath . '.php';
}

/**
 * Get filtered enquiries for admin (without user restriction)
 */
function getAdminFilteredEnquiries($filters = [], $sortBy = 'created_at', $sortOrder = 'DESC', $page = 1, $perPage = 25) {
    global $pdo;
    
    try {
        // Ensure filters is an array
        if (!is_array($filters)) {
            $filters = [];
        }
        
        $whereConditions = [];
        $params = [];
        
        // Status filter
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        // Assigned to filter
        if (isset($filters['assigned_to_user_id'])) {
            if ($filters['assigned_to_user_id'] === '0' || $filters['assigned_to_user_id'] === 0) {
                $whereConditions[] = "(assigned_to_user_id IS NULL OR assigned_to_user_id = 0)";
            } else {
                $whereConditions[] = "assigned_to_user_id = ?";
                $params[] = $filters['assigned_to_user_id'];
            }
        }
        
        // Course filter
        if (!empty($filters['course_interest'])) {
            $whereConditions[] = "course_interest = ?";
            $params[] = $filters['course_interest'];
        }
        
        // Date range filters
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR course_interest LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Validate sort parameters
        $allowedSortColumns = ['id', 'full_name', 'email', 'phone', 'course_interest', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        $stmt = $pdo->prepare("
            SELECT e.*,
                u.username as assigned_to_username,
                u.full_name as assigned_to_name
            FROM enquiries e
            LEFT JOIN users u ON e.assigned_to_user_id = u.id
            {$whereClause}
            ORDER BY {$sortBy} {$sortOrder}
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error getting admin filtered enquiries: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Get enquiry count for admin with filters
 */
function getAdminEnquiryCount($filters = []) {
    global $pdo;
    
    try {
        // Ensure filters is an array
        if (!is_array($filters)) {
            $filters = [];
        }
        
        $whereConditions = [];
        $params = [];
        
        // Status filter
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        // Assigned to filter
        if (isset($filters['assigned_to_user_id'])) {
            if ($filters['assigned_to_user_id'] === '0' || $filters['assigned_to_user_id'] === 0) {
                $whereConditions[] = "(assigned_to_user_id IS NULL OR assigned_to_user_id = 0)";
            } else {
                $whereConditions[] = "assigned_to_user_id = ?";
                $params[] = $filters['assigned_to_user_id'];
            }
        }
        
        // Course filter
        if (!empty($filters['course_interest'])) {
            $whereConditions[] = "course_interest = ?";
            $params[] = $filters['course_interest'];
        }
        
        // Date range filters
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR course_interest LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM enquiries
            {$whereClause}
        ");
        
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
        
    } catch (PDOException $e) {
        logEvent("Error getting admin enquiry count: " . $e->getMessage(), 'ERROR');
        return 0;
    }
}
