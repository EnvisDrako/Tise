<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Terms of Service";
$page_description = "Read our terms of service, user agreement, and policies for using our educational platform and services";
$page_keywords = "terms of service, user agreement, policies, terms and conditions, legal";
$page_image = "/images/terms-bg.jpg";
$page_type = "website";

include __DIR__ . '/../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-file-contract"></i> Terms of Service</h1>
        <p class="lead">Please read these terms carefully before using our services</p>
    </div>
</div>

<div class="container section">
    <div class="content-wrapper">
        <div class="terms-content">
            <p class="last-updated"><strong>Last Updated:</strong> <?php echo date('F j, Y'); ?></p>

            <section class="terms-section">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using this website and our educational services, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to these terms, please do not use our services.</p>
            </section>

            <section class="terms-section">
                <h2>2. Use of Services</h2>
                <h3>2.1 Eligibility</h3>
                <p>You must be at least 16 years old to use our services. By using our services, you represent and warrant that you meet this age requirement.</p>
                
                <h3>2.2 Account Registration</h3>
                <p>To access certain features, you may need to create an account. You agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain and update your information</li>
                    <li>Keep your password secure and confidential</li>
                    <li>Accept responsibility for all activities under your account</li>
                </ul>

                <h3>2.3 Prohibited Activities</h3>
                <p>You agree not to:</p>
                <ul>
                    <li>Use the service for any illegal purpose</li>
                    <li>Violate any laws in your jurisdiction</li>
                    <li>Infringe on intellectual property rights</li>
                    <li>Transmit viruses or malicious code</li>
                    <li>Attempt to gain unauthorized access</li>
                    <li>Interfere with the proper functioning of the service</li>
                </ul>
            </section>

            <section class="terms-section">
                <h2>3. Educational Services</h2>
                <h3>3.1 Course Enrollment</h3>
                <p>Enrollment in courses is subject to availability and acceptance. We reserve the right to refuse enrollment to any applicant.</p>
                
                <h3>3.2 Course Content</h3>
                <p>All course materials, including but not limited to lectures, videos, documents, and assignments, are the intellectual property of our institution and are provided for educational purposes only.</p>
                
                <h3>3.3 Certification</h3>
                <p>Certificates are awarded upon successful completion of course requirements. Certificate issuance is at our sole discretion and subject to verification of completion.</p>
            </section>

            <section class="terms-section">
                <h2>4. Payment Terms</h2>
                <h3>4.1 Fees</h3>
                <p>Course fees are as published on our website and are subject to change. All fees must be paid in full before course commencement unless otherwise agreed.</p>
                
                <h3>4.2 Refund Policy</h3>
                <p>Refunds are available within 7 days of course commencement, subject to our refund policy. After this period, fees are non-refundable.</p>
                
                <h3>4.3 Payment Methods</h3>
                <p>We accept various payment methods as indicated during the enrollment process. All payments are processed securely.</p>
            </section>

            <section class="terms-section">
                <h2>5. Intellectual Property</h2>
                <p>All content on this website, including text, graphics, logos, images, audio clips, video clips, and software, is the property of our institution or its content suppliers and is protected by copyright laws.</p>
                
                <p>You may not:</p>
                <ul>
                    <li>Reproduce, distribute, or display any content without permission</li>
                    <li>Modify or create derivative works</li>
                    <li>Use content for commercial purposes</li>
                    <li>Remove copyright or proprietary notices</li>
                </ul>
            </section>

            <section class="terms-section">
                <h2>6. Privacy and Data Protection</h2>
                <p>Your privacy is important to us. Please review our <a href="<?php echo url('privacy-policy'); ?>">Privacy Policy</a> to understand how we collect, use, and protect your personal information.</p>
            </section>

            <section class="terms-section">
                <h2>7. Disclaimers</h2>
                <h3>7.1 Service Availability</h3>
                <p>We strive to provide uninterrupted service but do not guarantee that our services will be available at all times. We may suspend or discontinue services for maintenance or other reasons.</p>
                
                <h3>7.2 Educational Outcomes</h3>
                <p>While we provide quality education, we do not guarantee specific outcomes, employment, or career advancement as a result of completing our courses.</p>
                
                <h3>7.3 Third-Party Links</h3>
                <p>Our website may contain links to third-party websites. We are not responsible for the content or practices of these external sites.</p>
            </section>

            <section class="terms-section">
                <h2>8. Limitation of Liability</h2>
                <p>To the fullest extent permitted by law, we shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of our services.</p>
            </section>

            <section class="terms-section">
                <h2>9. Indemnification</h2>
                <p>You agree to indemnify and hold harmless our institution, its officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses arising from your use of our services or violation of these terms.</p>
            </section>

            <section class="terms-section">
                <h2>10. Modifications to Terms</h2>
                <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of our services after changes constitutes acceptance of the modified terms.</p>
            </section>

            <section class="terms-section">
                <h2>11. Termination</h2>
                <p>We may terminate or suspend your access to our services immediately, without prior notice, for any reason, including breach of these terms.</p>
            </section>

            <section class="terms-section">
                <h2>12. Governing Law</h2>
                <p>These terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without regard to its conflict of law provisions.</p>
            </section>

            <section class="terms-section">
                <h2>13. Dispute Resolution</h2>
                <p>Any disputes arising from these terms or your use of our services shall be resolved through binding arbitration in accordance with the rules of [Arbitration Body].</p>
            </section>

            <section class="terms-section">
                <h2>14. Contact Information</h2>
                <p>If you have any questions about these Terms of Service, please contact us:</p>
                <div class="contact-info">
                    <p><i class="fas fa-envelope"></i> Email: legal@eduportal.com</p>
                    <p><i class="fas fa-phone"></i> Phone: +1 234 567 8900</p>
                    <p><i class="fas fa-map-marker-alt"></i> Address: 123 Education Street, Knowledge City</p>
                </div>
            </section>

            <div class="terms-acceptance">
                <p><strong>By using our services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</strong></p>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4rem 0 3rem;
    text-align: center;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.content-wrapper {
    max-width: 900px;
    margin: 0 auto;
}

.terms-content {
    background: white;
    padding: 3rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.last-updated {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e0e0e0;
}

.terms-section {
    margin-bottom: 3rem;
}

.terms-section h2 {
    color: #2c3e50;
    font-size: 1.8rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #3498db;
}

.terms-section h3 {
    color: #34495e;
    font-size: 1.3rem;
    margin-top: 1.5rem;
    margin-bottom: 0.8rem;
}

.terms-section p {
    line-height: 1.8;
    color: #555;
    margin-bottom: 1rem;
}

.terms-section ul {
    margin-left: 2rem;
    margin-bottom: 1rem;
}

.terms-section ul li {
    line-height: 1.8;
    color: #555;
    margin-bottom: 0.5rem;
}

.terms-section a {
    color: #3498db;
    text-decoration: none;
}

.terms-section a:hover {
    text-decoration: underline;
}

.contact-info {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.contact-info p {
    margin-bottom: 0.5rem;
}

.contact-info i {
    color: #3498db;
    margin-right: 0.5rem;
    width: 20px;
}

.terms-acceptance {
    background: #fff3cd;
    border: 2px solid #ffc107;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 2rem;
    text-align: center;
}

.terms-acceptance p {
    margin: 0;
    color: #856404;
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 2rem;
    }
    
    .terms-content {
        padding: 1.5rem;
    }
    
    .terms-section h2 {
        font-size: 1.5rem;
    }
}
</style>

<?php include __DIR__ . '/../includes/public_footer.php'; ?>
