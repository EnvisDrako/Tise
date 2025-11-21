<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Contact Us";
$page_description = "Get in touch with us for admissions, course information, or any other queries";
include '../includes/public_header.php';
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
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Visit Us</h3>
                <div class="contact-details">
                    <p><strong>Main Campus</strong></p>
                    <p>123 Education Street<br>
                    Knowledge City, State 12345<br>
                    Country</p>
                </div>
                <a href="https://maps.google.com" target="_blank" class="btn btn-outline">
                    <i class="fas fa-directions"></i> Get Directions
                </a>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3>Call Us</h3>
                <div class="contact-details">
                    <p><strong>Main Office:</strong> <a href="tel:+12345678900">+1 234 567 8900</a></p>
                    <p><strong>Admissions:</strong> <a href="tel:+12345678901">+1 234 567 8901</a></p>
                    <p><strong>Placement Cell:</strong> <a href="tel:+12345678902">+1 234 567 8902</a></p>
                    <p><strong>Student Support:</strong> <a href="tel:+12345678903">+1 234 567 8903</a></p>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email Us</h3>
                <div class="contact-details">
                    <p><strong>General:</strong> <a href="mailto:info@eduportal.com">info@eduportal.com</a></p>
                    <p><strong>Admissions:</strong> <a href="mailto:admissions@eduportal.com">admissions@eduportal.com</a></p>
                    <p><strong>Placement:</strong> <a href="mailto:placement@eduportal.com">placement@eduportal.com</a></p>
                    <p><strong>Support:</strong> <a href="mailto:support@eduportal.com">support@eduportal.com</a></p>
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

<section class="departments section">
    <div class="container">
        <div class="section-title">
            <h2>Department Contacts</h2>
            <p>Connect directly with specific departments for specialized assistance</p>
        </div>
        <div class="departments-grid">
            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3>Admissions Office</h3>
                <p>Course information, enrollment process, eligibility requirements</p>
                <div class="department-contact">
                    <p><i class="fas fa-phone"></i> +1 234 567 8901</p>
                    <p><i class="fas fa-envelope"></i> admissions@eduportal.com</p>
                    <p><i class="fas fa-user"></i> Ms. Sarah Johnson, Admissions Director</p>
                </div>
                <div class="department-hours">
                    <strong>Available:</strong> Mon-Fri 9 AM - 5 PM
                </div>
            </div>

            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Placement Cell</h3>
                <p>Career guidance, job placement assistance, industry connections</p>
                <div class="department-contact">
                    <p><i class="fas fa-phone"></i> +1 234 567 8902</p>
                    <p><i class="fas fa-envelope"></i> placement@eduportal.com</p>
                    <p><i class="fas fa-user"></i> Mr. Michael Davis, Placement Head</p>
                </div>
                <div class="department-hours">
                    <strong>Available:</strong> Mon-Fri 10 AM - 4 PM
                </div>
            </div>

            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3>Academic Office</h3>
                <p>Course curriculum, academic support, faculty coordination</p>
                <div class="department-contact">
                    <p><i class="fas fa-phone"></i> +1 234 567 8904</p>
                    <p><i class="fas fa-envelope"></i> academic@eduportal.com</p>
                    <p><i class="fas fa-user"></i> Prof. Emily Wilson, Academic Head</p>
                </div>
                <div class="department-hours">
                    <strong>Available:</strong> Mon-Fri 9 AM - 5 PM
                </div>
            </div>

            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Student Support</h3>
                <p>Technical support, student services, general assistance</p>
                <div class="department-contact">
                    <p><i class="fas fa-phone"></i> +1 234 567 8903</p>
                    <p><i class="fas fa-envelope"></i> support@eduportal.com</p>
                    <p><i class="fas fa-user"></i> Ms. Lisa Anderson, Support Manager</p>
                </div>
                <div class="department-hours">
                    <strong>Available:</strong> Mon-Sat 8 AM - 6 PM
                </div>
            </div>

            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>Finance Office</h3>
                <p>Fee payment, financial aid, scholarship information</p>
                <div class="department-contact">
                    <p><i class="fas fa-phone"></i> +1 234 567 8905</p>
                    <p><i class="fas fa-envelope"></i> finance@eduportal.com</p>
                    <p><i class="fas fa-user"></i> Mr. Robert Taylor, Finance Manager</p>
                </div>
                <div class="department-hours">
                    <strong>Available:</strong> Mon-Fri 9 AM - 4 PM
                </div>
            </div>

            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Corporate Relations</h3>
                <p>Industry partnerships, corporate training, collaboration opportunities</p>
                <div class="department-contact">
                    <p><i class="fas fa-phone"></i> +1 234 567 8906</p>
                    <p><i class="fas fa-envelope"></i> corporate@eduportal.com</p>
                    <p><i class="fas fa-user"></i> Ms. Jennifer Lee, Corporate Relations</p>
                </div>
                <div class="department-hours">
                    <strong>Available:</strong> Mon-Fri 10 AM - 5 PM
                </div>
            </div>
        </div>
    </div>
</section>

