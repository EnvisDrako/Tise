<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "About Us";
$page_description = "Learn about our institution's history, mission, leadership team, and commitment to educational excellence. Discover our modern facilities, expert faculty, and student success stories";
$page_keywords = "about us, educational institution, history, mission, leadership, faculty, facilities, accreditation";
$page_image = "/images/about-building.jpg";
$page_type = "website";

// Structured data for about page
$structured_data = [
    "@context" => "https://schema.org",
    "@type" => "AboutPage",
    "name" => "About EduPortal",
    "description" => $page_description,
    "mainEntity" => [
        "@type" => "EducationalOrganization",
        "name" => "EduPortal",
        "foundingDate" => "2014",
        "founder" => [
            "@type" => "Person",
            "name" => "Dr. Sarah Williams",
            "jobTitle" => "Director & Founder"
        ],
        "numberOfEmployees" => "50+",
        "alumni" => [
            "@type" => "QuantitativeValue",
            "value" => "500+"
        ]
    ]
];

include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>About Our Institution</h1>
        <p>Committed to excellence in education and shaping future leaders</p>
    </div>
</div>

<section class="about-intro section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>Our Story</h2>
                <p>Founded with a vision to provide world-class education and training, our institution has been at the forefront of educational innovation for over a decade. We believe in nurturing talent, fostering creativity, and preparing students for the challenges of tomorrow.</p>
                <p>Our commitment to excellence is reflected in our comprehensive curriculum, state-of-the-art facilities, and dedicated faculty who bring years of industry experience to the classroom.</p>
            </div>
            <div class="about-image">
                <img src="/images/about-building.jpg" alt="Our Institution" data-src="/images/about-building.jpg" class="lazy">
            </div>
        </div>
    </div>
</section>

<section class="mission-vision section">
    <div class="container">
        <div class="card-grid">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3>Our Mission</h3>
                <p>To provide quality education and training that empowers students with the knowledge, skills, and confidence needed to excel in their chosen careers and contribute meaningfully to society.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Our Vision</h3>
                <p>To be recognized as a leading educational institution that transforms lives through innovative learning experiences and creates a positive impact on the global community.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Our Values</h3>
                <p>Excellence, Integrity, Innovation, Inclusivity, and Empowerment guide everything we do. We foster an environment of respect, collaboration, and continuous learning.</p>
            </div>
        </div>
    </div>
</section>

<section class="leadership section">
    <div class="container">
        <div class="section-title">
            <h2>Leadership Team</h2>
            <p>Meet the visionaries who guide our institution towards excellence</p>
        </div>
        <div class="leadership-grid">
            <div class="leader-card">
                <div class="leader-image">
                    <img src="/images/director.jpg" alt="Dr. Sarah Williams" data-src="/images/director.jpg" class="lazy">
                </div>
                <div class="leader-info">
                    <h3>Dr. Sarah Williams</h3>
                    <h4>Director & Founder</h4>
                    <p>With over 20 years of experience in education and industry, Dr. Williams leads our institution with a passion for innovation and student success.</p>
                    <div class="leader-qualifications">
                        <span>Ph.D. in Computer Science</span>
                        <span>Former Industry Executive</span>
                    </div>
                </div>
            </div>
            <div class="leader-card">
                <div class="leader-image">
                    <img src="/images/academic-head.jpg" alt="Prof. Michael Johnson" data-src="/images/academic-head.jpg" class="lazy">
                </div>
                <div class="leader-info">
                    <h3>Prof. Michael Johnson</h3>
                    <h4>Academic Head</h4>
                    <p>Prof. Johnson oversees our academic programs and ensures the highest standards of curriculum design and delivery.</p>
                    <div class="leader-qualifications">
                        <span>M.Tech in Information Technology</span>
                        <span>15+ Years Teaching Experience</span>
                    </div>
                </div>
            </div>
            <div class="leader-card">
                <div class="leader-image">
                    <img src="/images/placement-head.jpg" alt="Ms. Emily Davis" data-src="/images/placement-head.jpg" class="lazy">
                </div>
                <div class="leader-info">
                    <h3>Ms. Emily Davis</h3>
                    <h4>Placement & Career Services Head</h4>
                    <p>Ms. Davis leads our placement initiatives and maintains strong industry connections to ensure excellent career opportunities for our students.</p>
                    <div class="leader-qualifications">
                        <span>MBA in Human Resources</span>
                        <span>Industry Relations Expert</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="facilities section">
    <div class="container">
        <div class="section-title">
            <h2>Our Facilities</h2>
            <p>Modern infrastructure designed to enhance the learning experience</p>
        </div>
        <div class="facilities-grid">
            <div class="facility-item">
                <div class="facility-icon">
                    <i class="fas fa-desktop"></i>
                </div>
                <h3>Computer Labs</h3>
                <p>State-of-the-art computer labs with latest hardware and software for hands-on learning.</p>
            </div>
            <div class="facility-item">
                <div class="facility-icon">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <h3>Smart Classrooms</h3>
                <p>Interactive smart boards and multimedia equipment for engaging classroom sessions.</p>
            </div>
            <div class="facility-item">
                <div class="facility-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>Library</h3>
                <p>Comprehensive library with books, journals, and digital resources for research and study.</p>
            </div>
            <div class="facility-item">
                <div class="facility-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h3>High-Speed Internet</h3>
                <p>Campus-wide Wi-Fi connectivity ensuring seamless access to online resources.</p>
            </div>
            <div class="facility-item">
                <div class="facility-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Seminar Halls</h3>
                <p>Well-equipped seminar halls for workshops, guest lectures, and presentations.</p>
            </div>
            <div class="facility-item">
                <div class="facility-icon">
                    <i class="fas fa-coffee"></i>
                </div>
                <h3>Student Lounge</h3>
                <p>Comfortable spaces for students to relax, collaborate, and network with peers.</p>
            </div>
        </div>
    </div>
