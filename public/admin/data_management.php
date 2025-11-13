<?php
/**
 * Admin Data Management
 * Scheduled deletion and retention management system
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Page title
$pageTitle = 'Data Management & Retention';

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['ajax']) {
        case 'update_policy':
            handleUpdatePolicy();
            break;
        case 'schedule_deletion':
            handleScheduleDeletion();
            break;
        case 'archive_data':
            handleArchiveData();
            break;
        case 'rollback':
            handleRollback();
            break;
        case 'get_records':
            handleGetRecords();
            break;
        case 'confirm_deletion':
            handleConfirmDeletion();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    exit;
}

// Get retention policies and statistics
$retentionPolicies = getRetentionPolicies();
$retentionStatus = getRetentionStatus();
$recentDeletions = getRecentDeletions();
$archivedData = getArchivedDataSummary();

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-database"></i> Data Management & Retention
        </h1>
        <p class="lead">Configure retention policies and manage scheduled data deletion and archival</p>
    </div>
</div>

<!-- Data Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-info">
            <div class="stat-number"><?php echo number_format($retentionStatus['total_records']); ?></div>
            <div class="stat-label">Total Records</div>
            <i class="fas fa-database stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning">
            <div class="stat-number"><?php echo number_format($retentionStatus['records_to_review']); ?></div>
            <div class="stat-label">Need Review</div>
            <i class="fas fa-exclamation-triangle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success">
            <div class="stat-number"><?php echo number_format($archivedData['total_archived']); ?></div>
            <div class="stat-label">Archived Records</div>
            <i class="fas fa-archive stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-danger">
            <div class="stat-number"><?php echo formatBytes($archivedData['total_size']); ?></div>
            <div class="stat-label">Archive Size</div>
            <i class="fas fa-hdd stat-icon"></i>
        </div>
    </div>
</div>

<!-- Retention Policies Configuration -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-cog"></i> Retention Policies Configuration</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data Type</th>
                                <th>Retention Period</th>
                                <th>Archive Before Delete</th>
                                <th>Auto Delete</th>
                                <th>Records to Process</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($retentionPolicies as $policy): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-<?php echo getDataTypeIcon($policy['data_type']); ?>"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $policy['data_type'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo $policy['retention_days']; ?> days
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($policy['archive_before_delete']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($policy['auto_delete']): ?>
                                            <span class="badge bg-warning">Enabled</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $count = getRecordsToProcessCount($policy['data_type'], $policy['retention_days']);
                                        if ($count > 0): ?>
                                            <span class="badge bg-warning"><?php echo number_format($count); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="editPolicy('<?php echo $policy['data_type']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($count > 0): ?>
                                                <button class="btn btn-outline-warning" 
                                                        onclick="reviewRecords('<?php echo $policy['data_type']; ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="scheduleCleanup('<?php echo $policy['data_type']; ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Retention Guidelines</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb"></i> Best Practices</h6>
                    <ul class="mb-0">
                        <li>Always archive before deletion</li>
                        <li>Test retention policies on small datasets first</li>
                        <li>Review audit trails regularly</li>
                        <li>Keep backups before bulk operations</li>
                        <li>Set reasonable retention periods</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Warning</h6>
                    <p class="mb-0">Deletion operations are permanent. Always ensure data is properly archived before deletion.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Deletions and Archive Activity -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Recent Deletion Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Data Type</th>
                                <th>Records</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDeletions as $deletion): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($deletion['created_at'])); ?></td>
                                    <td>
                                        <i class="fas fa-<?php echo getDataTypeIcon($deletion['data_type']); ?>"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $deletion['data_type'])); ?>
                                    </td>
                                    <td><?php echo number_format($deletion['records_count']); ?></td>
                                    <td>
                                        <?php if ($deletion['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($deletion['status'] === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($deletion['status'] === 'archived'): ?>
                                            <span class="badge bg-info">Archived</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($deletion['can_rollback']): ?>
                                            <button class="btn btn-sm btn-outline-warning" 
                                                    onclick="rollbackDeletion(<?php echo $deletion['id']; ?>)">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewAuditTrail(<?php echo $deletion['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-archive"></i> Archive Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($archivedData['by_type'] as $type => $data): ?>
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <i class="fas fa-<?php echo getDataTypeIcon($type); ?> fa-2x text-muted mb-2"></i>
                                <h6><?php echo ucfirst(str_replace('_', ' ', $type)); ?></h6>
                                <p class="mb-0">
                                    <strong><?php echo number_format($data['count']); ?></strong> records<br>
                                    <small class="text-muted"><?php echo formatBytes($data['size']); ?></small>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm" onclick="manageArchives()">
                        <i class="fas fa-cog"></i> Manage Archives
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="downloadArchive()">
                        <i class="fas fa-download"></i> Download Archive
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Edit Policy Modal -->
<div class="modal fade" id="editPolicyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Retention Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="policyForm">
                    <input type="hidden" id="policyDataType" name="data_type">
                    
                    <div class="mb-3">
                        <label class="form-label">Retention Period (Days)</label>
                        <input type="number" class="form-control" id="retentionDays" name="retention_days" min="1" max="3650">
                        <div class="form-text">Number of days to keep records before deletion</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="archiveBeforeDelete" name="archive_before_delete">
                            <label class="form-check-label" for="archiveBeforeDelete">
                                Archive data before deletion
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="autoDelete" name="auto_delete">
                            <label class="form-check-label" for="autoDelete">
                                Enable automatic deletion
                            </label>
                        </div>
                        <div class="form-text">If disabled, deletions require manual approval</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePolicy()">Save Policy</button>
            </div>
        </div>
    </div>
</div>

<!-- Review Records Modal -->
<div class="modal fade" id="reviewRecordsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Records for Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="recordsToReview">
                    <!-- Records will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="archiveSelected()">Archive Selected</button>
                <button type="button" class="btn btn-danger" onclick="deleteSelected()">Delete Selected</button>
            </div>
        </div>
    </div>
</div>

<!-- Audit Trail Modal -->
<div class="modal fade" id="auditTrailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Trail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="auditTrailContent">
                    <!-- Audit trail will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Data Management JavaScript Functions
let currentDataType = '';

function editPolicy(dataType) {
    currentDataType = dataType;
    
    // Load current policy data
    fetch(`?ajax=get_policy&data_type=${dataType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('policyDataType').value = dataType;
                document.getElementById('retentionDays').value = data.policy.retention_days;
                document.getElementById('archiveBeforeDelete').checked = data.policy.archive_before_delete;
                document.getElementById('autoDelete').checked = data.policy.auto_delete;
                
                new bootstrap.Modal(document.getElementById('editPolicyModal')).show();
            }
        });
}

function savePolicy() {
    const formData = new FormData(document.getElementById('policyForm'));
    
    fetch('?ajax=update_policy', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Policy updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editPolicyModal')).hide();
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    });
}

function reviewRecords(dataType) {
    currentDataType = dataType;
    
    fetch(`?ajax=get_records&data_type=${dataType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('recordsToReview').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('reviewRecordsModal')).show();
            }
        });
}

function scheduleCleanup(dataType) {
    if (confirm(`Are you sure you want to schedule cleanup for ${dataType.replace('_', ' ')}? This action cannot be undone.`)) {
        fetch(`?ajax=schedule_deletion&data_type=${dataType}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Cleanup scheduled successfully', 'success');
                location.reload();
            } else {
                showAlert(data.message, 'danger');
            }
        });
    }
}

function archiveSelected() {
    const selected = getSelectedRecords();
    if (selected.length === 0) {
        showAlert('Please select records to archive', 'warning');
        return;
    }
    
    fetch('?ajax=archive_data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            data_type: currentDataType,
            record_ids: selected
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Records archived successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('reviewRecordsModal')).hide();
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    });
}

function deleteSelected() {
    const selected = getSelectedRecords();
    if (selected.length === 0) {
        showAlert('Please select records to delete', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selected.length} records? This action cannot be undone.`)) {
        fetch('?ajax=confirm_deletion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                data_type: currentDataType,
                record_ids: selected
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Records deleted successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('reviewRecordsModal')).hide();
                location.reload();
            } else {
                showAlert(data.message, 'danger');
            }
        });
    }
}

function getSelectedRecords() {
    const checkboxes = document.querySelectorAll('input[name="record_ids[]"]:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function rollbackDeletion(deletionId) {
    if (confirm('Are you sure you want to rollback this deletion? This will restore the deleted records.')) {
        fetch(`?ajax=rollback&deletion_id=${deletionId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Deletion rolled back successfully', 'success');
                location.reload();
            } else {
                showAlert(data.message, 'danger');
            }
        });
    }
}

function viewAuditTrail(deletionId) {
    fetch(`?ajax=get_audit_trail&deletion_id=${deletionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('auditTrailContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('auditTrailModal')).show();
            }
        });
}

function manageArchives() {
    window.location.href = 'bulk_operations.php?tab=archives';
}

function downloadArchive() {
    window.location.href = '?ajax=download_archive';
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?php
// Backend Functions for Data Management

function getRetentionPolicies() {
    global $conn;
    
    // Default retention policies
    $defaultPolicies = [
        'users' => ['retention_days' => 2555, 'archive_before_delete' => true, 'auto_delete' => false], // 7 years
        'enquiries' => ['retention_days' => 1095, 'archive_before_delete' => true, 'auto_delete' => false], // 3 years
        'students' => ['retention_days' => 3650, 'archive_before_delete' => true, 'auto_delete' => false], // 10 years
        'certificate_requests' => ['retention_days' => 1825, 'archive_before_delete' => true, 'auto_delete' => false], // 5 years
        'call_logs' => ['retention_days' => 365, 'archive_before_delete' => true, 'auto_delete' => true], // 1 year
        'marklists' => ['retention_days' => 3650, 'archive_before_delete' => true, 'auto_delete' => false] // 10 years
    ];
    
    // Check if retention_policies table exists, create if not
    $checkTable = "SHOW TABLES LIKE 'retention_policies'";
    $result = mysqli_query($conn, $checkTable);
    
    if (mysqli_num_rows($result) == 0) {
        // Create retention_policies table
        $createTable = "
            CREATE TABLE retention_policies (
                id INT PRIMARY KEY AUTO_INCREMENT,
                data_type VARCHAR(50) UNIQUE NOT NULL,
                retention_days INT NOT NULL DEFAULT 365,
                archive_before_delete BOOLEAN DEFAULT TRUE,
                auto_delete BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        mysqli_query($conn, $createTable);
        
        // Insert default policies
        foreach ($defaultPolicies as $dataType => $policy) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO retention_policies (data_type, retention_days, archive_before_delete, auto_delete) 
                VALUES (?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, 'siii', 
                $dataType, 
                $policy['retention_days'], 
                $policy['archive_before_delete'], 
                $policy['auto_delete']
            );
            mysqli_stmt_execute($stmt);
        }
    }
    
    // Get current policies
    $query = "SELECT * FROM retention_policies ORDER BY data_type";
    $result = mysqli_query($conn, $query);
    
    $policies = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $policies[] = $row;
    }
    
    return $policies;
}

function getRetentionStatus() {
    global $conn;
    
    $status = [
        'total_records' => 0,
        'records_to_review' => 0
    ];
    
    // Count total records across all tables
    $tables = ['users', 'enquiries', 'students', 'certificate_requests', 'call_logs', 'marklists'];
    
    foreach ($tables as $table) {
        $query = "SELECT COUNT(*) as count FROM $table";
        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $status['total_records'] += $row['count'];
        }
    }
    
    // Count records that need review (based on retention policies)
    $policies = getRetentionPolicies();
    foreach ($policies as $policy) {
        $count = getRecordsToProcessCount($policy['data_type'], $policy['retention_days']);
        $status['records_to_review'] += $count;
    }
    
    return $status;
}

function getRecordsToProcessCount($dataType, $retentionDays) {
    global $conn;
    
    $cutoffDate = date('Y-m-d', strtotime("-$retentionDays days"));
    
    switch ($dataType) {
        case 'users':
            $query = "SELECT COUNT(*) as count FROM users WHERE created_at < ? AND is_active = 0";
            break;
        case 'enquiries':
            $query = "SELECT COUNT(*) as count FROM enquiries WHERE created_at < ? AND status IN ('not_interested', 'enrolled')";
            break;
        case 'students':
            $query = "SELECT COUNT(*) as count FROM students WHERE created_at < ? AND batch_year < YEAR(CURDATE()) - 5";
            break;
        case 'certificate_requests':
            $query = "SELECT COUNT(*) as count FROM certificate_requests WHERE requested_at < ? AND status = 'approved'";
            break;
        case 'call_logs':
            $query = "SELECT COUNT(*) as count FROM call_logs WHERE call_timestamp < ?";
            break;
        case 'marklists':
            $query = "SELECT COUNT(*) as count FROM marklists WHERE created_at < ? AND exam_date < ?";
            break;
        default:
            return 0;
    }
    
    $stmt = mysqli_prepare($conn, $query);
    if ($dataType === 'marklists') {
        mysqli_stmt_bind_param($stmt, 'ss', $cutoffDate, $cutoffDate);
    } else {
        mysqli_stmt_bind_param($stmt, 's', $cutoffDate);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

function getRecentDeletions() {
    global $conn;
    
    // Check if deletion_log table exists, create if not
    $checkTable = "SHOW TABLES LIKE 'deletion_log'";
    $result = mysqli_query($conn, $checkTable);
    
    if (mysqli_num_rows($result) == 0) {
        $createTable = "
            CREATE TABLE deletion_log (
                id INT PRIMARY KEY AUTO_INCREMENT,
                data_type VARCHAR(50) NOT NULL,
                records_count INT NOT NULL,
                status ENUM('pending', 'completed', 'failed', 'archived') DEFAULT 'pending',
                can_rollback BOOLEAN DEFAULT TRUE,
                archive_path VARCHAR(255),
                deleted_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                FOREIGN KEY (deleted_by) REFERENCES users(id)
            )
        ";
        mysqli_query($conn, $createTable);
    }
    
    $query = "
        SELECT dl.*, u.full_name as deleted_by_name 
        FROM deletion_log dl 
        LEFT JOIN users u ON dl.deleted_by = u.id 
        ORDER BY dl.created_at DESC 
        LIMIT 10
    ";
    
    $result = mysqli_query($conn, $query);
    $deletions = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $deletions[] = $row;
    }
    
    return $deletions;
}

function getArchivedDataSummary() {
    global $conn;
    
    // Check if archived_data table exists, create if not
    $checkTable = "SHOW TABLES LIKE 'archived_data'";
    $result = mysqli_query($conn, $checkTable);
    
    if (mysqli_num_rows($result) == 0) {
        $createTable = "
            CREATE TABLE archived_data (
                id INT PRIMARY KEY AUTO_INCREMENT,
                data_type VARCHAR(50) NOT NULL,
                records_count INT NOT NULL,
                file_size BIGINT NOT NULL,
                archive_path VARCHAR(255) NOT NULL,
                compression_type VARCHAR(20) DEFAULT 'zip',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_data_type (data_type),
                INDEX idx_created_at (created_at)
            )
        ";
        mysqli_query($conn, $createTable);
    }
    
    $query = "
        SELECT 
            data_type,
            SUM(records_count) as count,
            SUM(file_size) as size
        FROM archived_data 
        GROUP BY data_type
    ";
    
    $result = mysqli_query($conn, $query);
    $summary = [
        'total_archived' => 0,
        'total_size' => 0,
        'by_type' => []
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $summary['total_archived'] += $row['count'];
        $summary['total_size'] += $row['size'];
        $summary['by_type'][$row['data_type']] = [
            'count' => $row['count'],
            'size' => $row['size']
        ];
    }
    
    return $summary;
}

function getDataTypeIcon($dataType) {
    $icons = [
        'users' => 'users',
        'enquiries' => 'phone',
        'students' => 'graduation-cap',
        'certificate_requests' => 'certificate',
        'call_logs' => 'phone-alt',
        'marklists' => 'list-alt'
    ];
    
    return $icons[$dataType] ?? 'database';
}

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}

// AJAX Handler Functions
function handleUpdatePolicy() {
    global $conn;
    
    $dataType = $_POST['data_type'] ?? '';
    $retentionDays = (int)($_POST['retention_days'] ?? 365);
    $archiveBeforeDelete = isset($_POST['archive_before_delete']) ? 1 : 0;
    $autoDelete = isset($_POST['auto_delete']) ? 1 : 0;
    
    if (empty($dataType)) {
        echo json_encode(['success' => false, 'message' => 'Data type is required']);
        return;
    }
    
    $stmt = mysqli_prepare($conn, "
        UPDATE retention_policies 
        SET retention_days = ?, archive_before_delete = ?, auto_delete = ?, updated_at = NOW()
        WHERE data_type = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'iiis', $retentionDays, $archiveBeforeDelete, $autoDelete, $dataType);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Policy updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update policy']);
    }
}

function handleScheduleDeletion() {
    global $conn;
    
    $dataType = $_GET['data_type'] ?? '';
    
    if (empty($dataType)) {
        echo json_encode(['success' => false, 'message' => 'Data type is required']);
        return;
    }
    
    // Get retention policy
    $stmt = mysqli_prepare($conn, "SELECT * FROM retention_policies WHERE data_type = ?");
    mysqli_stmt_bind_param($stmt, 's', $dataType);
    mysqli_stmt_execute($stmt);
    $policy = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (!$policy) {
        echo json_encode(['success' => false, 'message' => 'Retention policy not found']);
        return;
    }
    
    // Count records to be deleted
    $count = getRecordsToProcessCount($dataType, $policy['retention_days']);
    
    if ($count == 0) {
        echo json_encode(['success' => false, 'message' => 'No records to delete']);
        return;
    }
    
    // Log the deletion request
    $stmt = mysqli_prepare($conn, "
        INSERT INTO deletion_log (data_type, records_count, status, deleted_by) 
        VALUES (?, ?, 'pending', ?)
    ");
    
    mysqli_stmt_bind_param($stmt, 'sii', $dataType, $count, $_SESSION['user_id']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Deletion scheduled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to schedule deletion']);
    }
}

function handleArchiveData() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $dataType = $input['data_type'] ?? '';
    $recordIds = $input['record_ids'] ?? [];
    
    if (empty($dataType) || empty($recordIds)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Create archive directory if it doesn't exist
    $archiveDir = __DIR__ . '/../../archives/' . date('Y/m');
    if (!is_dir($archiveDir)) {
        mkdir($archiveDir, 0755, true);
    }
    
    $archiveFile = $archiveDir . '/' . $dataType . '_' . date('Y-m-d_H-i-s') . '.json';
    
    // Get records to archive
    $records = getRecordsForArchive($dataType, $recordIds);
    
    // Save to archive file
    $archiveData = [
        'data_type' => $dataType,
        'archived_at' => date('Y-m-d H:i:s'),
        'archived_by' => $_SESSION['user_id'],
        'records' => $records
    ];
    
    if (file_put_contents($archiveFile, json_encode($archiveData, JSON_PRETTY_PRINT))) {
        // Log the archive
        $stmt = mysqli_prepare($conn, "
            INSERT INTO archived_data (data_type, records_count, file_size, archive_path) 
            VALUES (?, ?, ?, ?)
        ");
        
        $fileSize = filesize($archiveFile);
        $recordCount = count($records);
        
        mysqli_stmt_bind_param($stmt, 'siis', $dataType, $recordCount, $fileSize, $archiveFile);
        mysqli_stmt_execute($stmt);
        
        echo json_encode(['success' => true, 'message' => 'Data archived successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create archive']);
    }
}

function getRecordsForArchive($dataType, $recordIds) {
    global $conn;
    
    $placeholders = str_repeat('?,', count($recordIds) - 1) . '?';
    $query = "SELECT * FROM $dataType WHERE id IN ($placeholders)";
    
    $stmt = mysqli_prepare($conn, $query);
    $types = str_repeat('i', count($recordIds));
    mysqli_stmt_bind_param($stmt, $types, ...$recordIds);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $records = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
    
    return $records;
}

function handleConfirmDeletion() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $dataType = $input['data_type'] ?? '';
    $recordIds = $input['record_ids'] ?? [];
    
    if (empty($dataType) || empty($recordIds)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Archive first if policy requires it
    $stmt = mysqli_prepare($conn, "SELECT archive_before_delete FROM retention_policies WHERE data_type = ?");
    mysqli_stmt_bind_param($stmt, 's', $dataType);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $policy = mysqli_fetch_assoc($result);
    
    if ($policy && $policy['archive_before_delete']) {
        // Archive the data first
        handleArchiveData();
    }
    
    // Delete the records
    $placeholders = str_repeat('?,', count($recordIds) - 1) . '?';
    $query = "DELETE FROM $dataType WHERE id IN ($placeholders)";
    
    $stmt = mysqli_prepare($conn, $query);
    $types = str_repeat('i', count($recordIds));
    mysqli_stmt_bind_param($stmt, $types, ...$recordIds);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update deletion log
        $stmt = mysqli_prepare($conn, "
            UPDATE deletion_log 
            SET status = 'completed', completed_at = NOW() 
            WHERE data_type = ? AND status = 'pending' 
            ORDER BY created_at DESC LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 's', $dataType);
        mysqli_stmt_execute($stmt);
        
        echo json_encode(['success' => true, 'message' => 'Records deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete records']);
    }
}

function handleRollback() {
    // Rollback functionality would require more complex implementation
    // This is a placeholder for the rollback feature
    echo json_encode(['success' => false, 'message' => 'Rollback functionality not yet implemented']);
}

function handleGetRecords() {
    global $conn;
    
    $dataType = $_GET['data_type'] ?? '';
    
    if (empty($dataType)) {
        echo json_encode(['success' => false, 'message' => 'Data type is required']);
        return;
    }
    
    // Get retention policy
    $stmt = mysqli_prepare($conn, "SELECT retention_days FROM retention_policies WHERE data_type = ?");
    mysqli_stmt_bind_param($stmt, 's', $dataType);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $policy = mysqli_fetch_assoc($result);
    
    if (!$policy) {
        echo json_encode(['success' => false, 'message' => 'Policy not found']);
        return;
    }
    
    $cutoffDate = date('Y-m-d', strtotime("-{$policy['retention_days']} days"));
    
    // Get records based on data type
    $records = getRecordsToReview($dataType, $cutoffDate);
    
    // Generate HTML for records review
    $html = generateRecordsReviewHTML($dataType, $records);
    
    echo json_encode(['success' => true, 'html' => $html]);
}

function getRecordsToReview($dataType, $cutoffDate) {
    global $conn;
    
    switch ($dataType) {
        case 'users':
            $query = "SELECT id, full_name, email, created_at FROM users WHERE created_at < ? AND is_active = 0 LIMIT 100";
            break;
        case 'enquiries':
            $query = "SELECT id, full_name, phone, status, created_at FROM enquiries WHERE created_at < ? AND status IN ('not_interested', 'enrolled') LIMIT 100";
            break;
        case 'students':
            $query = "SELECT id, full_name, student_id, course, created_at FROM students WHERE created_at < ? AND batch_year < YEAR(CURDATE()) - 5 LIMIT 100";
            break;
        case 'certificate_requests':
            $query = "SELECT id, student_id, request_type, status, requested_at FROM certificate_requests WHERE requested_at < ? AND status = 'approved' LIMIT 100";
            break;
        case 'call_logs':
            $query = "SELECT id, enquiry_id, call_outcome, call_duration, call_timestamp FROM call_logs WHERE call_timestamp < ? LIMIT 100";
            break;
        case 'marklists':
            $query = "SELECT id, student_id, subject, marks_obtained, exam_date FROM marklists WHERE created_at < ? AND exam_date < ? LIMIT 100";
            break;
        default:
            return [];
    }
    
    $stmt = mysqli_prepare($conn, $query);
    if ($dataType === 'marklists') {
        mysqli_stmt_bind_param($stmt, 'ss', $cutoffDate, $cutoffDate);
    } else {
        mysqli_stmt_bind_param($stmt, 's', $cutoffDate);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $records = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
    
    return $records;
}

function generateRecordsReviewHTML($dataType, $records) {
    if (empty($records)) {
        return '<p class="text-muted">No records found for review.</p>';
    }
    
    $html = '<div class="table-responsive">';
    $html .= '<table class="table table-sm">';
    $html .= '<thead><tr>';
    $html .= '<th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>';
    
    // Dynamic headers based on data type
    $firstRecord = $records[0];
    foreach (array_keys($firstRecord) as $column) {
        if ($column !== 'id') {
            $html .= '<th>' . ucfirst(str_replace('_', ' ', $column)) . '</th>';
        }
    }
    
    $html .= '</tr></thead><tbody>';
    
    foreach ($records as $record) {
        $html .= '<tr>';
        $html .= '<td><input type="checkbox" name="record_ids[]" value="' . $record['id'] . '"></td>';
        
        foreach ($record as $key => $value) {
            if ($key !== 'id') {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
        }
        
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table></div>';
    
    $html .= '<script>
        function toggleSelectAll() {
            const selectAll = document.getElementById("selectAll");
            const checkboxes = document.querySelectorAll("input[name=\'record_ids[]\']");
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }
    </script>';
    
    return $html;
}

// Render footer
renderAdminFooter();
?>
                        