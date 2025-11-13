<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Accessibility Statement";
$page_description = "EduPortal is committed to ensuring digital accessibility for people with disabilities. Learn about our accessibility features and compliance efforts";
$page_keywords = "accessibility, WCAG compliance, disability support, screen reader, keyboard navigation";
$page_type = "website";
include '../includes/public_header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Accessibility Statement</h1>
        <p>Committed to digital inclusion for all</p>
    </div>
</div>

<section class="accessibility-content section">
    <div class="container">
        <div class="accessibility-document">
            <h2>Our Commitment</h2>
            <p>EduPortal is committed to ensuring that our website is accessible to people with disabilities. We strive to adhere to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA standards.</p>
            
            <h2>Accessibility Features</h2>
            <p>Our website includes the following accessibility features:</p>
            <ul>
                <li><strong>Keyboard Navigation:</strong> All interactive elements can be accessed using keyboard navigation</li>
                <li><strong>Screen Reader Support:</strong> Proper heading structure and ARIA labels for screen readers</li>
                <li><strong>High Contrast:</strong> Support for high contrast mode preferences</li>
                <li><strong>Reduced Motion:</strong> Respects user preferences for reduced motion</li>
                <li><strong>Focus Indicators:</strong> Clear visual focus indicators for keyboard users</li>
                <li><strong>Alt Text:</strong> Descriptive alternative text for images</li>
                <li><strong>Skip Links:</strong> Skip to main content functionality</li>
            </ul>
            
            <h2>Keyboard Shortcuts</h2>
            <ul>
                <li><strong>Tab:</strong> Navigate forward through interactive elements</li>
                <li><strong>Shift + Tab:</strong> Navigate backward through interactive elements</li>
                <li><strong>Enter/Space:</strong> Activate buttons and links</li>
                <li><strong>Escape:</strong> Close modals and dropdown menus</li>
            </ul>
            
            <h2>Browser and Assistive Technology Support</h2>
            <p>Our website is designed to work with:</p>
            <ul>
                <li>Modern web browsers (Chrome, Firefox, Safari, Edge)</li>
                <li>Screen readers (NVDA, JAWS, VoiceOver)</li>
                <li>Voice recognition software</li>
                <li>Keyboard-only navigation</li>
            </ul>
            
            <h2>Ongoing Efforts</h2>
            <p>We continuously work to improve the accessibility of our website by:</p>
            <ul>
                <li>Regular accessibility audits and testing</li>
                <li>User feedback incorporation</li>
                <li>Staff training on accessibility best practices</li>
                <li>Staying updated with accessibility standards</li>
            </ul>
            
            <h2>Feedback and Contact</h2>
            <p>We welcome your feedback on the accessibility of our website. If you encounter any accessibility barriers or have suggestions for improvement, please contact us:</p>
            
            <div class="contact-methods">
                <p><strong>Email:</strong> accessibility@eduportal.com</p>
                <p><strong>Phone:</strong> +1 234 567 8900</p>
                <p><strong>Address:</strong> 123 Education Street, Knowledge City, State 12345</p>
            </div>
            
            <p>We aim to respond to accessibility feedback within 2 business days.</p>
            
            <h2>Third-Party Content</h2>
            <p>Some content on our website may be provided by third parties. We work with our partners to ensure their content meets accessibility standards, but if you encounter issues with third-party content, please let us know.</p>
            
            <p><strong>Last updated:</strong> January 1, 2024</p>
        </div>
    </div>
</section>

<style>
.accessibility-document {
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.8;
}

.accessibility-document h2 {
    color: #2c3e50;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.accessibility-document ul {
    margin-left: 2rem;
    margin-bottom: 1.5rem;
}

.accessibility-document li {
    margin-bottom: 0.5rem;
}

.contact-methods {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.contact-methods p {
    margin-bottom: 0.5rem;
}
</style>

<?php include '../includes/public_footer.php'; ?>