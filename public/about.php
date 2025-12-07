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
                <p>Taset Academy was founded with a clear vision—to deliver world-class skill training that bridges the gap between learning and employability. For over a decade, we have been at the forefront of educational innovation, empowering learners with industry-ready skills and practical expertise.</p>
                <p>Driven by our motto “Skill Training • Certification • Placements,” we focus on nurturing talent, fostering creativity, and preparing students for the dynamic challenges of the modern workforce. Our cutting-edge curriculum, advanced training labs, and dedicated faculty with strong industry backgrounds ensure that every learner receives a high-quality, future-oriented education.</p>
                <p>At Taset Academy, we don't just teach—we transform potential into professional success.</p>
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
                <p>To deliver high-quality, job-oriented skill training that equips students with practical knowledge, globally recognized certifications, and the confidence to excel in their chosen careers—ultimately enabling them to contribute meaningfully to industry and society.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Our Vision</h3>
                <p>To be a leading skill-development institution recognized for transforming lives through innovative training programs, strong industry partnerships, and a commitment to building a skilled and employable global community.</p>
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
                    <img src="images\jomon.jpg" alt="Mr. Jomon Cheriyan James" data-src="images\jomon.jpg" class="lazy">
                </div>
                <div class="leader-info">
                    <h3>Mr. Jomon Cheriyan James</h3>
                    <h4>Director & Founder</h4>
                </div>
            </div>
            <div class="leader-card">
                <div class="leader-image">
                    <img src="/images/academic-head.jpg" alt="Mr. K M Riyad" data-src="/images/academic-head.jpg" class="lazy">
                </div>
                <div class="leader-info">
                    <h3>Mr. K M Riyad</h3>
                    <h4>Director</h4>
                </div>
            </div>
            <div class="leader-card">
                <div class="leader-image">
                    <img src="images\perumal.jpg" alt="Dr. Perumal Koshy" data-src="images\perumal.jpg" class="lazy">
                </div>
                <div class="leader-info">
                    <h3>Dr. Perumal Koshy</h3>
                    <h4>Academic Consultant</h4>
                </div>
            </div>
            <div class="leader-card">
                <div class="leader-image">
                    <img src="images\varghese.jpg" alt="Mr. Varghese Mathew" data-src="images\varghese.jpg" class="lazy">
                </div>
                <div class="leader-info">
                    <h3>Mr. Varghese Mathew</h3>
                    <h4>Director</h4>
                </div>
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
                <p>Mallappally<br>Pathanamthitta<br>Kozhencherry</p>
            </div>
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Phone</h3>
                <p>Main: +91 8111954300</p>
            </div>
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email</h3>
                <p>tasetacademy@gmail.com</p>
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
    grid-template-columns: 1fr;
    gap: 1.5rem;
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