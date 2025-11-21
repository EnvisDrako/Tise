<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Courses & Programs";
$page_description = "Explore our comprehensive range of courses designed to enhance your skills and advance your career. From web development to data science, digital marketing to business analytics";
$page_keywords = "courses, programs, web development, data science, digital marketing, business analytics, certification, professional training";
$page_image = "/images/course-web-development.jpg";
$page_type = "website";

// Get courses from database
require_once '../includes/db_connect.php';

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY created_at DESC");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $courses = [];
}

// Structured data for courses page
$course_data = [
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Professional Courses",
    "description" => $page_description,
    "numberOfItems" => 6,
    "itemListElement" => [
        [
            "@type" => "Course",
            "name" => "Full Stack Web Development",
            "description" => "Master modern web technologies including HTML5, CSS3, JavaScript, React, Node.js",
            "provider" => [
                "@type" => "EducationalOrganization",
                "name" => "EduPortal"
            ],
            "courseMode" => "blended",
            "timeRequired" => "P6M",
            "offers" => [
                "@type" => "Offer",
                "price" => "799",
                "priceCurrency" => "USD"
            ]
        ],
        [
            "@type" => "Course",
            "name" => "Data Science & Analytics",
            "description" => "Learn Python, R, machine learning, statistical analysis, and data visualization",
            "provider" => [
                "@type" => "EducationalOrganization",
                "name" => "EduPortal"
            ],
            "courseMode" => "blended",
            "timeRequired" => "P8M",
            "offers" => [
                "@type" => "Offer",
                "price" => "1299",
                "priceCurrency" => "USD"
            ]
        ]
    ]
];

include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Courses & Programs</h1>
        <p>Choose from our wide range of industry-relevant courses</p>
    </div>
</div>

<section class="courses-filter section">
    <div class="container">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="course-type">Course Type:</label>
                <select id="course-type" class="filter-select" data-filter="courseType">
                    <option value="all">All Types</option>
                    <option value="technical">Technical</option>
                    <option value="business">Business</option>
                    <option value="creative">Creative</option>
                    <option value="language">Language</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="duration">Duration:</label>
                <select id="duration" class="filter-select" data-filter="duration">
                    <option value="all">All Durations</option>
                    <option value="short">1-3 Months</option>
                    <option value="medium">4-6 Months</option>
                    <option value="long">6+ Months</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="level">Level:</label>
                <select id="level" class="filter-select" data-filter="level">
                    <option value="all">All Levels</option>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="fee-range">Fee Range:</label>
                <select id="fee-range" class="filter-select" data-filter="feeRange">
                    <option value="all">All Ranges</option>
                    <option value="low">Under $500</option>
                    <option value="medium">$500 - $1000</option>
                    <option value="high">Above $1000</option>
                </select>
            </div>
            <div class="filter-group search-group">
                <label for="search">Search:</label>
                <div class="search-input-wrapper">
                    <input type="text" id="search" class="search-input" placeholder="Search courses, keywords, or faculty...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>
        <div class="results-count">Showing all courses</div>
    </div>
</section>

<section class="courses-grid section">
    <div class="container">
        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h3>No Courses Available</h3>
                <p>Courses will be displayed here once they are added by the admin.</p>
            </div>
        <?php else: ?>
        <div class="courses-container">
            <?php foreach ($courses as $course): 
                $features = !empty($course['features']) ? explode(',', $course['features']) : [];
            ?>
            <div class="course-card filterable-item searchable-item" 
                 data-level="<?php echo strtolower($course['level']); ?>">
                <?php if ($course['image_url']): ?>
                <div class="course-image">
                    <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                </div>
                <?php endif; ?>
                <div class="course-content">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    <div class="course-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($course['duration']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-signal"></i>
                            <span><?php echo htmlspecialchars($course['level']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span><?php echo htmlspecialchars($course['fee']); ?></span>
                        </div>
                    </div>
                    <?php if ($course['faculty']): ?>
                    <div class="course-faculty">
                        <strong>Faculty:</strong> <?php echo htmlspecialchars($course['faculty']); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($features)): ?>
                    <div class="course-features">
                        <?php foreach ($features as $feature): ?>
                            <span class="feature"><?php echo htmlspecialchars(trim($feature)); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="course-actions">
                        <a href="<?php echo url('enquiry'); ?>" class="btn btn-primary">Enroll Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
            </section>

<style>
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 5rem;
    color: #bdc3c7;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #7f8c8d;
    margin-bottom: 2rem;
}

.courses-filter {
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

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
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

.search-input-wrapper i {
    position: absolute;
    right: 1rem;
    color: #666;
    pointer-events: none;
}

.results-count {
    text-align: center;
    color: #666;
    font-weight: 500;
    margin-top: 1rem;
}

.courses-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.course-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.course-image {
    position: relative;
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

.course-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.course-content {
    padding: 2rem;
}

.course-category {
    color: #3498db;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.course-content h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.course-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.course-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.detail-item i {
    color: #3498db;
    width: 16px;
}

.course-faculty {
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    color: #555;
}

.course-features {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.feature {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .filter-controls {
        grid-template-columns: 1fr;
    }
    
    .courses-container {
        grid-template-columns: 1fr;
    }
    
    .course-details {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/public_footer.php'; ?>