<?php
/**
 * Admin Student Oversight
 * Comprehensive student data management with filtering and bulk operations
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include admin authorization
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Page title
$pageTitle = 'Student Oversight';

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_eligibility':
                    $student_id = (int)$_POST['student_id'];
                    $is_eligible = isset($_POST['is_exam_eligible']) ? 1 : 0;
                    
                    executeQuery("UPDATE students SET is_exam_eligible = ? WHERE id = ?", [$is_eligible, $student_id]);
                    
                    $success_message = "Student exam eligibility updated successfully!";
                    logEvent("Admin updated student ID {$student_id} exam eligibility to " . ($is_eligible ? 'eligible' : 'not eligible'), 'INFO');
                    break;
                    
                case 'bulk_action':
                    $selected_students = $_POST['selected_students'] ?? [];
                    $bulk_action = $_POST['bulk_action'];
                    
                    if (empty($selected_students)) {
                        throw new Exception("No students selected for bulk action.");
                    }
                    
                    $placeholders = str_repeat('?,', count($selected_students) - 1) . '?';
                    
                    switch ($bulk_action) {
                        case 'mark_eligible':
                            executeQuery("UPDATE students SET is_exam_eligible = 1 WHERE id IN ({$placeholders})", $selected_students);
                            $success_message = count($selected_students) . " students marked as exam eligible!";
                            break;
                        case 'mark_not_eligible':
                            executeQuery("UPDATE students SET is_exam_eligible = 0 WHERE id IN ({$placeholders})", $selected_students);
                            $success_message = count($selected_students) . " students marked as not exam eligible!";
                            break;
                        case 'archive':
                            executeQuery("UPDATE students SET status = 'archived' WHERE id IN ({$placeholders})", $selected_students);
                            $success_message = count($selected_students) . " students archived successfully!";
                            break;
                        case 'delete':
                            // Check for associated marklists and certificate requests
                            $marklistCount = fetchOne("SELECT COUNT(*) as count FROM marklists WHERE student_id IN ({$placeholders})", $selected_students)['count'];
                            $certificateCount = fetchOne("SELECT COUNT(*) as count FROM certificate_requests WHERE student_id IN ({$placeholders})", $selected_students)['count'];
                            
                            if ($marklistCount > 0 || $certificateCount > 0) {
                                throw new Exception("Cannot delete students with associated marklists or certificate requests. Please remove them first.");
                            }
                            
                            executeQuery("DELETE FROM students WHERE id IN ({$placeholders})", $selected_students);
                            $success_message = count($selected_students) . " students deleted successfully!";
                            break;
                    }
                    
                    logEvent("Admin performed bulk action '{$bulk_action}' on " . count($selected_students) . " students", 'INFO');
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error in student management: " . $e->getMessage(), 'ERROR');
    }
}

// Handle export requests
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $filters = [];
    
    // Apply filters for export
    if (!empty($_GET['institution_filter'])) $filters['institution_id'] = $_GET['institution_filter'];
    if (!empty($_GET['course_filter'])) $filters['course'] = $_GET['course_filter'];
    if (!empty($_GET['batch_filter'])) $filters['batch_year'] = $_GET['batch_filter'];
    if (!empty($_GET['eligibility_filter'])) $filters['is_exam_eligible'] = $_GET['eligibility_filter'];
    if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
    if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
    if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
    
    $students = getFilteredStudents($filters);
    
    switch ($format) {
        case 'csv':
            exportStudentsCSV($students);
            break;
        case 'excel':
            exportStudentsExcel($students);
            break;
        case 'pdf':
            exportStudentsPDF($students);
            break;
    }
    exit;
}

// Get filter parameters
$institution_filter = $_GET['institution_filter'] ?? '';
$course_filter = $_GET['course_filter'] ?? '';
$batch_filter = $_GET['batch_filter'] ?? '';
$eligibility_filter = $_GET['eligibility_filter'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;

// Build filters
$filters = [];
if ($institution_filter) $filters['institution_id'] = $institution_filter;
if ($course_filter) $filters['course'] = $course_filter;
if ($batch_filter) $filters['batch_year'] = $batch_filter;
if ($eligibility_filter !== '') $filters['is_exam_eligible'] = $eligibility_filter;
if ($date_from) $filters['date_from'] = $date_from;
if ($date_to) $filters['date_to'] = $date_to;
if ($search) $filters['search'] = $search;

// Get filtered students
$students = getFilteredStudents($filters, $sort_by, $sort_order, $page, $per_page);
$total_students = getStudentCount($filters);
$pagination = getPaginationData($total_students, $page, $per_page);

// Get institutions for filter dropdown
$institutions = fetchAll("SELECT id, name FROM institutions WHERE is_active = 1 ORDER BY name");

// Get unique courses and batch years for filters
$courses = fetchAll("SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != '' ORDER BY course");
$batch_years = fetchAll("SELECT DISTINCT batch_year FROM students WHERE batch_year IS NOT NULL ORDER BY batch_year DESC");

// Get student statistics
$studentStats = getStudentStatistics();

// Render header
renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-gradient mb-2">
                    <i class="fas fa-graduation-cap"></i> Student Oversight
                </h1>
                <p class="lead mb-0">Comprehensive student data management with filtering and bulk operations</p>
            </div>
            <div>
                <a href="<?php echo url('admin/add_student.php'); ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-user-plus"></i> Add Student
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

<!-- Student Statistics -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($studentStats['total']); ?></h4>
                <p class="mb-0">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($studentStats['eligible']); ?></h4>
                <p class="mb-0">Exam Eligible</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($studentStats['not_eligible']); ?></h4>
                <p class="mb-0">Not Eligible</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($studentStats['institutions']); ?></h4>
                <p class="mb-0">Institutions</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($studentStats['today']); ?></h4>
                <p class="mb-0">Today</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-dark text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($studentStats['this_month']); ?></h4>
                <p class="mb-0">This Month</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="filter-section">
            <h5><i class="fas fa-filter"></i> Filter Students</h5>
            <form method="GET" class="row g-3" id="filterForm">
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
                    <label for="course_filter" class="form-label">Course</label>
                    <select name="course_filter" id="course_filter" class="form-select">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['course']); ?>" 
                                    <?php echo $course_filter === $course['course'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="batch_filter" class="form-label">Batch Year</label>
                    <select name="batch_filter" id="batch_filter" class="form-select">
                        <option value="">All Batches</option>
                        <?php foreach ($batch_years as $batch): ?>
                            <option value="<?php echo $batch['batch_year']; ?>" 
                                    <?php echo $batch_filter == $batch['batch_year'] ? 'selected' : ''; ?>>
                                <?php echo $batch['batch_year']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="eligibility_filter" class="form-label">Eligibility</label>
                    <select name="eligibility_filter" id="eligibility_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $eligibility_filter === '1' ? 'selected' : ''; ?>>Eligible</option>
                        <option value="0" <?php echo $eligibility_filter === '0' ? 'selected' : ''; ?>>Not Eligible</option>
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
            </form>
            <div class="row mt-3">
                <div class="col-md-6">
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Search by name, student ID, email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <button type="button" onclick="applyFilters()" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="students.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('today')">Today</button>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('week')">This Week</button>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('eligible')">Eligible Only</button>
                        <button type="button" class="btn btn-info" onclick="setQuickFilter('not_eligible')">Not Eligible</button>
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
                <select name="bulk_action" class="form-select">
                    <option value="">Select Action</option>
                    <optgroup label="Exam Eligibility">
                        <option value="mark_eligible">Mark as Exam Eligible</option>
                        <option value="mark_not_eligible">Mark as Not Eligible</option>
                    </optgroup>
                    <optgroup label="Management">
                        <option value="archive">Archive Selected</option>
                        <option value="delete">Delete Selected</option>
                    </optgroup>
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
                <li><a class="dropdown-item" href="#" onclick="exportStudents('csv')">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportStudents('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportStudents('pdf')">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Students Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> Students (<?php echo number_format($total_students); ?> total)</h5>
                <div class="card-tools">
                    <span class="badge bg-primary"><?php echo count($students); ?> shown</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Students Found</h5>
                        <p class="text-muted">No students match your current filters.</p>
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
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'student_id', 'sort_order' => $sort_by === 'student_id' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Student ID <?php echo $sort_by === 'student_id' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'full_name', 'sort_order' => $sort_by === 'full_name' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Name <?php echo $sort_by === 'full_name' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Contact Info</th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'course', 'sort_order' => $sort_by === 'course' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Course <?php echo $sort_by === 'course' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'batch_year', 'sort_order' => $sort_by === 'batch_year' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Batch <?php echo $sort_by === 'batch_year' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Institution</th>
                                    <th>Eligibility</th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'created_at', 'sort_order' => $sort_by === 'created_at' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                            Registered <?php echo $sort_by === 'created_at' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_students[]" value="<?php echo $student['id']; ?>" class="student-checkbox">
                                        </td>
                                        <td><?php echo $student['id']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($student['student_id']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                            <?php if ($student['photo_path']): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-camera"></i> Photo available
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['email']): ?>
                                                <div>
                                                    <i class="fas fa-envelope"></i>
                                                    <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>">
                                                        <?php echo htmlspecialchars($student['email']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($student['phone']): ?>
                                                <div>
                                                    <i class="fas fa-phone"></i>
                                                    <a href="tel:<?php echo htmlspecialchars($student['phone']); ?>">
                                                        <?php echo htmlspecialchars($student['phone']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!$student['email'] && !$student['phone']): ?>
                                                <span class="text-muted">No contact info</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['course']): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($student['course']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Not specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['batch_year']): ?>
                                                <span class="badge bg-primary"><?php echo $student['batch_year']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($student['institution_name'] ?: 'Unknown'); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $student['is_exam_eligible'] ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo $student['is_exam_eligible'] ? 'Eligible' : 'Not Eligible'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($student['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info" 
                                                        onclick="viewStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editEligibility(<?php echo $student['id']; ?>, <?php echo $student['is_exam_eligible'] ? 'true' : 'false'; ?>)"
                                                        title="Edit Eligibility">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="viewMarklists(<?php echo $student['id']; ?>)"
                                                        title="View Marklists">
                                                    <i class="fas fa-list-alt"></i>
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
                        <nav aria-label="Students pagination" class="mt-3">
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
</div><
!-- View Student Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-graduate"></i> Student Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="studentDetails">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Eligibility Modal -->
<div class="modal fade" id="editEligibilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Exam Eligibility
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_eligibility">
                    <input type="hidden" name="student_id" id="eligibility_student_id">
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_exam_eligible" id="is_exam_eligible" class="form-check-input" value="1">
                            <label for="is_exam_eligible" class="form-check-label">
                                <strong>Student is eligible for examinations</strong>
                            </label>
                        </div>
                        <small class="text-muted">Check this box to mark the student as eligible for examinations.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Eligibility
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Marklists Modal -->
<div class="modal fade" id="viewMarklistsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-list-alt"></i> Student Marklists
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="marklistDetails">
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

.student-checkbox {
    transform: scale(1.2);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

.card.bg-primary, .card.bg-success, .card.bg-warning, .card.bg-info, .card.bg-secondary, .card.bg-dark {
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
</style>

<script>
// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// View student functionality
function viewStudent(student) {
    const detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-user"></i> Personal Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Student ID:</strong></td>
                        <td><span class="badge bg-secondary">${escapeHtml(student.student_id)}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td>${escapeHtml(student.full_name)}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${student.email ? `<a href="mailto:${escapeHtml(student.email)}">${escapeHtml(student.email)}</a>` : 'Not provided'}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>${student.phone ? `<a href="tel:${escapeHtml(student.phone)}">${escapeHtml(student.phone)}</a>` : 'Not provided'}</td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>${student.address ? escapeHtml(student.address) : 'Not provided'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-graduation-cap"></i> Academic Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Course:</strong></td>
                        <td>${student.course ? escapeHtml(student.course) : 'Not specified'}</td>
                    </tr>
                    <tr>
                        <td><strong>Batch Year:</strong></td>
                        <td>${student.batch_year ? student.batch_year : 'Not specified'}</td>
                    </tr>
                    <tr>
                        <td><strong>Institution:</strong></td>
                        <td>${student.institution_name ? escapeHtml(student.institution_name) : 'Unknown'}</td>
                    </tr>
                    <tr>
                        <td><strong>Exam Eligibility:</strong></td>
                        <td><span class="badge ${student.is_exam_eligible ? 'bg-success' : 'bg-warning'}">${student.is_exam_eligible ? 'Eligible' : 'Not Eligible'}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Registered By:</strong></td>
                        <td>${student.registered_by_name ? escapeHtml(student.registered_by_name) : 'Unknown'}</td>
                    </tr>
                    <tr>
                        <td><strong>Registration Date:</strong></td>
                        <td>${new Date(student.created_at).toLocaleString()}</td>
                    </tr>
                </table>
            </div>
        </div>
        ${student.photo_path ? `
        <div class="row mt-3">
            <div class="col-12">
                <h6><i class="fas fa-camera"></i> Student Photo</h6>
                <div class="text-center">
                    <img src="${escapeHtml(student.photo_path)}" alt="Student Photo" class="img-thumbnail" style="max-width: 200px;">
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('studentDetails').innerHTML = detailsHtml;
    const modal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
    modal.show();
}

// Edit eligibility functionality
function editEligibility(studentId, currentEligibility) {
    document.getElementById('eligibility_student_id').value = studentId;
    document.getElementById('is_exam_eligible').checked = currentEligibility;
    
    const modal = new bootstrap.Modal(document.getElementById('editEligibilityModal'));
    modal.show();
}

// View marklists functionality
function viewMarklists(studentId) {
    document.getElementById('marklistDetails').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading marklists...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('viewMarklistsModal'));
    modal.show();
    
    // In a real implementation, this would fetch marklist data via AJAX
    setTimeout(() => {
        document.getElementById('marklistDetails').innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Marklist details for student ID ${studentId} would be displayed here. 
                This would include all subjects, marks, grades, and examination history.
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Total Marks</th>
                            <th>Grade</th>
                            <th>Exam Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No marklist data available or feature not yet implemented.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }, 1000);
}

// Bulk action confirmation
function confirmBulkAction() {
    const selected = document.querySelectorAll('.student-checkbox:checked');
    const action = document.querySelector('select[name="bulk_action"]').value;
    
    if (selected.length === 0) {
        alert('Please select at least one student.');
        return false;
    }
    
    if (!action) {
        alert('Please select a bulk action.');
        return false;
    }
    
    const actionText = action.replace('mark_', '').replace('_', ' ');
    return confirm(`Are you sure you want to ${actionText} ${selected.length} selected student(s)?`);
}

// Export functionality
function exportStudents(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    
    const url = `?${params.toString()}`;
    window.open(url, '_blank');
}

// Apply filters functionality
function applyFilters() {
    const form = document.getElementById('filterForm');
    const search = document.getElementById('search').value;
    
    // Add search to form
    const searchInput = document.createElement('input');
    searchInput.type = 'hidden';
    searchInput.name = 'search';
    searchInput.value = search;
    form.appendChild(searchInput);
    
    form.submit();
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
        case 'eligible':
            document.getElementById('eligibility_filter').value = '1';
            break;
        case 'not_eligible':
            document.getElementById('eligibility_filter').value = '0';
            break;
    }
    
    applyFilters();
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('#filterForm select, #filterForm input[type="date"]');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            applyFilters();
        });
    });
    
    // Debounced search
    const searchInput = document.getElementById('search');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500);
    });
    
    // Update bulk action form with selected checkboxes
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        const selected = document.querySelectorAll('.student-checkbox:checked');
        
        // Clear existing hidden inputs
        const existingInputs = this.querySelectorAll('input[name="selected_students[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected student IDs
        selected.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_students[]';
            input.value = checkbox.value;
            this.appendChild(input);
        });
    });
});
</script>

<?php
/**
 * Helper functions for student management
 */

