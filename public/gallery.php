<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Gallery";
$page_description = "Explore our campus facilities, events, and student activities through our photo and video gallery";

// Get gallery events from database
require_once '../includes/db_connect.php';

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT * FROM gallery_events WHERE status='active' ORDER BY event_date DESC, created_at DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
}

include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Photo & Video Gallery</h1>
        <p>Discover our vibrant campus life and modern facilities</p>
    </div>
</div>

<section class="gallery-filter section">
    <div class="container">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" class="filter-select" data-filter="category">
                    <option value="all">All Categories</option>
                    <option value="facilities">Facilities</option>
                    <option value="events">Events</option>
                    <option value="activities">Student Activities</option>
                    <option value="graduation">Graduation</option>
                    <option value="workshops">Workshops</option>
                    <option value="campus">Campus Life</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="media-type">Media Type:</label>
                <select id="media-type" class="filter-select" data-filter="mediaType">
                    <option value="all">All Media</option>
                    <option value="photo">Photos</option>
                    <option value="video">Videos</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="date-range">Date:</label>
                <select id="date-range" class="filter-select" data-filter="dateRange">
                    <option value="all">All Time</option>
                    <option value="recent">Last 3 Months</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                </select>
            </div>
            <div class="search-group">
                <input type="text" class="search-input" placeholder="Search by event name, description, or tags...">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="results-count">Showing all media</div>
    </div>
</section>

<section class="gallery-grid section">
    <div class="container">
        <?php if (empty($events)): ?>
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <h3>No Gallery Events Yet</h3>
                <p>Gallery events will be displayed here once they are added by the admin.</p>
            </div>
        <?php else: ?>
        <div class="gallery-container">
            <?php foreach ($events as $event): 
                // Get photos for this event
                $stmt = $pdo->prepare("SELECT * FROM gallery_photos WHERE event_id=? ORDER BY is_thumbnail DESC, display_order ASC LIMIT 1");
                $stmt->execute([$event['id']]);
                $firstPhoto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $thumbnailUrl = $event['thumbnail_url'] ?: ($firstPhoto['image_url'] ?? '');
                
                // Fix image URL path
                if ($thumbnailUrl) {
                    if (strpos($thumbnailUrl, 'http://') === 0 || strpos($thumbnailUrl, 'https://') === 0) {
                        // External URL - use as is
                    } elseif (strpos($thumbnailUrl, '/uploads/') === 0) {
                        // Absolute path from root - convert to relative
                        $thumbnailUrl = '.' . $thumbnailUrl;
                    } elseif (strpos($thumbnailUrl, 'uploads/') === 0) {
                        // Relative path without leading dot
                        $thumbnailUrl = './' . $thumbnailUrl;
                    }
                }
            ?>
            <div class="gallery-item filterable-item searchable-item" 
                 data-category="<?php echo htmlspecialchars($event['category'] ?? 'general'); ?>">
                <div class="gallery-image">
                    <?php if ($thumbnailUrl): ?>
                        <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'; console.error('Failed to load:', '<?php echo htmlspecialchars($thumbnailUrl); ?>');">
                        <div class="placeholder-image" style="display:none;">
                            <i class="fas fa-image-slash"></i>
                        </div>
                    <?php else: ?>
                        <div class="placeholder-image">
                            <i class="fas fa-images"></i>
                        </div>
                    <?php endif; ?>
                    <div class="gallery-overlay">
                        <button class="view-btn" onclick="viewEvent(<?php echo $event['id']; ?>)">
                            <i class="fas fa-eye"></i> View Album
                        </button>
                    </div>
                </div>
                <div class="gallery-card-content">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <?php if ($event['description']): ?>
                        <p class="description"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?><?php echo strlen($event['description']) > 100 ? '...' : ''; ?></p>
                    <?php endif; ?>
                    <div class="gallery-meta">
                        <?php 
                        // Only show date if it's valid and not null/empty
                        if ($event['event_date'] && $event['event_date'] != '0000-00-00' && strtotime($event['event_date']) > 0): 
                        ?>
                            <span class="date"><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                        <?php endif; ?>
                        <span class="count"><i class="fas fa-images"></i> <?php echo $event['photo_count']; ?> photos</span>
                        <?php if ($event['category']): ?>
                            <span class="category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['category']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
            </section>

