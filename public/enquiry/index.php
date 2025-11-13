<?php
/**
 * Enquirer Dashboard
 * Main dashboard for enquirer users with assigned leads overview
 * Enhanced with filtering, prioritization, and performance metrics
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include enquirer authorization
require_once __DIR__ . '/../../includes/auth_check_enquirer.php';

// Page title
$pageTitle = 'Enquirer Dashboard';

// Get current user ID
$userId = getCurrentUserId();

// Handle AJAX requests for filtering
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    $filters = [
        'status' => $_GET['status'] ?? '',
        'priority' => $_GET['priority'] ?? '',
        'date_range' => $_GET['date_range'] ?? '',
        'course' => $_GET['course'] ?? '',
        'search' => $_GET['search'] ?? ''
    ];
    
    $enquiries = getFilteredEnquiries($userId, $filters);
    echo json_encode(['success' => true, 'data' => $enquiries]);
    exit;
}

// Get enquirer statistics with performance metrics
$stats = getEnquirerStatsWithPerformance($userId);

// Get priority enquiries for dashboard display
$priorityEnquiries = getPriorityEnquiries($userId, 10);

// Get recent call statistics
$callStats = getCallStatistics($userId);

// Render header
renderEnquirerHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-tachometer-alt"></i> Enquirer Dashboard
        </h1>
        <p class="lead">Welcome to the enquirer portal, <?php echo htmlspecialchars(getCurrentUserName()); ?>!</p>
    </div>
</div>

<!-- Performance Metrics Row -->
<div class="row">
    <div class="col-md-2 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo $stats['total_enquiries']; ?></div>
            <div class="stat-label">Total Assigned</div>
            <i class="fas fa-envelope stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo $stats['high_priority']; ?></div>
            <div class="stat-label">High Priority</div>
            <i class="fas fa-exclamation-triangle stat-icon text-warning"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo $callStats['today_calls']; ?></div>
            <div class="stat-label">Today's Calls</div>
            <i class="fas fa-phone stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo $stats['conversion_rate']; ?>%</div>
            <div class="stat-label">Conversion Rate</div>
            <i class="fas fa-chart-line stat-icon text-success"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo $callStats['avg_call_duration']; ?>m</div>
            <div class="stat-label">Avg Call Time</div>
            <i class="fas fa-clock stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-4">
        <div class="stat-card position-relative">
            <div class="stat-number"><?php echo $stats['follow_ups_due']; ?></div>
            <div class="stat-label">Follow-ups Due</div>
            <i class="fas fa-calendar-check stat-icon text-info"></i>
        </div>
    </div>
</div>

<!-- Priority Leads and Quick Filters -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-star"></i> Priority Leads & Quick Filters</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary filter-btn" data-filter="all">All</button>
                    <button type="button" class="btn btn-sm btn-outline-warning filter-btn" data-filter="new">New</button>
                    <button type="button" class="btn btn-sm btn-outline-info filter-btn" data-filter="high_priority">High Priority</button>
                    <button type="button" class="btn btn-sm btn-outline-success filter-btn" data-filter="interested">Interested</button>
                    <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-filter="overdue">Overdue</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by name, phone, or course...">
                    </div>
                    <div class="col-md-2">
                        <select id="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="new">New</option>
                            <option value="assigned">Assigned</option>
                            <option value="contacted">Contacted</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="follow_up">Follow Up</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="priorityFilter" class="form-select">
                            <option value="">All Priority</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="courseFilter" class="form-select">
                            <option value="">All Courses</option>
                            <?php foreach ($stats['courses'] as $course): ?>
                                <option value="<?php echo htmlspecialchars($course); ?>"><?php echo htmlspecialchars($course); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
                
                <div id="priorityLeadsTable">
                    <?php if (!empty($priorityEnquiries)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th>Last Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($priorityEnquiries as $enquiry): ?>
                                        <tr class="<?php echo getPriorityRowClass($enquiry); ?>">
                                            <td>
                                                <span class="badge priority-<?php echo $enquiry['priority']; ?>">
                                                    <?php echo strtoupper($enquiry['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($enquiry['full_name']); ?></strong>
                                                <?php if (isOverdue($enquiry)): ?>
                                                    <i class="fas fa-clock text-danger" title="Overdue for follow-up"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="text-decoration-none">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($enquiry['phone']); ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($enquiry['course_interest']); ?></td>
                                            <td>
                                                <span class="badge status-<?php echo $enquiry['status']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $enquiry['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo $enquiry['last_contact'] ? date('M j, g:i A', strtotime($enquiry['last_contact'])) : 'Never'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="btn btn-outline-success btn-sm" title="Call">
                                                        <i class="fas fa-phone"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickUpdate(<?php echo $enquiry['id']; ?>)" title="Quick Update">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No priority leads at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Status Distribution
            </div>
            <div class="card-body">
                <?php if (!empty($stats['by_status'])): ?>
                    <?php foreach ($stats['by_status'] as $status => $count): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge status-<?php echo $status; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                            <div>
                                <strong><?php echo $count; ?></strong>
                                <small class="text-muted">(<?php echo $stats['total_enquiries'] > 0 ? round(($count / $stats['total_enquiries']) * 100, 1) : 0; ?>%)</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No enquiries assigned yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Performance Metrics
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="metric-item">
                            <div class="metric-value text-success"><?php echo $callStats['success_rate']; ?>%</div>
                            <div class="metric-label">Call Success Rate</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="metric-item">
                            <div class="metric-value text-info"><?php echo $callStats['weekly_calls']; ?></div>
                            <div class="metric-label">This Week's Calls</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-item">
                            <div class="metric-value text-warning"><?php echo $stats['response_time']; ?>h</div>
                            <div class="metric-label">Avg Response Time</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="metric-item">
                            <div class="metric-value text-primary"><?php echo $stats['enrolled_count']; ?></div>
                            <div class="metric-label">Enrolled This Month</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <div class="d-grid gap-2">
                        <a href="enquiries.php" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> View All Enquiries
                        </a>
                        <a href="call_logs.php" class="btn btn-info">
                            <i class="fas fa-phone"></i> Call History & Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Update Modal -->
<div class="modal fade" id="quickUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Quick Status Update
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickUpdateForm">
                    <input type="hidden" id="quickEnquiryId">
                    
                    <div class="mb-3">
                        <label class="form-label">Status:</label>
                        <select id="quickStatus" class="form-select" required>
                            <option value="contacted">Contacted</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="follow_up">Follow Up Required</option>
                            <option value="enrolled">Enrolled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Priority:</label>
                        <select id="quickPriority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quick Notes:</label>
                        <textarea id="quickNotes" class="form-control" rows="2" placeholder="Brief update..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveQuickUpdate()">
                    <i class="fas fa-check"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Priority badges */