function getFilteredStudents($filters = [], $sort_by = 'created_at', $sort_order = 'DESC', $page = 1, $per_page = 25) {
    $whereConditions = [];
    $params = [];
    
    // Apply filters
    if (!empty($filters['institution_id'])) {
        $whereConditions[] = "s.institution_id = ?";
        $params[] = $filters['institution_id'];
    }
    
    if (!empty($filters['course'])) {
        $whereConditions[] = "s.course = ?";
        $params[] = $filters['course'];
    }
    
    if (!empty($filters['batch_year'])) {
        $whereConditions[] = "s.batch_year = ?";
        $params[] = $filters['batch_year'];
    }
    
    if (isset($filters['is_exam_eligible'])) {
        $whereConditions[] = "s.is_exam_eligible = ?";
        $params[] = $filters['is_exam_eligible'];
    }
    
    if (!empty($filters['date_from'])) {
        $whereConditions[] = "DATE(s.created_at) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $whereConditions[] = "DATE(s.created_at) <= ?";
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['search'])) {
        $whereConditions[] = "(s.full_name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Validate sort parameters
    $allowedSorts = ['id', 'student_id', 'full_name', 'course', 'batch_year', 'created_at'];
    if (!in_array($sort_by, $allowedSorts)) {
        $sort_by = 'created_at';
    }
    
    $sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    $query = "
        SELECT s.*, 
               i.name as institution_name,
               u.full_name as registered_by_name
        FROM students s 
        LEFT JOIN institutions i ON s.institution_id = i.id 
        LEFT JOIN users u ON s.registered_by = u.id
        {$whereClause}
        ORDER BY s.{$sort_by} {$sort_order}
        LIMIT {$per_page} OFFSET {$offset}
    ";
    
    return fetchAll($query, $params);
}

