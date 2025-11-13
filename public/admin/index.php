<?php
/**
 * Admin Dashboard
 * Main dashboard for admin users with system overview and filtering widgets
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Page title
$pageTitle = 'Admin Dashboard';

// Handle AJAX requests for filtered statistics
if (isset($_GET['ajax']) && $_GET['ajax'] === 'stats') {
    header('Content-Type: application/json');
    
    $filters = [];
    if (!empty($_GET['date_from'])) {
        $filters['date_from'] = $_GET['date_from'];
    }
    if (!empty($_GET['date_to'])) {
        $filters['date_to'] = $_GET['date_to'];
    }
    
    $filteredStats = getFilteredStatistics($filters);
    echo json_encode($filteredStats);
    exit;
}

// Get system statistics
$stats = getSystemStatistics();

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
        </h1>
        <p class="lead">Welcome to the admin portal, <?php echo htmlspecialchars(getCurrentUserName()); ?>!</p>
    </div>
</div>

<!-- Filtering Widgets -->
<div class="row mb-4">
    <div class="col-12">
        <div class="filter-section">
            <h5><i class="fas fa-filter"></i> Dashboard Filters</h5>
            <form id="dashboardFilters" class="row g-3">
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="dateFrom" name="date_from">
                </div>
                <div class="col-md-3">
                    <label for="dateTo" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="dateTo" name="date_to">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quick Filters</label>
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuickFilter('today')">Today</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuickFilter('week')">This Week</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuickFilter('month')">This Month</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Main Statistics Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative" onclick="window.location.href='<?php echo url('admin/users'); ?>'">
            <div class="stat-number"><?php echo number_format($stats['users']['total_users']); ?></div>
            <div class="stat-label">Total Users</div>
            <div class="stat-breakdown">
                <small>Active: <?php echo $stats['users']['active_users']; ?> | Inactive: <?php echo $stats['users']['inactive_users']; ?></small>
            </div>
            <i class="fas fa-users stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative" onclick="window.location.href='<?php echo url('admin/institutions'); ?>'">
            <div class="stat-number"><?php echo number_format($stats['institutions']['total_institutions']); ?></div>
            <div class="stat-label">Institutions</div>
            <div class="stat-breakdown">
                <small>Active: <?php echo $stats['institutions']['active_institutions']; ?> | Inactive: <?php echo $stats['institutions']['inactive_institutions']; ?></small>
            </div>
            <i class="fas fa-university stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative" onclick="window.location.href='<?php echo url('admin/enquiries'); ?>'">
            <div class="stat-number"><?php echo number_format($stats['enquiries']['total_enquiries']); ?></div>
            <div class="stat-label">Enquiries</div>
            <div class="stat-breakdown">
                <small>New: <?php echo $stats['enquiries']['new_enquiries']; ?> | Today: <?php echo $stats['enquiries']['today_enquiries']; ?></small>
            </div>
            <i class="fas fa-envelope stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card position-relative" onclick="window.location.href='<?php echo url('admin/students'); ?>'">
            <div class="stat-number"><?php echo number_format($stats['students']['total_students']); ?></div>
            <div class="stat-label">Students</div>
            <div class="stat-breakdown">
                <small>Eligible: <?php echo $stats['students']['exam_eligible_students']; ?> | Today: <?php echo $stats['students']['today_registrations']; ?></small>
            </div>
            <i class="fas fa-graduation-cap stat-icon"></i>
        </div>
    </div>
</div>

<!-- Detailed Statistics Row -->
<div class="row">
    <!-- User Role Breakdown -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users-cog"></i> User Roles
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Admins:</span>
                    <span class="badge bg-danger"><?php echo $stats['users']['admin_count']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Faculty:</span>
                    <span class="badge bg-success"><?php echo $stats['users']['faculty_count']; ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Enquirers:</span>
                    <span class="badge bg-info"><?php echo $stats['users']['enquirer_count']; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enquiry Status Breakdown -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Enquiry Status
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <small>New:</small>
                    <span class="badge status-new"><?php echo $stats['enquiries']['new_enquiries']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small>Assigned:</small>
                    <span class="badge status-assigned"><?php echo $stats['enquiries']['assigned_enquiries']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small>Contacted:</small>
                    <span class="badge status-contacted"><?php echo $stats['enquiries']['contacted_enquiries']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small>Interested:</small>
                    <span class="badge status-interested"><?php echo $stats['enquiries']['interested_enquiries']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small>Not Interested:</small>
                    <span class="badge status-not-interested"><?php echo $stats['enquiries']['not_interested_enquiries']; ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <small>Enrolled:</small>
                    <span class="badge status-enrolled"><?php echo $stats['enquiries']['enrolled_enquiries']; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Certificate Requests -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-certificate"></i> Certificates
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Pending:</span>
                    <span class="badge status-pending"><?php echo $stats['certificates']['pending_requests']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Approved:</span>
                    <span class="badge status-approved"><?php echo $stats['certificates']['approved_requests']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Rejected:</span>
                    <span class="badge status-rejected"><?php echo $stats['certificates']['rejected_requests']; ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Today:</span>
                    <span class="badge bg-primary"><?php echo $stats['certificates']['today_requests']; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo url('admin/users'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="<?php echo url('admin/institutions'); ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-university"></i> Institutions
                    </a>
                    <a href="<?php echo url('admin/enquiries'); ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-envelope"></i> Enquiries
                    </a>
                    <a href="<?php echo url('admin/certificates'); ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-certificate"></i> Certificates
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock"></i> Recent Enquiries</span>
                <a href="<?php echo url('admin/enquiries'); ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($stats['recent_enquiries'])): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No recent enquiries</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['recent_enquiries'] as $enquiry): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($enquiry['full_name']); ?></h6>
                                        <p class="mb-1 text-muted small"><?php echo htmlspecialchars($enquiry['course_interest']); ?></p>
                                    </div>
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($enquiry['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-graduate"></i> Recent Students</span>
                <a href="<?php echo url('admin/students'); ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($stats['recent_students'])): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-user-graduate fa-2x mb-2"></i>
                        <p>No recent student registrations</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['recent_students'] as $student): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h6>
                                        <p class="mb-1 text-muted small">
                                            <?php echo htmlspecialchars($student['course']); ?> - 
                                            <?php echo htmlspecialchars($student['institution_name'] ?: 'Unknown Institution'); ?>
                                        </p>
                                    </div>
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($student['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Health and Alerts -->
<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle"></i> System Alerts & Notifications
            </div>
            <div class="card-body">
                <?php
                $alerts = [];
                
                // Check for unassigned enquiries
                if ($stats['enquiries']['unassigned_enquiries'] > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'fas fa-user-slash',
                        'message' => $stats['enquiries']['unassigned_enquiries'] . ' enquiries are unassigned and need attention.',
                        'action' => 'enquiries.php'
                    ];
                }
                
                // Check for pending certificate requests
                if ($stats['certificates']['pending_requests'] > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'icon' => 'fas fa-certificate',
                        'message' => $stats['certificates']['pending_requests'] . ' certificate requests are pending approval.',
                        'action' => 'certificates.php'
                    ];
                }
                
                // Check for inactive institutions
                if ($stats['institutions']['inactive_institutions'] > 0) {
                    $alerts[] = [
                        'type' => 'secondary',
                        'icon' => 'fas fa-university',
                        'message' => $stats['institutions']['inactive_institutions'] . ' institutions are currently inactive.',
                        'action' => 'institutions.php'
                    ];
                }
                
                if (empty($alerts)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        All systems are running smoothly! No immediate attention required.
                    </div>
                <?php else: ?>
                    <?php foreach ($alerts as $alert): ?>
                        <div class="alert alert-<?php echo $alert['type']; ?> d-flex justify-content-between align-items-center">
                            <span>
                                <i class="<?php echo $alert['icon']; ?>"></i>
                                <?php echo $alert['message']; ?>
                            </span>
                            <a href="<?php echo $alert['action']; ?>" class="btn btn-sm btn-outline-<?php echo $alert['type']; ?>">
                                Take Action
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Session Information
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>User ID:</strong> <?php echo getCurrentUserId(); ?>
                </div>
                <div class="mb-2">
                    <strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
                <div class="mb-2">
                    <strong>Role:</strong> 
                    <span class="badge bg-danger"><?php echo htmlspecialchars(getCurrentUserRole()); ?></span>
                </div>
                <div class="mb-2">
                    <strong>Login Time:</strong><br>
                    <small class="text-muted"><?php echo date('M j, Y g:i A', $_SESSION['login_time']); ?></small>
                </div>
                <div class="mb-2">
                    <strong>Last Activity:</strong><br>
                    <small class="text-muted"><?php echo date('M j, Y g:i A', $_SESSION['last_activity']); ?></small>
                </div>
                <div>
                    <strong>Session Token:</strong><br>
                    <small class="text-muted font-monospace"><?php echo substr($_SESSION['session_token'], 0, 12) . '...'; ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.stat-breakdown {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-top: 0.5rem;
}

.list-group-item:last-child {
    border-bottom: none !important;
}

.alert {
    border-left: 4px solid;
}

.alert-warning {
    border-left-color: #ffc107;
}

.alert-info {
    border-left-color: #0dcaf0;
}

.alert-secondary {
    border-left-color: #6c757d;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

#dashboardFilters .btn-group .btn {
    font-size: 0.75rem;
}
</style>

<script>
// Dashboard filtering functionality
function applyFilters() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    if (dateFrom || dateTo) {
        // Show loading state
        showLoadingState();
        
        // Make AJAX request for filtered statistics
        const params = new URLSearchParams();
        params.append('ajax', 'stats');
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        
        fetch(`?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                updateFilteredStats(data);
                hideLoadingState();
            })
            .catch(error => {
                console.error('Error fetching filtered stats:', error);
                hideLoadingState();
            });
    }
}

function clearFilters() {
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    location.reload(); // Reload to show all data
}

function setQuickFilter(period) {
    const today = new Date();
    let fromDate;
    
    switch (period) {
        case 'today':
            fromDate = today.toISOString().split('T')[0];
            document.getElementById('dateFrom').value = fromDate;
            document.getElementById('dateTo').value = fromDate;
            break;
        case 'week':
            fromDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            document.getElementById('dateFrom').value = fromDate.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];
            break;
        case 'month':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            document.getElementById('dateFrom').value = fromDate.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];
            break;
    }
    
    applyFilters();
}

function showLoadingState() {
    // Add loading spinners to stat cards
    document.querySelectorAll('.stat-number').forEach(el => {
        el.innerHTML = '<div class="loading-spinner"></div>';
    });
}

function hideLoadingState() {
    // Remove loading state (would be replaced by actual data)
}

function updateFilteredStats(data) {
    // Update statistics with filtered data
    // This would update the display with filtered results
    console.log('Filtered stats:', data);
}

// Auto-refresh dashboard every 5 minutes
setInterval(() => {
    if (!document.getElementById('dateFrom').value && !document.getElementById('dateTo').value) {
        location.reload();
    }
}, 300000);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php renderAdminFooter(); ?>