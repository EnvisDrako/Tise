<?php
/**
 * Admin - Courses Management
 */
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../../includes/db_connect.php';

$pageTitle = 'Courses Management';

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
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $fee = trim($_POST['fee'] ?? '');
        $faculty = trim($_POST['faculty'] ?? '');
        $features = trim($_POST['features'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        // Auto-convert Google Drive links
        if (!empty($image_url)) {
            $image_url = convertGoogleDriveLink($image_url);
        }
        
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO courses (title, description, duration, level, fee, faculty, features, image_url, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $description, $duration, $level, $fee, $faculty, $features, $image_url, $status]);
                $message = 'Course added successfully!';
                $messageType = 'success';
            } else {
                $stmt = $pdo->prepare("UPDATE courses SET title=?, description=?, duration=?, level=?, fee=?, faculty=?, features=?, image_url=?, status=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$title, $description, $duration, $level, $fee, $faculty, $features, $image_url, $status, $id]);
                $message = 'Course updated successfully!';
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
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id=?");
            $stmt->execute([$id]);
            $message = 'Course deleted successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all courses
try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $courses = [];
    $message = 'Error loading courses: ' . $e->getMessage();
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
        <h1>Courses Management</h1>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Add New Course
        </button>
    </div>
    
    <div class="courses-grid">
        <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <?php if ($course['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                <?php endif; ?>
                <div class="course-content">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                    <div class="course-meta">
                        <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['duration']); ?></span>
                        <span><i class="fas fa-signal"></i> <?php echo htmlspecialchars($course['level']); ?></span>
                        <span><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($course['fee']); ?></span>
                    </div>
                    <div class="course-status">
                        <span class="badge badge-<?php echo $course['status']; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </span>
                    </div>
                    <div class="course-actions">
                        <button class="btn btn-sm btn-info" onclick='editCourse(<?php echo json_encode($course); ?>)'>
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['title']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="courseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Course</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" id="courseForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="courseId">
            
            <div class="form-group">
                <label>Course Title *</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Duration *</label>
                    <input type="text" name="duration" id="duration" class="form-control" placeholder="e.g., 6 Months" required>
                </div>
                <div class="form-group">
                    <label>Level *</label>
                    <select name="level" id="level" class="form-control" required>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Fee *</label>
                    <input type="text" name="fee" id="fee" class="form-control" placeholder="e.g., $999" required>
                </div>
                <div class="form-group">
                    <label>Faculty</label>
                    <input type="text" name="faculty" id="faculty" class="form-control" placeholder="Instructor name">
                </div>
            </div>
            
            <div class="form-group">
                <label>Features (comma-separated)</label>
                <input type="text" name="features" id="features" class="form-control" placeholder="e.g., Hands-on Projects, Certificate, Job Support">
            </div>
            
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image_url" id="image_url" class="form-control" placeholder="Paste any Google Drive link or image URL">
                <small class="form-text">
                    ✨ <strong>Just paste any link!</strong> We'll automatically convert it for you.
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem; font-size: 0.85rem;">
                        <li>📁 Local: /images/course-name.jpg</li>
                        <li>☁️ Google Drive: Just paste the share link (any format works!)</li>
                        <li>🌐 Any URL: https://example.com/image.jpg</li>
                    </ul>
                    <strong>Google Drive:</strong> Share file → Copy link → Paste here → Done! 🎉
                </small>
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
                <button type="submit" class="btn btn-primary">Save Course</button>
            </div>
        </form>
    </div>
</div>

<style>
.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.course-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.course-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.course-content {
    padding: 1.5rem;
}

.course-content h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.course-meta {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    font-size: 0.9rem;
    color: #666;
}

.course-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.course-status {
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

.course-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
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
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 600px;
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
    document.getElementById('modalTitle').textContent = 'Add New Course';
    document.getElementById('formAction').value = 'add';
    document.getElementById('courseForm').reset();
    document.getElementById('courseModal').style.display = 'block';
}

function editCourse(course) {
    document.getElementById('modalTitle').textContent = 'Edit Course';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('courseId').value = course.id;
    document.getElementById('title').value = course.title;
    document.getElementById('description').value = course.description;
    document.getElementById('duration').value = course.duration;
    document.getElementById('level').value = course.level;
    document.getElementById('fee').value = course.fee;
    document.getElementById('faculty').value = course.faculty || '';
    document.getElementById('features').value = course.features || '';
    document.getElementById('image_url').value = course.image_url || '';
    document.getElementById('status').value = course.status;
    document.getElementById('courseModal').style.display = 'block';
}

function deleteCourse(id, title) {
    if (confirm('Are you sure you want to delete "' + title + '"?')) {
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
    document.getElementById('courseModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('courseModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php renderAdminFooter(); ?>
