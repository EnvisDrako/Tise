<?php
/**
 * Admin Institution Management
 * Full CRUD operations for institutions with search and sorting
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Page title
$pageTitle = 'Institution Management';

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_institution':
                    $name = sanitizeInput($_POST['name']);
                    $code = sanitizeInput($_POST['code']);
                    $address = sanitizeInput($_POST['address']);
                    $phone = sanitizeInput($_POST['phone']);
                    $email = sanitizeInput($_POST['email']);
                    $established_date = $_POST['established_date'] ?: null;
                    
                    // Validation
                    if (empty($name) || empty($code)) {
                        throw new Exception("Institution name and code are required.");
                    }
                    
                    if (!empty($email) && !isValidEmail($email)) {
                        throw new Exception("Invalid email address.");
                    }
                    
                    if (!empty($phone) && !isValidPhone($phone)) {
                        throw new Exception("Invalid phone number.");
                    }
                    
                    // Check if code already exists
                    $existing = fetchOne("SELECT id FROM institutions WHERE code = ?", [$code]);
                    if ($existing) {
                        throw new Exception("Institution code already exists.");
                    }
                    
                    // Create institution
                    $query = "INSERT INTO institutions (name, code, address, phone, email, established_date, is_active, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
                    executeQuery($query, [$name, $code, $address, $phone, $email, $established_date]);
                    
                    $success_message = "Institution created successfully!";
                    logEvent("Admin created new institution: {$name} (Code: {$code})", 'INFO');
                    break;
                    
                case 'update_institution':
                    $institution_id = (int)$_POST['institution_id'];
                    $name = sanitizeInput($_POST['name']);
                    $code = sanitizeInput($_POST['code']);
                    $address = sanitizeInput($_POST['address']);
                    $phone = sanitizeInput($_POST['phone']);
                    $email = sanitizeInput($_POST['email']);
                    $established_date = $_POST['established_date'] ?: null;
                    $is_active = isset($_POST['is_active']) ? 1 : 0;
                    
                    // Validation
                    if (empty($name) || empty($code)) {
                        throw new Exception("Institution name and code are required.");
                    }
                    
                    if (!empty($email) && !isValidEmail($email)) {
                        throw new Exception("Invalid email address.");
                    }
                    
                    // Check if code already exists for other institutions
                    $existing = fetchOne("SELECT id FROM institutions WHERE code = ? AND id != ?", [$code, $institution_id]);
                    if ($existing) {
                        throw new Exception("Institution code already exists for another institution.");
                    }
                    
                    // Update institution
                    $query = "UPDATE institutions SET name = ?, code = ?, address = ?, phone = ?, email = ?, established_date = ?, is_active = ? WHERE id = ?";
                    executeQuery($query, [$name, $code, $address, $phone, $email, $established_date, $is_active, $institution_id]);
                    
                    $success_message = "Institution updated successfully!";
                    logEvent("Admin updated institution ID: {$institution_id}", 'INFO');
                    break;
                    
                case 'delete_institution':
                    $institution_id = (int)$_POST['institution_id'];
                    
                    // Check if institution has associated users or students
                    $userCount = fetchOne("SELECT COUNT(*) as count FROM users WHERE institution_id = ?", [$institution_id])['count'];
                    $studentCount = fetchOne("SELECT COUNT(*) as count FROM students WHERE institution_id = ?", [$institution_id])['count'];
                    
                    if ($userCount > 0 || $studentCount > 0) {
                        throw new Exception("Cannot delete institution with associated users or students. Please reassign or remove them first.");
                    }
                    
                    // Delete institution
                    executeQuery("DELETE FROM institutions WHERE id = ?", [$institution_id]);
                    
                    $success_message = "Institution deleted successfully!";
                    logEvent("Admin deleted institution ID: {$institution_id}", 'INFO');
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error in institution management: " . $e->getMessage(), 'ERROR');
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;

// Build filters
$filters = [];
if ($search) $filters['search'] = $search;
if ($status_filter !== '') $filters['is_active'] = $status_filter;

// Get filtered institutions
$institutions = getFilteredInstitutions($filters, $sort_by, $sort_order, $page, $per_page);
$total_institutions = getInstitutionCount($filters);
$pagination = getPaginationData($total_institutions, $page, $per_page);

// Get institution statistics
$institutionStats = getInstitutionStatistics();

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-university"></i> Institution Management
        </h1>
        <p class="lead">Manage educational institutions with search and sorting capabilities</p>
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

<!-- Institution Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo number_format($institutionStats['total']); ?></h4>
                        <p class="mb-0">Total Institutions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-university fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo number_format($institutionStats['active']); ?></h4>
                        <p class="mb-0">Active Institutions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo number_format($institutionStats['inactive']); ?></h4>
                        <p class="mb-0">Inactive Institutions</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-pause-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo number_format($institutionStats['total_students']); ?></h4>
                        <p class="mb-0">Total Students</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-graduate fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="filter-section">
            <h5><i class="fas fa-filter"></i> Filter Institutions</h5>
            <form method="GET" class="row g-3" id="filterForm">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Name, code, address..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">Status</label>
                    <select name="status_filter" id="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="institutions.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createInstitutionModal">
                            <i class="fas fa-plus"></i> Add Institution
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Institutions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> Institutions (<?php echo number_format($total_institutions); ?> total)</h5>
                <div class="card-tools">
                    <span class="badge bg-primary"><?php echo count($institutions); ?> shown</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($institutions)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-university fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Institutions Found</h5>
                        <p class="text-muted">No institutions match your current search criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'id', 'sort_order' => $sort_by === 'id' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            ID <?php echo $sort_by === 'id' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'name', 'sort_order' => $sort_by === 'name' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Name <?php echo $sort_by === 'name' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'code', 'sort_order' => $sort_by === 'code' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Code <?php echo $sort_by === 'code' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Contact Info</th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'established_date', 'sort_order' => $sort_by === 'established_date' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Established <?php echo $sort_by === 'established_date' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Students</th>
                                    <th>Faculty</th>
                                    <th>Status</th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'created_at', 'sort_order' => $sort_by === 'created_at' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Created <?php echo $sort_by === 'created_at' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($institutions as $institution): ?>
                                    <tr>
                                        <td><?php echo $institution['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
                                            <?php if ($institution['address']): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i> 
                                                    <?php echo htmlspecialchars(substr($institution['address'], 0, 50)) . (strlen($institution['address']) > 50 ? '...' : ''); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($institution['code']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($institution['email']): ?>
                                                <div>
                                                    <i class="fas fa-envelope"></i>
                                                    <a href="mailto:<?php echo htmlspecialchars($institution['email']); ?>">
                                                        <?php echo htmlspecialchars($institution['email']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($institution['phone']): ?>
                                                <div>
                                                    <i class="fas fa-phone"></i>
                                                    <a href="tel:<?php echo htmlspecialchars($institution['phone']); ?>">
                                                        <?php echo htmlspecialchars($institution['phone']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!$institution['email'] && !$institution['phone']): ?>
                                                <span class="text-muted">No contact info</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($institution['established_date']): ?>
                                                <?php echo date('M j, Y', strtotime($institution['established_date'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo number_format($institution['student_count']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo number_format($institution['faculty_count']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo $institution['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $institution['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($institution['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info" 
                                                        onclick="viewInstitution(<?php echo htmlspecialchars(json_encode($institution)); ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editInstitution(<?php echo htmlspecialchars(json_encode($institution)); ?>)"
                                                        title="Edit Institution">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteInstitution(<?php echo $institution['id']; ?>, '<?php echo htmlspecialchars($institution['name']); ?>')"
                                                        title="Delete Institution">
                                                    <i class="fas fa-trash"></i>
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
                        <nav aria-label="Institutions pagination" class="mt-3">
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
</div><!-
- Create Institution Modal -->
<div class="modal fade" id="createInstitutionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-university"></i> Create New Institution
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_institution">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="create_name" class="form-label">Institution Name *</label>
                            <input type="text" name="name" id="create_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="create_code" class="form-label">Institution Code *</label>
                            <input type="text" name="code" id="create_code" class="form-control" required 
                                   placeholder="e.g., INST001">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="create_address" class="form-label">Address</label>
                        <textarea name="address" id="create_address" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_phone" class="form-label">Phone</label>
                            <input type="tel" name="phone" id="create_phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label">Email</label>
                            <input type="email" name="email" id="create_email" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="create_established_date" class="form-label">Established Date</label>
                        <input type="date" name="established_date" id="create_established_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Institution
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Institution Modal -->
<div class="modal fade" id="editInstitutionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-university"></i> Edit Institution
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_institution">
                    <input type="hidden" name="institution_id" id="edit_institution_id">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="edit_name" class="form-label">Institution Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_code" class="form-label">Institution Code *</label>
                            <input type="text" name="code" id="edit_code" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="tel" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_established_date" class="form-label">Established Date</label>
                            <input type="date" name="established_date" id="edit_established_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input" value="1">
                                <label for="edit_is_active" class="form-check-label">Active Institution</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Institution
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Institution Modal -->
<div class="modal fade" id="viewInstitutionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-university"></i> Institution Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="institutionDetails">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.table th a {
    color: inherit;
    text-decoration: none;
}

.table th a:hover {
    color: #667eea;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

.card.bg-primary, .card.bg-success, .card.bg-warning, .card.bg-info {
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>

<script>
// Edit institution functionality
function editInstitution(institution) {
    document.getElementById('edit_institution_id').value = institution.id;
    document.getElementById('edit_name').value = institution.name;
    document.getElementById('edit_code').value = institution.code;
    document.getElementById('edit_address').value = institution.address || '';
    document.getElementById('edit_phone').value = institution.phone || '';
    document.getElementById('edit_email').value = institution.email || '';
    document.getElementById('edit_established_date').value = institution.established_date || '';
    document.getElementById('edit_is_active').checked = institution.is_active == 1;
    
    const modal = new bootstrap.Modal(document.getElementById('editInstitutionModal'));
    modal.show();
}

// View institution functionality
function viewInstitution(institution) {
    const detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-university"></i> Basic Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>${escapeHtml(institution.name)}</td>
                    </tr>
                    <tr>
                        <td><strong>Code:</strong></td>
                        <td><span class="badge bg-secondary">${escapeHtml(institution.code)}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge status-${institution.is_active ? 'active' : 'inactive'}">${institution.is_active ? 'Active' : 'Inactive'}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Established:</strong></td>
                        <td>${institution.established_date ? new Date(institution.established_date).toLocaleDateString() : 'Not specified'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-address-card"></i> Contact Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${institution.email ? `<a href="mailto:${escapeHtml(institution.email)}">${escapeHtml(institution.email)}</a>` : 'Not provided'}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>${institution.phone ? `<a href="tel:${escapeHtml(institution.phone)}">${escapeHtml(institution.phone)}</a>` : 'Not provided'}</td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>${institution.address ? escapeHtml(institution.address) : 'Not provided'}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6><i class="fas fa-chart-bar"></i> Statistics</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>${institution.student_count || 0}</h4>
                                <p class="mb-0">Students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>${institution.faculty_count || 0}</h4>
                                <p class="mb-0">Faculty</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>${new Date(institution.created_at).toLocaleDateString()}</h4>
                                <p class="mb-0">Created</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('institutionDetails').innerHTML = detailsHtml;
    const modal = new bootstrap.Modal(document.getElementById('viewInstitutionModal'));
    modal.show();
}

// Delete institution functionality
function deleteInstitution(institutionId, institutionName) {
    if (confirm(`Are you sure you want to delete institution "${institutionName}"? This action cannot be undone and will fail if the institution has associated users or students.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_institution">
            <input type="hidden" name="institution_id" value="${institutionId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status_filter');
    
    // Debounced search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
    
    // Immediate filter on status change
    statusFilter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});
</script>

<?php
/**
 * Helper functions for institution management
 */

