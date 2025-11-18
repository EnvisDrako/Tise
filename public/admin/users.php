<?php
/**
 * Admin User Management
 * Comprehensive user management with advanced filtering and bulk operations
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Page title
$pageTitle = 'User Management';

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_user':
                    $username = sanitizeInput($_POST['username']);
                    $password = $_POST['password'];
                    $role = sanitizeInput($_POST['role']);
                    $institution_id = !empty($_POST['institution_id']) ? (int)$_POST['institution_id'] : null;
                    $full_name = sanitizeInput($_POST['full_name']);
                    $email = sanitizeInput($_POST['email']);
                    $phone = sanitizeInput($_POST['phone']);
                    
                    // Validation
                    if (empty($username) || empty($password) || empty($role) || empty($full_name) || empty($email)) {
                        throw new Exception("All required fields must be filled.");
                    }
                    
                    if (!isValidEmail($email)) {
                        throw new Exception("Invalid email address.");
                    }
                    
                    if (!empty($phone) && !isValidPhone($phone)) {
                        throw new Exception("Invalid phone number.");
                    }
                    
                    // Check if username or email already exists
                    $existing = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
                    if ($existing) {
                        throw new Exception("Username or email already exists.");
                    }
                    
                    // Create user
                    $passwordHash = hashPassword($password);
                    $query = "INSERT INTO users (username, password_hash, role, institution_id, full_name, email, phone, is_active, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
                    executeQuery($query, [$username, $passwordHash, $role, $institution_id, $full_name, $email, $phone]);
                    
                    $success_message = "User created successfully!";
                    logEvent("Admin created new user: {$username} (Role: {$role})", 'INFO');
                    break;
                    
                case 'update_user':
                    $user_id = (int)$_POST['user_id'];
                    $username = sanitizeInput($_POST['username']);
                    $role = sanitizeInput($_POST['role']);
                    $institution_id = !empty($_POST['institution_id']) ? (int)$_POST['institution_id'] : null;
                    $full_name = sanitizeInput($_POST['full_name']);
                    $email = sanitizeInput($_POST['email']);
                    $phone = sanitizeInput($_POST['phone']);
                    $is_active = isset($_POST['is_active']) ? 1 : 0;
                    
                    // Validation
                    if (empty($username) || empty($role) || empty($full_name) || empty($email)) {
                        throw new Exception("All required fields must be filled.");
                    }
                    
                    if (!isValidEmail($email)) {
                        throw new Exception("Invalid email address.");
                    }
                    
                    // Check if username or email already exists for other users
                    $existing = fetchOne("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?", [$username, $email, $user_id]);
                    if ($existing) {
                        throw new Exception("Username or email already exists for another user.");
                    }
                    
                    // Update user
                    $query = "UPDATE users SET username = ?, role = ?, institution_id = ?, full_name = ?, email = ?, phone = ?, is_active = ? WHERE id = ?";
                    executeQuery($query, [$username, $role, $institution_id, $full_name, $email, $phone, $is_active, $user_id]);
                    
                    // Update password if provided
                    if (!empty($_POST['password'])) {
                        $passwordHash = hashPassword($_POST['password']);
                        executeQuery("UPDATE users SET password_hash = ? WHERE id = ?", [$passwordHash, $user_id]);
                    }
                    
                    $success_message = "User updated successfully!";
                    logEvent("Admin updated user ID: {$user_id}", 'INFO');
                    break;
                    
                case 'bulk_action':
                    $selected_users = $_POST['selected_users'] ?? [];
                    $bulk_action = $_POST['bulk_action'];
                    
                    if (empty($selected_users)) {
                        throw new Exception("No users selected for bulk action.");
                    }
                    
                    $placeholders = str_repeat('?,', count($selected_users) - 1) . '?';
                    
                    switch ($bulk_action) {
                        case 'activate':
                            executeQuery("UPDATE users SET is_active = 1 WHERE id IN ({$placeholders})", $selected_users);
                            $success_message = count($selected_users) . " users activated successfully!";
                            break;
                        case 'deactivate':
                            executeQuery("UPDATE users SET is_active = 0 WHERE id IN ({$placeholders})", $selected_users);
                            $success_message = count($selected_users) . " users deactivated successfully!";
                            break;
                        case 'delete':
                            executeQuery("DELETE FROM users WHERE id IN ({$placeholders})", $selected_users);
                            $success_message = count($selected_users) . " users deleted successfully!";
                            break;
                    }
                    
                    logEvent("Admin performed bulk action '{$bulk_action}' on " . count($selected_users) . " users", 'INFO');
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error in user management: " . $e->getMessage(), 'ERROR');
    }
}

// Handle AJAX export requests
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $filters = [];
    
    // Apply filters for export
    if (!empty($_GET['role_filter'])) $filters['role'] = $_GET['role_filter'];
    if (!empty($_GET['institution_filter'])) $filters['institution_id'] = $_GET['institution_filter'];
    if (!empty($_GET['status_filter'])) $filters['is_active'] = $_GET['status_filter'];
    if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
    
    $users = getFilteredUsers($filters);
    
    switch ($format) {
        case 'csv':
            exportUsersCSV($users);
            break;
        case 'excel':
            exportUsersExcel($users);
            break;
        // REMOVED: case 'pdf'
    }
    exit;
}

// Get filter parameters
$role_filter = $_GET['role_filter'] ?? '';
$institution_filter = $_GET['institution_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$search = $_GET['search'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;

// Build filters
$filters = [];
if ($role_filter) $filters['role'] = $role_filter;
if ($institution_filter) $filters['institution_id'] = $institution_filter;
if ($status_filter !== '') $filters['is_active'] = $status_filter;
if ($search) $filters['search'] = $search;

// Get filtered users
$users = getFilteredUsers($filters, $sort_by, $sort_order, $page, $per_page);
$total_users = getUserCount($filters);
$pagination = getPaginationData($total_users, $page, $per_page);

// Get institutions for dropdowns
$institutions = fetchAll("SELECT id, name FROM institutions WHERE is_active = 1 ORDER BY name");

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-users"></i> User Management
        </h1>
        <p class="lead">Manage all system users with advanced filtering and bulk operations</p>
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

<div class="row mb-4">
    <div class="col-12">
        <div class="filter-section">
            <h5><i class="fas fa-filter"></i> Filter Users</h5>
            <form method="GET" class="row g-3" id="filterForm">
                <div class="col-md-2">
                    <label for="role_filter" class="form-label">Role</label>
                    <select name="role_filter" id="role_filter" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="faculty" <?php echo $role_filter === 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                        <option value="enquirer" <?php echo $role_filter === 'enquirer' ? 'selected' : ''; ?>>Enquirer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="institution_filter" class="form-label">Institution</label>
                    <select name="institution_filter" id="institution_filter" class="form-select">
                        <option value="">All Institutions</option>
                        <?php foreach ($institutions as $institution): ?>
                            <option value="<?php echo $institution['id']; ?>" 
                                    <?php echo $institution_filter == $institution['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($institution['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">Status</label>
                    <select name="status_filter" id="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Name, email, username..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <form method="POST" id="bulkActionForm" class="d-flex gap-2 align-items-end">
            <input type="hidden" name="action" value="bulk_action">
            <div>
                <label class="form-label">Bulk Actions</label>
                <select name="bulk_action" class="form-select">
                    <option value="">Select Action</option>
                    <option value="activate">Activate Selected</option>
                    <option value="deactivate">Deactivate Selected</option>
                    <option value="delete">Delete Selected</option>
                </select>
            </div>
            <button type="submit" class="btn btn-warning" onclick="return confirmBulkAction()">
                <i class="fas fa-cogs"></i> Apply
            </button>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportUsers('csv')">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportUsers('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a></li>
                </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> Users (<?php echo number_format($total_users); ?> total)</h5>
                <div class="card-tools">
                    <span class="badge bg-primary"><?php echo count($users); ?> shown</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Users Found</h5>
                        <p class="text-muted">No users match your current filters.</p>
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
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'username', 'sort_order' => $sort_by === 'username' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Username <?php echo $sort_by === 'username' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'email', 'sort_order' => $sort_by === 'email' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Email <?php echo $sort_by === 'email' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'role', 'sort_order' => $sort_by === 'role' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Role <?php echo $sort_by === 'role' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Institution</th>
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
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>" class="user-checkbox">
                                        </td>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                            <?php if ($user['phone']): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $user['role'] === 'admin' ? 'danger' : 
                                                    ($user['role'] === 'faculty' ? 'success' : 'info'); 
                                            ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['institution_name']): ?>
                                                <small><?php echo htmlspecialchars($user['institution_name']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                                        title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"
                                                        title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Users pagination" class="mt-3">
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

<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i> Create New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_user">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_username" class="form-label">Username *</label>
                            <input type="text" name="username" id="create_username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_password" class="form-label">Password *</label>
                            <input type="password" name="password" id="create_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_role" class="form-label">Role *</label>
                            <select name="role" id="create_role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="faculty">Faculty</option>
                                <option value="enquirer">Enquirer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_institution_id" class="form-label">Institution</label>
                            <select name="institution_id" id="create_institution_id" class="form-select">
                                <option value="">No Institution</option>
                                <?php foreach ($institutions as $institution): ?>
                                    <option value="<?php echo $institution['id']; ?>">
                                        <?php echo htmlspecialchars($institution['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="create_full_name" class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="create_full_name" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label">Email *</label>
                            <input type="email" name="email" id="create_email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_phone" class="form-label">Phone</label>
                            <input type="tel" name="phone" id="create_phone" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit"></i> Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_username" class="form-label">Username *</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_password" class="form-label">New Password</label
                            <input type="password" name="password" id="edit_password" class="form-control" 
                                   placeholder="Leave blank to keep current password">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">Role *</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="faculty">Faculty</option>
                                <option value="enquirer">Enquirer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_institution_id" class="form-label">Institution</label>
                            <select name="institution_id" id="edit_institution_id" class="form-select">
                                <option value="">No Institution</option>
                                <?php foreach ($institutions as $institution): ?>
                                    <option value="<?php echo $institution['id']; ?>">
                                        <?php echo htmlspecialchars($institution['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email *</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="tel" name="phone" id="edit_phone" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input" value="1">
                            <label for="edit_is_active" class="form-check-label">Active User</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
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

.user-checkbox {
    transform: scale(1.2);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}
</style>

<script>
// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Edit user functionality
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_institution_id').value = user.institution_id || '';
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_is_active').checked = user.is_active == 1;
    
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

// Delete user functionality
function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="bulk_action">
            <input type="hidden" name="bulk_action" value="delete">
            <input type="hidden" name="selected_users[]" value="${userId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk action confirmation
function confirmBulkAction() {
    const selected = document.querySelectorAll('.user-checkbox:checked');
    const action = document.querySelector('select[name="bulk_action"]').value;
    
    if (selected.length === 0) {
        alert('Please select at least one user.');
        return false;
    }
    
    if (!action) {
        alert('Please select a bulk action.');
        return false;
    }
    
    const actionText = action === 'delete' ? 'delete' : action;
    return confirm(`Are you sure you want to ${actionText} ${selected.length} selected user(s)?`);
}

// Export functionality
function exportUsers(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    
    const url = `?${params.toString()}`;
    window.open(url, '_blank');
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('#filterForm select, #filterForm input[type="text"]');
    
    filterInputs.forEach(input => {
        if (input.type === 'text') {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        } else {
            input.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
    
    // Update bulk action form with selected checkboxes
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        const selected = document.querySelectorAll('.user-checkbox:checked');
        
        // Clear existing hidden inputs
        const existingInputs = this.querySelectorAll('input[name="selected_users[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected user IDs
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_users[]';
            input.value = checkbox.value;
            this.appendChild(input);
        });
    });
});
</script>

<?php
/**
 * Helper functions for user management
 */

