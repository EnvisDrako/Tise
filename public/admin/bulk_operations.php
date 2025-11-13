<?php
/**
 * Admin Bulk Operations Dashboard
 * System-wide data management with bulk operations
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Page title
$pageTitle = 'Bulk Operations Dashboard';

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['ajax']) {
        case 'start_operation':
            handleStartOperation();
            break;
        case 'cancel_operation':
            handleCancelOperation();
            break;
        case 'get_progress':
            handleGetProgress();
            break;
        case 'schedule_operation':
            handleScheduleOperation();
            break;
        case 'get_operations':
            handleGetOperations();
            break;
        case 'download_results':
            handleDownloadResults();
            break;
        case 'cleanup_completed':
            handleCleanupCompleted();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    exit;
}

// Get current operations and statistics
$activeOperations = getActiveOperations();
$recentOperations = getRecentOperations();
$operationStats = getOperationStatistics();
$scheduledOperations = getScheduledOperations();

// Get tab parameter
$activeTab = $_GET['tab'] ?? 'operations';

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-tasks"></i> Bulk Operations Dashboard
        </h1>
        <p class="lead">Manage system-wide bulk operations, scheduling, and data processing</p>
    </div>
</div>

<!-- Operation Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary">
            <div class="stat-number"><?php echo $operationStats['active_operations']; ?></div>
            <div class="stat-label">Active Operations</div>
            <i class="fas fa-play stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info">
            <div class="stat-number"><?php echo $operationStats['scheduled_operations']; ?></div>
            <div class="stat-label">Scheduled</div>
            <i class="fas fa-clock stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success">
            <div class="stat-number"><?php echo $operationStats['completed_today']; ?></div>
            <div class="stat-label">Completed Today</div>
            <i class="fas fa-check stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning">
            <div class="stat-number"><?php echo formatBytes($operationStats['data_processed']); ?></div>
            <div class="stat-label">Data Processed</div>
            <i class="fas fa-database stat-icon"></i>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs mb-4" id="operationTabs">
    <li class="nav-item">
        <a class="nav-link <?php echo $activeTab === 'operations' ? 'active' : ''; ?>" 
           href="?tab=operations">
            <i class="fas fa-play"></i> Active Operations
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $activeTab === 'new' ? 'active' : ''; ?>" 
           href="?tab=new">
            <i class="fas fa-plus"></i> New Operation
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $activeTab === 'scheduled' ? 'active' : ''; ?>" 
           href="?tab=scheduled">
            <i class="fas fa-clock"></i> Scheduled
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $activeTab === 'history' ? 'active' : ''; ?>" 
           href="?tab=history">
            <i class="fas fa-history"></i> History
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $activeTab === 'archives' ? 'active' : ''; ?>" 
           href="?tab=archives">
            <i class="fas fa-archive"></i> Archives
        </a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    
    <!-- Active Operations Tab -->
    <?php if ($activeTab === 'operations'): ?>
    <div class="tab-pane fade show active">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-play"></i> Active Operations</h5>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshOperations()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activeOperations)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Active Operations</h5>
                                <p class="text-muted">All bulk operations have completed</p>
                                <a href="?tab=new" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Start New Operation
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Operation</th>
                                            <th>Type</th>
                                            <th>Progress</th>
                                            <th>Records</th>
                                            <th>Started</th>
                                            <th>ETA</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activeOperations as $operation): ?>
                                            <tr id="operation-<?php echo $operation['id']; ?>">
                                                <td>
                                                    <strong><?php echo htmlspecialchars($operation['operation_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($operation['description']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getOperationTypeColor($operation['operation_type']); ?>">
                                                        <?php echo ucfirst($operation['operation_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress mb-1" style="height: 20px;">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $operation['progress_percentage']; ?>%"
                                                             id="progress-<?php echo $operation['id']; ?>">
                                                            <?php echo $operation['progress_percentage']; ?>%
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo number_format($operation['processed_records']); ?> / 
                                                        <?php echo number_format($operation['total_records']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo number_format($operation['total_records']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('M j, H:i', strtotime($operation['started_at'])); ?>
                                                </td>
                                                <td>
                                                    <span id="eta-<?php echo $operation['id']; ?>">
                                                        <?php echo calculateETA($operation); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-info" 
                                                                onclick="viewOperationDetails(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" 
                                                                onclick="pauseOperation(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" 
                                                                onclick="cancelOperation(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-stop"></i>
                                                        </button>
                                                    </div>
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
        </div>
    </div>
    <?php endif; ?>
    
    <!-- New Operation Tab -->
    <?php if ($activeTab === 'new'): ?>
    <div class="tab-pane fade show active">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> Create New Bulk Operation</h5>
                    </div>
                    <div class="card-body">
                        <form id="newOperationForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Operation Type</label>
                                        <select class="form-select" id="operationType" name="operation_type" required onchange="updateOperationOptions()">
                                            <option value="">Select Operation Type</option>
                                            <option value="delete">Bulk Delete</option>
                                            <option value="archive">Bulk Archive</option>
                                            <option value="export">Bulk Export</option>
                                            <option value="update">Bulk Update</option>
                                            <option value="cleanup">Data Cleanup</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Data Type</label>
                                        <select class="form-select" id="dataType" name="data_type" required onchange="updateDataOptions()">
                                            <option value="">Select Data Type</option>
                                            <option value="users">Users</option>
                                            <option value="enquiries">Enquiries</option>
                                            <option value="students">Students</option>
                                            <option value="certificate_requests">Certificate Requests</option>
                                            <option value="call_logs">Call Logs</option>
                                            <option value="marklists">Marklists</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Operation Name</label>
                                <input type="text" class="form-control" id="operationName" name="operation_name" 
                                       placeholder="Enter a descriptive name for this operation" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="operationDescription" name="description" rows="3"
                                          placeholder="Describe what this operation will do"></textarea>
                            </div>
                            
                            <!-- Dynamic Options Based on Operation Type -->
                            <div id="operationOptions">
                                <!-- Options will be loaded here based on selection -->
                            </div>
                            
                            <!-- Scheduling Options -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-clock"></i> Scheduling Options</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="runNow" value="now" checked>
                                        <label class="form-check-label" for="runNow">
                                            Run immediately
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="schedule_type" id="runLater" value="scheduled">
                                        <label class="form-check-label" for="runLater">
                                            Schedule for later
                                        </label>
                                    </div>
                                    
                                    <div id="scheduleOptions" class="mt-3" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Schedule Date</label>
                                                <input type="date" class="form-control" name="schedule_date" 
                                                       min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Schedule Time</label>
                                                <input type="time" class="form-control" name="schedule_time">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Safety Options -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-shield-alt"></i> Safety Options</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="createBackup" name="create_backup" checked>
                                        <label class="form-check-label" for="createBackup">
                                            Create backup before operation
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="dryRun" name="dry_run">
                                        <label class="form-check-label" for="dryRun">
                                            Dry run (preview only, don't execute)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sendNotification" name="send_notification" checked>
                                        <label class="form-check-label" for="sendNotification">
                                            Send notification when complete
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary" onclick="startOperation()">
                            <i class="fas fa-play"></i> Start Operation
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="previewOperation()">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="resetForm()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle"></i> Operation Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb"></i> Best Practices</h6>
                            <ul class="mb-0">
                                <li>Always create backups for destructive operations</li>
                                <li>Use dry run to preview changes first</li>
                                <li>Schedule large operations during off-peak hours</li>
                                <li>Monitor progress and be ready to cancel if needed</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Warning</h6>
                            <p class="mb-0">Bulk operations can affect large amounts of data. Always verify your selections before proceeding.</p>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-bar"></i> Quick Stats</h6>
                    </div>
                    <div class="card-body">
                        <div id="quickStats">
                            <p class="text-muted">Select operation type and data type to see statistics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Scheduled Operations Tab -->
    <?php if ($activeTab === 'scheduled'): ?>
    <div class="tab-pane fade show active">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> Scheduled Operations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($scheduledOperations)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Scheduled Operations</h5>
                                <p class="text-muted">No operations are currently scheduled</p>
                                <a href="?tab=new" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Schedule New Operation
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Operation</th>
                                            <th>Type</th>
                                            <th>Data Type</th>
                                            <th>Scheduled For</th>
                                            <th>Records</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($scheduledOperations as $operation): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($operation['operation_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($operation['description']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getOperationTypeColor($operation['operation_type']); ?>">
                                                        <?php echo ucfirst($operation['operation_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-<?php echo getDataTypeIcon($operation['data_type']); ?>"></i>
                                                    <?php echo ucfirst(str_replace('_', ' ', $operation['data_type'])); ?>
                                                </td>
                                                <td>
                                                    <?php echo date('M j, Y H:i', strtotime($operation['scheduled_at'])); ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo getTimeUntil($operation['scheduled_at']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo number_format($operation['estimated_records']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($operation['created_by_name']); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" 
                                                                onclick="editScheduledOperation(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" 
                                                                onclick="runScheduledNow(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" 
                                                                onclick="cancelScheduled(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
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
        </div>
    </div>
    <?php endif; ?>
    
    <!-- History Tab -->
    <?php if ($activeTab === 'history'): ?>
    <div class="tab-pane fade show active">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-history"></i> Operation History</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="exportHistory()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="btn btn-outline-danger" onclick="cleanupHistory()">
                                <i class="fas fa-trash"></i> Cleanup Old
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Options -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filterType" onchange="filterHistory()">
                                    <option value="">All Types</option>
                                    <option value="delete">Delete</option>
                                    <option value="archive">Archive</option>
                                    <option value="export">Export</option>
                                    <option value="update">Update</option>
                                    <option value="cleanup">Cleanup</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filterStatus" onchange="filterHistory()">
                                    <option value="">All Status</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control form-control-sm" id="filterDate" onchange="filterHistory()">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" id="filterSearch" 
                                       placeholder="Search operations..." onkeyup="filterHistory()">
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>Operation</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Records</th>
                                        <th>Duration</th>
                                        <th>Completed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOperations as $operation): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($operation['operation_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($operation['description']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getOperationTypeColor($operation['operation_type']); ?>">
                                                    <?php echo ucfirst($operation['operation_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($operation['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php elseif ($operation['status'] === 'failed'): ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php elseif ($operation['status'] === 'cancelled'): ?>
                                                    <span class="badge bg-warning">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo number_format($operation['processed_records']); ?> / 
                                                <?php echo number_format($operation['total_records']); ?>
                                            </td>
                                            <td>
                                                <?php echo formatDuration($operation['duration_seconds']); ?>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y H:i', strtotime($operation['completed_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" 
                                                            onclick="viewOperationLog(<?php echo $operation['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($operation['has_results']): ?>
                                                        <button class="btn btn-outline-success" 
                                                                onclick="downloadResults(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($operation['can_repeat']): ?>
                                                        <button class="btn btn-outline-primary" 
                                                                onclick="repeatOperation(<?php echo $operation['id']; ?>)">
                                                            <i class="fas fa-redo"></i>
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
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Archives Tab -->
    <?php if ($activeTab === 'archives'): ?>
    <div class="tab-pane fade show active">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-archive"></i> Archive Management</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $archives = getArchivesList();
                        if (empty($archives)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Archives Found</h5>
                                <p class="text-muted">No archived data available</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Archive Name</th>
                                            <th>Data Type</th>
                                            <th>Records</th>
                                            <th>Size</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($archives as $archive): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-file-archive"></i>
                                                    <?php echo htmlspecialchars($archive['archive_name']); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-<?php echo getDataTypeIcon($archive['data_type']); ?>"></i>
                                                    <?php echo ucfirst(str_replace('_', ' ', $archive['data_type'])); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo number_format($archive['records_count']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo formatBytes($archive['file_size']); ?>
                                                </td>
                                                <td>
                                                    <?php echo date('M j, Y H:i', strtotime($archive['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-info" 
                                                                onclick="previewArchive(<?php echo $archive['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" 
                                                                onclick="downloadArchive(<?php echo $archive['id']; ?>)">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" 
                                                                onclick="restoreArchive(<?php echo $archive['id']; ?>)">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" 
                                                                onclick="deleteArchive(<?php echo $archive['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
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
        </div>
    </div>
    <?php endif; ?>
    
</div>

<!-- Modals -->
<!-- Operation Details Modal -->
<div class="modal fade" id="operationDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Operation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="operationDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Operation Preview Modal -->
<div class="modal fade" id="operationPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Operation Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="operationPreviewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmOperation()">
                    <i class="fas fa-play"></i> Confirm & Start
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Bulk Operations JavaScript Functions
let operationRefreshInterval;
let currentPreviewData = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set up form event listeners
    document.querySelectorAll('input[name="schedule_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const scheduleOptions = document.getElementById('scheduleOptions');
            scheduleOptions.style.display = this.value === 'scheduled' ? 'block' : 'none';
        });
    });
    
    // Start auto-refresh for active operations
    if (document.querySelector('.tab-pane.show.active')) {
        startAutoRefresh();
    }
});

function updateOperationOptions() {
    const operationType = document.getElementById('operationType').value;
    const dataType = document.getElementById('dataType').value;
    const optionsContainer = document.getElementById('operationOptions');
    
    if (!operationType) {
        optionsContainer.innerHTML = '';
        return;
    }
    
    let optionsHTML = '<div class="card"><div class="card-header"><h6>Operation Options</h6></div><div class="card-body">';
    
    switch (operationType) {
        case 'delete':
            optionsHTML += `
                <div class="mb-3">
                    <label class="form-label">Delete Criteria</label>
                    <select class="form-select" name="delete_criteria">
                        <option value="older_than">Records older than specified date</option>
                        <option value="status_based">Based on status</option>
                        <option value="custom_filter">Custom filter</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date Threshold</label>
                    <input type="date" class="form-control" name="date_threshold" max="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="soft_delete" id="softDelete">
                    <label class="form-check-label" for="softDelete">
                        Soft delete (mark as deleted instead of permanent removal)
                    </label>
                </div>
            `;
            break;
            
        case 'archive':
            optionsHTML += `
                <div class="mb-3">
                    <label class="form-label">Archive Format</label>
                    <select class="form-select" name="archive_format">
                        <option value="json">JSON</option>
                        <option value="csv">CSV</option>
                        <option value="xml">XML</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Compression</label>
                    <select class="form-select" name="compression">
                        <option value="zip">ZIP</option>
                        <option value="gzip">GZIP</option>
                        <option value="none">No Compression</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="delete_after_archive" id="deleteAfterArchive">
                    <label class="form-check-label" for="deleteAfterArchive">
                        Delete records after successful archive
                    </label>
                </div>
            `;
            break;
            
        case 'export':
            optionsHTML += `
                <div class="mb-3">
                    <label class="form-label">Export Format</label>
                    <select class="form-select" name="export_format">
                        <option value="csv">CSV</option>
                        <option value="excel">Excel (XLSX)</option>
                        <option value="pdf">PDF Report</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fields to Include</label>
                    <div id="fieldSelection">
                        <!-- Fields will be populated based on data type -->
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="include_related" id="includeRelated">
                    <label class="form-check-label" for="includeRelated">
                        Include related data
                    </label>
                </div>
            `;
            break;
            
        case 'update':
            optionsHTML += `
                <div class="mb-3">
                    <label class="form-label">Update Field</label>
                    <select class="form-select" name="update_field">
                        <option value="">Select field to update</option>
                        <!-- Fields will be populated based on data type -->
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Value</label>
                    <input type="text" class="form-control" name="new_value" placeholder="Enter new value">
                </div>
                <div class="mb-3">
                    <label class="form-label">Update Condition</label>
                    <textarea class="form-control" name="update_condition" rows="3" 
                              placeholder="Specify conditions for records to update"></textarea>
                </div>
            `;
            break;
            
        case 'cleanup':
            optionsHTML += `
                <div class="mb-3">
                    <label class="form-label">Cleanup Type</label>
                    <select class="form-select" name="cleanup_type">
                        <option value="duplicates">Remove duplicates</option>
                        <option value="orphaned">Remove orphaned records</option>
                        <option value="invalid">Remove invalid data</option>
                        <option value="temp_files">Clean temporary files</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="aggressive_cleanup" id="aggressiveCleanup">
                    <label class="form-check-label" for="aggressiveCleanup">
                        Aggressive cleanup (may remove more data)
                    </label>
                </div>
            `;
            break;
    }
    
    optionsHTML += '</div></div>';
    optionsContainer.innerHTML = optionsHTML;
    
    // Update quick stats
    updateQuickStats();
}

function updateDataOptions() {
    updateQuickStats();
}

function updateQuickStats() {
    const operationType = document.getElementById('operationType').value;
    const dataType = document.getElementById('dataType').value;
    
    if (!operationType || !dataType) {
        document.getElementById('quickStats').innerHTML = '<p class="text-muted">Select operation type and data type to see statistics</p>';
        return;
    }
    
    // Fetch quick stats via AJAX
    fetch(`?ajax=get_quick_stats&operation_type=${operationType}&data_type=${dataType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('quickStats').innerHTML = data.html;
            }
        });
}

function startOperation() {
    const form = document.getElementById('newOperationForm');
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Show confirmation for destructive operations
    const operationType = formData.get('operation_type');
    if (['delete', 'cleanup'].includes(operationType)) {
        if (!confirm('This operation may permanently modify or delete data. Are you sure you want to proceed?')) {
            return;
        }
    }
    
    fetch('?ajax=start_operation', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Operation started successfully', 'success');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = '?tab=operations';
            }
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error starting operation: ' + error.message, 'danger');
    });
}

function previewOperation() {
    const form = document.getElementById('newOperationForm');
    const formData = new FormData(form);
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    formData.append('preview_only', '1');
    
    fetch('?ajax=start_operation', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('operationPreviewContent').innerHTML = data.preview_html;
            currentPreviewData = formData;
            new bootstrap.Modal(document.getElementById('operationPreviewModal')).show();
        } else {
            showAlert(data.message, 'danger');
        }
    });
}

function confirmOperation() {
    if (!currentPreviewData) return;
    
    currentPreviewData.delete('preview_only');
    
    fetch('?ajax=start_operation', {
        method: 'POST',
        body: currentPreviewData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('operationPreviewModal')).hide();
            showAlert('Operation started successfully', 'success');
            window.location.href = '?tab=operations';
        } else {
            showAlert(data.message, 'danger');
        }
    });
}

function resetForm() {
    document.getElementById('newOperationForm').reset();
    document.getElementById('operationOptions').innerHTML = '';
    document.getElementById('quickStats').innerHTML = '<p class="text-muted">Select operation type and data type to see statistics</p>';
}

function startAutoRefresh() {
    operationRefreshInterval = setInterval(refreshOperations, 5000); // Refresh every 5 seconds
}

function stopAutoRefresh() {
    if (operationRefreshInterval) {
        clearInterval(operationRefreshInterval);
    }
}

function refreshOperations() {
    fetch('?ajax=get_operations')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateOperationsTable(data.operations);
            }
        });
}

function updateOperationsTable(operations) {
    operations.forEach(operation => {
        const row = document.getElementById(`operation-${operation.id}`);
        if (row) {
            // Update progress bar
            const progressBar = document.getElementById(`progress-${operation.id}`);
            if (progressBar) {
                progressBar.style.width = `${operation.progress_percentage}%`;
                progressBar.textContent = `${operation.progress_percentage}%`;
            }
            
            // Update ETA
            const eta = document.getElementById(`eta-${operation.id}`);
            if (eta) {
                eta.textContent = calculateETA(operation);
            }
            
            // If operation is complete, reload page
            if (operation.status === 'completed' || operation.status === 'failed') {
                location.reload();
            }
        }
    });
}

function viewOperationDetails(operationId) {
    fetch(`?ajax=get_operation_details&id=${operationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('operationDetailsContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('operationDetailsModal')).show();
            }
        });
}

function pauseOperation(operationId) {
    if (confirm('Are you sure you want to pause this operation?')) {
        fetch(`?ajax=pause_operation&id=${operationId}`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Operation paused', 'info');
                    refreshOperations();
                } else {
                    showAlert(data.message, 'danger');
                }
            });
    }
}

function cancelOperation(operationId) {
    if (confirm('Are you sure you want to cancel this operation? This action cannot be undone.')) {
        fetch(`?ajax=cancel_operation&id=${operationId}`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Operation cancelled', 'warning');
                    refreshOperations();
                } else {
                    showAlert(data.message, 'danger');
                }
            });
    }
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

// Cleanup when leaving page
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});
</script>

<?php
// Backend Functions for Bulk Operations

function getActiveOperations() {
    global $conn;
    
    // Check if bulk_operations table exists, create if not
    createBulkOperationsTable();
    
    $query = "
        SELECT bo.*, u.full_name as created_by_name 
        FROM bulk_operations bo 
        LEFT JOIN users u ON bo.created_by = u.id 
        WHERE bo.status IN ('running', 'paused') 
        ORDER BY bo.started_at DESC
    ";
    
    $result = mysqli_query($conn, $query);
    $operations = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $operations[] = $row;
    }
    
    return $operations;
}

function getRecentOperations() {
    global $conn;
    
    createBulkOperationsTable();
    
    $query = "
        SELECT bo.*, u.full_name as created_by_name 
        FROM bulk_operations bo 
        LEFT JOIN users u ON bo.created_by = u.id 
        WHERE bo.status IN ('completed', 'failed', 'cancelled') 
        ORDER BY bo.completed_at DESC 
        LIMIT 50
    ";
    
    $result = mysqli_query($conn, $query);
    $operations = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate duration
        if ($row['started_at'] && $row['completed_at']) {
            $start = new DateTime($row['started_at']);
            $end = new DateTime($row['completed_at']);
            $row['duration_seconds'] = $end->getTimestamp() - $start->getTimestamp();
        } else {
            $row['duration_seconds'] = 0;
        }
        
        // Check if operation has downloadable results
        $row['has_results'] = !empty($row['result_file_path']) && file_exists($row['result_file_path']);
        
        // Check if operation can be repeated
        $row['can_repeat'] = in_array($row['operation_type'], ['export', 'archive']);
        
        $operations[] = $row;
    }
    
    return $operations;
}

function getOperationStatistics() {
    global $conn;
    
    createBulkOperationsTable();
    
    $stats = [
        'active_operations' => 0,
        'scheduled_operations' => 0,
        'completed_today' => 0,
        'data_processed' => 0
    ];
    
    // Active operations
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bulk_operations WHERE status IN ('running', 'paused')");
    $row = mysqli_fetch_assoc($result);
    $stats['active_operations'] = $row['count'];
    
    // Scheduled operations
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bulk_operations WHERE status = 'scheduled'");
    $row = mysqli_fetch_assoc($result);
    $stats['scheduled_operations'] = $row['count'];
    
    // Completed today
    $today = date('Y-m-d');
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bulk_operations WHERE DATE(completed_at) = '$today' AND status = 'completed'");
    $row = mysqli_fetch_assoc($result);
    $stats['completed_today'] = $row['count'];
    
    // Data processed (sum of processed records * average record size)
    $result = mysqli_query($conn, "SELECT SUM(processed_records) as total FROM bulk_operations WHERE status = 'completed'");
    $row = mysqli_fetch_assoc($result);
    $stats['data_processed'] = ($row['total'] ?? 0) * 1024; // Estimate 1KB per record
    
    return $stats;
}

function getScheduledOperations() {
    global $conn;
    
    createBulkOperationsTable();
    
    $query = "
        SELECT bo.*, u.full_name as created_by_name 
        FROM bulk_operations bo 
        LEFT JOIN users u ON bo.created_by = u.id 
        WHERE bo.status = 'scheduled' 
        ORDER BY bo.scheduled_at ASC
    ";
    
    $result = mysqli_query($conn, $query);
    $operations = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $operations[] = $row;
    }
    
    return $operations;
}

function getArchivesList() {
    global $conn;
    
    // Get archives from archived_data table
    $query = "
        SELECT 
            id,
            CONCAT(data_type, '_', DATE_FORMAT(created_at, '%Y%m%d_%H%i%s')) as archive_name,
            data_type,
            records_count,
            file_size,
            created_at
        FROM archived_data 
        ORDER BY created_at DESC
    ";
    
    $result = mysqli_query($conn, $query);
    $archives = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $archives[] = $row;
    }
    
    return $archives;
}

function createBulkOperationsTable() {
    global $conn;
    
    $checkTable = "SHOW TABLES LIKE 'bulk_operations'";
    $result = mysqli_query($conn, $checkTable);
    
    if (mysqli_num_rows($result) == 0) {
        $createTable = "
            CREATE TABLE bulk_operations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                operation_name VARCHAR(255) NOT NULL,
                operation_type ENUM('delete', 'archive', 'export', 'update', 'cleanup') NOT NULL,
                data_type VARCHAR(50) NOT NULL,
                description TEXT,
                status ENUM('scheduled', 'running', 'paused', 'completed', 'failed', 'cancelled') DEFAULT 'scheduled',
                total_records INT DEFAULT 0,
                processed_records INT DEFAULT 0,
                progress_percentage DECIMAL(5,2) DEFAULT 0,
                operation_config JSON,
                result_file_path VARCHAR(500),
                error_message TEXT,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                scheduled_at TIMESTAMP NULL,
                started_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                estimated_records INT DEFAULT 0,
                FOREIGN KEY (created_by) REFERENCES users(id),
                INDEX idx_status (status),
                INDEX idx_created_by (created_by),
                INDEX idx_scheduled_at (scheduled_at)
            )
        ";
        mysqli_query($conn, $createTable);
    }
}

function getOperationTypeColor($type) {
    $colors = [
        'delete' => 'danger',
        'archive' => 'info',
        'export' => 'success',
        'update' => 'warning',
        'cleanup' => 'secondary'
    ];
    
    return $colors[$type] ?? 'primary';
}

function calculateETA($operation) {
    if ($operation['progress_percentage'] <= 0) {
        return 'Calculating...';
    }
    
    $startTime = strtotime($operation['started_at']);
    $currentTime = time();
    $elapsed = $currentTime - $startTime;
    
    $remainingPercentage = 100 - $operation['progress_percentage'];
    $estimatedTotal = ($elapsed / $operation['progress_percentage']) * 100;
    $remainingTime = $estimatedTotal - $elapsed;
    
    if ($remainingTime <= 0) {
        return 'Almost done';
    }
    
    return formatDuration($remainingTime);
}

function formatDuration($seconds) {
    if ($seconds < 60) {
        return $seconds . 's';
    } elseif ($seconds < 3600) {
        return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return $hours . 'h ' . $minutes . 'm';
    }
}

function getTimeUntil($datetime) {
    $target = strtotime($datetime);
    $now = time();
    $diff = $target - $now;
    
    if ($diff <= 0) {
        return 'Overdue';
    }
    
    if ($diff < 3600) {
        return 'in ' . floor($diff / 60) . ' minutes';
    } elseif ($diff < 86400) {
        return 'in ' . floor($diff / 3600) . ' hours';
    } else {
        return 'in ' . floor($diff / 86400) . ' days';
    }
}

// AJAX Handler Functions
function handleStartOperation() {
    global $conn;
    
    $operationName = $_POST['operation_name'] ?? '';
    $operationType = $_POST['operation_type'] ?? '';
    $dataType = $_POST['data_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $scheduleType = $_POST['schedule_type'] ?? 'now';
    $previewOnly = isset($_POST['preview_only']);
    
    if (empty($operationName) || empty($operationType) || empty($dataType)) {
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        return;
    }
    
    // Build operation configuration
    $config = [];
    foreach ($_POST as $key => $value) {
        if (!in_array($key, ['operation_name', 'operation_type', 'data_type', 'description', 'schedule_type'])) {
            $config[$key] = $value;
        }
    }
    
    // Estimate records count
    $estimatedRecords = estimateRecordsCount($dataType, $config);
    
    if ($previewOnly) {
        // Generate preview
        $previewHtml = generateOperationPreview($operationType, $dataType, $config, $estimatedRecords);
        echo json_encode(['success' => true, 'preview_html' => $previewHtml]);
        return;
    }
    
    // Determine scheduled time
    $scheduledAt = null;
    if ($scheduleType === 'scheduled') {
        $scheduleDate = $_POST['schedule_date'] ?? '';
        $scheduleTime = $_POST['schedule_time'] ?? '';
        if ($scheduleDate && $scheduleTime) {
            $scheduledAt = $scheduleDate . ' ' . $scheduleTime . ':00';
        }
    }
    
    // Insert operation record
    $stmt = mysqli_prepare($conn, "
        INSERT INTO bulk_operations (
            operation_name, operation_type, data_type, description, 
            operation_config, estimated_records, created_by, scheduled_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $configJson = json_encode($config);
    mysqli_stmt_bind_param($stmt, 'sssssiis', 
        $operationName, $operationType, $dataType, $description,
        $configJson, $estimatedRecords, $_SESSION['user_id'], $scheduledAt
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $operationId = mysqli_insert_id($conn);
        
        if ($scheduleType === 'now') {
            // Start operation immediately (in a real implementation, this would be queued)
            echo json_encode([
                'success' => true, 
                'message' => 'Operation started successfully',
                'redirect' => '?tab=operations'
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Operation scheduled successfully',
                'redirect' => '?tab=scheduled'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create operation']);
    }
}

function estimateRecordsCount($dataType, $config) {
    global $conn;
    
    // Basic count query - in a real implementation, this would consider the config filters
    $query = "SELECT COUNT(*) as count FROM $dataType";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    
    return 0;
}

function generateOperationPreview($operationType, $dataType, $config, $estimatedRecords) {
    $html = '<div class="alert alert-info">';
    $html .= '<h6><i class="fas fa-info-circle"></i> Operation Preview</h6>';
    $html .= '<p><strong>Type:</strong> ' . ucfirst($operationType) . '</p>';
    $html .= '<p><strong>Data Type:</strong> ' . ucfirst(str_replace('_', ' ', $dataType)) . '</p>';
    $html .= '<p><strong>Estimated Records:</strong> ' . number_format($estimatedRecords) . '</p>';
    
    if ($operationType === 'delete') {
        $html .= '<div class="alert alert-warning mt-3">';
        $html .= '<strong>Warning:</strong> This operation will permanently delete ' . number_format($estimatedRecords) . ' records.';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

function handleCancelOperation() {
    global $conn;
    
    $operationId = $_GET['id'] ?? 0;
    
    if (!$operationId) {
        echo json_encode(['success' => false, 'message' => 'Operation ID required']);
        return;
    }
    
    $stmt = mysqli_prepare($conn, "
        UPDATE bulk_operations 
        SET status = 'cancelled', completed_at = NOW() 
        WHERE id = ? AND status IN ('running', 'paused', 'scheduled')
    ");
    
    mysqli_stmt_bind_param($stmt, 'i', $operationId);
    
    if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($conn) > 0) {
        echo json_encode(['success' => true, 'message' => 'Operation cancelled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel operation']);
    }
}

function handleGetOperations() {
    $operations = getActiveOperations();
    echo json_encode(['success' => true, 'operations' => $operations]);
}

// Render footer
renderAdminFooter();
?>