<?php
/**
 * Certificate Request Management
 * Allows faculty to submit and track certificate requests for their institution's students
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include faculty authorization
require_once __DIR__ . '/../../includes/auth_check_faculty.php';

// Page title
$pageTitle = 'Certificate Requests';

// Get institution details
$institution = getCurrentInstitution();
$institutionId = getCurrentUserInstitutionId();

// Initialize variables
$errors = [];
$success = '';

// Handle certificate request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $studentIds = $_POST['student_ids'] ?? [];
    $requestType = sanitizeInput($_POST['request_type'] ?? '');
    
    // Validation
    if (empty($studentIds)) {
        $errors[] = 'Please select at least one student';
    }
    
    if (empty($requestType)) {
        $errors[] = 'Please select a certificate type';
    }
    
    // Verify all students belong to faculty's institution
    if (!empty($studentIds) && empty($errors)) {
        try {
            $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM students 
                WHERE id IN ($placeholders) AND institution_id = ?
            ");
            $stmt->execute(array_merge($studentIds, [$institutionId]));
            $validCount = $stmt->fetchColumn();
            
            if ($validCount != count($studentIds)) {
                $errors[] = 'Some selected students do not belong to your institution';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error occurred while validating students';
            logError("Error validating students for certificate request: " . $e->getMessage());
        }
    }
    
    // Submit certificate requests
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO certificate_requests (
                    student_id, institution_id, request_type, requested_by
                ) VALUES (?, ?, ?, ?)
            ");
            
            $successCount = 0;
            foreach ($studentIds as $studentId) {
                $stmt->execute([$studentId, $institutionId, $requestType, getCurrentUserId()]);
                $successCount++;
            }
            
            $pdo->commit();
            $success = "Successfully submitted $successCount certificate request(s)";
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to submit certificate requests. Please try again.';
            logError("Error submitting certificate requests: " . $e->getMessage());
        }
    }
}

// Handle bulk operations
$bulkMessage = '';
$bulkError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $selectedRequests = $_POST['selected_requests'] ?? [];
    $bulkAction = $_POST['bulk_action'];
    
    if (!empty($selectedRequests)) {
        try {
            switch ($bulkAction) {
                case 'export_csv':
                    // Export selected requests to CSV
                    $placeholders = str_repeat('?,', count($selectedRequests) - 1) . '?';
                    $stmt = $pdo->prepare("
                        SELECT cr.*, s.student_id, s.full_name as student_name, s.course
                        FROM certificate_requests cr
                        JOIN students s ON cr.student_id = s.id
                        WHERE cr.id IN ($placeholders) AND cr.institution_id = ?
                        ORDER BY cr.requested_at DESC
                    ");
                    $stmt->execute(array_merge($selectedRequests, [$institutionId]));
                    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="certificate_requests_' . date('Y-m-d') . '.csv"');
                    
                    $output = fopen('php://output', 'w');
                    fputcsv($output, ['Student ID', 'Student Name', 'Course', 'Certificate Type', 'Status', 'Request Date', 'Processed Date', 'Comments']);
                    
                    foreach ($requests as $request) {
                        fputcsv($output, [
                            $request['student_id'],
                            $request['student_name'],
                            $request['course'],
                            $request['request_type'],
                            ucfirst($request['status']),
                            $request['requested_at'],
                            $request['processed_at'],
                            $request['approval_comments']
                        ]);
                    }
                    
                    fclose($output);
                    exit;
                    
                case 'cancel_pending':
                    // Cancel pending requests (delete them)
                    $placeholders = str_repeat('?,', count($selectedRequests) - 1) . '?';
                    $stmt = $pdo->prepare("
                        DELETE FROM certificate_requests 
                        WHERE id IN ($placeholders) AND institution_id = ? AND status = 'pending'
                    ");
                    $stmt->execute(array_merge($selectedRequests, [$institutionId]));
                    $affectedRows = $stmt->rowCount();
                    $bulkMessage = "$affectedRows pending certificate request(s) cancelled";
                    break;
            }
        } catch (PDOException $e) {
            $bulkError = 'Error performing bulk operation: ' . $e->getMessage();
            logError("Bulk operation error: " . $e->getMessage());
        }
    } else {
        $bulkError = 'Please select at least one certificate request';
    }
}

// Initialize filter variables
$filters = [
    'search' => sanitizeInput($_GET['search'] ?? ''),
    'student_id' => sanitizeInput($_GET['student_id'] ?? ''),
    'request_type' => sanitizeInput($_GET['request_type'] ?? ''),
    'status' => sanitizeInput($_GET['status'] ?? ''),
    'date_from' => sanitizeInput($_GET['date_from'] ?? ''),
    'date_to' => sanitizeInput($_GET['date_to'] ?? ''),
    'sort_by' => sanitizeInput($_GET['sort_by'] ?? 'requested_at'),
    'sort_order' => sanitizeInput($_GET['sort_order'] ?? 'DESC')
];

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Build query with filters
$whereConditions = ["cr.institution_id = ?"];
$queryParams = [$institutionId];

if (!empty($filters['search'])) {
    $whereConditions[] = "(s.student_id LIKE ? OR s.full_name LIKE ? OR cr.request_type LIKE ?)";
    $searchTerm = '%' . $filters['search'] . '%';
    $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($filters['student_id'])) {
    $whereConditions[] = "s.id = ?";
    $queryParams[] = $filters['student_id'];
}

if (!empty($filters['request_type'])) {
    $whereConditions[] = "cr.request_type = ?";
    $queryParams[] = $filters['request_type'];
}

if (!empty($filters['status'])) {
    $whereConditions[] = "cr.status = ?";
    $queryParams[] = $filters['status'];
}

if (!empty($filters['date_from'])) {
    $whereConditions[] = "DATE(cr.requested_at) >= ?";
    $queryParams[] = $filters['date_from'];
}

if (!empty($filters['date_to'])) {
    $whereConditions[] = "DATE(cr.requested_at) <= ?";
    $queryParams[] = $filters['date_to'];
}

$whereClause = implode(' AND ', $whereConditions);

// Validate sort parameters
$allowedSortColumns = ['student_name', 'request_type', 'status', 'requested_at', 'processed_at'];
$sortBy = in_array($filters['sort_by'], $allowedSortColumns) ? $filters['sort_by'] : 'requested_at';
$sortOrder = strtoupper($filters['sort_order']) === 'ASC' ? 'ASC' : 'DESC';

// Adjust sort column for query
$sortColumn = $sortBy === 'student_name' ? 's.full_name' : 'cr.' . $sortBy;

// Get total count
$countQuery = "
    SELECT COUNT(*) 
    FROM certificate_requests cr
    JOIN students s ON cr.student_id = s.id
    WHERE $whereClause
";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($queryParams);
$totalRecords = $stmt->fetchColumn();

// Get certificate requests with pagination
$query = "
    SELECT cr.*, s.student_id, s.full_name as student_name, s.course,
           u.full_name as approved_by_name
    FROM certificate_requests cr
    JOIN students s ON cr.student_id = s.id
    LEFT JOIN users u ON cr.approved_by = u.id
    WHERE $whereClause
    ORDER BY $sortColumn $sortOrder
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($queryParams);
$certificateRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filter options
$studentsQuery = "SELECT id, student_id, full_name FROM students WHERE institution_id = ? ORDER BY full_name";
$stmt = $pdo->prepare($studentsQuery);
$stmt->execute([$institutionId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$requestTypesQuery = "
    SELECT DISTINCT request_type 
    FROM certificate_requests 
    WHERE institution_id = ?
    ORDER BY request_type
";
$stmt = $pdo->prepare($requestTypesQuery);
$stmt->execute([$institutionId]);
$requestTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate pagination
$totalPages = ceil($totalRecords / $limit);

// Get statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM certificate_requests 
    WHERE institution_id = ?
";
$stmt = $pdo->prepare($statsQuery);
$stmt->execute([$institutionId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Render header
renderFacultyHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-gradient">
                <i class="fas fa-certificate"></i> Certificate Requests
            </h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
                <i class="fas fa-plus"></i> New Request
            </button>
        </div>
        
        <?php if ($institution): ?>
            <div class="alert alert-info">
                <i class="fas fa-university"></i>
                Managing certificate requests for <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-label">Total Requests</div>
            <i class="fas fa-certificate stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number text-warning"><?php echo number_format($stats['pending']); ?></div>
            <div class="stat-label">Pending</div>
            <i class="fas fa-clock stat-icon text-warning"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number text-success"><?php echo number_format($stats['approved']); ?></div>
            <div class="stat-label">Approved</div>
            <i class="fas fa-check-circle stat-icon text-success"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-number text-danger"><?php echo number_format($stats['rejected']); ?></div>
            <div class="stat-label">Rejected</div>
            <i class="fas fa-times-circle stat-icon text-danger"></i>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($bulkMessage): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($bulkMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($bulkError): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($bulkError); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter"></i> Filters & Search
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       value="<?php echo htmlspecialchars($filters['search']); ?>"
                       placeholder="Student, Certificate Type">
            </div>
            
            <div class="col-md-2">
                <label for="student_id" class="form-label">Student</label>
                <select class="form-select" id="student_id" name="student_id">
                    <option value="">All Students</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" 
                                <?php echo $filters['student_id'] == $student['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="request_type" class="form-label">Certificate Type</label>
                <select class="form-select" id="request_type" name="request_type">
                    <option value="">All Types</option>
                    <option value="Completion Certificate" <?php echo $filters['request_type'] === 'Completion Certificate' ? 'selected' : ''; ?>>Completion Certificate</option>
                    <option value="Degree Certificate" <?php echo $filters['request_type'] === 'Degree Certificate' ? 'selected' : ''; ?>>Degree Certificate</option>
                    <option value="Transcript" <?php echo $filters['request_type'] === 'Transcript' ? 'selected' : ''; ?>>Transcript</option>
                    <option value="Character Certificate" <?php echo $filters['request_type'] === 'Character Certificate' ? 'selected' : ''; ?>>Character Certificate</option>
                    <?php foreach ($requestTypes as $type): ?>
                        <?php if (!in_array($type, ['Completion Certificate', 'Degree Certificate', 'Transcript', 'Character Certificate'])): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" 
                                    <?php echo $filters['request_type'] === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="sort_by" class="form-label">Sort By</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="requested_at" <?php echo $filters['sort_by'] === 'requested_at' ? 'selected' : ''; ?>>Request Date</option>
                    <option value="student_name" <?php echo $filters['sort_by'] === 'student_name' ? 'selected' : ''; ?>>Student Name</option>
                    <option value="request_type" <?php echo $filters['sort_by'] === 'request_type' ? 'selected' : ''; ?>>Certificate Type</option>
                    <option value="status" <?php echo $filters['sort_by'] === 'status' ? 'selected' : ''; ?>>Status</option>
                    <option value="processed_at" <?php echo $filters['sort_by'] === 'processed_at' ? 'selected' : ''; ?>>Processed Date</option>
                </select>
            </div>
            
            <div class="col-md-1">
                <label for="sort_order" class="form-label">Order</label>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="DESC" <?php echo $filters['sort_order'] === 'DESC' ? 'selected' : ''; ?>>Desc</option>
                    <option value="ASC" <?php echo $filters['sort_order'] === 'ASC' ? 'selected' : ''; ?>>Asc</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Request Date Range</label>
                <div class="input-group">
                    <input type="date" 
                           class="form-control" 
                           name="date_from" 
                           value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                    <span class="input-group-text">to</span>
                    <input type="date" 
                           class="form-control" 
                           name="date_to" 
                           value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <a href="certificates.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <strong><?php echo number_format($totalRecords); ?></strong> certificate requests found
        <?php if ($totalRecords > 0): ?>
            (showing <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $totalRecords); ?>)
        <?php endif; ?>
    </div>
    
    <?php if ($totalRecords > 0): ?>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllRequests()">
                <i class="fas fa-check-square"></i> Select All
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                <i class="fas fa-square"></i> Clear Selection
            </button>
        </div>
    <?php endif; ?>
</div>

<?php if ($totalRecords > 0): ?>
    <form method="POST" id="bulkForm">
        <!-- Bulk Actions -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <select class="form-select" name="bulk_action" required>
                            <option value="">Select Bulk Action</option>
                            <option value="export_csv">Export to CSV</option>
                            <option value="cancel_pending">Cancel Pending Requests</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning" onclick="return confirmBulkAction()">
                            <i class="fas fa-cogs"></i> Apply to Selected
                        </button>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="selectedCount">0</span> requests selected
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Certificate Requests Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllRequests()">
                                </th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Certificate Type</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Processed Date</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificateRequests as $request): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="selected_requests[]" 
                                               value="<?php echo $request['id']; ?>"
                                               class="request-checkbox"
                                               onchange="updateSelectedCount()">
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($request['student_id']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($request['student_name']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['course']); ?></td>
                                    <td><?php echo htmlspecialchars($request['request_type']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($request['status']) {
                                            case 'pending':
                                                $statusClass = 'bg-warning';
                                                break;
                                            case 'approved':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($request['requested_at'])); ?>
                                        <br>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($request['requested_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($request['processed_at']): ?>
                                            <?php echo date('M j, Y', strtotime($request['processed_at'])); ?>
                                            <br>
                                            <small class="text-muted">
                                                by <?php echo htmlspecialchars($request['approved_by_name'] ?? 'Admin'); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-primary btn-sm"
                                                    onclick="viewRequest(<?php echo $request['id']; ?>)"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($request['status'] === 'approved' && $request['certificate_path']): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-sm"
                                                        onclick="downloadCertificate(<?php echo $request['id']; ?>)"
                                                        title="Download Certificate">
                                                    <i class="fas fa-download"></i>
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
    </form>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Certificate requests pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
    
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
            <h5>No Certificate Requests Found</h5>
            <p class="text-muted">No certificate requests match your current filters.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
                <i class="fas fa-plus"></i> Submit First Request
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- New Certificate Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestModalLabel">
                    <i class="fas fa-plus"></i> New Certificate Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Please correct the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="modal_request_type" class="form-label">Certificate Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_request_type" name="request_type" required>
                            <option value="">Select Certificate Type</option>
                            <option value="Completion Certificate">Completion Certificate</option>
                            <option value="Degree Certificate">Degree Certificate</option>
                            <option value="Transcript">Transcript</option>
                            <option value="Character Certificate">Character Certificate</option>
                            <option value="Migration Certificate">Migration Certificate</option>
                            <option value="Provisional Certificate">Provisional Certificate</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Students <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <div class="mb-2">
                                <input type="text" 
                                       class="form-control" 
                                       id="studentSearch" 
                                       placeholder="Search students..."
                                       onkeyup="filterStudents()">
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="selectAllStudents" onchange="toggleAllStudents()">
                                <label class="form-check-label" for="selectAllStudents">
                                    <strong>Select All Students</strong>
                                </label>
                            </div>
                            <hr>
                            <?php foreach ($students as $student): ?>
                                <div class="form-check student-item">
                                    <input type="checkbox" 
                                           class="form-check-input student-checkbox" 
                                           name="student_ids[]" 
                                           value="<?php echo $student['id']; ?>"
                                           id="student_<?php echo $student['id']; ?>"
                                           onchange="updateStudentSelection()">
                                    <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                        <strong><?php echo htmlspecialchars($student['student_id']); ?></strong> - 
                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text">
                            <span id="selectedStudentCount">0</span> student(s) selected
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_request" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Student selection functions
function filterStudents() {
    const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
    const studentItems = document.querySelectorAll('.student-item');
    
    studentItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function toggleAllStudents() {
    const selectAll = document.getElementById('selectAllStudents');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const visibleCheckboxes = Array.from(checkboxes).filter(cb => 
        cb.closest('.student-item').style.display !== 'none'
    );
    
    visibleCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateStudentSelection();
}

function updateStudentSelection() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedStudentCount').textContent = count;
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.student-checkbox');
    const visibleCheckboxes = Array.from(allCheckboxes).filter(cb => 
        cb.closest('.student-item').style.display !== 'none'
    );
    const checkedVisibleCheckboxes = visibleCheckboxes.filter(cb => cb.checked);
    const selectAll = document.getElementById('selectAllStudents');
    
    if (checkedVisibleCheckboxes.length === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
    } else if (checkedVisibleCheckboxes.length === visibleCheckboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
    } else {
        selectAll.indeterminate = true;
    }
}

// Bulk operations for certificate requests
function toggleAllRequests() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.request-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function selectAllRequests() {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAll').checked = true;
    updateSelectedCount();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.request-checkbox');
    const selectAll = document.getElementById('selectAll');
    
    if (count === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
    } else if (count === allCheckboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
    } else {
        selectAll.indeterminate = true;
    }
}

function confirmBulkAction() {
    const selectedCount = document.querySelectorAll('.request-checkbox:checked').length;
    const action = document.querySelector('select[name="bulk_action"]').value;
    
    if (selectedCount === 0) {
        alert('Please select at least one certificate request');
        return false;
    }
    
    if (!action) {
        alert('Please select a bulk action');
        return false;
    }
    
    let actionText = '';
    switch (action) {
        case 'export_csv':
            actionText = 'export to CSV';
            break;
        case 'cancel_pending':
            actionText = 'cancel (delete)';
            break;
    }
    
    return confirm(`Are you sure you want to ${actionText} ${selectedCount} selected certificate request(s)?`);
}

function viewRequest(requestId) {
    // Implement request view functionality
    alert('View request functionality - Request ID: ' + requestId);
}

function downloadCertificate(requestId) {
    // Implement certificate download functionality
    alert('Download certificate functionality - Request ID: ' + requestId);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    updateStudentSelection();
    
    // Show modal if there are errors
    <?php if (!empty($errors)): ?>
        var requestModal = new bootstrap.Modal(document.getElementById('requestModal'));
        requestModal.show();
    <?php endif; ?>
});
</script>

<?php renderFacultyFooter(); ?>