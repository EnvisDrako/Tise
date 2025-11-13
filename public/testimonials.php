<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Testimonials";
$page_description = "Read success stories and testimonials from our alumni and current students";
include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Student Testimonials</h1>
        <p>Success stories from our alumni and current students</p>
    </div>
</div>

<section class="testimonials-filter section">
    <div class="container">
        <div class="filter-controls">
            <div class="filter-group">
                <label for="course-filter">Course:</label>
                <select id="course-filter" class="filter-select" data-filter="course">
                    <option value="all">All Courses</option>
                    <option value="web-development">Web Development</option>
                    <option value="data-science">Data Science</option>
                    <option value="digital-marketing">Digital Marketing</option>
                    <option value="graphic-design">Graphic Design</option>
                    <option value="business-analytics">Business Analytics</option>
                    <option value="english-communication">English Communication</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="graduation-year">Graduation Year:</label>
                <select id="graduation-year" class="filter-select" data-filter="graduationYear">
                    <option value="all">All Years</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="industry">Industry:</label>
                <select id="industry" class="filter-select" data-filter="industry">
                    <option value="all">All Industries</option>
                    <option value="technology">Technology</option>
                    <option value="marketing">Marketing</option>
                    <option value="finance">Finance</option>
                    <option value="healthcare">Healthcare</option>
                    <option value="education">Education</option>
                    <option value="startup">Startup</option>
                </select>
            </div>
            <div class="search-group">
                <input type="text" class="search-input" placeholder="Search by name, company, or course...">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="results-count">Showing all testimonials</div>
    </div>
</section>

<section class="testimonials-grid section">
    <div class="container">
        <div class="testimonials-container">
            <!-- Sample testimonials with proper data attributes -->
            <div class="testimonial-card filterable-item searchable-item" 
                 data-course="web-development" 
                 data-graduation-year="2024" 
                 data-industry="technology">
                <div class="testimonial-header">
                    <div class="student-photo">
                        <img src="/images/student-sarah.jpg" alt="Sarah Johnson">
                    </div>
                    <div class="student-info">
                        <h3>Sarah Johnson</h3>
                        <div class="course-info">Full Stack Web Development</div>
                        <div class="graduation-info">Graduated: 2024</div>
                        <div class="current-role">Software Developer at TechCorp</div>
                    </div>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="testimonial-content">
                    <p>"The web development course completely transformed my career. The hands-on projects and industry mentorship helped me land my dream job at TechCorp."</p>
                </div>
            </div>
            
            <!-- Add more testimonials here -->
        </div>
    </div>
</section>

<?php include '../includes/public_footer.php'; ?>