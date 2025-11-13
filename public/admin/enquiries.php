<?php
/**
 * Admin Enquiries Management
 * Comprehensive enquiry management with filtering, assignment, and bulk operations
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Page title
$pageTitle = 'Manage Enquiries';

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'assign_enquiry':
                    $enquiry_id = (int)$_POST['enquiry_id'];
                    $assigned_to = (int)$_POST['assigned_to'];
                    
                    executeQuery("UPDATE enquiries SET assigned_to_user_id = ?, status = 'assigned' WHERE id = ?", [$assigned_to, $enquiry_id]);
                    
                    $success_message = "Enquiry assigned successfully!";
                    logEvent("Admin assigned enquiry ID {$enquiry_id} to user ID {$assigned_to}", 'INFO');
                    break;
                    
                case 'update_status':
                    $enquiry_id = (int)$_POST['enquiry_id'];
                    $status = sanitizeInput($_POST['status']);
                    
                    executeQuery("UPDATE enquiries SET status = ? WHERE id = ?", [$status, $enquiry_id]);
                    
                    $success_message = "Enquiry status updated successfully!";
                    logEvent("Admin updated enquiry ID {$enquiry_id} status to {$status}", 'INFO');
                    break;
                    
                case 'bulk_action':
                    $selected_enquiries = $_POST['selected_enquiries'] ?? [];
                    $bulk_action = $_POST['bulk_action'];
                    
                    if (empty($selected_enquiries)) {
                        throw new Exception("No enquiries selected for bulk action.");
                    }
                    
                    $placeholders = str_repeat('?,', count($selected_enquiries) - 1) . '?';
                    
                    switch ($bulk_action) {
                        case 'assign':
                            $assigned_to = (int)$_POST['bulk_assigned_to'];
                            executeQuery("UPDATE enquiries SET assigned_to_user_id = ?, status = 'assigned' WHERE id IN ({$placeholders})", 
                                       array_merge([$assigned_to], $selected_enquiries));
                            $success_message = count($selected_enquiries) . " enquiries assigned successfully!";
                            break;
                        case 'status_new':
                            executeQuery("UPDATE enquiries SET status = 'new' WHERE id IN ({$placeholders})", $selected_enquiries);
                            $success_message = count($selected_enquiries) . " enquiries marked as new!";
                            break;
                        case 'status_contacted':
                            executeQuery("UPDATE enquiries SET status = 'contacted' WHERE id IN ({$placeholders})", $selected_enquiries);
                            $success_message = count($selected_enquiries) . " enquiries marked as contacted!";
                            break;
                        case 'status_interested':
                            executeQuery("UPDATE enquiries SET status = 'interested' WHERE id IN ({$placeholders})", $selected_enquiries);
                            $success_message = count($selected_enquiries) . " enquiries marked as interested!";
                            break;
                        case 'status_not_interested':
                            executeQuery("UPDATE enquiries SET status = 'not_interested' WHERE id IN ({$placeholders})", $selected_enquiries);
                            $success_message = count($selected_enquiries) . " enquiries marked as not interested!";
                            break;
                        case 'archive':
                            executeQuery("UPDATE enquiries SET status = 'archived' WHERE id IN ({$placeholders})", $selected_enquiries);
                            $success_message = count($selected_enquiries) . " enquiries archived successfully!";
                            break;
                        case 'delete':
                            executeQuery("DELETE FROM enquiries WHERE id IN ({$placeholders})", $selected_enquiries);
                            $success_message = count($selected_enquiries) . " enquiries deleted successfully!";
                            break;
                    }
                    
                    logEvent("Admin performed bulk action '{$bulk_action}' on " . count($selected_enquiries) . " enquiries", 'INFO');
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error in enquiry management: " . $e->getMessage(), 'ERROR');
    }
}

// Handle export requests
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $filters = [];
    
    // Apply filters for export
    if (!empty($_GET['status_filter'])) $filters['status'] = $_GET['status_filter'];
    if (!empty($_GET['assigned_filter'])) $filters['assigned_to_user_id'] = $_GET['assigned_filter'];
    if (!empty($_GET['course_filter'])) $filters['course_interest'] = $_GET['course_filter'];
    if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
    if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
    if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
    
    $enquiries = getAdminFilteredEnquiries($filters);
    
    switch ($format) {
        case 'csv':
            exportEnquiriesCSV($enquiries);
            break;
        case 'excel':
            exportEnquiriesExcel($enquiries);
            break;
        case 'pdf':
            exportEnquiriesPDF($enquiries);
            break;
    }
    exit;
}

// Get filter parameters
$status_filter = $_GET['status_filter'] ?? '';
$assigned_filter = $_GET['assigned_filter'] ?? '';
$course_filter = $_GET['course_filter'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;

// Build filters
$filters = [];
if ($status_filter) $filters['status'] = $status_filter;
if ($assigned_filter) $filters['assigned_to_user_id'] = $assigned_filter;
if ($course_filter) $filters['course_interest'] = $course_filter;
if ($date_from) $filters['date_from'] = $date_from;
if ($date_to) $filters['date_to'] = $date_to;
if ($search) $filters['search'] = $search;

// Get filtered enquiries
$enquiries = getAdminFilteredEnquiries($filters, $sort_by, $sort_order, $page, $per_page);
$total_enquiries = getAdminEnquiryCount($filters);
$pagination = getPaginationData($total_enquiries, $page, $per_page);

// Get enquirer users for assignment dropdown
$enquirers = fetchAll("SELECT id, username, full_name FROM users WHERE role = 'enquirer' AND is_active = 1 ORDER BY full_name");

// Get unique course interests for filter
$courses = fetchAll("SELECT DISTINCT course_interest FROM enquiries WHERE course_interest IS NOT NULL AND course_interest != '' ORDER BY course_interest");

// Get enquiry statistics
$enquiryStats = getEnquiryStatistics();

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-gradient mb-2">
                    <i class="fas fa-envelope"></i> Manage Enquiries
                </h1>
                <p class="lead mb-0">Comprehensive enquiry management with filtering, assignment, and bulk operations</p>
            </div>
            <div>
                <a href="<?php echo url('admin/add_enquiry.php'); ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-plus-circle"></i> Add Enquiry
                </a>
            </div>
        </div>
    </div>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Enquiry Statistics -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enquiryStats['total']); ?></h4>
                <p class="mb-0">Total</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enquiryStats['new']); ?></h4>
                <p class="mb-0">New</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enquiryStats['assigned']); ?></h4>
                <p class="mb-0">Assigned</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enquiryStats['contacted']); ?></h4>
                <p class="mb-0">Contacted</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enquiryStats['unassigned']); ?></h4>
                <p class="mb-0">Unassigned</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-dark text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enquiryStats['today']); ?></h4>
                <p class="mb-0">Today</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="filter-section">
            <h5><i class="fas fa-filter"></i> Filter Enquiries</h5>
            <form method="GET" class="row g-3" id="filterForm">
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">Status</label>
                    <select name="status_filter" id="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="assigned" <?php echo $status_filter === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                        <option value="contacted" <?php echo $status_filter === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                        <option value="interested" <?php echo $status_filter === 'interested' ? 'selected' : ''; ?>>Interested</option>
                        <option value="not_interested" <?php echo $status_filter === 'not_interested' ? 'selected' : ''; ?>>Not Interested</option>
                        <option value="enrolled" <?php echo $status_filter === 'enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="assigned_filter" class="form-label">Assigned To</label>
                    <select name="assigned_filter" id="assigned_filter" class="form-select">
                        <option value="">All Assignments</option>
                        <option value="0" <?php echo $assigned_filter === '0' ? 'selected' : ''; ?>>Unassigned</option>
                        <?php foreach ($enquirers as $enquirer): ?>
                            <option value="<?php echo $enquirer['id']; ?>" 
                                    <?php echo $assigned_filter == $enquirer['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($enquirer['full_name'] ?: $enquirer['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="course_filter" class="form-label">Course</label>
                    <select name="course_filter" id="course_filter" class="form-select">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['course_interest']); ?>" 
                                    <?php echo $course_filter === $course['course_interest'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_interest']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-2">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="button" onclick="document.getElementById('filterForm').submit()" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="enquiries.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('today')">Today</button>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('week')">This Week</button>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('unassigned')">Unassigned</button>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('new')">New Only</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions and Export -->
<div class="row mb-3">
    <div class="col-md-8">
        <form method="POST" id="bulkActionForm" class="d-flex gap-2 align-items-end flex-wrap">
            <input type="hidden" name="action" value="bulk_action">
            <div>
                <label class="form-label">Bulk Actions</label>
                <select name="bulk_action" id="bulk_action" class="form-select">
                    <option value="">Select Action</option>
                    <optgroup label="Assignment">
                        <option value="assign">Assign to Enquirer</option>
                    </optgroup>
                    <optgroup label="Status Updates">
                        <option value="status_new">Mark as New</option>
                        <option value="status_contacted">Mark as Contacted</option>
                        <option value="status_interested">Mark as Interested</option>
                        <option value="status_not_interested">Mark as Not Interested</option>
                    </optgroup>
                    <optgroup label="Management">
                        <option value="archive">Archive Selected</option>
                        <option value="delete">Delete Selected</option>
                    </optgroup>
                </select>
            </div>
            <div id="bulk_assigned_to_container" style="display: none;">
                <label class="form-label">Assign To</label>
                <select name="bulk_assigned_to" id="bulk_assigned_to" class="form-select">
                    <option value="">Select Enquirer</option>
                    <?php foreach ($enquirers as $enquirer): ?>
                        <option value="<?php echo $enquirer['id']; ?>">
                            <?php echo htmlspecialchars($enquirer['full_name'] ?: $enquirer['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-warning" onclick="return confirmBulkAction()">
                <i class="fas fa-cogs"></i> Apply
            </button>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportEnquiries('csv')">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportEnquiries('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportEnquiries('pdf')">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Enquiries Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> Enquiries (<?php echo number_format($total_enquiries); ?> total)</h5>
                <div class="card-tools">
                    <span class="badge bg-primary"><?php echo count($enquiries); ?> shown</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($enquiries)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Enquiries Found</h5>
                        <p class="text-muted">No enquiries match your current filters.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'id', 'sort_order' => $sort_by === 'id' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            ID <?php echo $sort_by === 'id' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'full_name', 'sort_order' => $sort_by === 'full_name' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Name <?php echo $sort_by === 'full_name' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Contact Info</th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'course_interest', 'sort_order' => $sort_by === 'course_interest' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Course Interest <?php echo $sort_by === 'course_interest' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'status', 'sort_order' => $sort_by === 'status' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Status <?php echo $sort_by === 'status' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Assigned To</th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'created_at', 'sort_order' => $sort_by === 'created_at' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Submitted <?php echo $sort_by === 'created_at' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enquiries as $enquiry): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_enquiries[]" value="<?php echo $enquiry['id']; ?>" class="enquiry-checkbox">
                                        </td>
                                        <td><?php echo $enquiry['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($enquiry['full_name']); ?></strong>
                                            <?php if ($enquiry['message']): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-comment"></i> 
                                                    <?php echo htmlspecialchars(substr($enquiry['message'], 0, 50)) . (strlen($enquiry['message']) > 50 ? '...' : ''); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($enquiry['email']): ?>
                                                <div>
                                                    <i class="fas fa-envelope"></i>
                                                    <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>">
                                                        <?php echo htmlspecialchars($enquiry['email']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($enquiry['phone']): ?>
                                                <div>
                                                    <i class="fas fa-phone"></i>
                                                    <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>">
                                                        <?php echo htmlspecialchars($enquiry['phone']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($enquiry['course_interest']): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($enquiry['course_interest']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Not specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo $enquiry['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $enquiry['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($enquiry['assigned_to_user_id']): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($enquiry['assigned_to_name'] ?: $enquiry['assigned_to_username']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-user-slash"></i> Unassigned
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y g:i A', strtotime($enquiry['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="viewEnquiry(<?php echo htmlspecialchars(json_encode($enquiry)); ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="assignEnquiry(<?php echo $enquiry['id']; ?>)"
                                                        title="Assign to Staff">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        onclick="updateStatus(<?php echo $enquiry['id']; ?>, '<?php echo $enquiry['status']; ?>')"
                                                        title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Enquiries pagination" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['has_previous']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- View Enquiry Modal -->
<div class="modal fade" id="viewEnquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope"></i> Enquiry Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="enquiryDetails">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Enquiry Modal -->
<div class="modal fade" id="assignEnquiryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i> Assign Enquiry
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="assign_enquiry">
                    <input type="hidden" name="enquiry_id" id="assignEnquiryId">
                    
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign to Enquirer Staff:</label>
                        <select name="assigned_to" id="assigned_to" class="form-select" required>
                            <option value="">Select Staff Member</option>
                            <?php foreach ($enquirers as $enquirer): ?>
                                <option value="<?php echo $enquirer['id']; ?>">
                                    <?php echo htmlspecialchars($enquirer['full_name'] ?: $enquirer['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if (empty($enquirers)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No enquirer staff members found. Please create enquirer user accounts first.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" <?php echo empty($enquirers) ? 'disabled' : ''; ?>>
                        <i class="fas fa-check"></i> Assign Enquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Update Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="enquiry_id" id="updateStatusEnquiryId">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">New Status:</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="new">New</option>
                            <option value="assigned">Assigned</option>
                            <option value="contacted">Contacted</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="enrolled">Enrolled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.status-new { background-color: #17a2b8; }
.status-assigned { background-color: #ffc107; color: #000; }
.status-contacted { background-color: #28a745; }
.status-interested { background-color: #007bff; }
.status-not_interested { background-color: #6c757d; }
.status-enrolled { background-color: #28a745; }
.status-archived { background-color: #6c757d; }

.table th a {
    color: inherit;
    text-decoration: none;
}

.table th a:hover {
    color: #667eea;
}

.enquiry-checkbox {
    transform: scale(1.2);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

.card.bg-primary, .card.bg-info, .card.bg-warning, .card.bg-success, .card.bg-secondary, .card.bg-dark {
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>

<script>
// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.enquiry-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// View enquiry functionality
function viewEnquiry(enquiry) {
    const detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-user"></i> Personal Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>${escapeHtml(enquiry.full_name)}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><a href="mailto:${escapeHtml(enquiry.email)}">${escapeHtml(enquiry.email)}</a></td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td><a href="tel:${escapeHtml(enquiry.phone)}">${escapeHtml(enquiry.phone)}</a></td>
                    </tr>
                    <tr>
                        <td><strong>Course Interest:</strong></td>
                        <td>${enquiry.course_interest ? escapeHtml(enquiry.course_interest) : 'Not specified'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-info-circle"></i> Status Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge status-${enquiry.status}">${enquiry.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Assigned To:</strong></td>
                        <td>${enquiry.assigned_to_name ? escapeHtml(enquiry.assigned_to_name) : 'Unassigned'}</td>
                    </tr>
                    <tr>
                        <td><strong>Submitted:</strong></td>
                        <td>${new Date(enquiry.created_at).toLocaleString()}</td>
                    </tr>
                    <tr>
                        <td><strong>Documents:</strong></td>
                        <td>${enquiry.documents_path ? '<i class="fas fa-paperclip"></i> Attached' : 'None'}</td>
                    </tr>
                </table>
            </div>
        </div>
        ${enquiry.message ? `
        <div class="row mt-3">
            <div class="col-12">
                <h6><i class="fas fa-comment"></i> Message</h6>
                <div class="alert alert-light">
                    ${escapeHtml(enquiry.message)}
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('enquiryDetails').innerHTML = detailsHtml;
    const modal = new bootstrap.Modal(document.getElementById('viewEnquiryModal'));
    modal.show();
}

// Assign enquiry functionality
function assignEnquiry(enquiryId) {
    document.getElementById('assignEnquiryId').value = enquiryId;
    const modal = new bootstrap.Modal(document.getElementById('assignEnquiryModal'));
    modal.show();
}

// Update status functionality
function updateStatus(enquiryId, currentStatus) {
    document.getElementById('updateStatusEnquiryId').value = enquiryId;
    document.getElementById('status').value = currentStatus;
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

// Bulk action confirmation
function confirmBulkAction() {
    const selected = document.querySelectorAll('.enquiry-checkbox:checked');
    const action = document.getElementById('bulk_action').value;
    
    if (selected.length === 0) {
        alert('Please select at least one enquiry.');
        return false;
    }
    
    if (!action) {
        alert('Please select a bulk action.');
        return false;
    }
    
    if (action === 'assign') {
        const assignedTo = document.getElementById('bulk_assigned_to').value;
        if (!assignedTo) {
            alert('Please select an enquirer to assign to.');
            return false;
        }
    }
    
    const actionText = action.replace('status_', '').replace('_', ' ');
    return confirm(`Are you sure you want to ${actionText} ${selected.length} selected enquiry(ies)?`);
}

// Export functionality
function exportEnquiries(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    
    const url = `?${params.toString()}`;
    window.open(url, '_blank');
}

// Quick filter functionality
function setQuickFilter(type) {
    const form = document.getElementById('filterForm');
    
    // Clear existing filters
    form.querySelectorAll('input, select').forEach(input => {
        if (input.name !== 'search') {
            input.value = '';
        }
    });
    
    const today = new Date().toISOString().split('T')[0];
    const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    switch (type) {
        case 'today':
            document.getElementById('date_from').value = today;
            document.getElementById('date_to').value = today;
            break;
        case 'week':
            document.getElementById('date_from').value = weekAgo;
            document.getElementById('date_to').value = today;
            break;
        case 'unassigned':
            document.getElementById('assigned_filter').value = '0';
            break;
        case 'new':
            document.getElementById('status_filter').value = 'new';
            break;
    }
    
    form.submit();
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show/hide bulk assignment dropdown
document.addEventListener('DOMContentLoaded', function() {
    const bulkActionSelect = document.getElementById('bulk_action');
    const bulkAssignedToContainer = document.getElementById('bulk_assigned_to_container');
    
    bulkActionSelect.addEventListener('change', function() {
        if (this.value === 'assign') {
            bulkAssignedToContainer.style.display = 'block';
        } else {
            bulkAssignedToContainer.style.display = 'none';
        }
    });
    
    // Update bulk action form with selected checkboxes
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        const selected = document.querySelectorAll('.enquiry-checkbox:checked');
        
        // Clear existing hidden inputs
        const existingInputs = this.querySelectorAll('input[name="selected_enquiries[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected enquiry IDs
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_enquiries[]';
            input.value = checkbox.value;
            this.appendChild(input);
        });
    });
    
    // Auto-submit form on filter change
    const filterInputs = document.querySelectorAll('#filterForm select, #filterForm input[type="date"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // Debounced search
    const searchInput = document.getElementById('search');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
});
</script>

<?php
/**
 * Helper functions for enquiry management
 * Note: getFilteredEnquiries() is defined in includes/functions.php
 */

