<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Courses & Programs";
$page_description = "Explore our comprehensive range of courses designed to enhance your skills and advance your career. From web development to data science, digital marketing to business analytics";
$page_keywords = "courses, programs, web development, data science, digital marketing, business analytics, certification, professional training";
$page_image = "/images/course-web-development.jpg";
$page_type = "website";

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
            <div class="search-group">
                <input type="text" class="search-input" placeholder="Search courses, keywords, or faculty...">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="results-count">Showing all courses</div>
    </div>
</section>

<section class="courses-grid section">
    <div class="container">
        <div class="courses-container">
            <!-- Web Development Course -->
            <div class="course-card filterable-item searchable-item" 
                 data-course-type="technical" 
                 data-duration="medium" 
                 data-level="beginner" 
                 data-fee-range="medium">
                <div class="course-image">
                    <img src="/images/course-web-development.jpg" alt="Web Development" data-src="/images/course-web-development.jpg" class="lazy">
                    <div class="course-badge">Popular</div>
                </div>
                <div class="course-content">
                    <div class="course-category">Technical</div>
                    <h3>Full Stack Web Development</h3>
                    <p>Master modern web technologies including HTML5, CSS3, JavaScript, React, Node.js, and database management.</p>
                    <div class="course-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>6 Months</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-signal"></i>
                            <span>Beginner</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>25 Students</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span>$799</span>
                        </div>
                    </div>
                    <div class="course-faculty">
                        <strong>Faculty:</strong> Prof. John Smith, Ms. Sarah Wilson
                    </div>
                    <div class="course-features">
                        <span class="feature">Hands-on Projects</span>
                        <span class="feature">Industry Mentorship</span>
                        <span class="feature">Job Assistance</span>
                    </div>
                    <div class="course-actions">
                        <a href="#" class="btn btn-primary">Enroll Now</a>
                        <button class="btn-share" data-title="Full Stack Web Development Course" data-description="Master modern web technologies including HTML5, CSS3, JavaScript, React, Node.js" data-image="/images/course-web-development.jpg">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Science Course -->
            <div class="course-card filterable-item searchable-item" 
                 data-course-type="technical" 
                 data-duration="long" 
                 data-level="intermediate" 
                 data-fee-range="high">
                <div class="course-image">
                    <img src="/images/course-data-science.jpg" alt="Data Science" data-src="/images/course-data-science.jpg" class="lazy">
                    <div class="course-badge">High Demand</div>
                </div>
                <div class="course-content">
                    <div class="course-category">Technical</div>
                    <h3>Data Science & Analytics</h3>
                    <p>Learn Python, R, machine learning, statistical analysis, and data visualization with real-world projects.</p>
                    <div class="course-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>8 Months</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-signal"></i>
                            <span>Intermediate</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>20 Students</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span>$1299</span>
                        </div>
                    </div>
                    <div class="course-faculty">
                        <strong>Faculty:</strong> Dr. Michael Chen, Prof. Lisa Anderson
                    </div>
                    <div class="course-features">
                        <span class="feature">Real Data Projects</span>
                        <span class="feature">Industry Tools</span>
                        <span class="feature">Certification</span>
                    </div>
                    <a href="#" class="btn btn-primary">Enroll Now</a>
                </div>
            </div>

            <!-- Digital Marketing Course -->
            <div class="course-card filterable-item searchable-item" 
                 data-course-type="business" 
                 data-duration="medium" 
                 data-level="beginner" 
                 data-fee-range="low">
                <div class="course-image">
                    <img src="/images/course-digital-marketing.jpg" alt="Digital Marketing" data-src="/images/course-digital-marketing.jpg" class="lazy">
                    <div class="course-badge">New</div>
                </div>
                <div class="course-content">
                    <div class="course-category">Business</div>
                    <h3>Digital Marketing Mastery</h3>
                    <p>Master SEO, SEM, social media marketing, content strategy, email marketing, and analytics.</p>
                    <div class="course-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>4 Months</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-signal"></i>
                            <span>Beginner</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>30 Students</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span>$499</span>
                        </div>
                    </div>
                    <div class="course-faculty">
                        <strong>Faculty:</strong> Ms. Emily Davis, Mr. Robert Taylor
                    </div>
                    <div class="course-features">
                        <span class="feature">Live Campaigns</span>
                        <span class="feature">Google Certified</span>
                        <span class="feature">Portfolio Building</span>
                    </div>
                    <a href="#" class="btn btn-primary">Enroll Now</a>
                </div>
            </div>

            <!-- Graphic Design Course -->
            <div class="course-card filterable-item searchable-item" 
                 data-course-type="creative" 
                 data-duration="medium" 
                 data-level="beginner" 
                 data-fee-range="medium">
                <div class="course-image">
                    <img src="/images/course-graphic-design.jpg" alt="Graphic Design" data-src="/images/course-graphic-design.jpg" class="lazy">
                </div>
                <div class="course-content">
                    <div class="course-category">Creative</div>
                    <h3>Graphic Design & Visual Arts</h3>
                    <p>Learn Adobe Creative Suite, typography, branding, logo design, and visual communication principles.</p>
                    <div class="course-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>5 Months</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-signal"></i>
                            <span>Beginner</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>20 Students</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span>$699</span>
                        </div>
                    </div>
                    <div class="course-faculty">
                        <strong>Faculty:</strong> Ms. Jennifer Lee, Mr. David Brown
                    </div>
                    <div class="course-features">
                        <span class="feature">Creative Projects</span>
                        <span class="feature">Adobe Certified</span>
                        <span class="feature">Portfolio Review</span>
                    </div>
                    <a href="#" class="btn btn-primary">Enroll Now</a>
                </div>
            </div>

            <!-- Business Analytics Course -->
            <div class="course-card filterable-item searchable-item" 
                 data-course-type="business" 
                 data-duration="long" 
                 data-level="intermediate" 
                 data-fee-range="high">
                <div class="course-image">
                    <img src="/images/course-business-analytics.jpg" alt="Business Analytics" data-src="/images/course-business-analytics.jpg" class="lazy">
                </div>
                <div class="course-content">
                    <div class="course-category">Business</div>
                    <h3>Business Analytics & Intelligence</h3>
                    <p>Master Excel, SQL, Tableau, Power BI, and statistical analysis for business decision making.</p>
                    <div class="course-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>7 Months</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-signal"></i>
                            <span>Intermediate</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>18 Students</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span>$1099</span>
                        </div>
                    </div>
                    <div class="course-faculty">
                        <strong>Faculty:</strong> Prof. Mark Johnson, Ms. Anna White
                    </div>
                    <div class="course-features">
                        <span class="feature">Case Studies</span>
                        <span class="feature">Industry Tools</span>
                        <span class="feature">Internship Support</span>
                    </div>
                    <a href="#" class="btn btn-primary">Enroll Now</a>
                </div>
            </div>

            <!-- English Communication Course -->
            <div class="course-card filterable-item searchable-item" 
                 data-course-type="language" 
                 data-duration="short" 
                 data-level="beginner" 
                 data-fee-range="low">
                <div class="course-image">
                    <img src="/images/course-english.jpg" alt="English Communication" data-src="/images/course-english.jpg" class="lazy">
                </div>
                <div class="course-content">
                    <div class="course-category">Language</div>
                    <h3>Business English Communication</h3>
                    <p>Improve your English speaking, writing, and presentation skills for professional environments.</p>
                    <div class="course-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>3 Months</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-signal"></i>
                            <span>Beginner</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>25 Students</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span>$299</span>
                        </div>
                    </div>
                    <div class="course-faculty">
                        <strong>Faculty:</strong> Ms. Rachel Green, Mr. James Wilson
                    </div>
                    <div class="course-features">
                        <span class="feature">Speaking Practice</span>
                        <span class="feature">Mock Interviews</span>
                        <span class="feature">Certification</span>
                    </div>
                    <a href="#" class="btn btn-primary">Enroll Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
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