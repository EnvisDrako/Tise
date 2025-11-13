<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Privacy Policy";
$page_description = "Learn about how EduPortal collects, uses, and protects your personal information. Our commitment to privacy and data security";
$page_keywords = "privacy policy, data protection, personal information, cookies, GDPR compliance";
$page_type = "website";
include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Privacy Policy</h1>
        <p>Your privacy is important to us</p>
    </div>
</div>

<section class="privacy-content section">
    <div class="container">
        <div class="privacy-document">
            <p><strong>Last updated:</strong> January 1, 2024</p>
            
            <h2>1. Information We Collect</h2>
            <p>We collect information you provide directly to us, such as when you:</p>
            <ul>
                <li>Fill out enquiry forms</li>
                <li>Subscribe to our newsletter</li>
                <li>Contact us via email or phone</li>
                <li>Use our website and services</li>
            </ul>
            
            <h2>2. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Respond to your enquiries and provide customer service</li>
                <li>Send you newsletters and educational content</li>
                <li>Improve our website and services</li>
                <li>Comply with legal obligations</li>
            </ul>
            
            <h2>3. Information Sharing</h2>
            <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p>
            
            <h2>4. Data Security</h2>
            <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
            
            <h2>5. Cookies</h2>
            <p>Our website uses cookies to enhance your browsing experience. You can choose to disable cookies through your browser settings.</p>
            
            <h2>6. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Correct inaccurate information</li>
                <li>Request deletion of your information</li>
                <li>Unsubscribe from our communications</li>
            </ul>
            
            <h2>7. Contact Us</h2>
            <p>If you have any questions about this Privacy Policy, please contact us at:</p>
            <p>Email: privacy@eduportal.com<br>
            Phone: +1 234 567 8900</p>
        </div>
    </div>
</section>

<style>
.privacy-document {
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
}

.privacy-document h2 {
    color: #2c3e50;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.privacy-document ul {
    margin-left: 2rem;
    margin-bottom: 1.5rem;
}

.privacy-document li {
    margin-bottom: 0.5rem;
}
</style>

<?php include '../includes/public_footer.php'; ?>