</section>

<section class="contact-info section">
    <div class="container">
        <div class="section-title">
            <h2>Get in Touch</h2>
            <p>Visit us or contact us for more information</p>
        </div>
        <div class="contact-grid">
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Address</h3>
                <p>123 Education Street<br>Knowledge City, State 12345<br>Country</p>
            </div>
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Phone</h3>
                <p>Main: +1 234 567 8900<br>Admissions: +1 234 567 8901<br>Placement: +1 234 567 8902</p>
            </div>
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email</h3>
                <p>info@eduportal.com<br>admissions@eduportal.com<br>placement@eduportal.com</p>
            </div>
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Office Hours</h3>
                <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 9:00 AM - 2:00 PM<br>Sunday: Closed</p>
            </div>
        </div>
    </div>
</section>

<style>
.about-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
}

.about-text h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 2.2rem;
}

.about-text p {
    margin-bottom: 1.5rem;
    line-height: 1.8;
    color: #555;
}

.about-image img {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.mission-vision {
    background: #f8f9fa;
}

.leadership-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.leader-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.leader-card:hover {
    transform: translateY(-5px);
}

.leader-image {
    margin-bottom: 1.5rem;
}

.leader-image img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #3498db;
}

.leader-info h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
}

.leader-info h4 {
    color: #3498db;
    margin-bottom: 1rem;
    font-weight: 500;
}

.leader-info p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.leader-qualifications {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.leader-qualifications span {
    background: #ecf0f1;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    color: #2c3e50;
}

.facilities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.facility-item {
    text-align: center;
    padding: 2rem;
}

.facility-icon {
    font-size: 3rem;
    color: #3498db;
    margin-bottom: 1rem;
}

.facility-item h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.facility-item p {
    color: #666;
    line-height: 1.6;
}

.contact-info {
    background: #2c3e50;
    color: white;
}

.contact-info .section-title h2,
.contact-info .section-title p {
    color: white;
}

.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.contact-item {
    text-align: center;
    padding: 2rem;
}

.contact-icon {
    font-size: 3rem;
    color: #3498db;
    margin-bottom: 1rem;
}

.contact-item h3 {
    color: #3498db;
    margin-bottom: 1rem;
}

.contact-item p {
    color: #bdc3c7;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .about-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .leadership-grid {
        grid-template-columns: 1fr;
    }
    
    .facilities-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .contact-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/public_footer.php'; ?>