<?php
/**
 * Student Registration Form
 * Allows faculty to register new students for their institution
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include faculty authorization
require_once __DIR__ . '/../../includes/auth_check_faculty.php';

// Page title
$pageTitle = 'Register Student';

// Get institution details
$institution = getCurrentInstitution();
$institutionId = getCurrentUserInstitutionId();

// Initialize variables
$errors = [];
$success = false;
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $formData = [
        'student_id' => sanitizeInput($_POST['student_id'] ?? ''),
        'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'address' => sanitizeInput($_POST['address'] ?? ''),
        'course' => sanitizeInput($_POST['course'] ?? ''),
        'batch_year' => sanitizeInput($_POST['batch_year'] ?? '')
    ];
    
    // Validation
    if (empty($formData['student_id'])) {
        $errors[] = 'Student ID is required';
    }
    
    if (empty($formData['full_name'])) {
        $errors[] = 'Full name is required';
    }
    
    if (!empty($formData['email']) && !isValidEmail($formData['email'])) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (!empty($formData['phone']) && !isValidPhone($formData['phone'])) {
        $errors[] = 'Please enter a valid phone number';
    }
    
    if (empty($formData['course'])) {
        $errors[] = 'Course is required';
    }
    
    if (empty($formData['batch_year'])) {
        $errors[] = 'Batch year is required';
    }
    
    // Check for duplicate student ID
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
            $stmt->execute([$formData['student_id']]);
            if ($stmt->fetch()) {
                $errors[] = 'Student ID already exists. Please use a different ID.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error occurred while checking student ID';
            logError("Error checking duplicate student ID: " . $e->getMessage());
        }
    }
    
    // Handle photo upload
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/student_photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = $formData['student_id'] . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                $photoPath = 'uploads/student_photos/' . $fileName;
            } else {
                $errors[] = 'Failed to upload photo';
            }
        } else {
            $errors[] = 'Photo must be a JPG, JPEG, PNG, or GIF file';
        }
    }
    
    // Insert student record
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO students (
                    institution_id, student_id, full_name, email, phone, 
                    address, course, batch_year, photo_path, registered_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $institutionId,
                $formData['student_id'],
                $formData['full_name'],
                $formData['email'],
                $formData['phone'],
                $formData['address'],
                $formData['course'],
                $formData['batch_year'],
                $photoPath,
                getCurrentUserId()
            ]);
            
            $success = true;
            $formData = []; // Clear form data on success
            
        } catch (PDOException $e) {
            $errors[] = 'Failed to register student. Please try again.';
            logError("Error registering student: " . $e->getMessage());
        }
    }
}

// Render header
renderFacultyHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-gradient">
                <i class="fas fa-user-plus"></i> Register Student
            </h1>
            <a href="students.php" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> View Students
            </a>
        </div>
        
        <?php if ($institution): ?>
            <div class="alert alert-info">
                <i class="fas fa-university"></i>
                Registering student for <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        Student registered successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus"></i> Student Information
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="student_id" 
                                   name="student_id" 
                                   value="<?php echo htmlspecialchars($formData['student_id'] ?? ''); ?>"
                                   required>
                            <div class="form-text">Unique identifier for the student</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" 
                                  id="address" 
                                  name="address" 
                                  rows="3"><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="course" 
                                   name="course" 
                                   value="<?php echo htmlspecialchars($formData['course'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="batch_year" class="form-label">Batch Year <span class="text-danger">*</span></label>
                            <select class="form-select" id="batch_year" name="batch_year" required>
                                <option value="">Select Batch Year</option>
                                <?php 
                                $currentYear = date('Y');
                                for ($year = $currentYear + 2; $year >= $currentYear - 10; $year--): 
                                    $selected = ($formData['batch_year'] ?? '') == $year ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $year; ?>" <?php echo $selected; ?>><?php echo $year; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">Student Photo</label>
                        <input type="file" 
                               class="form-control" 
                               id="photo" 
                               name="photo" 
                               accept="image/*">
                        <div class="form-text">Optional. Accepted formats: JPG, JPEG, PNG, GIF (Max 5MB)</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Register Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Registration Guidelines
            </div>
            <div class="card-body">
                <h6>Student ID Format</h6>
                <p class="small">Use a unique identifier for each student. Consider including institution code and year.</p>
                
                <h6>Required Information</h6>
                <ul class="small">
                    <li>Student ID (must be unique)</li>
                    <li>Full Name</li>
                    <li>Course</li>
                    <li>Batch Year</li>
                </ul>
                
                <h6>Optional Information</h6>
                <ul class="small">
                    <li>Email Address</li>
                    <li>Phone Number</li>
                    <li>Address</li>
                    <li>Student Photo</li>
                </ul>
                
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle"></i>
                    Students can be marked as exam eligible later from the student management page.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Auto-generate student ID suggestion
document.getElementById('full_name').addEventListener('blur', function() {
    const studentIdField = document.getElementById('student_id');
    if (!studentIdField.value) {
        const name = this.value.trim();
        if (name) {
            const year = new Date().getFullYear();
            const nameParts = name.split(' ');
            const initials = nameParts.map(part => part.charAt(0).toUpperCase()).join('');
            const suggestion = `<?php echo $institution['code'] ?? 'STU'; ?>${year}${initials}${Math.floor(Math.random() * 100).toString().padStart(2, '0')}`;
            studentIdField.value = suggestion;
        }
    }
});
</script>

<?php renderFacultyFooter(); ?>