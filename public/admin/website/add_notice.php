<?php
/**
 * Website Management - Add/Edit Notice
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';

$pageTitle = 'Add Notice';
$success_message = '';
$error_message = '';
$notice = null;

// Check if editing
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($edit_id > 0) {
    $notice = fetchOne("SELECT * FROM notices WHERE id = ?", [$edit_id]);
    if ($notice) {
        $pageTitle = 'Edit Notice';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = sanitizeInput($_POST['title']);
        $content = $_POST['content']; // Allow HTML
        $type = sanitizeInput($_POST['type']);
        $start_date = sanitizeInput($_POST['start_date']);
        $end_date = !empty($_POST['end_date']) ? sanitizeInput($_POST['end_date']) : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle file upload
        $attachment_path = $notice['attachment_path'] ?? null;
        $attachment_name = $notice['attachment_name'] ?? null;
        
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/website/notices/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $file_name = 'notice_' . time() . '_' . uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
                $attachment_path = 'uploads/website/notices/' . $file_name;
                $attachment_name = $_FILES['attachment']['name'];
            }
        }
        
        if ($edit_id > 0) {
            // Update
            executeQuery("
                UPDATE notices SET 
                    title = ?, content = ?, type = ?, start_date = ?, end_date = ?, 
                    attachment_path = ?, attachment_name = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [$title, $content, $type, $start_date, $end_date, $attachment_path, $attachment_name, $is_active, $edit_id]);
            
            $success_message = "Notice updated successfully!";
            logEvent("Admin updated notice ID: {$edit_id}", 'INFO');
        } else {
            // Insert
            executeQuery("
                INSERT INTO notices (title, content, type, start_date, end_date, attachment_path, attachment_name, is_active, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$title, $content, $type, $start_date, $end_date, $attachment_path, $attachment_name, $is_active, getCurrentUserId()]);
            
            $success_message = "Notice created successfully!";
            logEvent("Admin created new notice: {$title}", 'INFO');
        }
        
        // Redirect after success
        header("Location: " . url('admin/website/notices.php'));
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error saving notice: " . $e->getMessage(), 'ERROR');
    }
}

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-bullhorn"></i> <?php echo $pageTitle; ?>
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
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Notice Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($notice['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="8" required><?php echo htmlspecialchars($notice['content'] ?? ''); ?></textarea>
                        <small class="text-muted">You can use basic HTML tags</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Notice Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="general" <?php echo ($notice['type'] ?? '') === 'general' ? 'selected' : ''; ?>>General</option>
                                <option value="important" <?php echo ($notice['type'] ?? '') === 'important' ? 'selected' : ''; ?>>Important</option>
                                <option value="urgent" <?php echo ($notice['type'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                                <option value="event" <?php echo ($notice['type'] ?? '') === 'event' ? 'selected' : ''; ?>>Event</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="attachment" class="form-label">Attachment (Optional)</label>
                            <input type="file" class="form-control" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <?php if (!empty($notice['attachment_name'])): ?>
                                <small class="text-muted">Current: <?php echo htmlspecialchars($notice['attachment_name']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo $notice['start_date'] ?? date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo $notice['end_date'] ?? ''; ?>">
                            <small class="text-muted">Leave empty for no expiry</small>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                               <?php echo ($notice['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Active (visible on website)
                        </label>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $edit_id > 0 ? 'Update' : 'Create'; ?> Notice
                        </button>
                        <a href="<?php echo url('admin/website/notices.php'); ?>" class="btn btn-secondary">
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
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Notice Types</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><span class="badge bg-secondary">General</span> - Regular announcements</li>
                    <li class="mb-2"><span class="badge bg-warning">Important</span> - Important information</li>
                    <li class="mb-2"><span class="badge bg-danger">Urgent</span> - Urgent notices</li>
                    <li class="mb-2"><span class="badge bg-info">Event</span> - Event announcements</li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small">
                    <li>Use clear, concise titles</li>
                    <li>Set end dates for time-sensitive notices</li>
                    <li>Attach relevant documents if needed</li>
                    <li>Use appropriate notice types for visibility</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php renderAdminFooter(); ?>
