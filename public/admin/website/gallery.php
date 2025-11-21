<?php
/**
 * Admin - Gallery Management
 */
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../../includes/db_connect.php';

$pageTitle = 'Gallery Management';

/**
 * Convert Google Drive folder link to get photos
 */
function convertGoogleDriveFolderLink($url) {
    // Extract folder ID from various Google Drive folder URL formats
    if (preg_match('/folders\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return $matches[1];
    }
    if (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return $matches[1];
    }
    return $url; // Return as is if already just an ID
}

/**
 * Convert Google Drive image link
 */
function convertGoogleDriveLink($url) {
    if (strpos($url, 'drive.google.com/uc?id=') !== false) {
        return $url;
    }
    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/uc?id=' . $matches[1];
    }
    if (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/uc?id=' . $matches[1];
    }
    return $url;
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_event' || $action === 'edit_event') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $event_date = trim($_POST['event_date'] ?? '');
        $thumbnail_url = trim($_POST['thumbnail_url'] ?? '');
        $drive_folder_id = trim($_POST['drive_folder_id'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $status = $_POST['status'] ?? 'active';
        
        // Handle thumbnail file upload
        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/gallery/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['thumbnail_file']['type'];
            $fileSize = $_FILES['thumbnail_file']['size'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            
            if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
                $fileExtension = strtolower(pathinfo($_FILES['thumbnail_file']['name'], PATHINFO_EXTENSION));
                $newFileName = 'event_thumb_' . time() . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $targetPath)) {
                    $thumbnail_url = '/uploads/gallery/' . $newFileName;
                }
            }
        }
        
        // Auto-convert links if URL provided instead of file
        if (!empty($thumbnail_url) && strpos($thumbnail_url, '/uploads/') === false) {
            $thumbnail_url = convertGoogleDriveLink($thumbnail_url);
        }
        if (!empty($drive_folder_id)) {
            $drive_folder_id = convertGoogleDriveFolderLink($drive_folder_id);
        }
        
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            
            if ($action === 'add_event') {
                $stmt = $pdo->prepare("INSERT INTO gallery_events (title, description, event_date, thumbnail_url, drive_folder_id, category, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $description, $event_date, $thumbnail_url, $drive_folder_id, $category, $status]);
                $message = 'Event created successfully!';
                $messageType = 'success';
            } else {
                $stmt = $pdo->prepare("UPDATE gallery_events SET title=?, description=?, event_date=?, thumbnail_url=?, drive_folder_id=?, category=?, status=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$title, $description, $event_date, $thumbnail_url, $drive_folder_id, $category, $status, $id]);
                $message = 'Event updated successfully!';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_event') {
        $id = $_POST['id'] ?? null;
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("DELETE FROM gallery_events WHERE id=?");
            $stmt->execute([$id]);
            $message = 'Event deleted successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'add_photo') {
        $event_id = $_POST['event_id'] ?? null;
        $title = trim($_POST['photo_title'] ?? '');
        $description = trim($_POST['photo_description'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $is_thumbnail = isset($_POST['is_thumbnail']) ? 1 : 0;
        
        if (!empty($image_url)) {
            $image_url = convertGoogleDriveLink($image_url);
        }
        
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            
            // If this is set as thumbnail, unset others
            if ($is_thumbnail) {
                $pdo->prepare("UPDATE gallery_photos SET is_thumbnail=0 WHERE event_id=?")->execute([$event_id]);
                $pdo->prepare("UPDATE gallery_events SET thumbnail_url=? WHERE id=?")->execute([$image_url, $event_id]);
            }
            
            $stmt = $pdo->prepare("INSERT INTO gallery_photos (event_id, title, description, image_url, is_thumbnail, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$event_id, $title, $description, $image_url, $is_thumbnail]);
            
            // Update photo count
            $pdo->prepare("UPDATE gallery_events SET photo_count = (SELECT COUNT(*) FROM gallery_photos WHERE event_id=?) WHERE id=?")->execute([$event_id, $event_id]);
            
            $message = 'Photo added successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_photo') {
        $id = $_POST['photo_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("DELETE FROM gallery_photos WHERE id=?");
            $stmt->execute([$id]);
            
            // Update photo count
            $pdo->prepare("UPDATE gallery_events SET photo_count = (SELECT COUNT(*) FROM gallery_photos WHERE event_id=?) WHERE id=?")->execute([$event_id, $event_id]);
            
            $message = 'Photo deleted successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'set_thumbnail') {
        $photo_id = $_POST['photo_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            
            // Get photo URL
            $stmt = $pdo->prepare("SELECT image_url FROM gallery_photos WHERE id=?");
            $stmt->execute([$photo_id]);
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($photo) {
                // Unset all thumbnails for this event
                $pdo->prepare("UPDATE gallery_photos SET is_thumbnail=0 WHERE event_id=?")->execute([$event_id]);
                // Set new thumbnail
                $pdo->prepare("UPDATE gallery_photos SET is_thumbnail=1 WHERE id=?")->execute([$photo_id]);
                // Update event thumbnail
                $pdo->prepare("UPDATE gallery_events SET thumbnail_url=? WHERE id=?")->execute([$photo['image_url'], $event_id]);
                
                $message = 'Thumbnail updated successfully!';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all events
try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT * FROM gallery_events ORDER BY event_date DESC, created_at DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
    $message = 'Error loading events: ' . $e->getMessage();
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
        <h1><i class="fas fa-images"></i> Gallery Management</h1>
        <div class="header-actions">
            <a href="test-image-url.php" class="btn btn-info" target="_blank">
                <i class="fas fa-vial"></i> Test Image URL
            </a>
            <a href="google-drive-images-guide.php" class="btn btn-secondary" target="_blank">
                <i class="fas fa-book"></i> Google Drive Guide
            </a>
            <button class="btn btn-primary" onclick="showAddEventModal()">
                <i class="fas fa-plus"></i> Create New Event/Album
            </button>
        </div>
    </div>
    
    <div class="events-grid">
        <?php foreach ($events as $event): ?>
            <?php
            // Get photos for this event
            $stmt = $pdo->prepare("SELECT * FROM gallery_photos WHERE event_id=? ORDER BY is_thumbnail DESC, display_order ASC, created_at DESC");
            $stmt->execute([$event['id']]);
            $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="event-card">
                <div class="event-thumbnail">
                    <?php if ($event['thumbnail_url']): 
                        // Fix image path for admin panel
                        $thumbnailUrl = $event['thumbnail_url'];
                        if (strpos($thumbnailUrl, 'http://') === 0 || strpos($thumbnailUrl, 'https://') === 0) {
                            // External URL - use as is
                        } elseif (strpos($thumbnailUrl, '/uploads/') === 0) {
                            // Absolute path from root - convert to relative from admin
                            $thumbnailUrl = '../../' . ltrim($thumbnailUrl, '/');
                        } elseif (strpos($thumbnailUrl, 'uploads/') === 0) {
                            // Relative path
                            $thumbnailUrl = '../../' . $thumbnailUrl;
                        }
                    ?>
                        <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="placeholder-thumb" style="display:none;">
                            <i class="fas fa-image-slash"></i>
                        </div>
                    <?php else: ?>
                        <div class="placeholder-thumb">
                            <i class="fas fa-images"></i>
                        </div>
                    <?php endif; ?>
                    <div class="event-badge">
                        <i class="fas fa-images"></i> <?php echo $event['photo_count']; ?> photos
                    </div>
                </div>
                <div class="event-content">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <?php if ($event['event_date']): ?>
                        <p class="event-date"><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                    <?php endif; ?>
                    <?php if ($event['category']): ?>
                        <span class="category-badge"><?php echo htmlspecialchars($event['category']); ?></span>
                    <?php endif; ?>
                    <span class="badge badge-<?php echo $event['status']; ?>">
                        <?php echo ucfirst($event['status']); ?>
                    </span>
                    <div class="event-actions">
                        <button class="btn btn-sm btn-success" onclick="showPhotosModal(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['title']); ?>')">
                            <i class="fas fa-images"></i> Manage Photos (<?php echo count($photos); ?>)
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="window.open('../../gallery-event.php?id=<?php echo $event['id']; ?>', '_blank')">
                            <i class="fas fa-external-link-alt"></i> View Public
                        </button>
                        <button class="btn btn-sm btn-info" onclick='editEvent(<?php echo json_encode($event); ?>)'>
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['title']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="eventModalTitle">Create New Event/Album</h2>
            <span class="close" onclick="closeEventModal()">&times;</span>
        </div>
        <form method="POST" id="eventForm" enctype="multipart/form-data">
            <input type="hidden" name="action" id="eventAction" value="add_event">
            <input type="hidden" name="id" id="eventId">
            
            <div class="form-group">
                <label>Event/Album Title *</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Event Date</label>
                    <input type="date" name="event_date" id="event_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="category" class="form-control" placeholder="e.g., Sports, Cultural, Academic">
                </div>
            </div>
            
            <div class="form-group">
                <label>Thumbnail Image</label>
                <div class="thumbnail-options">
                    <div class="option-tabs">
                        <button type="button" class="tab-btn active" onclick="showThumbnailTab('upload')">
                            <i class="fas fa-upload"></i> Upload from PC
                        </button>
                        <button type="button" class="tab-btn" onclick="showThumbnailTab('url')">
                            <i class="fas fa-link"></i> Use URL
                        </button>
                    </div>
                    
                    <div id="thumbnailUploadTab" class="tab-content active">
                        <div class="upload-preview-area" id="thumbnailPreviewArea">
                            <input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/*" style="display:none;" onchange="previewThumbnail(this)">
                            <label for="thumbnail_file" class="upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to select thumbnail image</p>
                                <small>JPG, PNG, GIF, WebP - Max 5MB</small>
                            </label>
                            <div id="thumbnailPreview" style="display:none;">
                                <img id="thumbnailPreviewImg" src="" alt="Preview">
                                <button type="button" class="remove-preview-btn" onclick="removeThumbnailPreview()">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="thumbnailUrlTab" class="tab-content">
                        <input type="text" name="thumbnail_url" id="thumbnail_url" class="form-control" placeholder="Paste any image URL or Google Drive link">
                        <small class="form-text">Paste image URL or Google Drive link</small>
                    </div>
                </div>
                <small class="form-text">Optional - You can also set thumbnail from photos later</small>
            </div>
            
            <div class="form-group">
                <label>Google Drive Folder ID/Link (Optional - For Reference Only)</label>
                <input type="text" name="drive_folder_id" id="drive_folder_id" class="form-control" placeholder="Paste Google Drive folder link">
                <small class="form-text">
                    📁 Paste the entire Google Drive folder share link - we'll extract the ID for reference.<br>
                    <strong>⚠️ Note:</strong> You still need to add each photo individually below with direct image links.<br>
                    <a href="google-drive-images-guide.php" target="_blank" style="color: #3498db;">📖 View Google Drive Guide</a>
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
                <button type="button" class="btn btn-secondary" onclick="closeEventModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Photos Management Modal -->
<div id="photosModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 id="photosModalTitle">Manage Photos</h2>
            <span class="close" onclick="closePhotosModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="photo-add-options">
                <button class="btn btn-primary" onclick="showAddPhotoForm()">
                    <i class="fas fa-link"></i> Add from URL
                </button>
                <button class="btn btn-success" onclick="showBulkUploadForm()">
                    <i class="fas fa-upload"></i> Bulk Upload from PC
                </button>
            </div>
            
            <!-- Add Photo from URL Form -->
            <div id="addPhotoForm" style="display:none; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <h4><i class="fas fa-link"></i> Add Photo from URL</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="add_photo">
                    <input type="hidden" name="event_id" id="photo_event_id">
                    
                    <div class="form-group">
                        <label>Image URL *</label>
                        <input type="text" name="image_url" class="form-control" placeholder="Paste image URL or Google Drive link" required>
                        <small class="form-text">
                            💡 For Google Drive: Use individual file links, not folder links.<br>
                            Format: <code>https://drive.google.com/uc?id=FILE_ID</code><br>
                            <a href="google-drive-images-guide.php" target="_blank" style="color: #3498db;">📖 Need help? View Guide</a>
                        </small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Title (Optional)</label>
                            <input type="text" name="photo_title" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_thumbnail"> Set as Event Thumbnail
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea name="photo_description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Add Photo</button>
                    <button type="button" class="btn btn-secondary" onclick="hideAddPhotoForm()">Cancel</button>
                </form>
            </div>
            
            <!-- Bulk Upload Form -->
            <div id="bulkUploadForm" style="display:none; margin-top: 1rem; padding: 1rem; background: #f0f8ff; border-radius: 8px; border: 2px dashed #3498db;">
                <h4><i class="fas fa-upload"></i> Bulk Upload Photos from PC</h4>
                <form id="bulkUploadFormElement" enctype="multipart/form-data">
                    <input type="hidden" name="event_id" id="bulk_event_id">
                    
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Drag & Drop multiple photos here</strong></p>
                        <p>or</p>
                        <label for="photoFiles" class="btn btn-primary">
                            <i class="fas fa-folder-open"></i> Select Multiple Files
                        </label>
                        <input type="file" id="photoFiles" name="photos[]" multiple accept="image/*" style="display:none;">
                        <p class="upload-info">
                            <small><i class="fas fa-info-circle"></i> Select multiple images at once | JPG, PNG, GIF, WebP | Max 5MB each</small>
                        </p>
                    </div>
                    
                    <div id="filePreview" class="file-preview"></div>
                    
                    <div id="uploadProgress" style="display:none;">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <p id="progressText">Uploading...</p>
                    </div>
                    
                    <div id="uploadResult" style="display:none;"></div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-success" onclick="uploadPhotos()" id="uploadBtn" disabled>
                            <i class="fas fa-upload"></i> Upload Photos
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="hideBulkUploadForm()">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Photos Grid -->
            <div id="photosGrid" class="photos-grid">
                <!-- Photos will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
.header-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.event-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.event-card:hover {
    transform: translateY(-5px);
}

.event-thumbnail {
    position: relative;
    height: 200px;
    background: #f0f0f0;
}

.event-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-thumb {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
}

.placeholder-thumb::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%); }
    100% { transform: translateX(100%) translateY(100%); }
}

.event-badge {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.event-content {
    padding: 1.5rem;
}

.event-content h3 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.event-date {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.category-badge {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    margin-right: 0.5rem;
}

.event-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1rem;
}

.modal-large {
    max-width: 900px;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.photo-item {
    position: relative;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.photo-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.photo-actions {
    padding: 0.5rem;
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.photo-title {
    padding: 0.5rem;
    background: #f8f9fa;
    font-size: 0.85rem;
    color: #666;
    text-align: center;
    border-top: 1px solid #e0e0e0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.thumbnail-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: #4caf50;
    color: white;
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: bold;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
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

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    overflow-y: auto;
}

.modal-content {
    background: white;
    margin: 3% auto;
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

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.form-text {
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.photo-add-options {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.upload-area {
    text-align: center;
    padding: 3rem 2rem;
    background: white;
    border: 3px dashed #3498db;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover {
    background: #f8f9fa;
    border-color: #2980b9;
}

.upload-area.drag-over {
    background: #e3f2fd;
    border-color: #1976d2;
    transform: scale(1.02);
}

.upload-area i {
    font-size: 4rem;
    color: #3498db;
    margin-bottom: 1rem;
}

.upload-area p {
    margin: 0.5rem 0;
    color: #666;
}

.upload-info {
    margin-top: 1rem;
    color: #999;
}

.file-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.preview-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.preview-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(231, 76, 60, 0.9);
    color: white;
    border: none;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-item .remove-btn:hover {
    background: #c0392b;
}

.preview-item .file-name {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.3rem;
    font-size: 0.7rem;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.progress-bar {
    width: 100%;
    height: 30px;
    background: #e0e0e0;
    border-radius: 15px;
    overflow: hidden;
    margin: 1rem 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    width: 0%;
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

#progressText {
    text-align: center;
    color: #666;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.upload-success {
    background: #d4edda;
    color: #155724;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #28a745;
    margin-top: 1rem;
}

.upload-error {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #dc3545;
    margin-top: 1rem;
}

.upload-error ul {
    margin: 0.5rem 0 0 1.5rem;
}

.thumbnail-options {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.option-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
}

.tab-btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    background: transparent;
    cursor: pointer;
    font-weight: 500;
    color: #666;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.tab-btn.active {
    background: white;
    color: #3498db;
    border-bottom: 3px solid #3498db;
}

.tab-content {
    display: none;
    padding: 1rem;
}

.tab-content.active {
    display: block;
}

.upload-preview-area {
    min-height: 150px;
}

.upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    border: 2px dashed #3498db;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.upload-label:hover {
    background: #e3f2fd;
    border-color: #2980b9;
}

.upload-label i {
    font-size: 3rem;
    color: #3498db;
    margin-bottom: 0.5rem;
}

.upload-label p {
    margin: 0.5rem 0;
    color: #2c3e50;
    font-weight: 500;
}

.upload-label small {
    color: #999;
}

#thumbnailPreview {
    text-align: center;
}

#thumbnailPreviewImg {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.remove-preview-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.3s ease;
}

.remove-preview-btn:hover {
    background: #c0392b;
}
</style>

<script>
let currentEventId = null;

function showAddEventModal() {
    document.getElementById('eventModalTitle').textContent = 'Create New Event/Album';
    document.getElementById('eventAction').value = 'add_event';
    document.getElementById('eventForm').reset();
    
    // Reset to upload tab
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-btn')[0].classList.add('active');
    document.getElementById('thumbnailUploadTab').classList.add('active');
    document.getElementById('thumbnailUrlTab').classList.remove('active');
    
    // Clear preview
    removeThumbnailPreview();
    
    document.getElementById('eventModal').style.display = 'block';
}

function editEvent(event) {
    document.getElementById('eventModalTitle').textContent = 'Edit Event/Album';
    document.getElementById('eventAction').value = 'edit_event';
    document.getElementById('eventId').value = event.id;
    document.getElementById('title').value = event.title;
    document.getElementById('description').value = event.description || '';
    document.getElementById('event_date').value = event.event_date || '';
    document.getElementById('category').value = event.category || '';
    document.getElementById('drive_folder_id').value = event.drive_folder_id || '';
    document.getElementById('status').value = event.status;
    
    // Handle thumbnail - if exists, show in URL tab
    if (event.thumbnail_url) {
        document.getElementById('thumbnail_url').value = event.thumbnail_url;
        // Switch to URL tab
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
        document.getElementById('thumbnailUploadTab').classList.remove('active');
        document.getElementById('thumbnailUrlTab').classList.add('active');
    } else {
        document.getElementById('thumbnail_url').value = '';
        // Reset to upload tab
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
        document.getElementById('thumbnailUploadTab').classList.add('active');
        document.getElementById('thumbnailUrlTab').classList.remove('active');
    }
    
    // Clear file input and preview
    removeThumbnailPreview();
    
    document.getElementById('eventModal').style.display = 'block';
}

function deleteEvent(id, title) {
    if (confirm('Are you sure you want to delete "' + title + '" and all its photos?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_event">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeEventModal() {
    document.getElementById('eventModal').style.display = 'none';
}

function showPhotosModal(eventId, title) {
    currentEventId = eventId;
    document.getElementById('photosModalTitle').textContent = 'Manage Photos - ' + title;
    document.getElementById('photo_event_id').value = eventId;
    loadPhotos(eventId);
    document.getElementById('photosModal').style.display = 'block';
}

function closePhotosModal() {
    document.getElementById('photosModal').style.display = 'none';
    hideAddPhotoForm();
}

function showAddPhotoForm() {
    document.getElementById('addPhotoForm').style.display = 'block';
}

function hideAddPhotoForm() {
    document.getElementById('addPhotoForm').style.display = 'none';
}

function showBulkUploadForm() {
    document.getElementById('addPhotoForm').style.display = 'none';
    document.getElementById('bulkUploadForm').style.display = 'block';
    document.getElementById('bulk_event_id').value = currentEventId;
    setupDragAndDrop();
}

function hideBulkUploadForm() {
    document.getElementById('bulkUploadForm').style.display = 'none';
    document.getElementById('filePreview').innerHTML = '';
    document.getElementById('photoFiles').value = '';
    document.getElementById('uploadBtn').disabled = true;
    document.getElementById('uploadBtn').innerHTML = '<i class="fas fa-upload"></i> Upload Photos';
    document.getElementById('uploadResult').style.display = 'none';
    document.getElementById('uploadProgress').style.display = 'none';
    selectedFiles = [];
}

let selectedFiles = [];

function setupDragAndDrop() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('photoFiles');
    
    // Remove any existing event listeners by cloning
    const newFileInput = fileInput.cloneNode(true);
    fileInput.parentNode.replaceChild(newFileInput, fileInput);
    
    // Click to browse - make sure it triggers file input
    uploadArea.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
            document.getElementById('photoFiles').click();
        }
    });
    
    // Also handle label click
    const browseLabel = uploadArea.querySelector('label[for="photoFiles"]');
    if (browseLabel) {
        browseLabel.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop area
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.add('drag-over'), false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('drag-over'), false);
    });
    
    // Handle dropped files
    uploadArea.addEventListener('drop', handleDrop, false);
    
    // Handle file input change - use the new cloned input
    document.getElementById('photoFiles').addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            handleFiles(this.files);
        }
    });
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

function handleFiles(files) {
    selectedFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
    
    if (selectedFiles.length === 0) {
        alert('Please select valid image files');
        return;
    }
    
    displayFilePreview();
    document.getElementById('uploadBtn').disabled = false;
}

function displayFilePreview() {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    
    // Add file count header
    if (selectedFiles.length > 0) {
        const countDiv = document.createElement('div');
        countDiv.style.cssText = 'padding: 0.75rem; background: #e3f2fd; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-weight: 600; color: #1976d2;';
        countDiv.innerHTML = `<i class="fas fa-images"></i> ${selectedFiles.length} file${selectedFiles.length > 1 ? 's' : ''} selected`;
        preview.appendChild(countDiv);
    }
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="${file.name}">
                <button class="remove-btn" onclick="removeFile(${index})" type="button">×</button>
                <div class="file-name">${file.name}</div>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    
    // Update upload button text
    const uploadBtn = document.getElementById('uploadBtn');
    if (selectedFiles.length > 0) {
        uploadBtn.innerHTML = `<i class="fas fa-upload"></i> Upload ${selectedFiles.length} Photo${selectedFiles.length > 1 ? 's' : ''}`;
    }
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    displayFilePreview();
    
    if (selectedFiles.length === 0) {
        document.getElementById('uploadBtn').disabled = true;
    }
}

function uploadPhotos() {
    if (selectedFiles.length === 0) {
        alert('Please select files to upload');
        return;
    }
    
    const formData = new FormData();
    formData.append('event_id', currentEventId);
    
    selectedFiles.forEach(file => {
        formData.append('photos[]', file);
    });
    
    // Show progress
    document.getElementById('uploadProgress').style.display = 'block';
    document.getElementById('uploadResult').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
    
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    // Create XMLHttpRequest for progress tracking
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressFill.style.width = percentComplete + '%';
            progressText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    // Show brief success message
                    displayUploadResult(response);
                    
                    // Close modal and reload immediately
                    setTimeout(() => {
                        closePhotosModal();
                        location.reload();
                    }, 800);
                } else {
                    // Show error and keep modal open
                    displayUploadResult(response);
                    document.getElementById('uploadProgress').style.display = 'none';
                    document.getElementById('uploadBtn').disabled = false;
                }
            } catch (e) {
                displayUploadResult({
                    success: false,
                    message: 'Error processing response'
                });
                document.getElementById('uploadProgress').style.display = 'none';
                document.getElementById('uploadBtn').disabled = false;
            }
        } else {
            displayUploadResult({
                success: false,
                message: 'Upload failed with status: ' + xhr.status
            });
            document.getElementById('uploadProgress').style.display = 'none';
            document.getElementById('uploadBtn').disabled = false;
        }
    });
    
    xhr.addEventListener('error', function() {
        displayUploadResult({
            success: false,
            message: 'Network error occurred'
        });
        document.getElementById('uploadProgress').style.display = 'none';
        document.getElementById('uploadBtn').disabled = false;
    });
    
    xhr.open('POST', 'upload-gallery-photos.php', true);
    xhr.send(formData);
}

function displayUploadResult(response) {
    const resultDiv = document.getElementById('uploadResult');
    resultDiv.style.display = 'block';
    
    if (response.success) {
        resultDiv.className = 'upload-success';
        resultDiv.innerHTML = `
            <strong><i class="fas fa-check-circle"></i> Success!</strong><br>
            ${response.message}
        `;
    } else {
        resultDiv.className = 'upload-error';
        let html = `
            <strong><i class="fas fa-times-circle"></i> Error!</strong><br>
            ${response.message}
        `;
        
        if (response.errors && response.errors.length > 0) {
            html += '<ul>';
            response.errors.forEach(error => {
                html += `<li>${error}</li>`;
            });
            html += '</ul>';
        }
        
        resultDiv.innerHTML = html;
    }
}

// Thumbnail upload functions
function showThumbnailTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.tab-btn').classList.add('active');
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    if (tab === 'upload') {
        document.getElementById('thumbnailUploadTab').classList.add('active');
        // Clear URL input when switching to upload
        document.getElementById('thumbnail_url').value = '';
    } else {
        document.getElementById('thumbnailUrlTab').classList.add('active');
        // Clear file input when switching to URL
        document.getElementById('thumbnail_file').value = '';
        removeThumbnailPreview();
    }
}

function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
            input.value = '';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnailPreviewImg').src = e.target.result;
            document.querySelector('.upload-label').style.display = 'none';
            document.getElementById('thumbnailPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function removeThumbnailPreview() {
    document.getElementById('thumbnail_file').value = '';
    document.getElementById('thumbnailPreview').style.display = 'none';
    document.querySelector('.upload-label').style.display = 'flex';
}

function loadPhotos(eventId) {
    const photosGrid = document.getElementById('photosGrid');
    photosGrid.innerHTML = '<p style="text-align:center; padding:2rem; color:#666;"><i class="fas fa-spinner fa-spin"></i> Loading photos...</p>';
    
    // Fetch photos via AJAX
    fetch('get-event-photos.php?event_id=' + eventId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPhotos(data.photos, eventId);
            } else {
                photosGrid.innerHTML = '<p style="text-align:center; padding:2rem; color:#e74c3c;">Error loading photos: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            photosGrid.innerHTML = '<p style="text-align:center; padding:2rem; color:#e74c3c;">Error loading photos. Please try again.</p>';
            console.error('Error:', error);
        });
}

function displayPhotos(photos, eventId) {
    const photosGrid = document.getElementById('photosGrid');
    
    if (photos.length === 0) {
        photosGrid.innerHTML = `
            <div style="text-align:center; padding:3rem; background:#f8f9fa; border-radius:10px;">
                <i class="fas fa-images" style="font-size:4rem; color:#ccc; margin-bottom:1rem;"></i>
                <p style="color:#666; font-size:1.1rem; margin:0;">No photos yet</p>
                <p style="color:#999; font-size:0.9rem;">Click "Add from URL" or "Bulk Upload from PC" to add photos</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    photos.forEach(photo => {
        html += `
            <div class="photo-item">
                ${photo.is_thumbnail == 1 ? '<div class="thumbnail-badge">Cover Photo</div>' : ''}
                <img src="${photo.image_url.startsWith('http') ? photo.image_url : '../../' + photo.image_url}" 
                     alt="${photo.title || 'Photo'}"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'200\\' height=\\'200\\'%3E%3Crect fill=\\'%23f0f0f0\\' width=\\'200\\' height=\\'200\\'/%3E%3Ctext fill=\\'%23999\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\'%3ENo Image%3C/text%3E%3C/svg%3E';">
                <div class="photo-actions">
                    ${photo.is_thumbnail != 1 ? `<button class="btn btn-sm btn-primary" onclick="setThumbnail(${photo.id}, ${eventId})" title="Set as Cover"><i class="fas fa-star"></i></button>` : ''}
                    <button class="btn btn-sm btn-danger" onclick="deletePhoto(${photo.id}, ${eventId})" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
                ${photo.title ? `<div class="photo-title">${photo.title}</div>` : ''}
            </div>
        `;
    });
    
    photosGrid.innerHTML = html;
}

function setThumbnail(photoId, eventId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="set_thumbnail">
        <input type="hidden" name="photo_id" value="${photoId}">
        <input type="hidden" name="event_id" value="${eventId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function deletePhoto(photoId, eventId) {
    if (confirm('Are you sure you want to delete this photo?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_photo">
            <input type="hidden" name="photo_id" value="${photoId}">
            <input type="hidden" name="event_id" value="${eventId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

window.onclick = function(event) {
    const eventModal = document.getElementById('eventModal');
    const photosModal = document.getElementById('photosModal');
    if (event.target == eventModal) {
        closeEventModal();
    }
    if (event.target == photosModal) {
        closePhotosModal();
    }
}
</script>

<?php renderAdminFooter(); ?>
