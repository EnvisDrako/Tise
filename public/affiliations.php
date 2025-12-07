<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Affiliations & Accreditations";
$page_description = "Our official certifications, partnerships, and accreditations with leading industry bodies and educational institutions";
include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Affiliations & Accreditations</h1>
        <p>Recognized by leading industry bodies and educational institutions</p>
    </div>
</div>

<section class="accreditations section">
    <div class="container">
        <div class="section-title">
            <h2>Official Accreditations</h2>
            <p>Our programs are accredited by prestigious educational and industry organizations</p>
        </div>
        <div class="accreditation-grid">
            <div class="accreditation-card">
                <div class="accreditation-logo">
                    <img src="images\lsc.png" alt="Logistics Skill Council" data-src="images\lsc.png" class="lazy">
                </div>
                <div class="accreditation-content">
                    <h3>Logistics Skill Council</h3>
                </div>
            </div>

            <div class="accreditation-card">
                <div class="accreditation-logo">
                    <img src="images\src_image.png" alt="SRC Community College" data-src="images\src_image.png" class="lazy">
                </div>
                <div class="accreditation-content">
                    <h3>SRC Community College</h3>
                </div>
            </div>

            <div class="accreditation-card">
                <div class="accreditation-logo">
                    <img src="images\directorlogo.png" alt="Director of Technical Education" data-src="images\directorlogo.png" class="lazy">
                </div>
                <div class="accreditation-content">
                    <h3>Director of Technical Education</h3>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.accreditation-grid,
.partnership-grid,
.university-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.professional-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.accreditation-card,
.partnership-card,
.university-card,
.professional-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.accreditation-card:hover,
.partnership-card:hover,
.university-card:hover,
.professional-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.accreditation-logo,
.partnership-logo,
.university-logo,
.professional-logo {
    text-align: center;
    margin-bottom: 1.5rem;
}

.accreditation-logo img,
.partnership-logo img,
.university-logo img,
.professional-logo img {
    width: 120px;
    height: 80px;
    object-fit: contain;
    border-radius: 8px;
}

.accreditation-content h3,
.partnership-content h3,
.university-content h3,
.professional-content h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
}

.accreditation-grade,
.partnership-type,
.affiliation-type {
    background: #3498db;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 1rem;
}

.accreditation-content p,
.partnership-content p,
.university-content p,
.professional-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.accreditation-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #555;
    font-size: 0.9rem;
}

.detail-item i {
    color: #3498db;
    width: 16px;
}

.partnership-benefits,
.membership-benefits {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.benefit {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.programs-offered {
    margin-top: 1rem;
}

.programs-offered h4 {
    color: #2c3e50;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.programs-offered ul {
    list-style: none;
    padding-left: 0;
}

.programs-offered ul li {
    color: #666;
    margin-bottom: 0.5rem;
    padding-left: 1.5rem;
    position: relative;
}

.programs-offered ul li:before {
    content: "•";
    color: #3498db;
    position: absolute;
    left: 0;
}

.recognition {
    background: #f8f9fa;
}

.recognition-timeline {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}

.recognition-timeline:before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #3498db;
    transform: translateX(-50%);
}

.timeline-item {
    display: flex;
    align-items: center;
    margin-bottom: 3rem;
    position: relative;
}

.timeline-item:nth-child(odd) {
    flex-direction: row;
}

.timeline-item:nth-child(even) {
    flex-direction: row-reverse;
}

.timeline-year {
    background: #3498db;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1.1rem;
    min-width: 80px;
    text-align: center;
    position: relative;
    z-index: 2;
}

.timeline-content {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin: 0 2rem;
    flex: 1;
}

.timeline-content h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.timeline-content p {
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .accreditation-grid,
    .partnership-grid,
    .university-grid {
        grid-template-columns: 1fr;
    }
    
    .professional-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .recognition-timeline:before {
        left: 30px;
    }
    
    .timeline-item {
        flex-direction: row !important;
        padding-left: 60px;
    }
    
    .timeline-year {
        position: absolute;
        left: 0;
        min-width: 60px;
        padding: 0.75rem;
        font-size: 0.9rem;
    }
    
    .timeline-content {
        margin: 0;
    }
}
</style>

<?php include '../includes/public_footer.php'; ?>