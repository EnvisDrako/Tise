<?php
/**
 * Add Student - Manual Entry and CSV Import
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../includes/auth_check_admin.php';

$pageTitle = 'Add Student';
$success_message = '';
$error_message = '';

// Helper to get the current logged-in admin ID safely
$current_user_id = $_SESSION['user_id'] ?? 1; // Default to 1 if session is missing

// Handle manual student addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    try {
        $student_id = sanitizeInput($_POST['student_id']);
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $course = sanitizeInput($_POST['course']);
        $batch_year = sanitizeInput($_POST['batch_year']);
        $institution_id = (int)$_POST['institution_id'];
        $enrollment_date = sanitizeInput($_POST['enrollment_date']);
        $status = sanitizeInput($_POST['status']);
        
        // Validate required fields
        if (empty($student_id) || empty($full_name) || empty($institution_id)) {
            throw new Exception("Student ID, Full Name, and Institution are required.");
        }
        
        // Check if student ID already exists
        $existing = fetchOne("SELECT id FROM students WHERE student_id = ?", [$student_id]);
        if ($existing) {
            throw new Exception("Student ID already exists.");
        }
        
        // Insert student (WITH registered_by)
        executeQuery("
            INSERT INTO students (
                student_id, full_name, email, phone, course, batch_year, 
                institution_id, enrollment_date, status, registered_by, created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [
            $student_id, $full_name, $email, $phone, $course, $batch_year, 
            $institution_id, $enrollment_date, $status, $current_user_id
        ]);
        
        $success_message = "Student added successfully!";
        logEvent("Admin added student: {$full_name} (ID: {$student_id})", 'INFO');
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error adding student: " . $e->getMessage(), 'ERROR');
    }
}

// Handle CSV import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_csv') {
    try {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a valid CSV file.");
        }
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            throw new Exception("Could not open CSV file.");
        }
        
        // Read header row
        $header = fgetcsv($handle);
        
        $imported = 0;
        $errors = [];
        $row_number = 1;
        
        while (($data = fgetcsv($handle)) !== false) {
            $row_number++;
            
            try {
                // Map CSV columns to fields
                $student_id = trim($data[0] ?? '');
                $full_name = trim($data[1] ?? '');
                $email = trim($data[2] ?? '');
                $phone = trim($data[3] ?? '');
                $course = trim($data[4] ?? '');
                $batch_year = trim($data[5] ?? '');
                $institution_id = (int)($data[6] ?? 0);
                $enrollment_date = trim($data[7] ?? date('Y-m-d'));
                $status = trim($data[8] ?? 'active');
                
                // Validate required fields
                if (empty($student_id) || empty($full_name) || empty($institution_id)) {
                    $errors[] = "Row {$row_number}: Missing required fields (Student ID, Name, or Institution)";
                    continue;
                }
                
                // Check if student already exists
                $existing = fetchOne("SELECT id FROM students WHERE student_id = ?", [$student_id]);
                if ($existing) {
                    $errors[] = "Row {$row_number}: Student ID {$student_id} already exists";
                    continue;
                }
                
                // Insert student (WITH registered_by)
                executeQuery("
                    INSERT INTO students (
                        student_id, full_name, email, phone, course, batch_year, 
                        institution_id, enrollment_date, status, registered_by, created_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ", [
                    $student_id, $full_name, $email, $phone, $course, $batch_year, 
                    $institution_id, $enrollment_date, $status, $current_user_id
                ]);
                
                $imported++;
                
            } catch (Exception $e) {
                $errors[] = "Row {$row_number}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $success_message = "Import completed! {$imported} students imported successfully.";
        if (!empty($errors)) {
            $error_message = "Some rows had errors:<br>" . implode("<br>", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $error_message .= "<br>... and " . (count($errors) - 10) . " more errors.";
            }
        }
        
        logEvent("Admin imported {$imported} students via CSV", 'INFO');
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error importing students CSV: " . $e->getMessage(), 'ERROR');
    }
}

// Get institutions for dropdown
$institutions = fetchAll("SELECT id, name, code FROM institutions WHERE is_active = 1 ORDER BY name");

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-user-plus"></i> Add Students
        </h1>
        <p class="lead">Add students manually or import from CSV file</p>
    </div>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-edit"></i> Manual Entry</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_student">
                    
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="student_id" name="student_id" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="course" class="form-label">Course</label>
                        <input type="text" class="form-control" id="course" name="course">
                    </div>
                    
                    <div class="mb-3">
                        <label for="batch_year" class="form-label">Batch Year</label>
                        <input type="text" class="form-control" id="batch_year" name="batch_year" placeholder="e.g., 2023-2024">
                    </div>
                    
                    <div class="mb-3">
                        <label for="institution_id" class="form-label">Institution <span class="text-danger">*</span></label>
                        <select class="form-select" id="institution_id" name="institution_id" required>
                            <option value="">Select Institution</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?php echo $inst['id']; ?>">
                                    <?php echo htmlspecialchars($inst['name']); ?> (<?php echo htmlspecialchars($inst['code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="enrollment_date" class="form-label">Enrollment Date</label>
                        <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="graduated">Graduated</option>
                            <option value="suspended">Suspended</option>
                            <option value="withdrawn">Withdrawn</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Student
                    </button>
                    <a href="<?php echo url('admin/students.php'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Students
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-file-csv"></i> CSV Import</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> CSV Format Instructions</h6>
                    <p class="mb-2">Your CSV file should have the following columns in order:</p>
                    <ol class="mb-2">
                        <li>Student ID (required)</li>
                        <li>Full Name (required)</li>
                        <li>Email</li>
                        <li>Phone</li>
                        <li>Course</li>
                        <li>Batch Year</li>
                        <li>Institution ID (required)</li>
                        <li>Enrollment Date (YYYY-MM-DD)</li>
                        <li>Status (active/graduated/suspended/withdrawn)</li>
                    </ol>
                    <p class="mb-0"><small>The first row should contain column headers.</small></p>
                </div>
                
                <div class="mb-3">
                    <a href="<?php echo url('admin/download_student_template.php'); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i> Download CSV Template
                    </a>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_csv">
                    
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Import Students
                    </button>
                </form>
                
                <hr>
                
                <h6>Available Institutions:</h6>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($institutions as $inst): ?>
                                <tr>
                                    <td><?php echo $inst['id']; ?></td>
                                    <td><?php echo htmlspecialchars($inst['name']); ?></td>
                                    <td><?php echo htmlspecialchars($inst['code']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php renderAdminFooter(); ?>