function getStudentCount($filters = []) {
    $whereConditions = [];
    $params = [];
    
    // Apply same filters as getFilteredStudents
    if (!empty($filters['institution_id'])) {
        $whereConditions[] = "institution_id = ?";
        $params[] = $filters['institution_id'];
    }
    
    if (!empty($filters['course'])) {
        $whereConditions[] = "course = ?";
        $params[] = $filters['course'];
    }
    
    if (!empty($filters['batch_year'])) {
        $whereConditions[] = "batch_year = ?";
        $params[] = $filters['batch_year'];
    }
    
    if (isset($filters['is_exam_eligible'])) {
        $whereConditions[] = "is_exam_eligible = ?";
        $params[] = $filters['is_exam_eligible'];
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
        $whereConditions[] = "(full_name LIKE ? OR student_id LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "SELECT COUNT(*) as count FROM students {$whereClause}";
    $result = fetchOne($query, $params);
    
    return $result['count'] ?? 0;
}

function getStudentStatistics() {
    try {
        $stats = fetchOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_exam_eligible = 1 THEN 1 ELSE 0 END) as eligible,
                SUM(CASE WHEN is_exam_eligible = 0 THEN 1 ELSE 0 END) as not_eligible,
                COUNT(DISTINCT institution_id) as institutions,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month
            FROM students
        ");
        
        return [
            'total' => $stats['total'] ?? 0,
            'eligible' => $stats['eligible'] ?? 0,
            'not_eligible' => $stats['not_eligible'] ?? 0,
            'institutions' => $stats['institutions'] ?? 0,
            'today' => $stats['today'] ?? 0,
            'this_month' => $stats['this_month'] ?? 0
        ];
        
    } catch (Exception $e) {
        logEvent("Error fetching student statistics: " . $e->getMessage(), 'ERROR');
        return [
            'total' => 0,
            'eligible' => 0,
            'not_eligible' => 0,
            'institutions' => 0,
            'today' => 0,
            'this_month' => 0
        ];
    }
}