function getFilteredInstitutions($filters = [], $sort_by = 'created_at', $sort_order = 'DESC', $page = 1, $per_page = 25) {
    $whereConditions = [];
    $params = [];
    
    // Apply filters
    if (!empty($filters['search'])) {
        $whereConditions[] = "(i.name LIKE ? OR i.code LIKE ? OR i.address LIKE ? OR i.email LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (isset($filters['is_active'])) {
        $whereConditions[] = "i.is_active = ?";
        $params[] = $filters['is_active'];
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Validate sort parameters
    $allowedSorts = ['id', 'name', 'code', 'established_date', 'created_at'];
    if (!in_array($sort_by, $allowedSorts)) {
        $sort_by = 'created_at';
    }
    
    $sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    $query = "
        SELECT i.*,
               COUNT(DISTINCT s.id) as student_count,
               COUNT(DISTINCT u.id) as faculty_count
        FROM institutions i
        LEFT JOIN students s ON i.id = s.institution_id
        LEFT JOIN users u ON i.id = u.institution_id AND u.role = 'faculty'
        {$whereClause}
        GROUP BY i.id
        ORDER BY i.{$sort_by} {$sort_order}
        LIMIT {$per_page} OFFSET {$offset}
    ";
    
    return fetchAll($query, $params);
}

function getInstitutionCount($filters = []) {
    $whereConditions = [];
    $params = [];
    
    // Apply same filters as getFilteredInstitutions
    if (!empty($filters['search'])) {
        $whereConditions[] = "(name LIKE ? OR code LIKE ? OR address LIKE ? OR email LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (isset($filters['is_active'])) {
        $whereConditions[] = "is_active = ?";
        $params[] = $filters['is_active'];
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT COUNT(*) as count FROM institutions {$whereClause}";
    $result = fetchOne($query, $params);
    
    return $result['count'] ?? 0;
}

function getInstitutionStatistics() {
    try {
        $stats = fetchOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
            FROM institutions
        ");
        
        $studentStats = fetchOne("
            SELECT COUNT(*) as total_students
            FROM students s
            INNER JOIN institutions i ON s.institution_id = i.id
            WHERE i.is_active = 1
        ");
        
        return [
            'total' => $stats['total'] ?? 0,
            'active' => $stats['active'] ?? 0,
            'inactive' => $stats['inactive'] ?? 0,
            'total_students' => $studentStats['total_students'] ?? 0
        ];
        
    } catch (Exception $e) {
        logEvent("Error fetching institution statistics: " . $e->getMessage(), 'ERROR');
        return [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'total_students' => 0
        ];
    }
}

renderAdminFooter();
?>