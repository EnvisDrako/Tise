<?php
/**
 * Add Enquiry - Manual Entry and CSV Import
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../includes/auth_check_admin.php';

$pageTitle = 'Add Enquiry';
$success_message = '';
$error_message = '';

// Handle manual enquiry addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_enquiry') {
    try {
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $course_interest = sanitizeInput($_POST['course_interest']);
        $message = sanitizeInput($_POST['message']);
        $status = sanitizeInput($_POST['status']);
        $assigned_to = !empty($_POST['assigned_to_user_id']) ? (int)$_POST['assigned_to_user_id'] : null;
        
        // Validate required fields
        if (empty($full_name) || (empty($email) && empty($phone))) {
            throw new Exception("Full Name and at least one contact method (Email or Phone) are required.");
        }
        
        // Corrected Query - Use this:
        executeQuery("
            INSERT INTO enquiries (
                full_name, email, phone, address, notes, -- Columns updated
                course_interest, education_level, preferred_schedule, status, assigned_to_user_id, created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [
            $full_name, $email, $phone, 
    
            // 👇 The Corrected Variables (Assuming $address and $notes are now defined)
            $address, $notes,
    
            $course_interest, $education_level, $preferred_schedule, $status, $assigned_to
        ]);
        
        $success_message = "Enquiry added successfully!";
        logEvent("Admin added enquiry: {$full_name}", 'INFO');
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error adding enquiry: " . $e->getMessage(), 'ERROR');
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
                // Indexes based on the Excel template: 
                // 0: Name, 1: Email, 2: Phone, 3: Course, 4: Message, 5: Status, 6: Assigned ID
                $full_name = trim($data[0] ?? '');
                $email = trim($data[1] ?? '');
                $phone = trim($data[2] ?? '');
                $course_interest = trim($data[3] ?? '');
                $message = trim($data[4] ?? '');
                $status = trim($data[5] ?? 'new');
                $assigned_to = !empty($data[6]) ? (int)$data[6] : null;
                
                // Validate required fields
                if (empty($full_name) || (empty($email) && empty($phone))) {
                    $errors[] = "Row {$row_number}: Missing required fields (Name and Email/Phone)";
                    continue;
                }
                
                // Insert enquiry
                executeQuery("
                    INSERT INTO enquiries (full_name, email, phone, course_interest, message, status, assigned_to_user_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ", [$full_name, $email, $phone, $course_interest, $message, $status, $assigned_to]);
                
                $imported++;
                
            } catch (Exception $e) {
                $errors[] = "Row {$row_number}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $success_message = "Import completed! {$imported} enquiries imported successfully.";
        if (!empty($errors)) {
            $error_message = "Some rows had errors:<br>" . implode("<br>", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $error_message .= "<br>... and " . (count($errors) - 10) . " more errors.";
            }
        }
        
        logEvent("Admin imported {$imported} enquiries via CSV", 'INFO');
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error importing enquiries CSV: " . $e->getMessage(), 'ERROR');
    }
}

// Get enquirer users for assignment dropdown
$enquirers = fetchAll("SELECT id, username, full_name FROM users WHERE role = 'enquirer' AND is_active = 1 ORDER BY full_name");

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-envelope-open-text"></i> Add Enquiries
        </h1>
        <p class="lead">Add enquiries manually or import from CSV file</p>
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
                <h5 class="mb-0"><i class="fas fa-edit"></i> Manual Entry</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_enquiry">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="course_interest" class="form-label">Course Interest</label>
                        <input type="text" class="form-control" id="course_interest" name="course_interest">
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="new">New</option>
                            <option value="assigned">Assigned</option>
                            <option value="contacted">Contacted</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="enrolled">Enrolled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assigned_to_user_id" class="form-label">Assign To</label>
                        <select class="form-select" id="assigned_to_user_id" name="assigned_to_user_id">
                            <option value="">Unassigned</option>
                            <?php foreach ($enquirers as $enquirer): ?>
                                <option value="<?php echo $enquirer['id']; ?>">
                                    <?php echo htmlspecialchars($enquirer['full_name'] ?: $enquirer['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Enquiry
                    </button>
                    <a href="<?php echo url('admin/enquiries.php'); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Enquiries
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
                        <li>Full Name (required)</li>
                        <li>Email (required if no Phone)</li>
                        <li>Phone (required if no Email)</li>
                        <li>Course Interest</li>
                        <li>Message</li>
                        <li>Status (new, assigned, contacted, etc.)</li>
                        <li>Assigned To User ID (optional number)</li>
                    </ol>
                    <p class="mb-0"><small>The first row should contain column headers.</small></p>
                </div>
                
                <div class="mb-3">
                    <a href="<?php echo url('admin/download_enquiry_template.php'); ?>" class="btn btn-outline-primary btn-sm">
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
                        <i class="fas fa-upload"></i> Import Enquiries
                    </button>
                </form>
                
                <hr>
                
                <h6>Available Enquirer Staff:</h6>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enquirers as $enquirer): ?>
                                <tr>
                                    <td><?php echo $enquirer['id']; ?></td>
                                    <td><?php echo htmlspecialchars($enquirer['full_name'] ?: $enquirer['username']); ?></td>
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