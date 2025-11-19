<?php
/**
 * Website Management - Downloads
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';

$pageTitle = 'Manage Downloads';
$success_message = '';
$error_message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'delete':
                    $id = (int)$_POST['id'];
                    $download = fetchOne("SELECT * FROM downloads WHERE id = ?", [$id]);
                    
                    // Delete file if it's an upload
                    if ($download && $download['file_type'] === 'upload' && $download['file_path']) {
                        $file_path = __DIR__ . '/../../' . $download['file_path'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                    
                    executeQuery("DELETE FROM downloads WHERE id = ?", [$id]);
                    $success_message = "Download deleted successfully!";
                    logEvent("Admin deleted download ID: {$id}", 'INFO');
                    break;
                    
                case 'toggle_status':
                    $id = (int)$_POST['id'];
                    $status = (int)$_POST['status'];
                    executeQuery("UPDATE downloads SET is_active = ? WHERE id = ?", [$status, $id]);
                    $success_message = "Download status updated!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error in download management: " . $e->getMessage(), 'ERROR');
    }
}

// Get all downloads
$downloads = fetchAll("
    SELECT d.*, u.full_name as created_by_name 
    FROM downloads d 
    LEFT JOIN users u ON d.created_by = u.id 
    ORDER BY d.created_at DESC
");

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-gradient mb-2">
                    <i class="fas fa-download"></i> Manage Downloads
                </h1>
                <p class="lead mb-0">Manage downloadable files and external links</p>
            </div>
            <div>
                <a href="<?php echo url('admin/website/add_download.php'); ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-plus-circle"></i> Add Download
                </a>
            </div>
        </div>
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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> All Downloads (<?php echo count($downloads); ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($downloads)): ?>
            <div class="text-center py-4">
                <i class="fas fa-download fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Downloads Found</h5>
                <p class="text-muted">Click "Add Download" to create your first download item.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Size/Link</th>
                            <th>Downloads</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($downloads as $download): ?>
                            <tr>
                                <td><?php echo $download['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($download['title']); ?></strong>
                                    <?php if ($download['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($download['description'], 0, 50)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-primary"><?php echo ucfirst($download['category']); ?></span></td>
                                <td>
                                    <?php if ($download['file_type'] === 'upload'): ?>
                                        <span class="badge bg-success"><i class="fas fa-file"></i> Upload</span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><i class="fas fa-link"></i> Link</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($download['file_type'] === 'upload'): ?>
                                        <?php echo $download['file_size'] ?? 'N/A'; ?>
                                    <?php else: ?>
                                        <small class="text-muted">External</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($download['download_count']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $download['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $download['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-sm btn-<?php echo $download['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $download['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo url('admin/website/add_download.php?id=' . $download['id']); ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteDownload(<?php echo $download['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteDownload(id) {
    if (confirm('Are you sure you want to delete this download? This will also delete the file if it was uploaded.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php renderAdminFooter(); ?>
