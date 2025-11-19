<?php
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';

$pageTitle = 'Add Placement';
$success_message = '';
$error_message = '';
$placement = null;

$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id > 0) {
    $placement = fetchOne("SELECT * FROM placements WHERE id = ?", [$edit_id]);
    if ($placement) $pageTitle = 'Edit Placement';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $student_name = sanitizeInput($_POST['student_name']);
        $company_name = sanitizeInput($_POST['company_name']);
        $position = sanitizeInput($_POST['position']);
        $package = sanitizeInput($_POST['package']);
        $placement_year = (int)$_POST['placement_year'];
        $description = sanitizeInput($_POST['description']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $company_logo = $placement['company_logo'] ?? null;
        
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/website/placements/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_ext = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
            $file_name = 'company_' . time() . '_' . uniqid() . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_dir . $file_name)) {
                $company_logo = 'uploads/website/placements/' . $file_name;
            }
        }
        
        if ($edit_id > 0) {
            executeQuery("
                UPDATE placements SET 
                    student_name = ?, company_name = ?, position = ?, package = ?, 
                    placement_year = ?, company_logo = ?, description = ?, 
                    is_featured = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [$student_name, $company_name, $position, $package, $placement_year, $company_logo, $description, $is_featured, $is_active, $edit_id]);
            
            $success_message = "Placement updated successfully!";
        } else {
            executeQuery("
                INSERT INTO placements (student_name, company_name, position, package, placement_year, company_logo, description, is_featured, is_active, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$student_name, $company_name, $position, $package, $placement_year, $company_logo, $description, $is_featured, $is_active, getCurrentUserId()]);
            
            $success_message = "Placement created successfully!";
        }
        
        header("Location: " . url('admin/website/placements.php'));
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $pageTitle; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="student_name" class="form-label">Student Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="student_name" name="student_name" 
                               value="<?php echo htmlspecialchars($placement['student_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name" 
                                   value="<?php echo htmlspecialchars($placement['company_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="position" name="position" 
                                   value="<?php echo htmlspecialchars($placement['position'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="package" class="form-label">Package (Salary)</label>
                            <input type="text" class="form-control" id="package" name="package" 
                                   value="<?php echo htmlspecialchars($placement['package'] ?? ''); ?>" placeholder="e.g., 12 LPA">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="placement_year" class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="placement_year" name="placement_year" 
                                   value="<?php echo $placement['placement_year'] ?? date('Y'); ?>" 
                                   min="2000" max="<?php echo date('Y') + 1; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_logo" class="form-label">Company Logo</label>
                        <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                        <?php if (!empty($placement['company_logo'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo asset($placement['company_logo']); ?>" alt="Current logo" style="height: 50px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($placement['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" 
                               <?php echo ($placement['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">
                            <i class="fas fa-star text-warning"></i> Featured Placement
                        </label>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                               <?php echo ($placement['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Active (visible on website)
                        </label>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $edit_id > 0 ? 'Update' : 'Create'; ?> Placement
                        </button>
                        <a href="<?php echo url('admin/website/placements.php'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php renderAdminFooter(); ?>
