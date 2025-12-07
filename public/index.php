<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Home";
$page_description = "Welcome to our leading educational institution offering quality courses and training programs with expert faculty, modern facilities, and excellent placement support";
$page_keywords = "education, courses, training, certification, career development, professional skills, placement support, expert faculty";
$page_image = "/images/hero-bg.jpg";
$page_type = "website";

// Get popular courses from database
require_once __DIR__ . '/../includes/db_connect.php';

$popularCourses = [];
$latestNotifications = [];

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    // Get 3 most popular courses (by enrollment or featured)
    $stmt = $pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY created_at DESC LIMIT 3");
    $popularCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get 3 latest notices (table is called 'notices' not 'notifications')
    $stmt = $pdo->query("SELECT * FROM notices WHERE is_active=1 ORDER BY created_at DESC LIMIT 3");
    $latestNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Silently fail, will show empty sections
    error_log("Database error on homepage: " . $e->getMessage());
}

// Structured data for home page
$structured_data = [
    "@context" => "https://schema.org",
    "@type" => "EducationalOrganization",
    "name" => "EduPortal - Educational Institution",
    "url" => "https://eduportal.com",
    "logo" => "https://eduportal.com/images/logo.png",
    "description" => $page_description,
    "foundingDate" => "2014",
    "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "123 Education Street",
        "addressLocality" => "Knowledge City",
        "addressRegion" => "State",
        "postalCode" => "12345",
        "addressCountry" => "Country"
    ],
    "contactPoint" => [
        "@type" => "ContactPoint",
        "telephone" => "+1-234-567-8900",
        "contactType" => "customer service",
        "email" => "info@eduportal.com",
        "availableLanguage" => "English"
    ],
    "sameAs" => [
        "https://facebook.com/eduportal",
        "https://twitter.com/eduportal",
        "https://linkedin.com/company/eduportal",
        "https://instagram.com/eduportal",
        "https://youtube.com/eduportal"
    ],
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => "4.8",
        "reviewCount" => "150",
        "bestRating" => "5"
    ]
];

include __DIR__ . '/../includes/public_header.php';
?>

<section class="hero-section">
    <div class="hero-content">
        <div class="container">
            <div class="hero-text">
                <h1>Welcome to Tast Academy</h1>
                <p>Nurturing talent, Realising dreams.</p>
                <div class="hero-buttons">
                    <a href="<?php echo url('courses'); ?>" class="btn btn-primary">Explore Courses</a>
                    <a href="<?php echo url('enquiry'); ?>" class="btn btn-secondary">Enquire Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-section section">
    <div class="container">
        <div class="section-title">
            <h2>Why Choose Us</h2>
            <p>Discover what makes us the preferred choice for quality education</p>
        </div>
        <div class="card-grid">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3>Expert Faculty</h3>
                <p>Learn from industry experts and experienced professionals who bring real-world knowledge to the classroom.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3>Modern Facilities</h3>
                <p>State-of-the-art infrastructure and technology to provide the best learning environment.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3>Industry Recognition</h3>
                <p>Accredited programs and certifications recognized by leading industry bodies.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Placement Support</h3>
                <p>Comprehensive career guidance and placement assistance to help you achieve your goals.</p>
            </div>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Students Trained</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50+</div>
                <div class="stat-label">Courses Offered</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">95%</div>
                <div class="stat-label">Placement Rate</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">10+</div>
                <div class="stat-label">Years Experience</div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($latestNotifications)): ?>
