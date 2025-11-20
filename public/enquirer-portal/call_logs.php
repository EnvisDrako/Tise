<?php
/**
 * Enquirer - Call History Management with Filtering
 * Comprehensive call log filtering, reporting, and performance analysis
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include enquirer authorization
require_once __DIR__ . '/../../includes/auth_check_enquirer.php';

// Page title
$pageTitle = 'Call History & Reports';

// Get current user ID
$userId = getCurrentUserId();

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['ajax']) {
        case 'filter_calls':
            $filters = [
                'outcome' => $_GET['outcome'] ?? '',
                'duration_min' => $_GET['duration_min'] ?? '',
                'duration_max' => $_GET['duration_max'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'follow_up' => $_GET['follow_up'] ?? '',
                'search' => $_GET['search'] ?? ''
            ];
            
            $calls = getFilteredCallLogs($userId, $filters);
            echo json_encode(['success' => true, 'data' => $calls]);
            exit;
            
        case 'export_report':
            $format = $_GET['format'] ?? 'csv';
            $filters = [
                'outcome' => $_GET['outcome'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? ''
            ];
            
            exportCallReport($userId, $filters, $format);
            exit;
            
        case 'performance_data':
            $period = $_GET['period'] ?? 'week';
            $data = getPerformanceData($userId, $period);
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
    }
}

// Handle manual call logging
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'log_call') {
        handleManualCallLog();
    }
}

// Get call statistics
$callStats = getCallStatisticsDetailed($userId);

// Get recent calls for initial display
$recentCalls = getFilteredCallLogs($userId, [], 20);

// Get enquiries for call logging dropdown
$assignedEnquiries = getAssignedEnquiries($userId);

/**
 * Handle manual call logging
 */
