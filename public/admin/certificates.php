<?php
/**
 * Certificate Approval System
 * Admin interface for managing certificate requests with advanced filtering and bulk operations
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Include certificate functions
require_once __DIR__ . '/certificate_functions.php';

// Page title
$pageTitle = 'Certificate Management';

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['ajax']) {
        case 'filter':
            $filteredData = getFilteredCertificates($_GET);
            echo json_encode($filteredData);
            exit;
            
        case 'approve':
            $result = approveCertificate($_POST['id'], $_POST['comments'] ?? '');
            echo json_encode($result);
            exit;
            
        case 'reject':
            $result = rejectCertificate($_POST['id'], $_POST['reason'] ?? '');
            echo json_encode($result);
            exit;
            
        case 'bulk_approve':
            $result = bulkApproveCertificates($_POST['ids'], $_POST['comments'] ?? '');
            echo json_encode($result);
            exit;
            
        case 'bulk_reject':
            $result = bulkRejectCertificates($_POST['ids'], $_POST['reason'] ?? '');
            echo json_encode($result);
            exit;
            
        case 'download':
            downloadCertificate($_GET['id']);
            exit;
            
        case 'bulk_download':
            bulkDownloadCertificates($_GET['ids'], $_GET['institution_id'] ?? null);
            exit;
            
        case 'bulk_delete':
            $result = bulkDeleteCertificates($_POST['ids'], $_POST['confirm'] ?? false);
            echo json_encode($result);
            exit;
            
        case 'scheduled_cleanup':
            $result = performScheduledCleanup($_POST['retention_days'] ?? 365);
            echo json_encode($result);
            exit;
            
        case 'archive':
            $result = archiveCertificates($_POST['status'], $_POST['archive_date'], $_POST['create_backup'] ?? false);
            echo json_encode($result);
            exit;
            
        case 'details':
            $details = getCertificateDetails($_GET['id']);
            if ($details) {
                renderCertificateDetailsModal($details);
            } else {
                echo '<div class="alert alert-danger">Certificate not found</div>';
            }
            exit;
    }
}

// Handle export requests
if (isset($_GET['export']) && $_GET['export'] === 'true') {
    $format = $_GET['format'] ?? 'csv';
    exportCertificates($_GET, $format);
    exit;
}

// Get filter parameters
$filters = [
    'institution_id' => $_GET['institution_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'request_type' => $_GET['request_type'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'search' => $_GET['search'] ?? '',
    'sort_by' => $_GET['sort_by'] ?? 'requested_at',
    'sort_order' => $_GET['sort_order'] ?? 'DESC',
    'page' => (int)($_GET['page'] ?? 1),
    'limit' => (int)($_GET['limit'] ?? 25)
];

// Get filtered certificate data
$certificateData = getFilteredCertificates($filters);
$certificates = $certificateData['data'];
$totalRecords = $certificateData['total'];
$totalPages = ceil($totalRecords / $filters['limit']);

// Get institutions for filter dropdown
$institutions = getAllInstitutions();

// Get certificate statistics
$stats = getCertificateStatistics();

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-gradient mb-2">
                    <i class="fas fa-certificate"></i> Certificate Management
                </h1>
                <p class="lead mb-0">Manage and approve certificate requests from institutions</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success" onclick="bulkApprove()" id="bulkApproveBtn" disabled>
                    <i class="fas fa-check"></i> Bulk Approve
                </button>
                <button type="button" class="btn btn-danger" onclick="bulkReject()" id="bulkRejectBtn" disabled>
                    <i class="fas fa-times"></i> Bulk Reject
                </button>
                <button type="button" class="btn btn-info" onclick="bulkDownload()" id="bulkDownloadBtn" disabled>
                    <i class="fas fa-download"></i> Bulk Download
                </button>
                <button type="button" class="btn btn-warning" onclick="bulkDelete()" id="bulkDeleteBtn" disabled>
                    <i class="fas fa-trash"></i> Bulk Delete
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Admin Tools
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="showCleanupModal()">
                            <i class="fas fa-broom"></i> Scheduled Cleanup
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="showArchiveModal()">
                            <i class="fas fa-archive"></i> Archive Old Records
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportFiltered('excel')">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportFiltered('pdf')">
                            <i class="fas fa-file-pdf"></i> Export to PDF
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-warning text-white">
            <div class="stat-number"><?php echo number_format($stats['pending']); ?></div>
            <div class="stat-label">Pending Requests</div>
            <i class="fas fa-clock stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success text-white">
            <div class="stat-number"><?php echo number_format($stats['approved']); ?></div>
            <div class="stat-label">Approved Today</div>
            <i class="fas fa-check-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-danger text-white">
            <div class="stat-number"><?php echo number_format($stats['rejected']); ?></div>
            <div class="stat-label">Rejected Today</div>
            <i class="fas fa-times-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info text-white">
            <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-label">Total Requests</div>
            <i class="fas fa-certificate stat-icon"></i>
        </div>
    </div>
</div>

<!-- Advanced Filtering Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter"></i> Advanced Filters
            <button class="btn btn-sm btn-outline-secondary float-end" type="button" onclick="toggleFilters()">
                <i class="fas fa-chevron-down" id="filterToggleIcon"></i>
            </button>
        </h5>
    </div>
    <div class="card-body" id="filterSection">
        <form id="certificateFilters" method="GET">
            <div class="row g-3">
                <!-- Institution Filter -->
                <div class="col-md-3">
                    <label for="institutionFilter" class="form-label">Institution</label>
                    <select class="form-select" id="institutionFilter" name="institution_id">
                        <option value="">All Institutions</option>
                        <?php foreach ($institutions as $institution): ?>
                            <option value="<?php echo $institution['id']; ?>" 
                                    <?php echo $filters['institution_id'] == $institution['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($institution['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="col-md-2">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <!-- Request Type Filter -->
                <div class="col-md-2">
                    <label for="requestTypeFilter" class="form-label">Request Type</label>
                    <select class="form-select" id="requestTypeFilter" name="request_type">
                        <option value="">All Types</option>
                        <option value="completion" <?php echo $filters['request_type'] === 'completion' ? 'selected' : ''; ?>>Completion</option>
                        <option value="transcript" <?php echo $filters['request_type'] === 'transcript' ? 'selected' : ''; ?>>Transcript</option>
                        <option value="diploma" <?php echo $filters['request_type'] === 'diploma' ? 'selected' : ''; ?>>Diploma</option>
                        <option value="marksheet" <?php echo $filters['request_type'] === 'marksheet' ? 'selected' : ''; ?>>Marksheet</option>
                    </select>
                </div>
                
                <!-- Priority Filter -->
                <div class="col-md-2">
                    <label for="priorityFilter" class="form-label">Priority</label>
                    <select class="form-select" id="priorityFilter" name="priority">
                        <option value="">All Priorities</option>
                        <option value="high" <?php echo $filters['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $filters['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $filters['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                
                <!-- Search -->
                <div class="col-md-3">
                    <label for="searchFilter" class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchFilter" name="search" 
                           placeholder="Student name, ID, institution..." 
                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
                
                <!-- Date Range -->
                <div class="col-md-2">
                    <label for="dateFrom" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="dateFrom" name="date_from" 
                           value="<?php echo $filters['date_from']; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="dateTo" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="dateTo" name="date_to" 
                           value="<?php echo $filters['date_to']; ?>">
                </div>
                
                <!-- Sort Options -->
                <div class="col-md-2">
                    <label for="sortBy" class="form-label">Sort By</label>
                    <select class="form-select" id="sortBy" name="sort_by">
                        <option value="requested_at" <?php echo $filters['sort_by'] === 'requested_at' ? 'selected' : ''; ?>>Request Date</option>
                        <option value="student_name" <?php echo $filters['sort_by'] === 'student_name' ? 'selected' : ''; ?>>Student Name</option>
                        <option value="institution_name" <?php echo $filters['sort_by'] === 'institution_name' ? 'selected' : ''; ?>>Institution</option>
                        <option value="priority" <?php echo $filters['sort_by'] === 'priority' ? 'selected' : ''; ?>>Priority</option>
                        <option value="status" <?php echo $filters['sort_by'] === 'status' ? 'selected' : ''; ?>>Status</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="sortOrder" class="form-label">Order</label>
                    <select class="form-select" id="sortOrder" name="sort_order">
                        <option value="DESC" <?php echo $filters['sort_order'] === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        <option value="ASC" <?php echo $filters['sort_order'] === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                    </select>
                </div>
                
                <!-- Filter Actions -->
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportFiltered()">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Filter Buttons -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="btn-group" role="group" aria-label="Quick filters">
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="setQuickFilter('pending')">
                            Pending Requests
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="setQuickFilter('approved_today')">
                            Approved Today
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="setQuickFilter('rejected_today')">
                            Rejected Today
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="setQuickFilter('high_priority')">
                            High Priority
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuickFilter('this_week')">
                            This Week
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
<div class="row mb-3">
    <div class="col-md-6">
        <p class="mb-0">
            Showing <?php echo number_format(count($certificates)); ?> of <?php echo number_format($totalRecords); ?> certificate requests
            <?php if (!empty(array_filter($filters, function($v) { return $v !== '' && $v !== 1 && $v !== 25 && $v !== 'requested_at' && $v !== 'DESC'; }))): ?>
                <span class="text-muted">(filtered)</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAll()">
                <i class="fas fa-check-square"></i> Select All
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectNone()">
                <i class="fas fa-square"></i> Select None
            </button>
        </div>
    </div>
</div>

<!-- Certificate Requests Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="certificatesTable">
                <thead class="table-dark">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                        </th>
                        <th>Student Details</th>
                        <th>Institution</th>
                        <th>Request Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($certificates)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-certificate fa-3x mb-3"></i>
                                    <p>No certificate requests found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($certificates as $cert): ?>
                            <tr data-id="<?php echo $cert['id']; ?>">
                                <td>
                                    <input type="checkbox" class="certificate-checkbox" value="<?php echo $cert['id']; ?>" 
                                           onchange="updateBulkButtons()">
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($cert['student_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">ID: <?php echo htmlspecialchars($cert['student_id']); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($cert['course']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold"><?php echo htmlspecialchars($cert['institution_name']); ?></span>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($cert['institution_code']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($cert['request_type'])); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $priorityClass = [
                                        'high' => 'bg-danger',
                                        'medium' => 'bg-warning',
                                        'low' => 'bg-secondary'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $priorityClass[$cert['priority']] ?? 'bg-secondary'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($cert['priority'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'pending' => 'bg-warning',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $statusClass[$cert['status']] ?? 'bg-secondary'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($cert['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?php echo date('M j, Y', strtotime($cert['requested_at'])); ?></span>
                                    <br>
                                    <small class="text-muted"><?php echo date('g:i A', strtotime($cert['requested_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($cert['status'] === 'pending'): ?>
                                            <button type="button" class="btn btn-success btn-sm" 
                                                    onclick="approveCertificate(<?php echo $cert['id']; ?>)" 
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="rejectCertificate(<?php echo $cert['id']; ?>)" 
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($cert['status'] === 'approved' && !empty($cert['certificate_path'])): ?>
                                            <button type="button" class="btn btn-info btn-sm" 
                                                    onclick="downloadCertificate(<?php echo $cert['id']; ?>)" 
                                                    title="Download">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                onclick="viewCertificateDetails(<?php echo $cert['id']; ?>)" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav aria-label="Certificate pagination" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($filters['page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $filters['page'] - 1])); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $filters['page'] - 2);
            $endPage = min($totalPages, $filters['page'] + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <li class="page-item <?php echo $i === $filters['page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $i])); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <?php if ($filters['page'] < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $filters['page'] + 1])); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Certificate Details Modal -->
<div class="modal fade" id="certificateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Certificate Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="certificateDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Certificate Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approvalForm">
                    <input type="hidden" id="approvalCertId" name="cert_id">
                    <div class="mb-3">
                        <label for="approvalComments" class="form-label">Approval Comments (Optional)</label>
                        <textarea class="form-control" id="approvalComments" name="comments" rows="3" 
                                  placeholder="Add any comments about the approval..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmApproval()">
                    <i class="fas fa-check"></i> Approve Certificate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Certificate Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectionForm">
                    <input type="hidden" id="rejectionCertId" name="cert_id">
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" name="reason" rows="3" 
                                  placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRejection()">
                    <i class="fas fa-times"></i> Reject Certificate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scheduled Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scheduled Cleanup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cleanupForm">
                    <div class="mb-3">
                        <label for="retentionDays" class="form-label">Retention Period (Days)</label>
                        <select class="form-select" id="retentionDays" name="retention_days">
                            <option value="90">90 days (3 months)</option>
                            <option value="180">180 days (6 months)</option>
                            <option value="365" selected>365 days (1 year)</option>
                            <option value="730">730 days (2 years)</option>
                            <option value="1095">1095 days (3 years)</option>
                        </select>
                        <div class="form-text">
                            Processed certificates older than this period will be archived and removed from active records.
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmCleanup" required>
                            <label class="form-check-label" for="confirmCleanup">
                                I understand that this action will permanently archive old certificate records
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="performCleanup()">
                    <i class="fas fa-broom"></i> Start Cleanup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive Certificate Records</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="archiveForm">
                    <div class="mb-3">
                        <label for="archiveStatus" class="form-label">Archive Records with Status</label>
                        <select class="form-select" id="archiveStatus" name="status">
                            <option value="approved">Approved Certificates</option>
                            <option value="rejected">Rejected Certificates</option>
                            <option value="all">All Processed Certificates</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="archiveDate" class="form-label">Older Than Date</label>
                        <input type="date" class="form-control" id="archiveDate" name="archive_date" required>
                        <div class="form-text">
                            Records processed before this date will be archived.
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="createBackup">
                            <label class="form-check-label" for="createBackup">
                                Create backup before archiving
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" onclick="performArchive()">
                    <i class="fas fa-archive"></i> Archive Records
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
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

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
}

.certificate-checkbox {
    transform: scale(1.2);
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.btn-group .btn {
    border-radius: 4px !important;
    margin-right: 2px;
}

.badge {
    font-size: 0.75rem;
}

.pagination .page-link {
    border-radius: 6px;
    margin: 0 2px;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-header .btn-close {
    filter: invert(1);
}

#filterSection {
    display: block;
}

#filterSection.collapsed {
    display: none;
}
</style>

<script>
// Certificate management functionality
let selectedCertificates = [];

// Toggle filter section
function toggleFilters() {
    const filterSection = document.getElementById('filterSection');
    const toggleIcon = document.getElementById('filterToggleIcon');
    
    if (filterSection.classList.contains('collapsed')) {
        filterSection.classList.remove('collapsed');
        filterSection.style.display = 'block';
        toggleIcon.classList.remove('fa-chevron-down');
        toggleIcon.classList.add('fa-chevron-up');
    } else {
        filterSection.classList.add('collapsed');
        filterSection.style.display = 'none';
        toggleIcon.classList.remove('fa-chevron-up');
        toggleIcon.classList.add('fa-chevron-down');
    }
}

// Clear all filters
function clearFilters() {
    document.getElementById('certificateFilters').reset();
    window.location.href = 'certificates.php';
}

// Set quick filters
function setQuickFilter(type) {
    const form = document.getElementById('certificateFilters');
    
    // Clear existing filters
    form.reset();
    
    const today = new Date().toISOString().split('T')[0];
    
    switch (type) {
        case 'pending':
            document.getElementById('statusFilter').value = 'pending';
            break;
        case 'approved_today':
            document.getElementById('statusFilter').value = 'approved';
            document.getElementById('dateFrom').value = today;
            document.getElementById('dateTo').value = today;
            break;
        case 'rejected_today':
            document.getElementById('statusFilter').value = 'rejected';
            document.getElementById('dateFrom').value = today;
            document.getElementById('dateTo').value = today;
            break;
        case 'high_priority':
            document.getElementById('priorityFilter').value = 'high';
            break;
        case 'this_week':
            const weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            document.getElementById('dateFrom').value = weekAgo.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today;
            break;
    }
    
    form.submit();
}

// Selection management
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.certificate-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkButtons();
}

function selectAll() {
    document.getElementById('selectAllCheckbox').checked = true;
    toggleSelectAll();
}

function selectNone() {
    document.getElementById('selectAllCheckbox').checked = false;
    toggleSelectAll();
}

function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.certificate-checkbox:checked');
    const bulkButtons = ['bulkApproveBtn', 'bulkRejectBtn', 'bulkDownloadBtn', 'bulkDeleteBtn'];
    
    selectedCertificates = Array.from(checkedBoxes).map(cb => cb.value);
    
    bulkButtons.forEach(btnId => {
        document.getElementById(btnId).disabled = selectedCertificates.length === 0;
    });
}

// Individual certificate actions
function approveCertificate(certId) {
    document.getElementById('approvalCertId').value = certId;
    new bootstrap.Modal(document.getElementById('approvalModal')).show();
}

function rejectCertificate(certId) {
    document.getElementById('rejectionCertId').value = certId;
    new bootstrap.Modal(document.getElementById('rejectionModal')).show();
}

function confirmApproval() {
    const certId = document.getElementById('approvalCertId').value;
    const comments = document.getElementById('approvalComments').value;
    
    fetch('certificates.php?ajax=approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${certId}&comments=${encodeURIComponent(comments)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Certificate approved successfully!', 'success');
            location.reload();
        } else {
            showAlert('Error approving certificate: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error approving certificate', 'danger');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('approvalModal')).hide();
}

function confirmRejection() {
    const certId = document.getElementById('rejectionCertId').value;
    const reason = document.getElementById('rejectionReason').value;
    
    if (!reason.trim()) {
        showAlert('Please provide a rejection reason', 'warning');
        return;
    }
    
    fetch('certificates.php?ajax=reject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${certId}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Certificate rejected successfully!', 'success');
            location.reload();
        } else {
            showAlert('Error rejecting certificate: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error rejecting certificate', 'danger');
    });
    
    bootstrap.Modal.getInstance(document.getElementById('rejectionModal')).hide();
}

// Bulk operations
function bulkApprove() {
    if (selectedCertificates.length === 0) return;
    
    const comments = prompt('Enter approval comments (optional):');
    if (comments === null) return; // User cancelled
    
    fetch('certificates.php?ajax=bulk_approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ids=${selectedCertificates.join(',')}&comments=${encodeURIComponent(comments)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`${data.count} certificates approved successfully!`, 'success');
            location.reload();
        } else {
            showAlert('Error in bulk approval: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error in bulk approval', 'danger');
    });
}

function bulkReject() {
    if (selectedCertificates.length === 0) return;
    
    const reason = prompt('Enter rejection reason:');
    if (!reason || !reason.trim()) {
        showAlert('Rejection reason is required', 'warning');
        return;
    }
    
    fetch('certificates.php?ajax=bulk_reject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ids=${selectedCertificates.join(',')}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`${data.count} certificates rejected successfully!`, 'success');
            location.reload();
        } else {
            showAlert('Error in bulk rejection: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error in bulk rejection', 'danger');
    });
}

function bulkDownload() {
    if (selectedCertificates.length === 0) return;
    
    const institutionId = document.getElementById('institutionFilter').value;
    const url = `certificates.php?ajax=bulk_download&ids=${selectedCertificates.join(',')}&institution_id=${institutionId}`;
    
    window.open(url, '_blank');
}

// Individual download
function downloadCertificate(certId) {
    window.open(`certificates.php?ajax=download&id=${certId}`, '_blank');
}

// View certificate details
function viewCertificateDetails(certId) {
    fetch(`certificates.php?ajax=details&id=${certId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('certificateDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('certificateDetailsModal')).show();
        })
        .catch(error => {
            showAlert('Error loading certificate details', 'danger');
        });
}

// Export filtered results
function exportFiltered(format = 'csv') {
    const form = document.getElementById('certificateFilters');
    const formData = new FormData(form);
    formData.append('export', 'true');
    formData.append('format', format);
    
    const params = new URLSearchParams(formData);
    window.open(`certificates.php?${params.toString()}`, '_blank');
}

// Bulk delete certificates
function bulkDelete() {
    if (selectedCertificates.length === 0) return;
    
    if (!confirm(`Are you sure you want to delete ${selectedCertificates.length} certificate request(s)? This action cannot be undone.`)) {
        return;
    }
    
    fetch('certificates.php?ajax=bulk_delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ids=${selectedCertificates.join(',')}&confirm=true`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`${data.count} certificate requests deleted successfully!`, 'success');
            location.reload();
        } else {
            showAlert('Error in bulk deletion: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error in bulk deletion', 'danger');
    });
}

// Show cleanup modal
function showCleanupModal() {
    new bootstrap.Modal(document.getElementById('cleanupModal')).show();
}

// Show archive modal
function showArchiveModal() {
    // Set default date to 1 year ago
    const oneYearAgo = new Date();
    oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
    document.getElementById('archiveDate').value = oneYearAgo.toISOString().split('T')[0];
    
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}

// Perform scheduled cleanup
function performCleanup() {
    const retentionDays = document.getElementById('retentionDays').value;
    const confirmCheckbox = document.getElementById('confirmCleanup');
    
    if (!confirmCheckbox.checked) {
        showAlert('Please confirm that you understand the cleanup process', 'warning');
        return;
    }
    
    fetch('certificates.php?ajax=scheduled_cleanup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `retention_days=${retentionDays}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Cleanup completed! ${data.archived_count} records archived, ${data.deleted_count} records deleted.`, 'success');
            bootstrap.Modal.getInstance(document.getElementById('cleanupModal')).hide();
            location.reload();
        } else {
            showAlert('Error in cleanup process: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error in cleanup process', 'danger');
    });
}

// Perform archive operation
function performArchive() {
    const status = document.getElementById('archiveStatus').value;
    const archiveDate = document.getElementById('archiveDate').value;
    const createBackup = document.getElementById('createBackup').checked;
    
    if (!archiveDate) {
        showAlert('Please select an archive date', 'warning');
        return;
    }
    
    fetch('certificates.php?ajax=archive', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `status=${status}&archive_date=${archiveDate}&create_backup=${createBackup}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Archive completed! ${data.archived_count} records archived.`, 'success');
            bootstrap.Modal.getInstance(document.getElementById('archiveModal')).hide();
            location.reload();
        } else {
            showAlert('Error in archive process: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error in archive process', 'danger');
    });
}

// Utility function to show alerts
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Update bulk buttons on page load
    updateBulkButtons();
});
</script>

<?php
// Include the certificate management functions
require_once 'certificate_functions.php';

renderAdminFooter();
?>