<section class="notifications-section section">
    <div class="container">
        <div class="section-title">
            <h2>Latest Updates</h2>
            <p>Stay informed with our recent announcements and news</p>
        </div>
        <div class="notifications-list">
            <?php foreach ($latestNotifications as $notification): ?>
            <div class="notification-item">
                <div class="notification-icon">
                    <?php 
                    // Different icon based on notice type
                    $iconClass = 'fa-bell';
                    if (isset($notification['type'])) {
                        switch($notification['type']) {
                            case 'urgent': $iconClass = 'fa-exclamation-circle'; break;
                            case 'important': $iconClass = 'fa-star'; break;
                            case 'event': $iconClass = 'fa-calendar-alt'; break;
                            default: $iconClass = 'fa-bell';
                        }
                    }
                    ?>
                    <i class="fas <?php echo $iconClass; ?>"></i>
                </div>
                <div class="notification-content">
                    <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr(strip_tags($notification['content']), 0, 150)); ?><?php echo strlen(strip_tags($notification['content'])) > 150 ? '...' : ''; ?></p>
                    <div class="notification-meta">
                        <span class="date"><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($notification['created_at'])); ?></span>
                        <?php if (isset($notification['type'])): ?>
                            <span class="category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars(ucfirst($notification['type'])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="<?php echo url('notifications'); ?>" class="btn btn-primary">View All Notifications</a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="courses-preview section">
    <div class="container">
        <div class="section-title">
            <h2>Popular Courses</h2>
            <p>Explore our most sought-after programs designed for career success</p>
        </div>
        <?php if (!empty($popularCourses)): ?>
        <div class="card-grid">
            <?php foreach ($popularCourses as $course): ?>
            <div class="card course-card">
                <div class="course-image">
                    <?php if ($course['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($course['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'200\'%3E%3Crect fill=\'%233498db\' width=\'400\' height=\'200\'/%3E%3Ctext fill=\'white\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'24\'%3E<?php echo htmlspecialchars($course['title']); ?>%3C/text%3E%3C/svg%3E';">
                    <?php else: ?>
                        <div style="width:100%; height:200px; background:#3498db; display:flex; align-items:center; justify-content:center; color:white; font-size:1.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="course-content">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?><?php echo strlen($course['description']) > 100 ? '...' : ''; ?></p>
                    <div class="course-meta">
                        <?php if ($course['duration']): ?>
                            <span class="duration"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['duration']); ?></span>
                        <?php endif; ?>
                        <?php if ($course['level']): ?>
                            <span class="level"><i class="fas fa-signal"></i> <?php echo htmlspecialchars($course['level']); ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo url('courses'); ?>" class="btn">Learn More</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <p>No courses available at the moment. Check back soon!</p>
        </div>
        <?php endif; ?>
        <div class="text-center">
            <a href="<?php echo url('courses'); ?>" class="btn btn-primary">View All Courses</a>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.9), rgba(44, 62, 80, 0.9)), url('<?php echo asset('images/hero-bg.jpg'); ?>');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 6rem 0;
    text-align: center;
}

.hero-text h1 {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.hero-text p {
    font-size: 1.3rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary {
    background: #e74c3c;
}

.btn-primary:hover {
    background: #c0392b;
}

.card-icon {
    font-size: 3rem;
    color: #3498db;
    margin-bottom: 1rem;
    text-align: center;
}

.stats-section {
    background: #2c3e50;
    color: white;
    padding: 4rem 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    text-align: center;
}

.stat-number {
    font-size: 3rem;
    font-weight: bold;
    color: #3498db;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
}

.course-card {
    overflow: hidden;
}

.course-image {
    height: 200px;
    overflow: hidden;
}

.course-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.course-card:hover .course-image img {
    transform: scale(1.05);
}

.course-content {
    padding: 1.5rem;
}

.course-meta {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    font-size: 0.9rem;
    color: #666;
}

.text-center {
    text-align: center;
    margin-top: 2rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: #f8f9fa;
    border-radius: 10px;
    color: #666;
}

.empty-state i {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 1rem;
}

.notifications-section {
    background: white;
}

.notifications-list {
    max-width: 900px;
    margin: 0 auto;
}

.notification-item {
    background: white;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    gap: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.notification-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.notification-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3498db, #2ecc71);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
}

.notification-content h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
}

.notification-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 0.75rem;
}

.notification-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.9rem;
    color: #999;
}

.notification-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

@media (max-width: 768px) {
    .hero-text h1 {
        font-size: 2.5rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .notification-item {
        flex-direction: column;
        text-align: center;
    }
    
    .notification-icon {
        margin: 0 auto;
    }
}
</style>

<?php include __DIR__ . '/../includes/public_footer.php'; ?>