<!-- Image/Video Modal -->
<div id="media-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-media">
            <img id="modal-image" src="" alt="">
            <video id="modal-video" controls style="display: none;">
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="modal-info">
            <h3 id="modal-title"></h3>
            <p id="modal-description"></p>
        </div>
    </div>
</div>

<style>
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 0;
    margin-bottom: 0;
}

.page-header .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: white;
}

.page-header p {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.9);
}

.section {
    padding: 2rem 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.gallery-filter {
    background: #f8f9fa;
    padding: 2rem 0;
}

.filter-controls {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.filter-select {
    padding: 0.75rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    background: white;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #3498db;
}

.search-group {
    position: relative;
    display: flex;
    align-items: flex-end;
}

.search-input {
    width: 100%;
    padding: 0.75rem 2.5rem 0.75rem 1rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
}

.search-group i {
    position: absolute;
    right: 1rem;
    color: #666;
}

.results-count {
    text-align: center;
    color: #666;
    font-weight: 500;
    margin-top: 1rem;
}

.gallery-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.gallery-item {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: white;
    display: flex;
    flex-direction: column;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.gallery-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-image img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.view-btn {
    background: white;
    color: #3498db;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.view-btn:hover {
    background: #3498db;
    color: white;
    transform: scale(1.05);
}

.gallery-card-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.gallery-card-content h3 {
    color: #2c3e50;
    margin-bottom: 0.75rem;
    font-size: 1.3rem;
    font-weight: 600;
}

.gallery-card-content .description {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    flex: 1;
}

.gallery-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: auto;
}

.gallery-meta span {
    background: #f0f0f0;
    color: #555;
    padding: 0.4rem 0.9rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.gallery-meta span i {
    font-size: 0.9rem;
}

.gallery-meta .date {
    background: #e3f2fd;
    color: #1976d2;
}

.gallery-meta .count {
    background: #f3e5f5;
    color: #7b1fa2;
}

.gallery-meta .category {
    background: #e8f5e9;
    color: #388e3c;
}

.gallery-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.gallery-actions button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.gallery-actions button:hover {
    background: rgba(255,255,255,0.3);
}

.media-type-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #3498db;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
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
    margin-bottom: 0;
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

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    position: relative;
    margin: 5% auto;
    padding: 2rem;
    width: 90%;
    max-width: 800px;
    background: white;
    border-radius: 15px;
}

.close {
    position: absolute;
    top: 1rem;
    right: 1.5rem;
    color: #aaa;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.modal-media img,
.modal-media video {
    width: 100%;
    height: auto;
    border-radius: 10px;
}

.modal-info {
    margin-top: 1.5rem;
}

.modal-info h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.modal-info p {
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .filter-controls {
        grid-template-columns: 1fr;
    }
    
    .gallery-container {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
        padding: 1rem;
    }
}
</style>

<script>
// View event function
function viewEvent(eventId) {
    window.location.href = 'gallery-event.php?id=' + eventId;
}

// Gallery modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('media-modal');
    const modalImage = document.getElementById('modal-image');
    const modalVideo = document.getElementById('modal-video');
    const modalTitle = document.getElementById('modal-title');
    const modalDescription = document.getElementById('modal-description');
    const closeBtn = document.querySelector('.close');

    // View image buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageSrc = this.dataset.image;
            const galleryItem = this.closest('.gallery-item');
            const title = galleryItem.querySelector('h3').textContent;
            const description = galleryItem.querySelector('p').textContent;

            modalImage.src = imageSrc;
            modalImage.style.display = 'block';
            modalVideo.style.display = 'none';
            modalTitle.textContent = title;
            modalDescription.textContent = description;
            modal.style.display = 'block';
        });
    });

    // Play video buttons
    document.querySelectorAll('.play-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const videoSrc = this.dataset.video;
            const galleryItem = this.closest('.gallery-item');
            const title = galleryItem.querySelector('h3').textContent;
            const description = galleryItem.querySelector('p').textContent;

            modalVideo.querySelector('source').src = videoSrc;
            modalVideo.load();
            modalVideo.style.display = 'block';
            modalImage.style.display = 'none';
            modalTitle.textContent = title;
            modalDescription.textContent = description;
            modal.style.display = 'block';
        });
    });

    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        modalVideo.pause();
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            modalVideo.pause();
        }
    });

    // Download functionality
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mediaUrl = this.dataset.image || this.dataset.video;
            const link = document.createElement('a');
            link.href = mediaUrl;
            link.download = mediaUrl.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
});
</script>

<?php include '../includes/public_footer.php'; ?>