<?php
/**
 * Enquirer - Status Update System with History
 * Comprehensive status management with history tracking and follow-up scheduling
 */

// Define system access constant
define('SYSTEM_ACCESS', true);

// Include enquirer authorization
require_once __DIR__ . '/../../includes/auth_check_enquirer.php';

// Page title
$pageTitle = 'Update Enquiry Status';

// Get current user ID
$userId = getCurrentUserId();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                handleStatusUpdate();
                break;
            case 'add_note':
                handleAddNote();
                break;
            case 'schedule_followup':
                handleScheduleFollowup();
                break;
        }
    }
}

// Get enquiry ID from URL or form
$enquiryId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['enquiry_id']) ? (int)$_POST['enquiry_id'] : 0);

// Get enquiry details if ID provided
$enquiry = null;
$history = [];
if ($enquiryId > 0) {
    $enquiry = getEnquiryDetails($enquiryId, $userId);
    if ($enquiry) {
        $history = getEnquiryHistory($enquiryId);
    }
}

// Get all assigned enquiries for dropdown
$assignedEnquiries = getAssignedEnquiries($userId);

/**
 * Handle status update with history tracking
 */
function handleStatusUpdate() {
    global $userId, $enquiryId;
    
    $newStatus = $_POST['status'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $followupDate = $_POST['followup_date'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    
    try {
        // Verify enquiry ownership
        $enquiry = getEnquiryDetails($enquiryId, $userId);
        if (!$enquiry) {
            throw new Exception("Enquiry not found or not assigned to you.");
        }
        
        $oldStatus = $enquiry['status'];
        
        // Start transaction
        global $pdo;
        $pdo->beginTransaction();
        
        // Update enquiry status
        $updateQuery = "
            UPDATE enquiries 
            SET status = ?, notes = CONCAT(COALESCE(notes, ''), ?, '\n'), updated_at = NOW()
            WHERE id = ? AND assigned_to_user_id = ?
        ";
        $stmt = $pdo->prepare($updateQuery);
        $noteEntry = $notes ? "[" . date('Y-m-d H:i:s') . "] " . $notes : '';
        $stmt->execute([$newStatus, $noteEntry, $enquiryId, $userId]);
        
        // Add to history
        addToHistory($enquiryId, $userId, 'status_update', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'priority' => $priority
        ]);
        
        // Schedule follow-up if provided
        if ($followupDate && $followupDate > date('Y-m-d')) {
            scheduleFollowup($enquiryId, $userId, $followupDate, $notes);
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Status updated successfully!";
        logEvent("Enquiry ID {$enquiryId} status updated from {$oldStatus} to {$newStatus}", 'INFO');
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Error updating status: " . $e->getMessage();
        logEvent("Error updating enquiry status: " . $e->getMessage(), 'ERROR');
    }
}

/**
 * Handle adding notes
 */
function handleAddNote() {
    global $userId, $enquiryId;
    
    $note = trim($_POST['note'] ?? '');
    $noteType = $_POST['note_type'] ?? 'general';
    
    if (empty($note)) {
        $_SESSION['error_message'] = "Note cannot be empty.";
        return;
    }
    
    try {
        // Verify enquiry ownership
        $enquiry = getEnquiryDetails($enquiryId, $userId);
        if (!$enquiry) {
            throw new Exception("Enquiry not found or not assigned to you.");
        }
        
        // Add to history
        addToHistory($enquiryId, $userId, 'note_added', [
            'note' => $note,
            'note_type' => $noteType
        ]);
        
        $_SESSION['success_message'] = "Note added successfully!";
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error adding note: " . $e->getMessage();
        logEvent("Error adding note: " . $e->getMessage(), 'ERROR');
    }
}

/**
 * Handle follow-up scheduling
 */
function handleScheduleFollowup() {
    global $userId, $enquiryId;
    
    $followupDate = $_POST['followup_date'] ?? '';
    $followupTime = $_POST['followup_time'] ?? '09:00';
    $reminder = $_POST['reminder'] ?? '';
    
    if (empty($followupDate)) {
        $_SESSION['error_message'] = "Follow-up date is required.";
        return;
    }
    
    try {
        $followupDateTime = $followupDate . ' ' . $followupTime;
        scheduleFollowup($enquiryId, $userId, $followupDateTime, $reminder);
        
        $_SESSION['success_message'] = "Follow-up scheduled successfully!";
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error scheduling follow-up: " . $e->getMessage();
        logEvent("Error scheduling follow-up: " . $e->getMessage(), 'ERROR');
    }
}

// Render header
renderEnquirerHeader($pageTitle);
?>

<div class="row">
    <div class="col-12">
        <h1 class="text-gradient mb-4">
            <i class="fas fa-edit"></i> Update Enquiry Status
        </h1>
        <p class="lead">Manage enquiry status with comprehensive history tracking</p>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Enquiry Selection -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-search"></i> Select Enquiry
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="mb-3">
                        <label for="enquiry_select" class="form-label">Choose Enquiry:</label>
                        <select name="id" id="enquiry_select" class="form-select" onchange="this.form.submit()">
                            <option value="">Select an enquiry...</option>
                            <?php foreach ($assignedEnquiries as $enq): ?>
                                <option value="<?php echo $enq['id']; ?>" <?php echo $enq['id'] == $enquiryId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($enq['full_name']); ?> - <?php echo htmlspecialchars($enq['phone']); ?>
                                    (<?php echo ucfirst($enq['status']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                
                <?php if ($enquiry): ?>
                    <div class="enquiry-summary">
                        <h6><i class="fas fa-user"></i> Current Enquiry</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?php echo htmlspecialchars($enquiry['full_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="text-decoration-none">
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($enquiry['phone']); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Course:</strong></td>
                                <td><?php echo htmlspecialchars($enquiry['course_interest']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge status-<?php echo $enquiry['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $enquiry['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Submitted:</strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($enquiry['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Status Update Form -->
    <div class="col-md-8 mb-4">
        <?php if ($enquiry): ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-edit"></i> Update Status & Add Notes
                </div>
                <div class="card-body">
                    <form method="POST" id="statusUpdateForm">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="enquiry_id" value="<?php echo $enquiryId; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">New Status:</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="assigned" <?php echo $enquiry['status'] === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                    <option value="contacted" <?php echo $enquiry['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                    <option value="interested" <?php echo $enquiry['status'] === 'interested' ? 'selected' : ''; ?>>Interested</option>
                                    <option value="not_interested" <?php echo $enquiry['status'] === 'not_interested' ? 'selected' : ''; ?>>Not Interested</option>
                                    <option value="follow_up" <?php echo $enquiry['status'] === 'follow_up' ? 'selected' : ''; ?>>Follow Up Required</option>
                                    <option value="enrolled" <?php echo $enquiry['status'] === 'enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority Level:</label>
                                <select name="priority" id="priority" class="form-select">
                                    <option value="low">Low Priority</option>
                                    <option value="medium" selected>Medium Priority</option>
                                    <option value="high">High Priority</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Update Notes:</label>
                            <textarea name="notes" id="notes" class="form-control" rows="4" 
                                      placeholder="Describe the interaction, outcome, or any important details..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="followup_date" class="form-label">Schedule Follow-up (Optional):</label>
                                <input type="date" name="followup_date" id="followup_date" class="form-control" 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="followup_time" class="form-label">Follow-up Time:</label>
                                <input type="time" name="followup_time" id="followup_time" class="form-control" value="09:00">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Notes Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-sticky-note"></i> Quick Notes
                </div>
                <div class="card-body">
                    <form method="POST" id="quickNoteForm">
                        <input type="hidden" name="action" value="add_note">
                        <input type="hidden" name="enquiry_id" value="<?php echo $enquiryId; ?>">
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <textarea name="note" class="form-control" rows="2" 
                                          placeholder="Add a quick note..." required></textarea>
                            </div>
                            <div class="col-md-2 mb-3">
                                <select name="note_type" class="form-select">
                                    <option value="general">General</option>
                                    <option value="call">Call Note</option>
                                    <option value="email">Email</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="reminder">Reminder</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-arrow-left fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Select an Enquiry</h5>
                    <p class="text-muted">Choose an enquiry from the left panel to update its status and manage history.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($enquiry && !empty($history)): ?>
<!-- History Timeline -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Enquiry History & Timeline
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($history as $entry): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas <?php echo getHistoryIcon($entry['action_type']); ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="mb-1"><?php echo getHistoryTitle($entry['action_type']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y g:i A', strtotime($entry['created_at'])); ?>
                                        by <?php echo htmlspecialchars($entry['user_name']); ?>
                                    </small>
                                </div>
                                <div class="timeline-body">
                                    <?php echo formatHistoryDetails($entry); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Status badges */
.status-new { background-color: #17a2b8; }
.status-assigned { background-color: #6c757d; }
.status-contacted { background-color: #28a745; }
.status-interested { background-color: #007bff; }
.status-not_interested { background-color: #6c757d; }
.status-follow_up { background-color: #fd7e14; }
.status-enrolled { background-color: #28a745; }

/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.timeline-header h6 {
    color: #495057;
    font-weight: 600;
}

.timeline-body {
    margin-top: 10px;
}

/* Enquiry summary */
.enquiry-summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}

.enquiry-summary h6 {
    color: #495057;
    margin-bottom: 15px;
}

/* Form enhancements */
.form-label {
    font-weight: 600;
    color: #495057;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -15px;
        width: 25px;
        height: 25px;
    }
}
</style>

<script>
function resetForm() {
    document.getElementById('statusUpdateForm').reset();
    document.getElementById('status').value = '<?php echo $enquiry['status'] ?? ''; ?>';
}

// Auto-save draft functionality
let draftTimer;
document.getElementById('notes').addEventListener('input', function() {
    clearTimeout(draftTimer);
    draftTimer = setTimeout(() => {
        saveDraft();
    }, 2000);
});

function saveDraft() {
    const notes = document.getElementById('notes').value;
    if (notes.trim()) {
        localStorage.setItem('enquiry_notes_draft_<?php echo $enquiryId; ?>', notes);
    }
}

function loadDraft() {
    const draft = localStorage.getItem('enquiry_notes_draft_<?php echo $enquiryId; ?>');
    if (draft) {
        document.getElementById('notes').value = draft;
    }
}

// Load draft on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($enquiry): ?>
        loadDraft();
    <?php endif; ?>
});

// Clear draft on successful submission
document.getElementById('statusUpdateForm').addEventListener('submit', function() {
    localStorage.removeItem('enquiry_notes_draft_<?php echo $enquiryId; ?>');
});

// Quick status buttons
function setQuickStatus(status) {
    document.getElementById('status').value = status;
    
    // Set appropriate priority based on status
    const priority = document.getElementById('priority');
    switch(status) {
        case 'new':
        case 'follow_up':
            priority.value = 'high';
            break;
        case 'interested':
            priority.value = 'medium';
            break;
        default:
            priority.value = 'low';
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 's':
                e.preventDefault();
                document.getElementById('statusUpdateForm').submit();
                break;
            case 'n':
                e.preventDefault();
                document.getElementById('notes').focus();
                break;
        }
    }
});
</script>

<?php renderEnquirerFooter(); ?>