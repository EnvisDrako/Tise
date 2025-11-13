<?php
/**
 * Enquirer - My Assigned Enquiries
 * View and manage assigned enquiries
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include enquirer authorization
require_once __DIR__ . '/../../includes/auth_check_enquirer.php';

// Page title
$pageTitle = 'My Enquiries';

// Get current user's assigned enquiries
$userId = getCurrentUserId();

try {
    $enquiries_query = "
        SELECT * FROM enquiries 
        WHERE assigned_to_user_id = ? 
        ORDER BY 
            CASE status 
                WHEN 'new' THEN 1 
                WHEN 'assigned' THEN 2 
                WHEN 'contacted' THEN 3 
                WHEN 'follow_up' THEN 4 
                ELSE 5 
            END,
            created_at DESC
    ";
    $enquiries = fetchAll($enquiries_query, [$userId]);
    
} catch (Exception $e) {
    $error_message = "Error fetching enquiries: " . $e->getMessage();
    $enquiries = [];
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $enquiry_id = (int)$_POST['enquiry_id'];
        $new_status = $_POST['status'];
        $notes = trim($_POST['notes'] ?? '');
        
        try {
            // Verify this enquiry is assigned to current user
            $verify_query = "SELECT id FROM enquiries WHERE id = ? AND assigned_to_user_id = ?";
            $verify_result = fetchOne($verify_query, [$enquiry_id, $userId]);
            
            if ($verify_result) {
                $update_query = "UPDATE enquiries SET status = ?, notes = ? WHERE id = ?";
                $result = executeQuery($update_query, [$new_status, $notes, $enquiry_id]);
                
                if ($result) {
                    $success_message = "Enquiry status updated successfully!";
                    logEvent("Enquirer updated enquiry ID {$enquiry_id} status to {$new_status}", 'INFO');
                    
                    // Refresh enquiries list
                    $enquiries = fetchAll($enquiries_query, [$userId]);
                } else {
                    $error_message = "Failed to update enquiry status.";
                }
            } else {
                $error_message = "You are not authorized to update this enquiry.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
            logEvent("Error updating enquiry status: " . $e->getMessage(), 'ERROR');
        }
    }
}

// Render header
renderEnquirerHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-envelope"></i> My Assigned Enquiries
        </h1>
        <p class="lead">Manage and follow up on your assigned enquiries</p>
    </div>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> My Enquiries</h5>
                <div class="card-tools">
                    <span class="badge bg-info"><?php echo count($enquiries); ?> Assigned</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($enquiries)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Enquiries Assigned</h5>
                        <p class="text-muted">You don't have any enquiries assigned to you yet. Check back later or contact your administrator.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Course Interest</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enquiries as $enquiry): ?>
                                    <tr class="<?php echo $enquiry['status'] === 'new' ? 'table-warning' : ''; ?>">
                                        <td><?php echo $enquiry['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($enquiry['full_name']); ?></strong>
                                            <?php if ($enquiry['status'] === 'new'): ?>
                                                <span class="badge bg-warning ms-1">NEW</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="text-decoration-none">
                                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($enquiry['email']); ?>
                                                </a>
                                            </div>
                                            <div>
                                                <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="text-decoration-none">
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($enquiry['phone']); ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($enquiry['course_interest']); ?></td>
                                        <td>
                                            <span class="badge status-<?php echo $enquiry['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $enquiry['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y g:i A', strtotime($enquiry['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="viewEnquiry(<?php echo $enquiry['id']; ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success" 
                                                        onclick="updateStatus(<?php echo $enquiry['id']; ?>, '<?php echo $enquiry['status']; ?>')"
                                                        title="Update Status">
                                                    <i class="fas fa-edit"></i>
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
    </div>
</div>

<!-- View Enquiry Modal -->
<div class="modal fade" id="viewEnquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope"></i> Enquiry Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="enquiryDetails">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Update Enquiry Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="enquiry_id" id="updateEnquiryId">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status:</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="assigned">Assigned</option>
                            <option value="contacted">Contacted</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="follow_up">Follow Up Required</option>
                            <option value="enrolled">Enrolled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional):</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about your interaction with this enquiry..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.status-new { background-color: #17a2b8; }
.status-assigned { background-color: #ffc107; color: #000; }
.status-contacted { background-color: #28a745; }
.status-interested { background-color: #007bff; }
.status-not_interested { background-color: #6c757d; }
.status-enrolled { background-color: #28a745; }
.status-follow_up { background-color: #fd7e14; }

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.table-warning {
    --bs-table-accent-bg: rgba(255, 193, 7, 0.1);
}
</style>

<script>
function viewEnquiry(enquiryId) {
    // Get enquiry data from the table row
    const row = event.target.closest('tr');
    const cells = row.querySelectorAll('td');
    
    const enquiryData = {
        id: cells[0].textContent,
        name: cells[1].querySelector('strong').textContent,
        email: cells[2].querySelector('a[href^="mailto:"]').textContent.replace('✉ ', ''),
        phone: cells[2].querySelector('a[href^="tel:"]').textContent.replace('📞 ', ''),
        course: cells[3].textContent,
        status: cells[4].textContent.trim(),
        submitted: cells[5].textContent.trim()
    };
    
    document.getElementById('enquiryDetails').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-user"></i> Personal Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Name:</strong></td><td>${enquiryData.name}</td></tr>
                    <tr><td><strong>Email:</strong></td><td><a href="mailto:${enquiryData.email}">${enquiryData.email}</a></td></tr>
                    <tr><td><strong>Phone:</strong></td><td><a href="tel:${enquiryData.phone}">${enquiryData.phone}</a></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-info-circle"></i> Enquiry Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Course Interest:</strong></td><td>${enquiryData.course}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-info">${enquiryData.status}</span></td></tr>
                    <tr><td><strong>Submitted:</strong></td><td>${enquiryData.submitted}</td></tr>
                </table>
            </div>
        </div>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Complete enquiry details including message, education level, experience, and documents would be displayed here in a full implementation.
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('viewEnquiryModal'));
    modal.show();
}

function updateStatus(enquiryId, currentStatus) {
    document.getElementById('updateEnquiryId').value = enquiryId;
    document.getElementById('status').value = currentStatus;
    
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}
</script>

<?php renderEnquirerFooter(); ?>