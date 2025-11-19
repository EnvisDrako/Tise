<?php
/**
 * Website Management - Notices
 * Admin interface for managing website notices
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';

$pageTitle = 'Manage Notices';
$success_message = '';
$error_message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'delete':
                    $id = (int)$_POST['id'];
                    executeQuery("DELETE FROM notices WHERE id = ?", [$id]);
                    $success_message = "Notice deleted successfully!";
                    logEvent("Admin deleted notice ID: {$id}", 'INFO');
                    break;
                    
                case 'toggle_status':
                    $id = (int)$_POST['id'];
                    $status = (int)$_POST['status'];
                    executeQuery("UPDATE notices SET is_active = ? WHERE id = ?", [$status, $id]);
                    $success_message = "Notice status updated!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error in notice management: " . $e->getMessage(), 'ERROR');
    }
}

// Get all notices
$notices = fetchAll("
    SELECT n.*, u.full_name as created_by_name 
    FROM notices n 
    LEFT JOIN users u ON n.created_by = u.id 
    ORDER BY n.created_at DESC
");

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-gradient mb-2">
                    <i class="fas fa-bullhorn"></i> Manage Notices
                </h1>
                <p class="lead mb-0">Post and manage website notices</p>
            </div>
            <div>
                <a href="<?php echo url('admin/website/add_notice.php'); ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-plus-circle"></i> Add Notice
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
        <h5 class="mb-0"><i class="fas fa-list"></i> All Notices (<?php echo count($notices); ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($notices)): ?>
            <div class="text-center py-4">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Notices Found</h5>
                <p class="text-muted">Click "Add Notice" to create your first notice.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notices as $notice): ?>
                            <tr>
                                <td><?php echo $notice['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($notice['title']); ?></strong>
                                    <?php if ($notice['attachment_path']): ?>
                                        <br><small class="text-muted"><i class="fas fa-paperclip"></i> Has attachment</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $typeColors = [
                                        'general' => 'secondary',
                                        'important' => 'warning',
                                        'urgent' => 'danger',
                                        'event' => 'info'
                                    ];
                                    $color = $typeColors[$notice['type']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>">
                                        <?php echo ucfirst($notice['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($notice['start_date'])); ?></td>
                                <td>
                                    <?php echo $notice['end_date'] ? date('M j, Y', strtotime($notice['end_date'])) : 'No expiry'; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $notice['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $notice['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-sm btn-<?php echo $notice['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $notice['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($notice['created_by_name']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo url('admin/website/add_notice.php?id=' . $notice['id']); ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteNotice(<?php echo $notice['id']; ?>)" title="Delete">
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
function deleteNotice(id) {
    if (confirm('Are you sure you want to delete this notice?')) {
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
