<?php
/**
 * Admin Downloads Center
 * Centralized download and export center for all system data
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../includes/ExportManager.php';
require_once __DIR__ . '/../../includes/DataFilterManager.php';

// Page title
$pageTitle = 'Downloads & Export Center';

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['ajax']) {
        case 'export':
            handleExportRequest();
            break;
        case 'schedule':
            handleScheduleRequest();
            break;
        case 'status':
            handleStatusRequest();
            break;
        case 'download':
            handleDownloadRequest();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    exit;
}

// Get export statistics
$exportStats = getExportStatistics();
$scheduledExports = getScheduledExports();
$recentExports = getRecentExports();

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-download"></i> Downloads & Export Center
        </h1>
        <p class="lead">Centralized data export and download management for all system data</p>
    </div>
</div>

<!-- Export Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($exportStats['total_exports']); ?></div>
            <div class="stat-label">Total Exports</div>
            <i class="fas fa-file-export stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($exportStats['today_exports']); ?></div>
            <div class="stat-label">Today's Exports</div>
            <i class="fas fa-calendar-day stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($exportStats['scheduled_exports']); ?></div>
            <div class="stat-label">Scheduled Exports</div>
            <i class="fas fa-clock stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo formatBytes($exportStats['total_size']); ?></div>
            <div class="stat-label">Total Size</div>
            <i class="fas fa-hdd stat-icon"></i>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-download"></i> System Data Export</h5>
            </div>
            <div class="card-body">
                <form id="exportForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dataType" class="form-label">Data Type</label>
                                <select class="form-select" id="dataType" name="data_type" required>
                                    <option value="">Select data type...</option>
                                    <option value="users">Users</option>
                                    <option value="institutions">Institutions</option>
                                    <option value="students">Students</option>
                                    <option value="enquiries">Enquiries</option>
                                    <option value="certificates">Certificate Requests</option>
                                    <option value="call_logs">Call Logs</option>
                                    <option value="marklists">Marklists</option>
                                    <option value="all_data">Complete System Data</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="exportFormat" class="form-label">Export Format</label>
                                <select class="form-select" id="exportFormat" name="format" required>
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel (XLSX)</option>
                                    <option value="pdf">PDF Report</option>
                                    <option value="json">JSON</option>
                                    <option value="xml">XML</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date Range Filter -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateFrom" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="dateFrom" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateTo" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="dateTo" name="date_to">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Filters -->
                    <div id="additionalFilters" class="mb-3" style="display: none;">
                        <h6>Additional Filters</h6>
                        <div class="row" id="filterContainer">
                            <!-- Dynamic filters will be loaded here -->
                        </div>
                    </div>
                    
                    <!-- Export Options -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Export Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="includeHeaders" name="include_headers" checked>
                                    <label class="form-check-label" for="includeHeaders">
                                        Include column headers
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="includeMetadata" name="include_metadata" checked>
                                    <label class="form-check-label" for="includeMetadata">
                                        Include export metadata
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="compressFile" name="compress">
                                    <label class="form-check-label" for="compressFile">
                                        Compress file (ZIP)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailDelivery" name="email_delivery">
                                    <label class="form-check-label" for="emailDelivery">
                                        Email download link
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Options (shown when email delivery is checked) -->
                    <div id="emailOptions" class="mb-3" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="emailAddress" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="emailAddress" name="email_address" 
                                       value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="emailSubject" class="form-label">Email Subject</label>
                                <input type="text" class="form-control" id="emailSubject" name="email_subject" 
                                       value="System Data Export">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Now
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="showScheduleModal()">
                            <i class="fas fa-clock"></i> Schedule Export
                        </button>
                        <button type="button" class="btn btn-info" onclick="showCustomReportModal()">
                            <i class="fas fa-chart-bar"></i> Custom Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Export Guidelines</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb"></i> Tips</h6>
                    <ul class="mb-0">
                        <li>Large exports may take several minutes</li>
                        <li>Use date filters to reduce file size</li>
                        <li>Email delivery is recommended for large files</li>
                        <li>Compressed files save storage space</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Limits</h6>
                    <ul class="mb-0">
                        <li>Maximum 50,000 records per export</li>
                        <li>Files expire after 24 hours</li>
                        <li>Sensitive data is automatically masked</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Exports -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-history"></i> Recent Exports</h5>
                <button class="btn btn-sm btn-outline-danger" onclick="cleanupOldExports()">
                    <i class="fas fa-trash"></i> Cleanup Old Files
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($recentExports)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-export fa-3x mb-3"></i>
                        <p>No recent exports found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data Type</th>
                                    <th>Format</th>
                                    <th>Records</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentExports as $export): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-<?php echo getDataTypeIcon($export['data_type']); ?>"></i>
                                            <?php echo htmlspecialchars(ucfirst($export['data_type'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo strtoupper($export['format']); ?></span>
                                        </td>
                                        <td><?php echo number_format($export['record_count']); ?></td>
                                        <td><?php echo formatBytes($export['file_size']); ?></td>
                                        <td>
                                            <small><?php echo date('M j, g:i A', strtotime($export['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($export['status'] === 'completed'): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php elseif ($export['status'] === 'processing'): ?>
                                                <span class="badge bg-warning">Processing</span>
                                            <?php elseif ($export['status'] === 'failed'): ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo ucfirst($export['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($export['status'] === 'completed' && file_exists($export['file_path'])): ?>
                                                <a href="?ajax=download&id=<?php echo $export['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteExport(<?php echo $export['id']; ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Scheduled Exports</h5>
            </div>
            <div class="card-body">
                <?php if (empty($scheduledExports)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                        <p>No scheduled exports</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($scheduledExports as $scheduled): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars(ucfirst($scheduled['data_type'])); ?></h6>
                                    <small class="text-muted">
                                        <?php echo strtoupper($scheduled['format']); ?> • 
                                        <?php echo $scheduled['frequency']; ?>
                                    </small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editSchedule(<?php echo $scheduled['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteSchedule(<?php echo $scheduled['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    Next run: <?php echo date('M j, g:i A', strtotime($scheduled['next_run'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Export Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Export</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scheduleDataType" class="form-label">Data Type</label>
                                <select class="form-select" id="scheduleDataType" name="data_type" required>
                                    <option value="users">Users</option>
                                    <option value="institutions">Institutions</option>
                                    <option value="students">Students</option>
                                    <option value="enquiries">Enquiries</option>
                                    <option value="certificates">Certificate Requests</option>
                                    <option value="call_logs">Call Logs</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scheduleFormat" class="form-label">Format</label>
                                <select class="form-select" id="scheduleFormat" name="format" required>
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="frequency" class="form-label">Frequency</label>
                                <select class="form-select" id="frequency" name="frequency" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scheduleTime" class="form-label">Time</label>
                                <input type="time" class="form-control" id="scheduleTime" name="time" value="02:00" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="scheduleEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="scheduleEmail" name="email" 
                               value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="scheduleCompress" name="compress" checked>
                        <label class="form-check-label" for="scheduleCompress">
                            Compress files
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSchedule()">Schedule Export</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Report Modal -->
<div class="modal fade" id="customReportModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Custom Report Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customReportForm">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Data Sources</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeUsers" name="sources[]" value="users">
                                <label class="form-check-label" for="includeUsers">Users</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeInstitutions" name="sources[]" value="institutions">
                                <label class="form-check-label" for="includeInstitutions">Institutions</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeStudents" name="sources[]" value="students">
                                <label class="form-check-label" for="includeStudents">Students</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeEnquiries" name="sources[]" value="enquiries">
                                <label class="form-check-label" for="includeEnquiries">Enquiries</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Report Type</h6>
                            <div class="form-check">
                                <input class="form-check-radio" type="radio" name="report_type" id="summaryReport" value="summary" checked>
                                <label class="form-check-label" for="summaryReport">Summary Report</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-radio" type="radio" name="report_type" id="detailedReport" value="detailed">
                                <label class="form-check-label" for="detailedReport">Detailed Report</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-radio" type="radio" name="report_type" id="analyticsReport" value="analytics">
                                <label class="form-check-label" for="analyticsReport">Analytics Report</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Output Options</h6>
                            <div class="mb-3">
                                <label for="reportFormat" class="form-label">Format</label>
                                <select class="form-select" id="reportFormat" name="format">
                                    <option value="pdf">PDF Report</option>
                                    <option value="excel">Excel Workbook</option>
                                    <option value="csv">CSV Data</option>
                                </select>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeCharts" name="include_charts" checked>
                                <label class="form-check-label" for="includeCharts">Include Charts</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="generateCustomReport()">Generate Report</button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.stat-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2.5rem;
    opacity: 0.3;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.modal-xl {
    max-width: 1200px;
}

.form-check {
    margin-bottom: 0.5rem;
}

.alert ul {
    padding-left: 1.2rem;
}

.border-bottom:last-child {
    border-bottom: none !important;
}
</style>

<script>
// Export form handling
document.getElementById('exportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    
    // Add AJAX parameter
    formData.append('ajax', 'export');
    
    fetch('?ajax=export', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Export started successfully! You will be notified when it\'s ready.');
            if (data.download_url) {
                setTimeout(() => {
                    window.location.href = data.download_url;
                }, 2000);
            }
            // Refresh the recent exports list
            setTimeout(() => location.reload(), 3000);
        } else {
            showAlert('danger', 'Export failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Export error:', error);
        showAlert('danger', 'An error occurred during export');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-download"></i> Export Now';
    });
});

// Data type change handler
document.getElementById('dataType').addEventListener('change', function() {
    const dataType = this.value;
    const filtersContainer = document.getElementById('additionalFilters');
    const filterContainer = document.getElementById('filterContainer');
    
    if (dataType && dataType !== 'all_data') {
        // Load specific filters for the data type
        loadDataTypeFilters(dataType);
        filtersContainer.style.display = 'block';
    } else {
        filtersContainer.style.display = 'none';
    }
});

// Email delivery checkbox handler
document.getElementById('emailDelivery').addEventListener('change', function() {
    const emailOptions = document.getElementById('emailOptions');
    emailOptions.style.display = this.checked ? 'block' : 'none';
});

// Load data type specific filters
function loadDataTypeFilters(dataType) {
    const filterContainer = document.getElementById('filterContainer');
    let filtersHtml = '';
    
    switch (dataType) {
        case 'users':
            filtersHtml = `
                <div class="col-md-4">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="filters[role]">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="faculty">Faculty</option>
                        <option value="enquirer">Enquirer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="filters[is_active]">
                        <option value="">All Users</option>
                        <option value="1">Active Only</option>
                        <option value="0">Inactive Only</option>
                    </select>
                </div>
            `;
            break;
        case 'students':
            filtersHtml = `
                <div class="col-md-4">
                    <label class="form-label">Institution</label>
                    <select class="form-select" name="filters[institution_id]">
                        <option value="">All Institutions</option>
                        <?php foreach (getInstitutions() as $inst): ?>
                        <option value="<?php echo $inst['id']; ?>"><?php echo htmlspecialchars($inst['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Exam Eligibility</label>
                    <select class="form-select" name="filters[is_exam_eligible]">
                        <option value="">All Students</option>
                        <option value="1">Eligible</option>
                        <option value="0">Not Eligible</option>
                    </select>
                </div>
            `;
            break;
        case 'enquiries':
            filtersHtml = `
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="filters[status]">
                        <option value="">All Status</option>
                        <option value="new">New</option>
                        <option value="assigned">Assigned</option>
                        <option value="contacted">Contacted</option>
                        <option value="interested">Interested</option>
                        <option value="not_interested">Not Interested</option>
                        <option value="enrolled">Enrolled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assignment</label>
                    <select class="form-select" name="filters[assigned]">
                        <option value="">All Enquiries</option>
                        <option value="assigned">Assigned</option>
                        <option value="unassigned">Unassigned</option>
                    </select>
                </div>
            `;
            break;
    }
    
    filterContainer.innerHTML = filtersHtml;
}

// Schedule export modal
function showScheduleModal() {
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
}

// Save scheduled export
function saveSchedule() {
    const form = document.getElementById('scheduleForm');
    const formData = new FormData(form);
    formData.append('ajax', 'schedule');
    
    fetch('?ajax=schedule', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Export scheduled successfully!');
            bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('danger', 'Failed to schedule export: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Schedule error:', error);
        showAlert('danger', 'An error occurred while scheduling');
    });
}

// Custom report modal
function showCustomReportModal() {
    const modal = new bootstrap.Modal(document.getElementById('customReportModal'));
    modal.show();
}

// Generate custom report
function generateCustomReport() {
    const form = document.getElementById('customReportForm');
    const formData = new FormData(form);
    formData.append('ajax', 'export');
    formData.append('data_type', 'custom_report');
    
    fetch('?ajax=export', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Custom report generation started!');
            bootstrap.Modal.getInstance(document.getElementById('customReportModal')).hide();
            setTimeout(() => location.reload(), 3000);
        } else {
            showAlert('danger', 'Failed to generate report: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Report error:', error);
        showAlert('danger', 'An error occurred while generating report');
    });
}

// Delete export
function deleteExport(exportId) {
    if (confirm('Are you sure you want to delete this export?')) {
        fetch(`?ajax=delete&id=${exportId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Export deleted successfully');
                location.reload();
            } else {
                showAlert('danger', 'Failed to delete export');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showAlert('danger', 'An error occurred while deleting');
        });
    }
}

// Cleanup old exports
function cleanupOldExports() {
    if (confirm('This will delete all exports older than 24 hours. Continue?')) {
        fetch('?ajax=cleanup', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', `Cleaned up ${data.deleted_count} old exports`);
                location.reload();
            } else {
                showAlert('danger', 'Cleanup failed');
            }
        })
        .catch(error => {
            console.error('Cleanup error:', error);
            showAlert('danger', 'An error occurred during cleanup');
        });
    }
}

// Utility function to show alerts
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at the top of the page
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
}

// Auto-refresh export status every 30 seconds
setInterval(() => {
    // Check for any processing exports and update their status
    const processingRows = document.querySelectorAll('tr:has(.badge:contains("Processing"))');
    if (processingRows.length > 0) {
        // Refresh the page to update status
        location.reload();
    }
}, 30000);
</script>

<?php
/**
 * Handle export request
 */