function handleManualCallLog() {
    global $userId;
    
    $enquiryId = (int)$_POST['enquiry_id'];
    $outcome = $_POST['outcome'];
    $duration = (int)$_POST['duration'];
    $notes = trim($_POST['notes'] ?? '');
    $followUpRequired = isset($_POST['follow_up_required']) ? 1 : 0;
    
    try {
        // Verify enquiry ownership
        $enquiry = getEnquiryDetails($enquiryId, $userId);
        if (!$enquiry) {
            throw new Exception("Enquiry not found or not assigned to you.");
        }
        
        // Log the call
        $callId = logCall($enquiryId, $userId, $outcome, $duration, $notes, $followUpRequired);
        
        if ($callId) {
            $_SESSION['success_message'] = "Call logged successfully!";
            
            // Update enquiry status if needed
            if ($outcome === 'answered') {
                updateEnquiryStatus($enquiryId, 'contacted');
            }
        } else {
            $_SESSION['error_message'] = "Failed to log call.";
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error logging call: " . $e->getMessage();
        logEvent("Error logging call: " . $e->getMessage(), 'ERROR');
    }
}

// Render header
renderEnquirerHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-phone"></i> Call History & Performance Reports
        </h1>
        <p class="lead">Track your calling activity and analyze performance metrics</p>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Performance Overview -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $callStats['total_calls']; ?></div>
            <div class="stat-label">Total Calls</div>
            <i class="fas fa-phone stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $callStats['today_calls']; ?></div>
            <div class="stat-label">Today's Calls</div>
            <i class="fas fa-calendar-day stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $callStats['success_rate']; ?>%</div>
            <div class="stat-label">Success Rate</div>
            <i class="fas fa-chart-line stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $callStats['avg_duration']; ?>m</div>
            <div class="stat-label">Avg Duration</div>
            <i class="fas fa-clock stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $callStats['follow_ups']; ?></div>
            <div class="stat-label">Follow-ups Due</div>
            <i class="fas fa-calendar-check stat-icon"></i>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo $callStats['weekly_calls']; ?></div>
            <div class="stat-label">This Week</div>
            <i class="fas fa-calendar-week stat-icon"></i>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-filter"></i> Filters & Actions</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#logCallModal">
                        <i class="fas fa-plus"></i> Log Call
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportReport('csv')">CSV Report</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">Excel Report</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">PDF Summary</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Search:</label>
                        <input type="text" id="searchFilter" class="form-control" placeholder="Name, phone...">
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Outcome:</label>
                        <select id="outcomeFilter" class="form-select">
                            <option value="">All Outcomes</option>
                            <option value="answered">Answered</option>
                            <option value="no_answer">No Answer</option>
                            <option value="busy">Busy</option>
                            <option value="invalid">Invalid Number</option>
                            <option value="voicemail">Voicemail</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Duration (min):</label>
                        <div class="input-group">
                            <input type="number" id="durationMin" class="form-control" placeholder="Min" min="0">
                            <input type="number" id="durationMax" class="form-control" placeholder="Max" min="0">
                        </div>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Date From:</label>
                        <input type="date" id="dateFrom" class="form-control">
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Date To:</label>
                        <input type="date" id="dateTo" class="form-control">
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Follow-up:</label>
                        <select id="followUpFilter" class="form-select">
                            <option value="">All</option>
                            <option value="1">Required</option>
                            <option value="0">Not Required</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary filter-quick" data-filter="today">Today</button>
                            <button type="button" class="btn btn-outline-primary filter-quick" data-filter="yesterday">Yesterday</button>
                            <button type="button" class="btn btn-outline-primary filter-quick" data-filter="week">This Week</button>
                            <button type="button" class="btn btn-outline-primary filter-quick" data-filter="month">This Month</button>
                            <button type="button" class="btn btn-outline-success filter-quick" data-filter="answered">Answered Only</button>
                            <button type="button" class="btn btn-outline-warning filter-quick" data-filter="follow_up">Follow-ups</button>
                        </div>
                        
                        <div class="float-end">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call Logs Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Call History</h5>
            </div>
            <div class="card-body">
                <div id="callLogsContainer">
                    <?php if (!empty($recentCalls)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="callLogsTable">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Contact</th>
                                        <th>Duration</th>
                                        <th>Outcome</th>
                                        <th>Follow-up</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentCalls as $call): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo date('M j, Y', strtotime($call['call_timestamp'])); ?></div>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($call['call_timestamp'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($call['enquiry_name']); ?></div>
                                                <small class="text-muted">
                                                    <a href="tel:<?php echo htmlspecialchars($call['phone']); ?>" class="text-decoration-none">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($call['phone']); ?>
                                                    </a>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getDurationBadgeClass($call['call_duration']); ?>">
                                                    <?php echo formatDuration($call['call_duration']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge outcome-<?php echo $call['call_outcome']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $call['call_outcome'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($call['follow_up_required']): ?>
                                                    <i class="fas fa-calendar-check text-warning" title="Follow-up required"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-check text-muted" title="No follow-up needed"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($call['notes']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="showNotes('<?php echo htmlspecialchars($call['notes'], ENT_QUOTES); ?>')"
                                                            title="View Notes">
                                                        <i class="fas fa-sticky-note"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="tel:<?php echo htmlspecialchars($call['phone']); ?>" 
                                                       class="btn btn-outline-success" title="Call Again">
                                                        <i class="fas fa-phone"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="editCall(<?php echo $call['id']; ?>)" title="Edit">
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
                        <div class="text-center py-5">
                            <i class="fas fa-phone-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Call History</h5>
                            <p class="text-muted">Start making calls to see your history here.</p>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#logCallModal">
                                <i class="fas fa-plus"></i> Log Your First Call
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Charts -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-bar"></i> Call Outcomes Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="outcomeChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-line"></i> Daily Call Activity</h6>
            </div>
            <div class="card-body">
                <canvas id="activityChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Log Call Modal -->
<div class="modal fade" id="logCallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-phone"></i> Log Call Manually
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="log_call">
                    
                    <div class="mb-3">
                        <label for="enquiry_id" class="form-label">Enquiry:</label>
                        <select name="enquiry_id" id="enquiry_id" class="form-select" required>
                            <option value="">Select enquiry...</option>
                            <?php foreach ($assignedEnquiries as $enquiry): ?>
                                <option value="<?php echo $enquiry['id']; ?>">
                                    <?php echo htmlspecialchars($enquiry['full_name']); ?> - <?php echo htmlspecialchars($enquiry['phone']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="outcome" class="form-label">Call Outcome:</label>
                            <select name="outcome" id="outcome" class="form-select" required>
                                <option value="answered">Answered</option>
                                <option value="no_answer">No Answer</option>
                                <option value="busy">Busy</option>
                                <option value="invalid">Invalid Number</option>
                                <option value="voicemail">Voicemail</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">Duration (minutes):</label>
                            <input type="number" name="duration" id="duration" class="form-control" 
                                   min="0" max="120" value="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Call Notes:</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="What was discussed? Any important points?"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="follow_up_required" id="follow_up_required" class="form-check-input">
                        <label for="follow_up_required" class="form-check-label">
                            Follow-up required
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Log Call
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note"></i> Call Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notesContent">
                <!-- Notes content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Outcome badges */
.outcome-answered { background-color: #28a745; }
.outcome-no_answer { background-color: #ffc107; color: #000; }
.outcome-busy { background-color: #fd7e14; }
.outcome-invalid { background-color: #dc3545; }
.outcome-voicemail { background-color: #6f42c1; }

/* Duration badges */
.duration-short { background-color: #dc3545; }
.duration-medium { background-color: #ffc107; color: #000; }
.duration-long { background-color: #28a745; }

/* Stat cards */
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    position: relative;
}

.stat-card .stat-number {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-card .stat-label {
    font-size: 0.85rem;
    opacity: 0.9;
}

.stat-card .stat-icon {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 1.5rem;
    opacity: 0.3;
}

/* Filter buttons */
.filter-quick.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

/* Table enhancements */
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-top: none;
}

/* Loading state */
.loading {
    text-align: center;
    padding: 40px;
}

.loading i {
    font-size: 2rem;
    color: #6c757d;
}
</style>

<script>
let currentFilters = {};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    setupFilterHandlers();
    loadPerformanceCharts();
    
    // Set default date range to last 30 days
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('dateTo').value = today.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
});

function setupFilterHandlers() {
    // Quick filter buttons
    document.querySelectorAll('.filter-quick').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-quick').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            applyQuickFilter(this.dataset.filter);
        });
    });
    
    // Search with debouncing
    let searchTimeout;
    document.getElementById('searchFilter').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });
}

function applyQuickFilter(filter) {
    const today = new Date();
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    
    // Clear other filters
    document.getElementById('outcomeFilter').value = '';
    document.getElementById('followUpFilter').value = '';
    document.getElementById('searchFilter').value = '';
    
    switch(filter) {
        case 'today':
            dateFrom.value = today.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today.getTime() - (24 * 60 * 60 * 1000));
            dateFrom.value = yesterday.toISOString().split('T')[0];
            dateTo.value = yesterday.toISOString().split('T')[0];
            break;
        case 'week':
            const weekAgo = new Date(today.getTime() - (7 * 24 * 60 * 60 * 1000));
            dateFrom.value = weekAgo.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'month':
            const monthAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            dateFrom.value = monthAgo.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'answered':
            document.getElementById('outcomeFilter').value = 'answered';
            break;
        case 'follow_up':
            document.getElementById('followUpFilter').value = '1';
            break;
    }
    
    applyFilters();
}

function applyFilters() {
    const filters = {
        search: document.getElementById('searchFilter').value,
        outcome: document.getElementById('outcomeFilter').value,
        duration_min: document.getElementById('durationMin').value,
        duration_max: document.getElementById('durationMax').value,
        date_from: document.getElementById('dateFrom').value,
        date_to: document.getElementById('dateTo').value,
        follow_up: document.getElementById('followUpFilter').value
    };
    
    currentFilters = filters;
    
    // Show loading state
    const container = document.getElementById('callLogsContainer');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i><br>Loading call history...</div>';
    
    // Make AJAX request
    const params = new URLSearchParams(filters);
    params.append('ajax', 'filter_calls');
    
    fetch(`?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCallLogsTable(data.data);
            } else {
                container.innerHTML = '<div class="alert alert-danger">Error loading call history</div>';
            }
        })
        .catch(error => {
            console.error('Filter error:', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading call history</div>';
        });
}

function updateCallLogsTable(calls) {
    const container = document.getElementById('callLogsContainer');
    
    if (calls.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Calls Found</h5>
                <p class="text-muted">No calls match your current filters.</p>
            </div>
        `;
        return;
    }
    
    let tableHTML = `
        <div class="table-responsive">
            <table class="table table-hover" id="callLogsTable">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Contact</th>
                        <th>Duration</th>
                        <th>Outcome</th>
                        <th>Follow-up</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    calls.forEach(call => {
        const callDate = new Date(call.call_timestamp);
        const durationBadge = getDurationBadgeClass(call.call_duration);
        
        tableHTML += `
            <tr>
                <td>
                    <div class="fw-bold">${callDate.toLocaleDateString()}</div>
                    <small class="text-muted">${callDate.toLocaleTimeString()}</small>
                </td>
                <td>
                    <div class="fw-bold">${escapeHtml(call.enquiry_name)}</div>
                    <small class="text-muted">
                        <a href="tel:${escapeHtml(call.phone)}" class="text-decoration-none">
                            <i class="fas fa-phone"></i> ${escapeHtml(call.phone)}
                        </a>
                    </small>
                </td>
                <td>
                    <span class="badge ${durationBadge}">
                        ${formatDuration(call.call_duration)}
                    </span>
                </td>
                <td>
                    <span class="badge outcome-${call.call_outcome}">
                        ${call.call_outcome.replace('_', ' ').toUpperCase()}
                    </span>
                </td>
                <td>
                    ${call.follow_up_required ? 
                        '<i class="fas fa-calendar-check text-warning" title="Follow-up required"></i>' : 
                        '<i class="fas fa-check text-muted" title="No follow-up needed"></i>'
                    }
                </td>
                <td>
                    ${call.notes ? 
                        `<button type="button" class="btn btn-sm btn-outline-info" onclick="showNotes('${escapeHtml(call.notes)}')" title="View Notes"><i class="fas fa-sticky-note"></i></button>` : 
                        '<span class="text-muted">-</span>'
                    }
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="tel:${escapeHtml(call.phone)}" class="btn btn-outline-success" title="Call Again">
                            <i class="fas fa-phone"></i>
                        </a>
                        <button type="button" class="btn btn-outline-primary" onclick="editCall(${call.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableHTML += '</tbody></table></div>';
    container.innerHTML = tableHTML;
}

function clearFilters() {
    document.getElementById('searchFilter').value = '';
    document.getElementById('outcomeFilter').value = '';
    document.getElementById('durationMin').value = '';
    document.getElementById('durationMax').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('followUpFilter').value = '';
    
    document.querySelectorAll('.filter-quick').forEach(btn => btn.classList.remove('active'));
    
    applyFilters();
}

function exportReport(format) {
    const params = new URLSearchParams(currentFilters);
    params.append('ajax', 'export_report');
    params.append('format', format);
    
    window.open(`?${params.toString()}`, '_blank');
}

function showNotes(notes) {
    document.getElementById('notesContent').innerHTML = `<p>${escapeHtml(notes)}</p>`;
    const modal = new bootstrap.Modal(document.getElementById('notesModal'));
    modal.show();
}

function editCall(callId) {
    // Implementation for editing call logs
    alert('Edit functionality would be implemented here');
}

function getDurationBadgeClass(duration) {
    if (duration < 2) return 'duration-short';
    if (duration < 10) return 'duration-medium';
    return 'duration-long';
}

function formatDuration(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return minutes > 0 ? `${minutes}m ${remainingSeconds}s` : `${remainingSeconds}s`;
}

function loadPerformanceCharts() {
    // Load chart data and render charts
    // This would use Chart.js or similar library
    console.log('Performance charts would be loaded here');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php renderEnquirerFooter(); ?>