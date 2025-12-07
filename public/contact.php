<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Contact Us";
$page_description = "Get in touch with us for admissions, course information, or any other queries";
include '../includes/public_header.php';

// Get active courses from database
require_once '../includes/db_connect.php';
$courses = [];
try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT title FROM courses WHERE status='active' ORDER BY title ASC");
    $courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Silently fail
    error_log("Error loading courses: " . $e->getMessage());
}
?>

<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We're here to help you with your educational journey</p>
    </div>
</div>

<section class="contact-info section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Call Us</h3>
                <div class="contact-details">
                    <p><strong>Main Office:</strong> <a href="tel:+918547521277">+91 8547521277</a></p>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email Us</h3>
                <div class="contact-details">
                    <p><strong>General:</strong> <a href="mailto:tasetacademy@gmail.com">tasetacademy@gmail.com</a></p>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Office Hours</h3>
                <div class="contact-details">
                    <p><strong>Monday - Friday:</strong><br>9:00 AM - 6:00 PM</p>
                    <p><strong>Saturday:</strong><br>9:00 AM - 2:00 PM</p>
                    <p><strong>Sunday:</strong><br>Closed</p>
                    <p><strong>Holidays:</strong><br>By appointment only</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="map-section section">
    <div class="container">
        <div class="section-title">
            <h2>Find Us</h2>
            <p>Visit any of our three convenient locations</p>
        </div>
        
        <!-- Location Tabs -->
        <div class="location-tabs">
            <button class="location-tab active" onclick="showLocation(1)">
                <i class="fas fa-map-marker-alt"></i> Mallappally
            </button>
            <button class="location-tab" onclick="showLocation(2)">
                <i class="fas fa-map-marker-alt"></i> Kozhencherry
            </button>
            <button class="location-tab" onclick="showLocation(3)">
                <i class="fas fa-map-marker-alt"></i> Pathanamthitta
            </button>
        </div>

        <!-- Location 1 - Mallappally -->
        <div class="location-content active" id="location-1">
            <div class="map-container">
                <div class="map-placeholder">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15733.123456789!2d76.7097484!3d9.3358974!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMjAnMDkuMiJOIDc2wrA0MiczNS4xIkU!5e0!3m2!1sen!2sin!4v1234567890123"
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="map-info">
                    <h3>Mallappally Campus</h3>
                    <div class="location-details">
                        <div class="location-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Address</strong>
                                <p>Mallappally, Pathanamthitta District, Kerala</p>
                            </div>
                        </div>
                        <div class="location-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Operating Hours</strong>
                                <p>Mon-Fri: 9:00 AM - 6:00 PM<br>
                                Saturday: 9:00 AM - 2:00 PM</p>
                            </div>
                        </div>
                        <div class="location-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Contact</strong>
                                <p><a href="tel:+918547521277">+91 8547521277</a></p>
                            </div>
                        </div>
                    </div>
                    <a href="https://maps.app.goo.gl/rv6repheWCb8W2og7" 
                       target="_blank" 
                       class="btn btn-primary btn-directions">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>

        <!-- Location 2 - Kozhencherry -->
        <div class="location-content" id="location-2">
            <div class="map-container">
                <div class="map-placeholder">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15733.123456789!2d76.7097484!3d9.3358974!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMjAnMDkuMiJOIDc2wrA0MiczNS4xIkU!5e0!3m2!1sen!2sin!4v1234567890123"
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="map-info">
                    <h3>Kozhencherry Campus</h3>
                    <div class="location-details">
                        <div class="location-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Address</strong>
                                <p>Kozhencherry, Pathanamthitta District, Kerala</p>
                            </div>
                        </div>
                        <div class="location-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Operating Hours</strong>
                                <p>Mon-Fri: 9:00 AM - 6:00 PM<br>
                                Saturday: 9:00 AM - 2:00 PM</p>
                            </div>
                        </div>
                        <div class="location-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Contact</strong>
                                <p><a href="tel:+918547521277">+91 8547521277</a></p>
                            </div>
                        </div>
                    </div>
                    <a href="https://maps.app.goo.gl/K81pGTXhiycee6wG9" 
                       target="_blank" 
                       class="btn btn-primary btn-directions">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>

        <!-- Location 3 - Pathanamthitta -->
        <div class="location-content" id="location-3">
            <div class="map-container">
                <div class="map-placeholder">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15733.123456789!2d76.7097484!3d9.3358974!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMjAnMDkuMiJOIDc2wrA0MiczNS4xIkU!5e0!3m2!1sen!2sin!4v1234567890123"
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="map-info">
                    <h3>Pathanamthitta Campus</h3>
                    <div class="location-details">
                        <div class="location-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Address</strong>
                                <p>Pathanamthitta, Kerala</p>
                            </div>
                        </div>
                        <div class="location-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Operating Hours</strong>
                                <p>Mon-Fri: 9:00 AM - 6:00 PM<br>
                                Saturday: 9:00 AM - 2:00 PM</p>
                            </div>
                        </div>
                        <div class="location-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Contact</strong>
                                <p><a href="tel:+918547521277">+91 8547521277</a></p>
                            </div>
                        </div>
                    </div>
                    <a href="https://maps.app.goo.gl/NF9uyTYd7Namun4F6" 
                       target="_blank" 
                       class="btn btn-primary btn-directions">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="quick-contact section">
    <div class="container">
        <div class="section-title">
            <h2>Quick Contact</h2>
            <p>Send us a quick message and we'll get back to you within 24 hours</p>
        </div>
        <div class="quick-contact-container">
            <form class="quick-contact-form" method="POST" action="<?php echo url('enquiry'); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="full_name" placeholder="Your Name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email (Optional)" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Your Phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <select name="course_interest" class="form-control" required>
                            <option value="">Select Course Interest</option>
                            <?php if (!empty($courses)): ?>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course); ?>">
                                        <?php echo htmlspecialchars($course); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="message" placeholder="Your Message (Optional)" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                    <p class="form-note">Or <a href="<?php echo url('enquiry'); ?>">fill out our detailed enquiry form</a></p>
                </div>
            </form>
            
            <div class="contact-alternatives">
                <h3>Prefer Other Ways to Connect?</h3>
                <div class="alternatives-grid">
                    <a href="tel:+918547521277" class="alternative-option">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Call Now</strong>
                            <span>Speak directly with our team</span>
                        </div>
                    </a>
                    <a href="mailto:tasetacademy.com" class="alternative-option">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email Us</strong>
                            <span>Send detailed queries</span>
                        </div>
                    </a>
                    <a href="<?php echo url('enquiry'); ?>" class="alternative-option">
                        <i class="fas fa-file-alt"></i>
                        <div>
                            <strong>Detailed Form</strong>
                            <span>Complete enquiry with documents</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.contact-card {
    background: white;
    padding: 2.5rem 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.contact-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.contact-icon i {
    font-size: 2rem;
    color: white;
}

.contact-card h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.contact-details {
    text-align: left;
    margin-bottom: 1.5rem;
}

.contact-details p {
    margin-bottom: 0.75rem;
    color: #555;
    line-height: 1.5;
}

.contact-details a {
    color: #3498db;
    text-decoration: none;
}

.contact-details a:hover {
    text-decoration: underline;
}

.btn-outline {
    background: transparent;
    border: 2px solid #3498db;
    color: #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.location-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.location-tab {
    padding: 1rem 2rem;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    color: #555;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.location-tab:hover {
    border-color: #3498db;
    color: #3498db;
    transform: translateY(-2px);
}

.location-tab.active {
    background: #3498db;
    border-color: #3498db;
    color: white;
}

.location-tab i {
    font-size: 1.1rem;
}

.location-content {
    display: none;
    animation: fadeIn 0.4s ease;
}

.location-content.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.map-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.map-placeholder {
    height: 450px;
}

.map-placeholder iframe {
    width: 100%;
    height: 100%;
}

.map-info {
    padding: 2rem;
    display: flex;
    flex-direction: column;
}

.map-info h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.btn-directions {
    margin-top: auto;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    text-decoration: none;
}

.location-details {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.location-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.location-item i {
    width: 40px;
    height: 40px;
    background: #ecf0f1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.location-item strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.location-item p {
    color: #666;
    line-height: 1.5;
    margin: 0;
}

.quick-contact-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    max-width: 1000px;
    margin: 0 auto;
}

.quick-contact-form {
    background: white;
    padding: 2.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.form-note {
    margin-top: 1rem;
    text-align: center;
    color: #666;
    font-size: 0.9rem;
}

.form-note a {
    color: #3498db;
    text-decoration: none;
}

.contact-alternatives {
    background: white;
    padding: 2.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.contact-alternatives h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.alternatives-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.alternative-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid #ecf0f1;
    border-radius: 10px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
}

.alternative-option:hover {
    border-color: #3498db;
    background: #f8f9fa;
}

.alternative-option i {
    width: 40px;
    height: 40px;
    background: #3498db;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.alternative-option strong {
    display: block;
    margin-bottom: 0.25rem;
}

.alternative-option span {
    color: #666;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .location-tabs {
        flex-direction: column;
    }
    
    .location-tab {
        width: 100%;
        justify-content: center;
    }
    
    .map-container {
        grid-template-columns: 1fr;
    }
    
    .map-placeholder {
        height: 300px;
    }
    
    .map-info {
        order: -1;
    }
    
    .quick-contact-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>

<script>
function showLocation(locationNumber) {
    // Hide all location contents
    const contents = document.querySelectorAll('.location-content');
    contents.forEach(content => content.classList.remove('active'));
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.location-tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected location
    document.getElementById('location-' + locationNumber).classList.add('active');
    
    // Activate selected tab
    tabs[locationNumber - 1].classList.add('active');
}

function openLiveChat() {
    // Placeholder for live chat integration
    alert('Live chat feature will be integrated with your preferred chat service (e.g., Intercom, Zendesk, etc.)');
}
</script>

<?php include '../includes/public_footer.php'; ?>