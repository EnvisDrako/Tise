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
    
    <!-- Floating Scroll to Top Button -->
    <button id="scroll-to-top-btn" class="scroll-to-top-btn" aria-label="Scroll to top" style="position: fixed; bottom: 20px; right: 20px; z-index: 999; background: #3498db; color: white; border: none; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); display: none; align-items: center; justify-content: center; font-size: 20px; opacity: 0; transition: opacity 0.3s ease, transform 0.3s ease;">
        <i class="fas fa-arrow-up"></i>
    </button>
    

    <!-- Print-only header -->
    <div class="print-header print-only" style="display: none !important;">
        <h1>Taset Academy</h1>
        <p>Leading educational institution committed to excellence in education and training</p>
        <p>Website: <?php echo SITE_URL; ?> | Email: tasetacademy@gmail.com | Phone: +91 9447956412</p>
    </div>
    
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>
                    <img src="<?php echo asset('images/logos/logo-white.png'); ?>" alt="Taset Academy" class="footer-logo" style="height: 50px; width: auto; object-fit: contain; vertical-align: middle;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <span style="display:none;"><i class="fas fa-graduation-cap"></i> Taset Academy</span>
                </h3>
                <p>Leading educational institution committed to excellence in education and training.</p>
                <div class="social-links">
                    <a href="https://facebook.com/tasetacademy" target="_blank" rel="noopener" aria-label="Follow us on Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="https://twitter.com/tasetacademy" target="_blank" rel="noopener" aria-label="Follow us on Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://linkedin.com/company/tasetacademy" target="_blank" rel="noopener" aria-label="Connect with us on LinkedIn">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="https://instagram.com/tasetacademy" target="_blank" rel="noopener" aria-label="Follow us on Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://youtube.com/tasetacademy" target="_blank" rel="noopener" aria-label="Subscribe to our YouTube channel">
                        <i class="fab fa-youtube"></i>
                    </a>
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
                    <p><i class="fas fa-phone"></i> +91 9447956412</p>
                    <p><i class="fas fa-envelope"></i> tasetacademy@gmail.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Multiple Locations</p>
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
                <p>&copy; <?php echo date('Y'); ?> Taset Academy. All rights reserved.</p>
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
    <div class="print-footer print-only" style="display: none !important;">
        <p>Printed from: <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?> | 
           Date: <?php echo date('F j, Y'); ?> | 
           Page <?php echo '{page}'; ?> of <?php echo '{pages}'; ?></p>
    </div>
    
    <script src="<?php echo asset('js/public.js'); ?>"></script>
    <script src="<?php echo asset('js/seo-social.js'); ?>"></script>
</body>
</html>