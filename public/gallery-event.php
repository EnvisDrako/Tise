<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

require_once '../includes/db_connect.php';

$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    header('Location: gallery.php');
    exit;
}

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    // Get event details
    $stmt = $pdo->prepare("SELECT * FROM gallery_events WHERE id=? AND status='active'");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        header('Location: gallery.php');
        exit;
    }
    
    // Get all photos for this event
    $stmt = $pdo->prepare("SELECT * FROM gallery_photos WHERE event_id=? ORDER BY is_thumbnail DESC, display_order ASC, created_at ASC");
    $stmt->execute([$event_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    header('Location: gallery.php');
    exit;
}

$page_title = $event['title'] . " - Gallery";
$page_description = $event['description'] ?: "View photos from " . $event['title'];
include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="gallery.php">Gallery</a> / <?php echo htmlspecialchars($event['title']); ?>
        </div>
        <h1><?php echo htmlspecialchars($event['title']); ?></h1>
        <?php if ($event['description']): ?>
            <p><?php echo htmlspecialchars($event['description']); ?></p>
        <?php endif; ?>
        <div class="event-meta">
            <?php 
            // Only show date if it's valid and not null/empty
            if ($event['event_date'] && $event['event_date'] != '0000-00-00' && strtotime($event['event_date']) > 0): 
            ?>
                <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></span>
            <?php endif; ?>
            <span><i class="fas fa-images"></i> <?php echo count($photos); ?> Photos</span>
            <?php if ($event['category']): ?>
                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['category']); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="photos-section section">
    <div class="container">
        <?php if (empty($photos)): ?>
            <div class="empty-state">
                <i class="fas fa-image"></i>
                <h3>No Photos Yet</h3>
                <p>Photos will appear here once they are added to this event.</p>
            </div>
        <?php else: ?>
            <div class="photos-grid">
                <?php foreach ($photos as $photo): 
                    // Fix image URL path
                    $imageUrl = $photo['image_url'];
                    
                    // Handle different URL formats
                    if (strpos($imageUrl, 'http://') === 0 || strpos($imageUrl, 'https://') === 0) {
                        // External URL (Google Drive, etc.) - use as is
                    } elseif (strpos($imageUrl, '/uploads/') === 0) {
                        // Absolute path from root - convert to relative
                        $imageUrl = '.' . $imageUrl;
                    } elseif (strpos($imageUrl, 'uploads/') === 0) {
                        // Relative path without leading dot
                        $imageUrl = './' . $imageUrl;
                    }
                ?>
                    <div class="photo-card" onclick="openLightbox('<?php echo htmlspecialchars($imageUrl); ?>', '<?php echo htmlspecialchars($event['title']); ?>')">
                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'; console.error('Failed to load:', '<?php echo htmlspecialchars($imageUrl); ?>');">
                        <div class="placeholder-image" style="display:none;">
                            <i class="fas fa-image-slash"></i>
                            <span style="position:absolute; bottom:1rem; font-size:0.9rem; color:rgba(0,0,0,0.3);">Image not available</span>
                        </div>
                        <?php if ($photo['is_thumbnail']): ?>
                            <div class="thumbnail-badge">
                                <i class="fas fa-star"></i> Cover
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="gallery.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Gallery
            </a>
        </div>
    </div>
</section>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img id="lightbox-img" src="" alt="">
    <div id="lightbox-caption"></div>
</div>

<style>
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 0;
    margin-bottom: 2rem;
}

.page-header .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: white;
}

.page-header p {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.9);
    margin-bottom: 1rem;
}

.breadcrumb {
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.breadcrumb a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: white;
}

.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-top: 1rem;
    font-size: 1rem;
}

.event-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255,255,255,0.95);
}

.section {
    padding: 2rem 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.photo-card {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.photo-card:hover {
    transform: scale(1.05);
}

.photo-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    display: block;
}

.thumbnail-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: #f39c12;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
}

.photo-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.75rem;
    font-size: 0.9rem;
}

.back-link {
    text-align: center;
    margin-top: 3rem;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.lightbox {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    align-items: center;
    justify-content: center;
}

.lightbox img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 40px;
    color: white;
    font-size: 40px;
    cursor: pointer;
}

.lightbox-caption {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: 1.2rem;
    text-align: center;
}

.empty-state {
    text-align: center;
    padding: 5rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    margin: 2rem 0;
}

.empty-state i {
    font-size: 6rem;
    color: rgba(255,255,255,0.9);
    margin-bottom: 1.5rem;
    animation: float 3s ease-in-out infinite;
}

.empty-state h3 {
    color: white;
    margin-bottom: 1rem;
    font-size: 1.8rem;
    font-weight: 600;
}

.empty-state p {
    color: rgba(255,255,255,0.85);
    font-size: 1.1rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.placeholder-image {
    width: 100%;
    height: 250px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5rem;
    color: rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.placeholder-image::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
    animation: shimmer 2s infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%); }
    100% { transform: translateX(100%) translateY(100%); }
}
</style>

<script>
function viewEvent(eventId) {
    window.location.href = 'gallery-event.php?id=' + eventId;
}

function openLightbox(src, caption) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-caption').textContent = caption;
    document.getElementById('lightbox').style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});
</script>

<?php include '../includes/public_footer.php'; ?>
