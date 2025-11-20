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
                <h1>Excellence in Education</h1>
                <p>Empowering students with quality education and practical skills for a successful future</p>
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

<section class="courses-preview section">
    <div class="container">
        <div class="section-title">
            <h2>Popular Courses</h2>
            <p>Explore our most sought-after programs designed for career success</p>
        </div>
        <div class="card-grid">
            <div class="card course-card">
                <div class="course-image">
                    <img src="<?php echo asset('images/course-web-development.jpg'); ?>" alt="Web Development" data-src="<?php echo asset('images/course-web-development.jpg'); ?>" class="lazy">
                </div>
                <div class="course-content">
                    <h3>Web Development</h3>
                    <p>Master modern web technologies including HTML, CSS, JavaScript, and popular frameworks.</p>
                    <div class="course-meta">
                        <span class="duration"><i class="fas fa-clock"></i> 6 Months</span>
                        <span class="level"><i class="fas fa-signal"></i> Beginner</span>
                    </div>
                    <a href="<?php echo url('courses#web-development'); ?>" class="btn">Learn More</a>
                </div>
            </div>
            <div class="card course-card">
                <div class="course-image">
                    <img src="<?php echo asset('images/course-data-science.jpg'); ?>" alt="Data Science" data-src="<?php echo asset('images/course-data-science.jpg'); ?>" class="lazy">
                </div>
                <div class="course-content">
                    <h3>Data Science</h3>
                    <p>Learn data analysis, machine learning, and statistical modeling with Python and R.</p>
                    <div class="course-meta">
                        <span class="duration"><i class="fas fa-clock"></i> 8 Months</span>
                        <span class="level"><i class="fas fa-signal"></i> Intermediate</span>
                    </div>
                    <a href="<?php echo url('courses#data-science'); ?>" class="btn">Learn More</a>
                </div>
            </div>
            <div class="card course-card">
                <div class="course-image">
                    <img src="<?php echo asset('images/course-digital-marketing.jpg'); ?>" alt="Digital Marketing" data-src="<?php echo asset('images/course-digital-marketing.jpg'); ?>" class="lazy">
                </div>
                <div class="course-content">
                    <h3>Digital Marketing</h3>
                    <p>Master SEO, social media marketing, content strategy, and online advertising.</p>
                    <div class="course-meta">
                        <span class="duration"><i class="fas fa-clock"></i> 4 Months</span>
                        <span class="level"><i class="fas fa-signal"></i> Beginner</span>
                    </div>
                    <a href="<?php echo url('courses#digital-marketing'); ?>" class="btn">Learn More</a>
                </div>
            </div>
        </div>
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

@media (max-width: 768px) {
    .hero-text h1 {
        font-size: 2.5rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php include __DIR__ . '/../includes/public_footer.php'; ?>