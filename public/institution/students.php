<?php
/**
 * Student Management
 * Allows faculty to view and manage students for their institution
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include faculty authorization
require_once __DIR__ . '/../../includes/auth_check_faculty.php';

// Page title
$pageTitle = 'Student Management';

// Get institution details
$institution = getCurrentInstitution();
$institutionId = getCurrentUserInstitutionId();

// Initialize filter variables
$filters = [
    'search' => sanitizeInput($_GET['search'] ?? ''),
    'course' => sanitizeInput($_GET['course'] ?? ''),
    'batch_year' => sanitizeInput($_GET['batch_year'] ?? ''),
    'exam_eligible' => sanitizeInput($_GET['exam_eligible'] ?? ''),
    'sort_by' => sanitizeInput($_GET['sort_by'] ?? 'created_at'),
    'sort_order' => sanitizeInput($_GET['sort_order'] ?? 'DESC')
];

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Handle bulk operations
$bulkMessage = '';
$bulkError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $selectedStudents = $_POST['selected_students'] ?? [];
    $bulkAction = $_POST['bulk_action'];
    
    if (!empty($selectedStudents)) {
        try {
            switch ($bulkAction) {
                case 'mark_eligible':
                    $placeholders = str_repeat('?,', count($selectedStudents) - 1) . '?';
                    $stmt = $pdo->prepare("
                        UPDATE students 
                        SET is_exam_eligible = 1 
                        WHERE id IN ($placeholders) AND institution_id = ?
                    ");
                    $stmt->execute(array_merge($selectedStudents, [$institutionId]));
                    $bulkMessage = count($selectedStudents) . ' students marked as exam eligible';
                    break;
                    
                case 'mark_not_eligible':
                    $placeholders = str_repeat('?,', count($selectedStudents) - 1) . '?';
                    $stmt = $pdo->prepare("
                        UPDATE students 
                        SET is_exam_eligible = 0 
                        WHERE id IN ($placeholders) AND institution_id = ?
                    ");
                    $stmt->execute(array_merge($selectedStudents, [$institutionId]));
                    $bulkMessage = count($selectedStudents) . ' students marked as not exam eligible';
                    break;
                    
                case 'export_csv':
                    // Export selected students to CSV
                    $placeholders = str_repeat('?,', count($selectedStudents) - 1) . '?';
                    $stmt = $pdo->prepare("
                        SELECT student_id, full_name, email, phone, course, batch_year, 
                               is_exam_eligible, created_at
                        FROM students 
                        WHERE id IN ($placeholders) AND institution_id = ?
                        ORDER BY full_name
                    ");
                    $stmt->execute(array_merge($selectedStudents, [$institutionId]));
                    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');
                    
                    $output = fopen('php://output', 'w');
                    fputcsv($output, ['Student ID', 'Full Name', 'Email', 'Phone', 'Course', 'Batch Year', 'Exam Eligible', 'Registration Date']);
                    
                    foreach ($students as $student) {
                        fputcsv($output, [
                            $student['student_id'],
                            $student['full_name'],
                            $student['email'],
                            $student['phone'],
                            $student['course'],
                            $student['batch_year'],
                            $student['is_exam_eligible'] ? 'Yes' : 'No',
                            $student['created_at']
                        ]);
                    }
                    
                    fclose($output);
                    exit;
            }
        } catch (PDOException $e) {
            $bulkError = 'Error performing bulk operation: ' . $e->getMessage();
            logError("Bulk operation error: " . $e->getMessage());
        }
    } else {
        $bulkError = 'Please select at least one student';
    }
}

// Build query with filters
$whereConditions = ["s.institution_id = ?"];
$queryParams = [$institutionId];

if (!empty($filters['search'])) {
    $whereConditions[] = "(s.student_id LIKE ? OR s.full_name LIKE ? OR s.email LIKE ? OR s.phone LIKE ?)";
    $searchTerm = '%' . $filters['search'] . '%';
    $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($filters['course'])) {
    $whereConditions[] = "s.course = ?";
    $queryParams[] = $filters['course'];
}

if (!empty($filters['batch_year'])) {
    $whereConditions[] = "s.batch_year = ?";
    $queryParams[] = $filters['batch_year'];
}

if ($filters['exam_eligible'] !== '') {
    $whereConditions[] = "s.is_exam_eligible = ?";
    $queryParams[] = $filters['exam_eligible'];
}

$whereClause = implode(' AND ', $whereConditions);

// Validate sort parameters
$allowedSortColumns = ['student_id', 'full_name', 'email', 'course', 'batch_year', 'is_exam_eligible', 'created_at'];
$sortBy = in_array($filters['sort_by'], $allowedSortColumns) ? $filters['sort_by'] : 'created_at';
$sortOrder = strtoupper($filters['sort_order']) === 'ASC' ? 'ASC' : 'DESC';

// Get total count
$countQuery = "SELECT COUNT(*) FROM students s WHERE $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($queryParams);
$totalRecords = $stmt->fetchColumn();

// Get students with pagination
$query = "
    SELECT s.*, u.full_name as registered_by_name
    FROM students s
    LEFT JOIN users u ON s.registered_by = u.id
    WHERE $whereClause
    ORDER BY s.$sortBy $sortOrder
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($queryParams);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filter options
$coursesQuery = "SELECT DISTINCT course FROM students WHERE institution_id = ? AND course IS NOT NULL ORDER BY course";
$stmt = $pdo->prepare($coursesQuery);
$stmt->execute([$institutionId]);
$courses = $stmt->fetchAll(PDO::FETCH_COLUMN);

$batchYearsQuery = "SELECT DISTINCT batch_year FROM students WHERE institution_id = ? AND batch_year IS NOT NULL ORDER BY batch_year DESC";
$stmt = $pdo->prepare($batchYearsQuery);
$stmt->execute([$institutionId]);
$batchYears = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate pagination
$totalPages = ceil($totalRecords / $limit);

// Render header
renderFacultyHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-gradient">
                <i class="fas fa-graduation-cap"></i> Student Management
            </h1>
            <a href="register_student.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Register Student
            </a>
        </div>
        
        <?php if ($institution): ?>
            <div class="alert alert-info">
                <i class="fas fa-university"></i>
                Managing students for <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

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
                       placeholder="Student ID, Name, Email, Phone">
            </div>
            
            <div class="col-md-2">
                <label for="course" class="form-label">Course</label>
                <select class="form-select" id="course" name="course">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course); ?>" 
                                <?php echo $filters['course'] === $course ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="batch_year" class="form-label">Batch Year</label>
                <select class="form-select" id="batch_year" name="batch_year">
                    <option value="">All Years</option>
                    <?php foreach ($batchYears as $year): ?>
                        <option value="<?php echo $year; ?>" 
                                <?php echo $filters['batch_year'] == $year ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="exam_eligible" class="form-label">Exam Eligible</label>
                <select class="form-select" id="exam_eligible" name="exam_eligible">
                    <option value="">All Students</option>
                    <option value="1" <?php echo $filters['exam_eligible'] === '1' ? 'selected' : ''; ?>>Eligible</option>
                    <option value="0" <?php echo $filters['exam_eligible'] === '0' ? 'selected' : ''; ?>>Not Eligible</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="sort_by" class="form-label">Sort By</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="created_at" <?php echo $filters['sort_by'] === 'created_at' ? 'selected' : ''; ?>>Registration Date</option>
                    <option value="full_name" <?php echo $filters['sort_by'] === 'full_name' ? 'selected' : ''; ?>>Name</option>
                    <option value="student_id" <?php echo $filters['sort_by'] === 'student_id' ? 'selected' : ''; ?>>Student ID</option>
                    <option value="course" <?php echo $filters['sort_by'] === 'course' ? 'selected' : ''; ?>>Course</option>
                    <option value="batch_year" <?php echo $filters['sort_by'] === 'batch_year' ? 'selected' : ''; ?>>Batch Year</option>
                </select>
            </div>
            
            <div class="col-md-1">
                <label for="sort_order" class="form-label">Order</label>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="DESC" <?php echo $filters['sort_order'] === 'DESC' ? 'selected' : ''; ?>>Desc</option>
                    <option value="ASC" <?php echo $filters['sort_order'] === 'ASC' ? 'selected' : ''; ?>>Asc</option>
                </select>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <a href="students.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <strong><?php echo number_format($totalRecords); ?></strong> students found
        <?php if ($totalRecords > 0): ?>
            (showing <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $totalRecords); ?>)
        <?php endif; ?>
    </div>
    
    <?php if ($totalRecords > 0): ?>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllStudents()">
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
                            <option value="mark_eligible">Mark as Exam Eligible</option>
                            <option value="mark_not_eligible">Mark as Not Exam Eligible</option>
                            <option value="export_csv">Export to CSV</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning" onclick="return confirmBulkAction()">
                            <i class="fas fa-cogs"></i> Apply to Selected
                        </button>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="selectedCount">0</span> students selected
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Students Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllStudents()">
                                </th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Batch Year</th>
                                <th>Contact</th>
                                <th>Exam Eligible</th>
                                <th>Registered</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="selected_students[]" 
                                               value="<?php echo $student['id']; ?>"
                                               class="student-checkbox"
                                               onchange="updateSelectedCount()">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['student_id']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($student['photo_path']): ?>
                                                <img src="<?php echo htmlspecialchars($student['photo_path']); ?>" 
                                                     alt="Photo" 
                                                     class="rounded-circle me-2" 
                                                     width="32" 
                                                     height="32">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-white small"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                <?php if ($student['email']): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                    <td><?php echo htmlspecialchars($student['batch_year']); ?></td>
                                    <td>
                                        <?php if ($student['phone']): ?>
                                            <small><?php echo htmlspecialchars($student['phone']); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">No phone</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($student['is_exam_eligible']): ?>
                                            <span class="badge bg-success">Eligible</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Eligible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($student['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-primary btn-sm"
                                                    onclick="viewStudent(<?php echo $student['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-warning btn-sm"
                                                    onclick="editStudent(<?php echo $student['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
        <nav aria-label="Students pagination" class="mt-4">
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
            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
            <h5>No Students Found</h5>
            <p class="text-muted">No students match your current filters.</p>
            <a href="register_student.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Register First Student
            </a>
        </div>
    </div>
<?php endif; ?>

<script>
let selectedStudents = [];

function toggleAllStudents() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function selectAllStudents() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAll').checked = true;
    updateSelectedCount();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.student-checkbox');
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
    const selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
    const action = document.querySelector('select[name="bulk_action"]').value;
    
    if (selectedCount === 0) {
        alert('Please select at least one student');
        return false;
    }
    
    if (!action) {
        alert('Please select a bulk action');
        return false;
    }
    
    let actionText = '';
    switch (action) {
        case 'mark_eligible':
            actionText = 'mark as exam eligible';
            break;
        case 'mark_not_eligible':
            actionText = 'mark as not exam eligible';
            break;
        case 'export_csv':
            actionText = 'export to CSV';
            break;
    }
    
    return confirm(`Are you sure you want to ${actionText} ${selectedCount} selected student(s)?`);
}

function viewStudent(studentId) {
    // Implement student view modal or redirect
    alert('View student functionality - Student ID: ' + studentId);
}

function editStudent(studentId) {
    // Implement student edit modal or redirect
    alert('Edit student functionality - Student ID: ' + studentId);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>

<?php renderFacultyFooter(); ?>