function getFilteredUsers($filters = [], $sort_by = 'created_at', $sort_order = 'DESC', $page = 1, $per_page = 25) {
    $whereConditions = [];
    $params = [];
    
    // Apply filters
    if (!empty($filters['role'])) {
        $whereConditions[] = "u.role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($filters['institution_id'])) {
        $whereConditions[] = "u.institution_id = ?";
        $params[] = $filters['institution_id'];
    }
    
    if (isset($filters['is_active']) && $filters['is_active'] !== '') {
        $whereConditions[] = "u.is_active = ?";
        $params[] = $filters['is_active'];
    }
    
    if (!empty($filters['search'])) {
        $whereConditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Validate sort parameters
    $allowedSorts = ['id', 'username', 'full_name', 'email', 'role', 'created_at'];
    if (!in_array($sort_by, $allowedSorts)) {
        $sort_by = 'created_at';
    }
    
    $sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    $query = "
        SELECT u.*, i.name as institution_name
        FROM users u
        LEFT JOIN institutions i ON u.institution_id = i.id
        {$whereClause}
        ORDER BY u.{$sort_by} {$sort_order}
        LIMIT {$per_page} OFFSET {$offset}
    ";
    
    return fetchAll($query, $params);
}

function getUserCount($filters = []) {
    $whereConditions = [];
    $params = [];
    
    // Apply same filters as getFilteredUsers
    if (!empty($filters['role'])) {
        $whereConditions[] = "role = ?";
        $params[] = $filters['role'];
    }
    
    if (!empty($filters['institution_id'])) {
        $whereConditions[] = "institution_id = ?";
        $params[] = $filters['institution_id'];
    }
    
    if (isset($filters['is_active']) && $filters['is_active'] !== '') {
        $whereConditions[] = "is_active = ?";
        $params[] = $filters['is_active'];
    }
    
    if (!empty($filters['search'])) {
        $whereConditions[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT COUNT(*) as count FROM users {$whereClause}";
    $result = fetchOne($query, $params);
    
    return $result['count'] ?? 0;
}

function exportUsersCSV($users) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, ['ID', 'Username', 'Full Name', 'Email', 'Phone', 'Role', 'Institution', 'Status', 'Created']);
    
    // CSV data
    foreach ($users as $user) {
        fputcsv($output, [
            $user['id'],
            $user['username'],
            $user['full_name'],
            $user['email'],
            $user['phone'],
            $user['role'],
            $user['institution_name'] ?: 'N/A',
            $user['is_active'] ? 'Active' : 'Inactive',
            date('Y-m-d H:i:s', strtotime($user['created_at']))
        ]);
    }
    
    fclose($output);
}

function exportUsersExcel($users) {
    // For now, export as CSV with Excel-friendly format
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Institution</th><th>Status</th><th>Created</th></tr>';
    
    foreach ($users as $user) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($user['id']) . '</td>';
        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
        echo '<td>' . htmlspecialchars($user['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
        echo '<td>' . htmlspecialchars($user['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($user['role']) . '</td>';
        echo '<td>' . htmlspecialchars($user['institution_name'] ?: 'N/A') . '</td>';
        echo '<td>' . ($user['is_active'] ? 'Active' : 'Inactive') . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($user['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
}

renderAdminFooter();
?>