function exportStudentsCSV($students) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, ['ID', 'Student ID', 'Full Name', 'Email', 'Phone', 'Course', 'Batch Year', 'Institution', 'Exam Eligible', 'Registered By', 'Registration Date']);
    
    // CSV data
    foreach ($students as $student) {
        fputcsv($output, [
            $student['id'],
            $student['student_id'],
            $student['full_name'],
            $student['email'],
            $student['phone'],
            $student['course'],
            $student['batch_year'],
            $student['institution_name'] ?: 'Unknown',
            $student['is_exam_eligible'] ? 'Yes' : 'No',
            $student['registered_by_name'] ?: 'Unknown',
            date('Y-m-d H:i:s', strtotime($student['created_at']))
        ]);
    }
    
    fclose($output);
}

function exportStudentsExcel($students) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Student ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Course</th><th>Batch Year</th><th>Institution</th><th>Exam Eligible</th><th>Registered By</th><th>Registration Date</th></tr>';
    
    foreach ($students as $student) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($student['id']) . '</td>';
        echo '<td>' . htmlspecialchars($student['student_id']) . '</td>';
        echo '<td>' . htmlspecialchars($student['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($student['email']) . '</td>';
        echo '<td>' . htmlspecialchars($student['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($student['course']) . '</td>';
        echo '<td>' . htmlspecialchars($student['batch_year']) . '</td>';
        echo '<td>' . htmlspecialchars($student['institution_name'] ?: 'Unknown') . '</td>';
        echo '<td>' . ($student['is_exam_eligible'] ? 'Yes' : 'No') . '</td>';
        echo '<td>' . htmlspecialchars($student['registered_by_name'] ?: 'Unknown') . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($student['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
}

function exportStudentsPDF($students) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.pdf"');
    
    echo '<!DOCTYPE html><html><head><title>Students Export</title>';
    echo '<style>table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#f2f2f2;}</style>';
    echo '</head><body>';
    echo '<h1>Students Export - ' . date('Y-m-d') . '</h1>';
    echo '<table>';
    echo '<tr><th>ID</th><th>Student ID</th><th>Name</th><th>Email</th><th>Course</th><th>Batch</th><th>Institution</th><th>Eligible</th><th>Registered</th></tr>';
    
    foreach ($students as $student) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($student['id']) . '</td>';
        echo '<td>' . htmlspecialchars($student['student_id']) . '</td>';
        echo '<td>' . htmlspecialchars($student['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($student['email']) . '</td>';
        echo '<td>' . htmlspecialchars($student['course']) . '</td>';
        echo '<td>' . htmlspecialchars($student['batch_year']) . '</td>';
        echo '<td>' . htmlspecialchars($student['institution_name'] ?: 'Unknown') . '</td>';
        echo '<td>' . ($student['is_exam_eligible'] ? 'Yes' : 'No') . '</td>';
        echo '<td>' . htmlspecialchars(date('Y-m-d', strtotime($student['created_at']))) . '</td>';
        echo '</tr>';
    }
    
    echo '</table></body></html>';
}

renderAdminFooter();
?>