function handleExportRequest() {
    try {
        $dataType = $_POST['data_type'] ?? '';
        $format = $_POST['format'] ?? 'csv';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';
        $filters = $_POST['filters'] ?? [];
        
        $options = [
            'include_headers' => isset($_POST['include_headers']),
            'include_metadata' => isset($_POST['include_metadata']),
            'compress' => isset($_POST['compress']),
            'email_delivery' => isset($_POST['email_delivery']),
            'email_address' => $_POST['email_address'] ?? '',
            'email_subject' => $_POST['email_subject'] ?? 'System Data Export'
        ];
        
        // Validate required fields
        if (empty($dataType)) {
            throw new Exception('Data type is required');
        }
        
        // Start export process
        $exportId = startExportProcess($dataType, $format, $dateFrom, $dateTo, $filters, $options);
        
        echo json_encode([
            'success' => true,
            'message' => 'Export started successfully',
            'export_id' => $exportId
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Handle schedule request
 */
function handleScheduleRequest() {
    try {
        $dataType = $_POST['data_type'] ?? '';
        $format = $_POST['format'] ?? 'csv';
        $frequency = $_POST['frequency'] ?? 'daily';
        $time = $_POST['time'] ?? '02:00';
        $email = $_POST['email'] ?? '';
        $compress = isset($_POST['compress']);
        
        // Validate required fields
        if (empty($dataType) || empty($email)) {
            throw new Exception('Data type and email are required');
        }
        
        // Create scheduled export
        $scheduleId = createScheduledExport($dataType, $format, $frequency, $time, $email, $compress);
        
        echo json_encode([
            'success' => true,
            'message' => 'Export scheduled successfully',
            'schedule_id' => $scheduleId
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Handle download request
 */
function handleDownloadRequest() {
    try {
        $exportId = $_GET['id'] ?? '';
        
        if (empty($exportId)) {
            throw new Exception('Export ID is required');
        }
        
        $export = getExportById($exportId);
        
        if (!$export || !file_exists($export['file_path'])) {
            throw new Exception('Export file not found');
        }
        
        // Set headers for download
        header('Content-Type: ' . $export['mimetype']);
        header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
        header('Content-Length: ' . filesize($export['file_path']));
        
        // Output file
        readfile($export['file_path']);
        exit;
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

renderAdminFooter();
?>