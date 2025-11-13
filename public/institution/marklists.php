<?php
/**
 * Marklist Management
 * Allows faculty to upload and manage marklists for their institution's students
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include faculty authorization
require_once __DIR__ . '/../../includes/auth_check_faculty.php';

// Page title
$pageTitle = 'Marklist Management';

// Get institution details
$institution = getCurrentInstitution();
$institutionId = getCurrentUserInstitutionId();

// Initialize variables
$errors = [];
$success = '';

// Handle marklist upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_marklist'])) {
    $studentId = sanitizeInput($_POST['student_id'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $marksObtained = intval($_POST['marks_obtained'] ?? 0);
    $totalMarks = intval($_POST['total_marks'] ?? 0);
    $grade = sanitizeInput($_POST['grade'] ?? '');
    $examDate = sanitizeInput($_POST['exam_date'] ?? '');
    
    // Validation
    if (empty($studentId)) {
        $errors[] = 'Please select a student';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if ($marksObtained < 0 || $totalMarks <= 0) {
        $errors[] = 'Please enter valid marks';
    }
    
    if ($marksObtained > $totalMarks) {
        $errors[] = 'Marks obtained cannot be greater than total marks';
    }
    
    if (empty($examDate)) {
        $errors[] = 'Exam date is required';
    }
    
    // Verify student belongs to faculty's institution
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM students WHERE id = ? AND institution_id = ?");
            $stmt->execute([$studentId, $institutionId]);
            if (!$stmt->fetch()) {
                $errors[] = 'Invalid student selected';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error occurred';
            logError("Error verifying student: " . $e->getMessage());
        }
    }
    
    // Insert marklist record
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO marklists (
                    student_id, subject, marks_obtained, total_marks, 
                    grade, exam_date, uploaded_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $studentId,
                $subject,
                $marksObtained,
                $totalMarks,
                $grade,
                $examDate,
                getCurrentUserId()
            ]);
            
            $success = 'Marklist uploaded successfully';
            
        } catch (PDOException $e) {
            $errors[] = 'Failed to upload marklist. Please try again.';
            logError("Error uploading marklist: " . $e->getMessage());
        }
    }
}

// Handle bulk operations
$bulkMessage = '';
$bulkError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $selectedMarklists = $_POST['selected_marklists'] ?? [];
    $bulkAction = $_POST['bulk_action'];
    
    if (!empty($selectedMarklists)) {
        try {
            switch ($bulkAction) {
                case 'export_csv':
                    // Export selected marklists to CSV
                    $placeholders = str_repeat('?,', count($selectedMarklists) - 1) . '?';
                    $stmt = $pdo->prepare("
                        SELECT m.*, s.student_id, s.full_name as student_name, s.course
                        FROM marklists m
                        JOIN students s ON m.student_id = s.id
                        WHERE m.id IN ($placeholders) AND s.institution_id = ?
                        ORDER BY s.full_name, m.subject
                    ");
                    $stmt->execute(array_merge($selectedMarklists, [$institutionId]));
                    $marklists = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="marklists_export_' . date('Y-m-d') . '.csv"');
                    
                    $output = fopen('php://output', 'w');
                    fputcsv($output, ['Student ID', 'Student Name', 'Course', 'Subject', 'Marks Obtained', 'Total Marks', 'Grade', 'Percentage', 'Exam Date']);
                    
                    foreach ($marklists as $marklist) {
                        $percentage = $marklist['total_marks'] > 0 ? round(($marklist['marks_obtained'] / $marklist['total_marks']) * 100, 2) : 0;
                        fputcsv($output, [
                            $marklist['student_id'],
                            $marklist['student_name'],
                            $marklist['course'],
                            $marklist['subject'],
                            $marklist['marks_obtained'],
                            $marklist['total_marks'],
                            $marklist['grade'],
                            $percentage . '%',
                            $marklist['exam_date']
                        ]);
                    }
                    
                    fclose($output);
                    exit;
                    
                case 'generate_transcript':
                    // Generate transcript for selected marklists
                    $bulkMessage = 'Transcript generation feature will be implemented';
                    break;
                    
                case 'delete':
                    $placeholders = str_repeat('?,', count($selectedMarklists) - 1) . '?';
                    $stmt = $pdo->prepare("
                        DELETE m FROM marklists m
                        JOIN students s ON m.student_id = s.id
                        WHERE m.id IN ($placeholders) AND s.institution_id = ?
                    ");
                    $stmt->execute(array_merge($selectedMarklists, [$institutionId]));
                    $bulkMessage = count($selectedMarklists) . ' marklist records deleted';
                    break;
            }
        } catch (PDOException $e) {
            $bulkError = 'Error performing bulk operation: ' . $e->getMessage();
            logError("Bulk operation error: " . $e->getMessage());
        }
    } else {
        $bulkError = 'Please select at least one marklist record';
    }
}

// Initialize filter variables
$filters = [
    'search' => sanitizeInput($_GET['search'] ?? ''),
    'student_id' => sanitizeInput($_GET['student_id'] ?? ''),
    'course' => sanitizeInput($_GET['course'] ?? ''),
    'subject' => sanitizeInput($_GET['subject'] ?? ''),
    'grade_range' => sanitizeInput($_GET['grade_range'] ?? ''),
    'exam_date_from' => sanitizeInput($_GET['exam_date_from'] ?? ''),
    'exam_date_to' => sanitizeInput($_GET['exam_date_to'] ?? ''),
    'sort_by' => sanitizeInput($_GET['sort_by'] ?? 'exam_date'),
    'sort_order' => sanitizeInput($_GET['sort_order'] ?? 'DESC')
];

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Build query with filters
$whereConditions = ["s.institution_id = ?"];
$queryParams = [$institutionId];

if (!empty($filters['search'])) {
    $whereConditions[] = "(s.student_id LIKE ? OR s.full_name LIKE ? OR m.subject LIKE ?)";
    $searchTerm = '%' . $filters['search'] . '%';
    $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($filters['student_id'])) {
    $whereConditions[] = "s.id = ?";
    $queryParams[] = $filters['student_id'];
}

if (!empty($filters['course'])) {
    $whereConditions[] = "s.course = ?";
    $queryParams[] = $filters['course'];
}

if (!empty($filters['subject'])) {
    $whereConditions[] = "m.subject = ?";
    $queryParams[] = $filters['subject'];
}

if (!empty($filters['grade_range'])) {
    switch ($filters['grade_range']) {
        case 'A':
            $whereConditions[] = "m.grade IN ('A+', 'A', 'A-')";
            break;
        case 'B':
            $whereConditions[] = "m.grade IN ('B+', 'B', 'B-')";
            break;
        case 'C':
            $whereConditions[] = "m.grade IN ('C+', 'C', 'C-')";
            break;
        case 'fail':
            $whereConditions[] = "m.grade IN ('D', 'F')";
            break;
    }
}

if (!empty($filters['exam_date_from'])) {
    $whereConditions[] = "m.exam_date >= ?";
    $queryParams[] = $filters['exam_date_from'];
}

if (!empty($filters['exam_date_to'])) {
    $whereConditions[] = "m.exam_date <= ?";
    $queryParams[] = $filters['exam_date_to'];
}

$whereClause = implode(' AND ', $whereConditions);

// Validate sort parameters
$allowedSortColumns = ['student_name', 'subject', 'marks_obtained', 'grade', 'exam_date'];
$sortBy = in_array($filters['sort_by'], $allowedSortColumns) ? $filters['sort_by'] : 'exam_date';
$sortOrder = strtoupper($filters['sort_order']) === 'ASC' ? 'ASC' : 'DESC';

// Adjust sort column for query
$sortColumn = $sortBy === 'student_name' ? 's.full_name' : 'm.' . $sortBy;

// Get total count
$countQuery = "
    SELECT COUNT(*) 
    FROM marklists m
    JOIN students s ON m.student_id = s.id
    WHERE $whereClause
";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($queryParams);
$totalRecords = $stmt->fetchColumn();

// Get marklists with pagination
$query = "
    SELECT m.*, s.student_id, s.full_name as student_name, s.course,
           ROUND((m.marks_obtained / m.total_marks) * 100, 2) as percentage
    FROM marklists m
    JOIN students s ON m.student_id = s.id
    WHERE $whereClause
    ORDER BY $sortColumn $sortOrder
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($queryParams);
$marklists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get filter options
$studentsQuery = "SELECT id, student_id, full_name FROM students WHERE institution_id = ? ORDER BY full_name";
$stmt = $pdo->prepare($studentsQuery);
$stmt->execute([$institutionId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$coursesQuery = "SELECT DISTINCT course FROM students WHERE institution_id = ? AND course IS NOT NULL ORDER BY course";
$stmt = $pdo->prepare($coursesQuery);
$stmt->execute([$institutionId]);
$courses = $stmt->fetchAll(PDO::FETCH_COLUMN);

$subjectsQuery = "
    SELECT DISTINCT m.subject 
    FROM marklists m
    JOIN students s ON m.student_id = s.id
    WHERE s.institution_id = ?
    ORDER BY m.subject
";
$stmt = $pdo->prepare($subjectsQuery);
$stmt->execute([$institutionId]);
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate pagination
$totalPages = ceil($totalRecords / $limit);

// Render header
renderFacultyHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-gradient">
                <i class="fas fa-list-alt"></i> Marklist Management
            </h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload Marklist
            </button>
        </div>
        
        <?php if ($institution): ?>
            <div class="alert alert-info">
                <i class="fas fa-university"></i>
                Managing marklists for <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
            </div>
        <?php endif; ?>
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
                       placeholder="Student, Subject">
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
                <label for="subject" class="form-label">Subject</label>
                <select class="form-select" id="subject" name="subject">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo htmlspecialchars($subject); ?>" 
                                <?php echo $filters['subject'] === $subject ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="grade_range" class="form-label">Grade Range</label>
                <select class="form-select" id="grade_range" name="grade_range">
                    <option value="">All Grades</option>
                    <option value="A" <?php echo $filters['grade_range'] === 'A' ? 'selected' : ''; ?>>A Grades</option>
                    <option value="B" <?php echo $filters['grade_range'] === 'B' ? 'selected' : ''; ?>>B Grades</option>
                    <option value="C" <?php echo $filters['grade_range'] === 'C' ? 'selected' : ''; ?>>C Grades</option>
                    <option value="fail" <?php echo $filters['grade_range'] === 'fail' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>
            
            <div class="col-md-1">
                <label for="sort_by" class="form-label">Sort By</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="exam_date" <?php echo $filters['sort_by'] === 'exam_date' ? 'selected' : ''; ?>>Exam Date</option>
                    <option value="student_name" <?php echo $filters['sort_by'] === 'student_name' ? 'selected' : ''; ?>>Student</option>
                    <option value="subject" <?php echo $filters['sort_by'] === 'subject' ? 'selected' : ''; ?>>Subject</option>
                    <option value="marks_obtained" <?php echo $filters['sort_by'] === 'marks_obtained' ? 'selected' : ''; ?>>Marks</option>
                    <option value="grade" <?php echo $filters['sort_by'] === 'grade' ? 'selected' : ''; ?>>Grade</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Exam Date Range</label>
                <div class="input-group">
                    <input type="date" 
                           class="form-control" 
                           name="exam_date_from" 
                           value="<?php echo htmlspecialchars($filters['exam_date_from']); ?>">
                    <span class="input-group-text">to</span>
                    <input type="date" 
                           class="form-control" 
                           name="exam_date_to" 
                           value="<?php echo htmlspecialchars($filters['exam_date_to']); ?>">
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
                    <a href="marklists.php" class="btn btn-outline-secondary">
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
        <strong><?php echo number_format($totalRecords); ?></strong> marklist records found
        <?php if ($totalRecords > 0): ?>
            (showing <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $totalRecords); ?>)
        <?php endif; ?>
    </div>
    
    <?php if ($totalRecords > 0): ?>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllMarklists()">
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
                            <option value="generate_transcript">Generate Transcript</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-warning" onclick="return confirmBulkAction()">
                            <i class="fas fa-cogs"></i> Apply to Selected
                        </button>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="selectedCount">0</span> records selected
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Marklists Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllMarklists()">
                                </th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Percentage</th>
                                <th>Exam Date</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marklists as $marklist): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="selected_marklists[]" 
                                               value="<?php echo $marklist['id']; ?>"
                                               class="marklist-checkbox"
                                               onchange="updateSelectedCount()">
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($marklist['student_id']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($marklist['student_name']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($marklist['course']); ?></td>
                                    <td><?php echo htmlspecialchars($marklist['subject']); ?></td>
                                    <td>
                                        <strong><?php echo $marklist['marks_obtained']; ?></strong> / <?php echo $marklist['total_marks']; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo in_array($marklist['grade'], ['A+', 'A', 'A-']) ? 'success' : 
                                                (in_array($marklist['grade'], ['B+', 'B', 'B-']) ? 'primary' : 
                                                (in_array($marklist['grade'], ['C+', 'C', 'C-']) ? 'warning' : 'danger')); 
                                        ?>">
                                            <?php echo htmlspecialchars($marklist['grade']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo $marklist['percentage']; ?>%</strong>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($marklist['exam_date'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-warning btn-sm"
                                                    onclick="editMarklist(<?php echo $marklist['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="deleteMarklist(<?php echo $marklist['id']; ?>)">
                                                <i class="fas fa-trash"></i>
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
        <nav aria-label="Marklists pagination" class="mt-4">
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
            <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
            <h5>No Marklists Found</h5>
            <p class="text-muted">No marklist records match your current filters.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload First Marklist
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Upload Marklist Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-upload"></i> Upload Marklist
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_student_id" class="form-label">Student <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_student_id" name="student_id" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="modal_subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_subject" name="subject" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="modal_marks_obtained" class="form-label">Marks Obtained <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modal_marks_obtained" name="marks_obtained" min="0" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="modal_total_marks" class="form-label">Total Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modal_total_marks" name="total_marks" min="1" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="modal_grade" class="form-label">Grade</label>
                            <select class="form-select" id="modal_grade" name="grade">
                                <option value="">Select Grade</option>
                                <option value="A+">A+</option>
                                <option value="A">A</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B">B</option>
                                <option value="B-">B-</option>
                                <option value="C+">C+</option>
                                <option value="C">C</option>
                                <option value="C-">C-</option>
                                <option value="D">D</option>
                                <option value="F">F</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_exam_date" class="form-label">Exam Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="modal_exam_date" name="exam_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Percentage</label>
                        <div class="form-control-plaintext" id="percentage_display">
                            <span class="text-muted">Enter marks to calculate percentage</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="upload_marklist" class="btn btn-primary">
                        <i class="fas fa-save"></i> Upload Marklist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Percentage calculation
function calculatePercentage() {
    const obtained = parseFloat(document.getElementById('modal_marks_obtained').value) || 0;
    const total = parseFloat(document.getElementById('modal_total_marks').value) || 0;
    const display = document.getElementById('percentage_display');
    
    if (total > 0) {
        const percentage = ((obtained / total) * 100).toFixed(2);
        display.innerHTML = `<strong>${percentage}%</strong>`;
        
        // Auto-suggest grade based on percentage
        const gradeSelect = document.getElementById('modal_grade');
        if (!gradeSelect.value) {
            if (percentage >= 90) gradeSelect.value = 'A+';
            else if (percentage >= 85) gradeSelect.value = 'A';
            else if (percentage >= 80) gradeSelect.value = 'A-';
            else if (percentage >= 75) gradeSelect.value = 'B+';
            else if (percentage >= 70) gradeSelect.value = 'B';
            else if (percentage >= 65) gradeSelect.value = 'B-';
            else if (percentage >= 60) gradeSelect.value = 'C+';
            else if (percentage >= 55) gradeSelect.value = 'C';
            else if (percentage >= 50) gradeSelect.value = 'C-';
            else if (percentage >= 40) gradeSelect.value = 'D';
            else gradeSelect.value = 'F';
        }
    } else {
        display.innerHTML = '<span class="text-muted">Enter marks to calculate percentage</span>';
    }
}

document.getElementById('modal_marks_obtained').addEventListener('input', calculatePercentage);
document.getElementById('modal_total_marks').addEventListener('input', calculatePercentage);

// Bulk operations
let selectedMarklists = [];

function toggleAllMarklists() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.marklist-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function selectAllMarklists() {
    const checkboxes = document.querySelectorAll('.marklist-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAll').checked = true;
    updateSelectedCount();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.marklist-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.marklist-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count;
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.marklist-checkbox');
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
    const selectedCount = document.querySelectorAll('.marklist-checkbox:checked').length;
    const action = document.querySelector('select[name="bulk_action"]').value;
    
    if (selectedCount === 0) {
        alert('Please select at least one marklist record');
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
        case 'generate_transcript':
            actionText = 'generate transcript for';
            break;
        case 'delete':
            actionText = 'delete';
            break;
    }
    
    return confirm(`Are you sure you want to ${actionText} ${selectedCount} selected marklist record(s)?`);
}

function editMarklist(marklistId) {
    // Implement marklist edit functionality
    alert('Edit marklist functionality - Marklist ID: ' + marklistId);
}

function deleteMarklist(marklistId) {
    if (confirm('Are you sure you want to delete this marklist record?')) {
        // Implement delete functionality
        alert('Delete marklist functionality - Marklist ID: ' + marklistId);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    
    // Show modal if there are errors
    <?php if (!empty($errors)): ?>
        var uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
        uploadModal.show();
    <?php endif; ?>
});
</script>

<?php renderFacultyFooter(); ?>