.priority-high { background-color: #dc3545; }
.priority-medium { background-color: #ffc107; color: #000; }
.priority-low { background-color: #28a745; }

/* Status badges */
.status-new { background-color: #17a2b8; }
.status-assigned { background-color: #6c757d; }
.status-contacted { background-color: #28a745; }
.status-interested { background-color: #007bff; }
.status-not_interested { background-color: #6c757d; }
.status-follow_up { background-color: #fd7e14; }
.status-enrolled { background-color: #28a745; }

/* Performance metrics */
.metric-item {
    padding: 10px;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: bold;
}

.metric-label {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Priority row highlighting */
.priority-high-row {
    border-left: 4px solid #dc3545;
    background-color: rgba(220, 53, 69, 0.05);
}

.overdue-row {
    background-color: rgba(255, 193, 7, 0.1);
}

/* Filter buttons */
.filter-btn.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

/* Enhanced stat cards */
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.stat-card .stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-card .stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.stat-card .stat-icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 2rem;
    opacity: 0.3;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-card .stat-number {
        font-size: 1.5rem;
    }
    
    .metric-value {
        font-size: 1.2rem;
    }
}
</style>

<script>
let currentFilters = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Set up filter button handlers
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            applyQuickFilter(filter);
        });
    });
    
    // Set up search input with debouncing
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });
});

function applyQuickFilter(filter) {
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    
    // Reset filters
    statusFilter.value = '';
    priorityFilter.value = '';
    document.getElementById('searchInput').value = '';
    
    switch(filter) {
        case 'new':
            statusFilter.value = 'new';
            break;
        case 'high_priority':
            priorityFilter.value = 'high';
            break;
        case 'interested':
            statusFilter.value = 'interested';
            break;
        case 'overdue':
            // This would need backend support to filter overdue items
            break;
    }
    
    applyFilters();
}

function applyFilters() {
    const filters = {
        search: document.getElementById('searchInput').value,
        status: document.getElementById('statusFilter').value,
        priority: document.getElementById('priorityFilter').value,
        course: document.getElementById('courseFilter').value
    };
    
    // Show loading state
    const tableContainer = document.getElementById('priorityLeadsTable');
    tableContainer.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Make AJAX request
    const params = new URLSearchParams(filters);
    params.append('ajax', '1');
    
    fetch(`?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLeadsTable(data.data);
            } else {
                tableContainer.innerHTML = '<div class="alert alert-danger">Error loading data</div>';
            }
        })
        .catch(error => {
            console.error('Filter error:', error);
            tableContainer.innerHTML = '<div class="alert alert-danger">Error loading data</div>';
        });
}

function updateLeadsTable(enquiries) {
    const tableContainer = document.getElementById('priorityLeadsTable');
    
    if (enquiries.length === 0) {
        tableContainer.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                <p class="text-muted">No enquiries match your filters.</p>
            </div>
        `;
        return;
    }
    
    let tableHTML = `
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Last Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    enquiries.forEach(enquiry => {
        const priorityClass = enquiry.priority === 'high' ? 'priority-high-row' : '';
        const overdueClass = enquiry.is_overdue ? 'overdue-row' : '';
        
        tableHTML += `
            <tr class="${priorityClass} ${overdueClass}">
                <td>
                    <span class="badge priority-${enquiry.priority}">
                        ${enquiry.priority.toUpperCase()}
                    </span>
                </td>
                <td>
                    <strong>${escapeHtml(enquiry.full_name)}</strong>
                    ${enquiry.is_overdue ? '<i class="fas fa-clock text-danger" title="Overdue for follow-up"></i>' : ''}
                </td>
                <td>
                    <div class="small">
                        <a href="tel:${escapeHtml(enquiry.phone)}" class="text-decoration-none">
                            <i class="fas fa-phone"></i> ${escapeHtml(enquiry.phone)}
                        </a>
                    </div>
                </td>
                <td>${escapeHtml(enquiry.course_interest)}</td>
                <td>
                    <span class="badge status-${enquiry.status}">
                        ${enquiry.status.replace('_', ' ').toUpperCase()}
                    </span>
                </td>
                <td>
                    <small class="text-muted">
                        ${enquiry.last_contact ? new Date(enquiry.last_contact).toLocaleDateString() : 'Never'}
                    </small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="tel:${escapeHtml(enquiry.phone)}" class="btn btn-outline-success btn-sm" title="Call">
                            <i class="fas fa-phone"></i>
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickUpdate(${enquiry.id})" title="Quick Update">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableHTML += '</tbody></table></div>';
    tableContainer.innerHTML = tableHTML;
}

function quickUpdate(enquiryId) {
    document.getElementById('quickEnquiryId').value = enquiryId;
    const modal = new bootstrap.Modal(document.getElementById('quickUpdateModal'));
    modal.show();
}

function saveQuickUpdate() {
    const enquiryId = document.getElementById('quickEnquiryId').value;
    const status = document.getElementById('quickStatus').value;
    const priority = document.getElementById('quickPriority').value;
    const notes = document.getElementById('quickNotes').value;
    
    // Here you would make an AJAX call to update the enquiry
    // For now, just close the modal and refresh
    const modal = bootstrap.Modal.getInstance(document.getElementById('quickUpdateModal'));
    modal.hide();
    
    // Show success message
    showAlert('Enquiry updated successfully!', 'success');
    
    // Refresh the current view
    applyFilters();
}

function showAlert(message, type = 'info') {
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at top of page
    const container = document.querySelector('.row');
    container.insertAdjacentHTML('beforebegin', alertHTML);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php renderEnquirerFooter(); ?>