function getEnquiryCount($filters = []) {
    $whereConditions = [];
    $params = [];
    
    // Apply same filters as getFilteredEnquiries
    if (!empty($filters['status'])) {
        $whereConditions[] = "status = ?";
        $params[] = $filters['status'];
    }
    
    if (isset($filters['assigned_to_user_id'])) {
        if ($filters['assigned_to_user_id'] === '0') {
            $whereConditions[] = "assigned_to_user_id IS NULL";
        } else {
            $whereConditions[] = "assigned_to_user_id = ?";
            $params[] = $filters['assigned_to_user_id'];
        }
    }
    
    if (!empty($filters['course_interest'])) {
        $whereConditions[] = "course_interest = ?";
        $params[] = $filters['course_interest'];
    }
    
    if (!empty($filters['date_from'])) {
        $whereConditions[] = "DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $whereConditions[] = "DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['search'])) {
        $whereConditions[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT COUNT(*) as count FROM enquiries {$whereClause}";
    $result = fetchOne($query, $params);
    
    return $result['count'] ?? 0;
}

function getEnquiryStatistics() {
    try {
        $stats = fetchOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
                SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned,
                SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
                SUM(CASE WHEN assigned_to_user_id IS NULL THEN 1 ELSE 0 END) as unassigned,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
            FROM enquiries
        ");
        
        return [
            'total' => $stats['total'] ?? 0,
            'new' => $stats['new'] ?? 0,
            'assigned' => $stats['assigned'] ?? 0,
            'contacted' => $stats['contacted'] ?? 0,
            'unassigned' => $stats['unassigned'] ?? 0,
            'today' => $stats['today'] ?? 0
        ];
        
    } catch (Exception $e) {
        logEvent("Error fetching enquiry statistics: " . $e->getMessage(), 'ERROR');
        return [
            'total' => 0,
            'new' => 0,
            'assigned' => 0,
            'contacted' => 0,
            'unassigned' => 0,
            'today' => 0
        ];
    }
}

function exportEnquiriesCSV($enquiries) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="enquiries_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Course Interest', 'Status', 'Assigned To', 'Message', 'Submitted']);
    
    // CSV data
    foreach ($enquiries as $enquiry) {
        fputcsv($output, [
            $enquiry['id'],
            $enquiry['full_name'],
            $enquiry['email'],
            $enquiry['phone'],
            $enquiry['course_interest'],
            $enquiry['status'],
            $enquiry['assigned_to_name'] ?: 'Unassigned',
            $enquiry['message'],
            date('Y-m-d H:i:s', strtotime($enquiry['created_at']))
        ]);
    }
    
    fclose($output);
}

function exportEnquiriesExcel($enquiries) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="enquiries_export_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Course Interest</th><th>Status</th><th>Assigned To</th><th>Message</th><th>Submitted</th></tr>';
    
    foreach ($enquiries as $enquiry) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($enquiry['id']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['email']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['course_interest']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['status']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['assigned_to_name'] ?: 'Unassigned') . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['message']) . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($enquiry['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
}

function exportEnquiriesPDF($enquiries) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="enquiries_export_' . date('Y-m-d') . '.pdf"');
    
    echo '<!DOCTYPE html><html><head><title>Enquiries Export</title>';
    echo '<style>table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#f2f2f2;}</style>';
    echo '</head><body>';
    echo '<h1>Enquiries Export - ' . date('Y-m-d') . '</h1>';
    echo '<table>';
    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Course</th><th>Status</th><th>Assigned To</th><th>Submitted</th></tr>';
    
    foreach ($enquiries as $enquiry) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($enquiry['id']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['email']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['course_interest']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['status']) . '</td>';
        echo '<td>' . htmlspecialchars($enquiry['assigned_to_name'] ?: 'Unassigned') . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($enquiry['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</table></body></html>';
}

renderAdminFooter();
?>