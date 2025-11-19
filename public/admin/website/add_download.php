<?php
/**
 * Website Management - Add/Edit Download
 * Supports both file uploads and external links
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';

$pageTitle = 'Add Download';
$success_message = '';
$error_message = '';
$download = null;

// Check if editing
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id > 0) {
    $download = fetchOne("SELECT * FROM downloads WHERE id = ?", [$edit_id]);
    if ($download) {
        $pageTitle = 'Edit Download';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $category = sanitizeInput($_POST['category']);
        $file_type = sanitizeInput($_POST['file_type']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $file_path = $download['file_path'] ?? null;
        $external_link = $download['external_link'] ?? null;
        $file_size = $download['file_size'] ?? null;
        
        if ($file_type === 'link') {
            // External link
            $external_link = sanitizeInput($_POST['external_link']);
            $file_path = null;
            $file_size = null;
        } else {
            // File upload
            $external_link = null;
            
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/website/downloads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $file_name = 'download_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                    $file_path = 'uploads/website/downloads/' . $file_name;
                    $file_size = formatFileSize($_FILES['file']['size']);
                }
            }
        }
        
        if ($edit_id > 0) {
            // Update
            executeQuery("
                UPDATE downloads SET 
                    title = ?, description = ?, category = ?, file_type = ?, 
                    file_path = ?, external_link = ?, file_size = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [$title, $description, $category, $file_type, $file_path, $external_link, $file_size, $is_active, $edit_id]);
            
            $success_message = "Download updated successfully!";
            logEvent("Admin updated download ID: {$edit_id}", 'INFO');
        } else {
            // Insert
            executeQuery("
                INSERT INTO downloads (title, description, category, file_type, file_path, external_link, file_size, is_active, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$title, $description, $category, $file_type, $file_path, $external_link, $file_size, $is_active, getCurrentUserId()]);
            
            $success_message = "Download created successfully!";
            logEvent("Admin created new download: {$title}", 'INFO');
        }
        
        header("Location: " . url('admin/website/downloads.php'));
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error saving download: " . $e->getMessage(), 'ERROR');
    }
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-download"></i> <?php echo $pageTitle; ?>
        </h1>
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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="downloadForm">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($download['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($download['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="syllabus" <?php echo ($download['category'] ?? '') === 'syllabus' ? 'selected' : ''; ?>>Syllabus</option>
                                <option value="forms" <?php echo ($download['category'] ?? '') === 'forms' ? 'selected' : ''; ?>>Forms</option>
                                <option value="brochures" <?php echo ($download['category'] ?? '') === 'brochures' ? 'selected' : ''; ?>>Brochures</option>
                                <option value="prospectus" <?php echo ($download['category'] ?? '') === 'prospectus' ? 'selected' : ''; ?>>Prospectus</option>
                                <option value="results" <?php echo ($download['category'] ?? '') === 'results' ? 'selected' : ''; ?>>Results</option>
                                <option value="other" <?php echo ($download['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="file_type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="file_type" name="file_type" required onchange="toggleFileType()">
                                <option value="upload" <?php echo ($download['file_type'] ?? 'upload') === 'upload' ? 'selected' : ''; ?>>Upload File</option>
                                <option value="link" <?php echo ($download['file_type'] ?? '') === 'link' ? 'selected' : ''; ?>>External Link</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- File Upload Section -->
                    <div id="uploadSection" class="mb-3">
                        <label for="file" class="form-label">Upload File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip">
                        <?php if (!empty($download['file_path'])): ?>
                            <small class="text-muted">Current file: <?php echo basename($download['file_path']); ?> (<?php echo $download['file_size']; ?>)</small>
                        <?php endif; ?>
                        <small class="form-text text-muted d-block">Accepted: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP (Max 10MB)</small>
                    </div>
                    
                    <!-- External Link Section -->
                    <div id="linkSection" class="mb-3" style="display:none;">
                        <label for="external_link" class="form-label">External Link <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="external_link" name="external_link" 
                               value="<?php echo htmlspecialchars($download['external_link'] ?? ''); ?>" 
                               placeholder="https://example.com/file.pdf">
                        <small class="form-text text-muted">Enter the full URL including https://</small>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                               <?php echo ($download['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Active (visible on website)
                        </label>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $edit_id > 0 ? 'Update' : 'Create'; ?> Download
                        </button>
                        <a href="<?php echo url('admin/website/downloads.php'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Download Types</h6>
            </div>
            <div class="card-body">
                <p><strong>Upload File:</strong> Upload a file from your computer to the server.</p>
                <p><strong>External Link:</strong> Provide a link to a file hosted elsewhere (Google Drive, Dropbox, etc.).</p>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-folder"></i> Categories</h6>
            </div>
            <div class="card-body">
                <ul class="small list-unstyled">
                    <li><strong>Syllabus:</strong> Course syllabi</li>
                    <li><strong>Forms:</strong> Application forms</li>
                    <li><strong>Brochures:</strong> Marketing materials</li>
                    <li><strong>Prospectus:</strong> Course prospectus</li>
                    <li><strong>Results:</strong> Exam results</li>
                    <li><strong>Other:</strong> Miscellaneous files</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFileType() {
    const fileType = document.getElementById('file_type').value;
    const uploadSection = document.getElementById('uploadSection');
    const linkSection = document.getElementById('linkSection');
    const fileInput = document.getElementById('file');
    const linkInput = document.getElementById('external_link');
    
    if (fileType === 'link') {
        uploadSection.style.display = 'none';
        linkSection.style.display = 'block';
        fileInput.removeAttribute('required');
        linkInput.setAttribute('required', 'required');
    } else {
        uploadSection.style.display = 'block';
        linkSection.style.display = 'none';
        linkInput.removeAttribute('required');
        <?php if (!$edit_id): ?>
        fileInput.setAttribute('required', 'required');
        <?php endif; ?>
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleFileType();
});
</script>

<?php renderAdminFooter(); ?>
