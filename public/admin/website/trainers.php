<?php
/**
 * Admin - Trainers Management
 */
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../../includes/db_connect.php';

$pageTitle = 'Trainers Management';

/**
 * Convert Google Drive links to direct image URLs
 */
function convertGoogleDriveLink($url) {
    // If already in correct format, return as is
    if (strpos($url, 'drive.google.com/uc?id=') !== false) {
        return $url;
    }
    
    // Convert various Google Drive formats
    // Format 1: https://drive.google.com/file/d/FILE_ID/view
    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/uc?id=' . $matches[1];
    }
    
    // Format 2: https://drive.google.com/open?id=FILE_ID
    if (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/uc?id=' . $matches[1];
    }
    
    // Format 3: https://drive.google.com/uc?id=FILE_ID&export=download
    if (preg_match('/drive\.google\.com\/uc\?id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/uc?id=' . $matches[1];
    }
    
    // If not a Google Drive link, return original URL
    return $url;
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $experience = trim($_POST['experience'] ?? '');
        $qualifications = trim($_POST['qualifications'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        // Auto-convert Google Drive links
        if (!empty($image_url)) {
            $image_url = convertGoogleDriveLink($image_url);
        }
        
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO trainers (name, designation, specialization, experience, qualifications, bio, email, phone, image_url, linkedin, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $designation, $specialization, $experience, $qualifications, $bio, $email, $phone, $image_url, $linkedin, $status]);
                $message = 'Trainer added successfully!';
                $messageType = 'success';
            } else {
                $stmt = $pdo->prepare("UPDATE trainers SET name=?, designation=?, specialization=?, experience=?, qualifications=?, bio=?, email=?, phone=?, image_url=?, linkedin=?, status=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$name, $designation, $specialization, $experience, $qualifications, $bio, $email, $phone, $image_url, $linkedin, $status, $id]);
                $message = 'Trainer updated successfully!';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("DELETE FROM trainers WHERE id=?");
            $stmt->execute([$id]);
            $message = 'Trainer deleted successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all trainers
try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT * FROM trainers ORDER BY created_at DESC");
    $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $trainers = [];
    $message = 'Error loading trainers: ' . $e->getMessage();
    $messageType = 'error';
}

renderAdminHeader($pageTitle);
?>

<div class="admin-container">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="page-header">
        <h1>Trainers Management</h1>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Add New Trainer
        </button>
    </div>
    
    <div class="trainers-grid">
        <?php foreach ($trainers as $trainer): ?>
            <div class="trainer-card">
                <div class="trainer-image">
                    <?php if ($trainer['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($trainer['image_url']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>">
                    <?php else: ?>
                        <div class="placeholder-image">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="trainer-content">
                    <h3><?php echo htmlspecialchars($trainer['name']); ?></h3>
                    <p class="designation"><?php echo htmlspecialchars($trainer['designation']); ?></p>
                    <p class="specialization"><i class="fas fa-star"></i> <?php echo htmlspecialchars($trainer['specialization']); ?></p>
                    <p class="experience"><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($trainer['experience']); ?> experience</p>
                    <div class="trainer-status">
                        <span class="badge badge-<?php echo $trainer['status']; ?>">
                            <?php echo ucfirst($trainer['status']); ?>
                        </span>
                    </div>
                    <div class="trainer-actions">
                        <button class="btn btn-sm btn-info" onclick='editTrainer(<?php echo json_encode($trainer); ?>)'>
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTrainer(<?php echo $trainer['id']; ?>, '<?php echo htmlspecialchars($trainer['name']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="trainerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Trainer</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" id="trainerForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="trainerId">
            
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Designation *</label>
                    <input type="text" name="designation" id="designation" class="form-control" placeholder="e.g., Senior Instructor" required>
                </div>
                <div class="form-group">
                    <label>Specialization *</label>
                    <input type="text" name="specialization" id="specialization" class="form-control" placeholder="e.g., Web Development" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Experience *</label>
                    <input type="text" name="experience" id="experience" class="form-control" placeholder="e.g., 10+ Years" required>
                </div>
                <div class="form-group">
                    <label>Qualifications</label>
                    <input type="text" name="qualifications" id="qualifications" class="form-control" placeholder="e.g., M.Tech, PhD">
                </div>
            </div>
            
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" id="bio" class="form-control" rows="3" placeholder="Brief description about the trainer"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" id="phone" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image_url" id="image_url" class="form-control" placeholder="Paste any Google Drive link or image URL">
                <small class="form-text">
                    ✨ <strong>Just paste any link!</strong> We'll automatically convert it for you.
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.85rem;">
                        <li>📁 Local: /images/trainer-name.jpg</li>
                        <li>☁️ Google Drive: Just paste the share link (any format works!)</li>
                        <li>🌐 Any URL: https://example.com/image.jpg</li>
                    </ul>
                    <strong>Google Drive:</strong> Share file → Copy link → Paste here → Done! 🎉
                </small>
            </div>
            
            <div class="form-group">
                <label>LinkedIn Profile</label>
                <input type="text" name="linkedin" id="linkedin" class="form-control" placeholder="https://linkedin.com/in/username">
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Trainer</button>
            </div>
        </form>
    </div>
</div>

<style>
.trainers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.trainer-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.trainer-image {
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: #f0f0f0;
}

.trainer-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: #ccc;
}

.trainer-content {
    padding: 1.5rem;
}

.trainer-content h3 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.designation {
    color: #3498db;
    font-weight: 600;
    margin-bottom: 1rem;
}

.specialization, .experience {
    font-size: 0.9rem;
    color: #666;
    margin: 0.5rem 0;
}

.trainer-status {
    margin: 1rem 0;
}

.badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-inactive {
    background: #f8d7da;
    color: #721c24;
}

.trainer-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    justify-content: center;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    margin: 3% auto;
    padding: 0;
    width: 90%;
    max-width: 700px;
    border-radius: 10px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
}

.close {
    font-size: 2rem;
    cursor: pointer;
    color: #999;
}

.close:hover {
    color: #333;
}

.modal form {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-text {
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.form-text ul {
    margin: 0.5rem 0;
}

.form-text strong {
    color: #495057;
}
</style>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Trainer';
    document.getElementById('formAction').value = 'add';
    document.getElementById('trainerForm').reset();
    document.getElementById('trainerModal').style.display = 'block';
}

function editTrainer(trainer) {
    document.getElementById('modalTitle').textContent = 'Edit Trainer';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('trainerId').value = trainer.id;
    document.getElementById('name').value = trainer.name;
    document.getElementById('designation').value = trainer.designation;
    document.getElementById('specialization').value = trainer.specialization;
    document.getElementById('experience').value = trainer.experience;
    document.getElementById('qualifications').value = trainer.qualifications || '';
    document.getElementById('bio').value = trainer.bio || '';
    document.getElementById('email').value = trainer.email || '';
    document.getElementById('phone').value = trainer.phone || '';
    document.getElementById('image_url').value = trainer.image_url || '';
    document.getElementById('linkedin').value = trainer.linkedin || '';
    document.getElementById('status').value = trainer.status;
    document.getElementById('trainerModal').style.display = 'block';
}

function deleteTrainer(id, name) {
    if (confirm('Are you sure you want to delete "' + name + '"?')) {
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

function closeModal() {
    document.getElementById('trainerModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('trainerModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php renderAdminFooter(); ?>
