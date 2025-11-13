    </main>
    
    <!-- Social Media Sharing Buttons (Hidden by default, shown via JS) -->
    <div id="social-share-modal" class="social-share-modal">
        <div class="share-modal-content">
            <div class="share-header">
                <h3><i class="fas fa-share-alt"></i> Share This Page</h3>
                <button class="close-share-modal" aria-label="Close sharing modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="share-buttons">
                <a href="#" class="share-btn facebook-share" target="_blank" rel="noopener">
                    <i class="fab fa-facebook-f"></i>
                    <span>Facebook</span>
                </a>
                <a href="#" class="share-btn twitter-share" target="_blank" rel="noopener">
                    <i class="fab fa-twitter"></i>
                    <span>Twitter</span>
                </a>
                <a href="#" class="share-btn linkedin-share" target="_blank" rel="noopener">
                    <i class="fab fa-linkedin-in"></i>
                    <span>LinkedIn</span>
                </a>
                <a href="#" class="share-btn whatsapp-share" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                <a href="#" class="share-btn email-share" target="_blank" rel="noopener">
                    <i class="fas fa-envelope"></i>
                    <span>Email</span>
                </a>
                <button class="share-btn copy-link" data-url="">
                    <i class="fas fa-link"></i>
                    <span>Copy Link</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Floating Social Share Button -->
    <button id="floating-share-btn" class="floating-share-btn" aria-label="Share this page" style="position: fixed; bottom: 20px; right: 20px; z-index: 999; background: #e74c3c; color: white; border: none; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; font-size: 20px;">
        <i class="fas fa-share-alt"></i>
    </button>
    

    <!-- Print-only header -->
    <div class="print-header print-only" style="display: none;">
        <h1>EduPortal - Educational Institution</h1>
        <p>Leading educational institution committed to excellence in education and training</p>
        <p>Website: https://eduportal.com | Email: info@eduportal.com | Phone: +1 234 567 8900</p>
    </div>
    
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-graduation-cap"></i> EduPortal</h3>
                <p>Leading educational institution committed to excellence in education and training.</p>
                <div class="social-links">
                    <a href="https://facebook.com/eduportal" target="_blank" rel="noopener" aria-label="Follow us on Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="https://twitter.com/eduportal" target="_blank" rel="noopener" aria-label="Follow us on Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://linkedin.com/company/eduportal" target="_blank" rel="noopener" aria-label="Connect with us on LinkedIn">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="https://instagram.com/eduportal" target="_blank" rel="noopener" aria-label="Follow us on Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://youtube.com/eduportal" target="_blank" rel="noopener" aria-label="Subscribe to our YouTube channel">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
                <div class="newsletter-signup">
                    <h4>Stay Updated</h4>
                    <form class="newsletter-form" action="<?php echo url('api/newsletter'); ?>" method="POST">
                        <input type="email" name="email" placeholder="Enter your email" required class="newsletter-input">
                        <button type="submit" class="newsletter-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo url('about'); ?>">About Us</a></li>
                    <li><a href="<?php echo url('courses'); ?>">Courses</a></li>
                    <li><a href="<?php echo url('trainers'); ?>">Trainers</a></li>
                    <li><a href="<?php echo url('placements'); ?>">Placements</a></li>
                    <li><a href="<?php echo url('testimonials'); ?>">Success Stories</a></li>
                    <li><a href="<?php echo url('gallery'); ?>">Gallery</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Resources</h4>
                <ul>
                    <li><a href="<?php echo url('downloads'); ?>">Downloads</a></li>
                    <li><a href="<?php echo url('notices'); ?>">Notices</a></li>
                    <li><a href="<?php echo url('blog'); ?>">Blog</a></li>
                    <li><a href="<?php echo url('affiliations'); ?>">Affiliations</a></li>
                    <li><a href="<?php echo url('sitemap.xml'); ?>">Sitemap</a></li>
                    <li><a href="<?php echo url('privacy-policy'); ?>">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact Info</h4>
                <div class="contact-info">
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope"></i> info@eduportal.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Education Street, Knowledge City</p>
                    <p><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM, Sat: 9AM-2PM</p>
                </div>
                <div class="footer-certifications">
                    <h4>Certifications</h4>
                    <div class="cert-logos">
                        <img src="<?php echo asset('images/cert-iso.png'); ?>" alt="ISO Certified" class="cert-logo">
                        <img src="<?php echo asset('images/cert-accredited.png'); ?>" alt="Accredited Institution" class="cert-logo">
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> EduPortal. All rights reserved.</p>
                <div class="footer-links">
                    <a href="<?php echo url('terms'); ?>">Terms of Service</a>
                    <a href="<?php echo url('privacy-policy'); ?>">Privacy Policy</a>
                    <a href="<?php echo url('accessibility'); ?>">Accessibility</a>
                    <button id="share-page-btn" class="share-page-btn">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Print-only footer -->
    <div class="print-footer print-only" style="display: none;">
        <p>Printed from: <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?> | 
           Date: <?php echo date('F j, Y'); ?> | 
           Page <?php echo '{page}'; ?> of <?php echo '{pages}'; ?></p>
    </div>
    
    <script src="<?php echo asset('js/public.js'); ?>"></script>
    <script src="<?php echo asset('js/seo-social.js'); ?>"></script>
</body>
</html>