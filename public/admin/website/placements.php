<?php
/**
 * Website Management - Placements
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';

$pageTitle = 'Manage Placements';
$success_message = '';
$error_message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'delete':
                    $id = (int)$_POST['id'];
                    $placement = fetchOne("SELECT * FROM placements WHERE id = ?", [$id]);
                    
                    // Delete logo if exists
                    if ($placement && $placement['company_logo']) {
                        $logo_path = __DIR__ . '/../../' . $placement['company_logo'];
                        if (file_exists($logo_path)) {
                            unlink($logo_path);
                        }
                    }
                    
                    executeQuery("DELETE FROM placements WHERE id = ?", [$id]);
                    $success_message = "Placement deleted successfully!";
                    logEvent("Admin deleted placement ID: {$id}", 'INFO');
                    break;
                    
                case 'toggle_status':
                    $id = (int)$_POST['id'];
                    $status = (int)$_POST['status'];
                    executeQuery("UPDATE placements SET is_active = ? WHERE id = ?", [$status, $id]);
                    $success_message = "Placement status updated!";
                    break;
                    
                case 'toggle_featured':
                    $id = (int)$_POST['id'];
                    $featured = (int)$_POST['featured'];
                    executeQuery("UPDATE placements SET is_featured = ? WHERE id = ?", [$featured, $id]);
                    $success_message = "Featured status updated!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logEvent("Error in placement management: " . $e->getMessage(), 'ERROR');
    }
}

// Get all placements
$placements = fetchAll("
    SELECT p.*, u.full_name as created_by_name 
    FROM placements p 
    LEFT JOIN users u ON p.created_by = u.id 
    ORDER BY p.placement_year DESC, p.created_at DESC
");

// Get statistics
$stats = fetchOne("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN placement_year = YEAR(CURDATE()) THEN 1 END) as this_year,
        COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured
    FROM placements WHERE is_active = 1
");

renderAdminHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-gradient mb-2">
                    <i class="fas fa-briefcase"></i> Manage Placements
                </h1>
                <p class="lead mb-0">Showcase student placements and success stories</p>
            </div>
            <div>
                <a href="<?php echo url('admin/website/add_placement.php'); ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-plus-circle"></i> Add Placement
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?php echo number_format($stats['total']); ?></h3>
                <p class="mb-0">Total Placements</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?php echo number_format($stats['this_year']); ?></h3>
                <p class="mb-0">This Year</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3><?php echo number_format($stats['featured']); ?></h3>
                <p class="mb-0">Featured</p>
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
        <h5 class="mb-0"><i class="fas fa-list"></i> All Placements (<?php echo count($placements); ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($placements)): ?>
            <div class="text-center py-4">
                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Placements Found</h5>
                <p class="text-muted">Click "Add Placement" to create your first placement record.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Package</th>
                            <th>Year</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($placements as $placement): ?>
                            <tr>
                                <td><?php echo $placement['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($placement['student_name']); ?></strong></td>
                                <td>
                                    <?php if ($placement['company_logo']): ?>
                                        <img src="<?php echo asset($placement['company_logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($placement['company_name']); ?>" 
                                             style="height: 30px; margin-right: 8px;">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($placement['company_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($placement['position']); ?></td>
                                <td><?php echo htmlspecialchars($placement['package'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-info"><?php echo $placement['placement_year']; ?></span></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="id" value="<?php echo $placement['id']; ?>">
                                        <input type="hidden" name="featured" value="<?php echo $placement['is_featured'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-sm btn-<?php echo $placement['is_featured'] ? 'warning' : 'outline-secondary'; ?>">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $placement['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $placement['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-sm btn-<?php echo $placement['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $placement['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo url('admin/website/add_placement.php?id=' . $placement['id']); ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deletePlacement(<?php echo $placement['id']; ?>)" title="Delete">
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
function deletePlacement(id) {
    if (confirm('Are you sure you want to delete this placement record?')) {
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