<section class="map-section section">
    <div class="container">
        <div class="section-title">
            <h2>Find Us</h2>
            <p>Located in the heart of the education district with easy access to public transportation</p>
        </div>
        <div class="map-container">
            <div class="map-placeholder">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.123456789!2d-74.0059413!3d40.7127753!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzQ2LjAiTiA3NMKwMDAnMjEuNCJX!5e0!3m2!1sen!2sus!4v1234567890123"
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            <div class="map-info">
                <h3>Campus Location</h3>
                <div class="location-details">
                    <div class="location-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Address</strong>
                            <p>123 Education Street, Knowledge City, State 12345</p>
                        </div>
                    </div>
                    <div class="location-item">
                        <i class="fas fa-subway"></i>
                        <div>
                            <strong>Public Transport</strong>
                            <p>Metro Station: Education Hub (5 min walk)<br>
                            Bus Routes: 15, 23, 45, 67</p>
                        </div>
                    </div>
                    <div class="location-item">
                        <i class="fas fa-car"></i>
                        <div>
                            <strong>Parking</strong>
                            <p>Free parking available for students and visitors<br>
                            Covered parking: 200+ spaces</p>
                        </div>
                    </div>
                    <div class="location-item">
                        <i class="fas fa-landmark"></i>
                        <div>
                            <strong>Nearby Landmarks</strong>
                            <p>City Library (2 blocks), Central Park (1 km)<br>
                            Shopping Mall (500m), Food Court (200m)</p>
                        </div>
                    </div>
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
                            <option value="Web Development">Web Development</option>
                            <option value="Data Science">Data Science</option>
                            <option value="Digital Marketing">Digital Marketing</option>
                            <option value="Graphic Design">Graphic Design</option>
                            <option value="Business Analytics">Business Analytics</option>
                            <option value="English Communication">English Communication</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="address" placeholder="Your Address (Full Street/City)" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <textarea name="message" placeholder="Additional Message / Goals (Optional)" class="form-control" rows="3"></textarea>
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
                    <a href="tel:+12345678900" class="alternative-option">
                        <i class="fas fa-phone"></i>
                        <div>
                            <strong>Call Now</strong>
                            <span>Speak directly with our team</span>
                        </div>
                    </a>
                    <a href="mailto:info@eduportal.com" class="alternative-option">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email Us</strong>
                            <span>Send detailed queries</span>
                        </div>
                    </a>
                    <a href="#" class="alternative-option" onclick="openLiveChat()">
                        <i class="fas fa-comments"></i>
                        <div>
                            <strong>Live Chat</strong>
                            <span>Instant support available</span>
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

<section class="social-connect section">
    <div class="container">
        <div class="section-title">
            <h2>Connect With Us</h2>
            <p>Follow us on social media for updates, tips, and student success stories</p>
        </div>
        <div class="social-grid">
            <a href="#" class="social-card facebook">
                <i class="fab fa-facebook-f"></i>
                <div class="social-info">
                    <h3>Facebook</h3>
                    <p>Daily updates and community discussions</p>
                    <span class="followers">12.5K followers</span>
                </div>
            </a>
            <a href="#" class="social-card twitter">
                <i class="fab fa-twitter"></i>
                <div class="social-info">
                    <h3>Twitter</h3>
                    <p>Latest news and industry insights</p>
                    <span class="followers">8.2K followers</span>
                </div>
            </a>
            <a href="#" class="social-card linkedin">
                <i class="fab fa-linkedin-in"></i>
                <div class="social-info">
                    <h3>LinkedIn</h3>
                    <p>Professional networking and job updates</p>
                    <span class="followers">15.3K followers</span>
                </div>
            </a>
            <a href="#" class="social-card instagram">
                <i class="fab fa-instagram"></i>
                <div class="social-info">
                    <h3>Instagram</h3>
                    <p>Campus life and student achievements</p>
                    <span class="followers">9.7K followers</span>
                </div>
            </a>
            <a href="#" class="social-card youtube">
                <i class="fab fa-youtube"></i>
                <div class="social-info">
                    <h3>YouTube</h3>
                    <p>Course previews and educational content</p>
                    <span class="followers">25.1K subscribers</span>
                </div>
            </a>
            <a href="#" class="social-card whatsapp">
                <i class="fab fa-whatsapp"></i>
                <div class="social-info">
                    <h3>WhatsApp</h3>
                    <p>Quick support and instant updates</p>
                    <span class="followers">Join our group</span>
                </div>
            </a>
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

.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.department-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.department-card:hover {
    transform: translateY(-3px);
}

.department-icon {
    width: 60px;
    height: 60px;
    background: #ecf0f1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.department-icon i {
    font-size: 1.5rem;
    color: #3498db;
}

.department-card h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.department-card p {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.department-contact {
    margin-bottom: 1rem;
}

.department-contact p {
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.department-contact i {
    color: #3498db;
    width: 16px;
}

.department-hours {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #555;
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
    height: 400px;
}

.map-placeholder iframe {
    width: 100%;
    height: 100%;
}

.map-info {
    padding: 2rem;
}

.map-info h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
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

.social-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.social-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-decoration: none;
    color: #2c3e50;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.social-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.social-card i {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.social-card.facebook i { background: #1877f2; }
.social-card.twitter i { background: #1da1f2; }
.social-card.linkedin i { background: #0077b5; }
.social-card.instagram i { background: #e4405f; }
.social-card.youtube i { background: #ff0000; }
.social-card.whatsapp i { background: #25d366; }

.social-info h3 {
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
}

.social-info p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.followers {
    color: #3498db;
    font-size: 0.8rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .departments-grid {
        grid-template-columns: 1fr;
    }
    
    .map-container {
        grid-template-columns: 1fr;
    }
    
    .map-info {
        order: -1;
    }
    
    .quick-contact-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .social-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function openLiveChat() {
    // Placeholder for live chat integration
    alert('Live chat feature will be integrated with your preferred chat service (e.g., Intercom, Zendesk, etc.)');
}
</script>

<?php include '../includes/public_footer.php'; ?>