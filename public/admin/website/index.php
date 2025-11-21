<?php
/**
 * Website Management Dashboard
 */
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../../includes/db_connect.php';

$pageTitle = 'Website Management Dashboard';

// Get statistics
try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    // Courses stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM courses");
    $totalCourses = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM courses WHERE status='active'");
    $activeCourses = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Trainers stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM trainers");
    $totalTrainers = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM trainers WHERE status='active'");
    $activeTrainers = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Notices stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM notices");
    $totalNotices = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Downloads stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM downloads");
    $totalDownloads = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Placements stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM placements");
    $totalPlacements = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Gallery stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery_events");
    $totalGalleryEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT SUM(photo_count) as total FROM gallery_events");
    $totalPhotos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Recent courses
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 5");
    $recentCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent trainers
    $stmt = $pdo->query("SELECT * FROM trainers ORDER BY created_at DESC LIMIT 5");
    $recentTrainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $totalCourses = $activeCourses = $totalTrainers = $activeTrainers = 0;
    $totalNotices = $totalDownloads = $totalPlacements = 0;
    $recentCourses = $recentTrainers = [];
}

renderAdminHeader($pageTitle);
?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-globe"></i> Website Management Dashboard</h1>
        <p>Manage your website content, courses, trainers, and more</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card courses">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalCourses; ?></h3>
                <p>Total Courses</p>
                <span class="stat-detail"><?php echo $activeCourses; ?> Active</span>
            </div>
            <a href="courses.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="stat-card trainers">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalTrainers; ?></h3>
                <p>Total Trainers</p>
                <span class="stat-detail"><?php echo $activeTrainers; ?> Active</span>
            </div>
            <a href="trainers.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="stat-card notices">
            <div class="stat-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalNotices; ?></h3>
                <p>Notices</p>
                <span class="stat-detail">Announcements</span>
            </div>
            <a href="notices.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="stat-card downloads">
            <div class="stat-icon">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalDownloads; ?></h3>
                <p>Downloads</p>
                <span class="stat-detail">Files & Resources</span>
            </div>
            <a href="downloads.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="stat-card placements">
            <div class="stat-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalPlacements; ?></h3>
                <p>Placements</p>
                <span class="stat-detail">Success Stories</span>
            </div>
            <a href="placements.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="stat-card gallery">
            <div class="stat-icon">
                <i class="fas fa-images"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalGalleryEvents; ?></h3>
                <p>Gallery Events</p>
                <span class="stat-detail"><?php echo $totalPhotos; ?> Photos</span>
            </div>
            <a href="gallery.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
        <div class="actions-grid">
            <a href="courses.php" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <span>Add New Course</span>
            </a>
            <a href="trainers.php" class="action-card">
                <i class="fas fa-user-plus"></i>
                <span>Add New Trainer</span>
            </a>
            <a href="notices.php" class="action-card">
                <i class="fas fa-bell"></i>
                <span>Post Notice</span>
            </a>
            <a href="downloads.php" class="action-card">
                <i class="fas fa-file-upload"></i>
                <span>Upload File</span>
            </a>
            <a href="placements.php" class="action-card">
                <i class="fas fa-trophy"></i>
                <span>Add Placement</span>
            </a>
            <a href="gallery.php" class="action-card">
                <i class="fas fa-images"></i>
                <span>Create Gallery Event</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Content -->
    <div class="recent-content">
        <div class="recent-section">
            <div class="section-header">
                <h2><i class="fas fa-book"></i> Recent Courses</h2>
                <a href="courses.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="content-list">
                <?php if (empty($recentCourses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <p>No courses yet</p>
                        <a href="courses.php" class="btn btn-primary">Add Your First Course</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentCourses as $course): ?>
                        <div class="content-item">
                            <div class="item-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="item-content">
                                <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                                <p><?php echo htmlspecialchars(substr($course['description'], 0, 80)); ?>...</p>
                                <div class="item-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo $course['duration']; ?></span>
                                    <span><i class="fas fa-signal"></i> <?php echo $course['level']; ?></span>
                                    <span class="badge badge-<?php echo $course['status']; ?>">
                                        <?php echo ucfirst($course['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="recent-section">
            <div class="section-header">
                <h2><i class="fas fa-chalkboard-teacher"></i> Recent Trainers</h2>
                <a href="trainers.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="content-list">
                <?php if (empty($recentTrainers)): ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>No trainers yet</p>
                        <a href="trainers.php" class="btn btn-primary">Add Your First Trainer</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentTrainers as $trainer): ?>
                        <div class="content-item">
                            <div class="item-avatar">
                                <?php if ($trainer['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($trainer['image_url']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="item-content">
                                <h4><?php echo htmlspecialchars($trainer['name']); ?></h4>
                                <p class="designation"><?php echo htmlspecialchars($trainer['designation']); ?></p>
                                <div class="item-meta">
                                    <span><i class="fas fa-star"></i> <?php echo $trainer['specialization']; ?></span>
                                    <span><i class="fas fa-briefcase"></i> <?php echo $trainer['experience']; ?></span>
                                    <span class="badge badge-<?php echo $trainer['status']; ?>">
                                        <?php echo ucfirst($trainer['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}

.stat-card.courses::before { background: #3498db; }
.stat-card.trainers::before { background: #e74c3c; }
.stat-card.notices::before { background: #f39c12; }
.stat-card.downloads::before { background: #9b59b6; }
.stat-card.placements::before { background: #27ae60; }
.stat-card.gallery::before { background: #16a085; }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-card.courses .stat-icon { background: #e3f2fd; color: #3498db; }
.stat-card.trainers .stat-icon { background: #ffebee; color: #e74c3c; }
.stat-card.notices .stat-icon { background: #fff3e0; color: #f39c12; }
.stat-card.downloads .stat-icon { background: #f3e5f5; color: #9b59b6; }
.stat-card.placements .stat-icon { background: #e8f5e9; color: #27ae60; }
.stat-card.gallery .stat-icon { background: #e0f2f1; color: #16a085; }

.stat-content h3 {
    font-size: 2.5rem;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    color: #7f8c8d;
    margin: 0.5rem 0;
}

.stat-detail {
    font-size: 0.9rem;
    color: #95a5a6;
}

.stat-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1rem;
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    transition: gap 0.3s ease;
}

.stat-link:hover {
    gap: 1rem;
}

.quick-actions {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    margin: 2rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.quick-actions h2 {
    margin-bottom: 1.5rem;
    color: #2c3e50;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
}

.action-card:hover {
    background: #3498db;
    color: white;
    transform: translateY(-3px);
}

.action-card i {
    font-size: 2rem;
}

.recent-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.recent-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #ecf0f1;
}

.section-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.3rem;
}

.view-all {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: gap 0.3s ease;
}

.view-all:hover {
    gap: 1rem;
}

.content-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.content-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: background 0.3s ease;
}

.content-item:hover {
    background: #ecf0f1;
}

.item-icon {
    width: 50px;
    height: 50px;
    background: #3498db;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.item-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    background: #e74c3c;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.item-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-content {
    flex: 1;
}

.item-content h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.item-content p {
    margin: 0 0 0.5rem 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.item-content .designation {
    color: #e74c3c;
    font-weight: 600;
}

.item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.85rem;
    color: #95a5a6;
}

.item-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.badge {
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
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

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #95a5a6;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state p {
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .recent-content {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php renderAdminFooter(); ?>
