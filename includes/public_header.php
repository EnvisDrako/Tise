<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";
require_once __DIR__ . "/functions.php";
// Public website header with SEO optimization
$site_name = "Taset Academy";
$site_url = SITE_URL;
$default_image = "/images/og-default.jpg";
$twitter_handle = "@tasetacademy";

// Set default values if not provided
$page_title = isset($page_title) ? $page_title : 'Home';
$page_description = isset($page_description) ? $page_description : 'Leading educational institution offering quality courses and training programs with expert faculty and modern facilities';
$page_keywords = isset($page_keywords) ? $page_keywords : 'education, courses, training, certification, career development, professional skills';
$page_image = isset($page_image) ? $page_image : $default_image;
$page_type = isset($page_type) ? $page_type : 'website';
$canonical_url = isset($canonical_url) ? $canonical_url : $site_url . $_SERVER['REQUEST_URI'];

// Generate structured data
$structured_data = [
    "@context" => "https://schema.org",
    "@type" => "EducationalOrganization",
    "name" => $site_name,
    "url" => $site_url,
    "logo" => $site_url . "/images/logos/logo.png",
    "description" => "Taset Academy - Leading educational institution committed to excellence in education and training",
    "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "123 Education Street",
        "addressLocality" => "Knowledge City",
        "addressRegion" => "State",
        "postalCode" => "12345",
        "addressCountry" => "Country"
    ],
    "contactPoint" => [
        "@type" => "ContactPoint",
        "telephone" => "+1-234-567-8900",
        "contactType" => "customer service",
        "email" => "info@eduportal.com"
    ],
    "sameAs" => [
        "https://facebook.com/eduportal",
        "https://twitter.com/eduportal",
        "https://linkedin.com/company/eduportal",
        "https://instagram.com/eduportal"
    ]
];

// Add course-specific structured data if on courses page
if (isset($course_data) && is_array($course_data)) {
    $structured_data["@type"] = "Course";
    $structured_data = array_merge($structured_data, $course_data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Basic SEO Meta Tags -->
    <title><?php echo htmlspecialchars($page_title . ' - ' . $site_name); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($site_name); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">
    
    <!-- Open Graph Meta Tags for Social Media -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title . ' - ' . $site_name); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($site_url . $page_image); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($page_type); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name); ?>">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?php echo htmlspecialchars($twitter_handle); ?>">
    <meta name="twitter:creator" content="<?php echo htmlspecialchars($twitter_handle); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title . ' - ' . $site_name); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($site_url . $page_image); ?>">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="theme-color" content="#3498db">
    <meta name="msapplication-TileColor" content="#3498db">
    <meta name="application-name" content="<?php echo htmlspecialchars($site_name); ?>">
    
    <!-- Favicon and Icons -->
    <link rel="icon" type="image/x-icon" href="<?php echo asset('favicon.ico'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo asset('images/favicon-32x32.png'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo asset('images/favicon-16x16.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo asset('images/apple-touch-icon.png'); ?>">
    
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    <?php echo json_encode($structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
    </script>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo asset('css/public.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/print.css'); ?>" media="print">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Preconnect to external domains for performance -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="<?php echo url('/'); ?>">
                        <img src="<?php echo asset('images/logos/logo.png'); ?>" alt="Taset Academy" class="logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                        <span style="display:none;"><i class="fas fa-graduation-cap"></i> Taset Academy</span>
                    </a>
                </div>
                <div class="nav-menu" id="nav-menu">
                    <a href="<?php echo url('/'); ?>" class="nav-link">Home</a>
                    
                    <!-- About Us Dropdown -->
                    <div class="nav-dropdown">
                        <a href="<?php echo url('about'); ?>" class="nav-link dropdown-toggle">
                            About Us <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="<?php echo url('about'); ?>" class="dropdown-item">About Us</a>
                            <a href="<?php echo url('trainers'); ?>" class="dropdown-item">Our Trainers</a>
                            <a href="<?php echo url('affiliations'); ?>" class="dropdown-item">Affiliations</a>
                        </div>
                    </div>
                    
                    <a href="<?php echo url('courses'); ?>" class="nav-link">Courses</a>
                    
                    <!-- Gallery Dropdown -->
                    <div class="nav-dropdown">
                        <a href="<?php echo url('gallery'); ?>" class="nav-link dropdown-toggle">
                            Gallery <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="<?php echo url('gallery'); ?>" class="dropdown-item">Photo Gallery</a>
                            <a href="<?php echo url('placements'); ?>" class="dropdown-item">Placements</a>
                        </div>
                    </div>
                    
                    <a href="<?php echo url('notices'); ?>" class="nav-link">Notices</a>
                    <a href="<?php echo url('downloads'); ?>" class="nav-link">Downloads</a>
                    <a href="<?php echo url('contact'); ?>" class="nav-link">Contact</a>
                    <a href="<?php echo url('enquiry'); ?>" class="nav-link enquiry-btn">Enquire Now</a>
                    <a href="<?php echo url('login'); ?>" class="nav-link login-btn"><i class="fas fa-sign-in-alt"></i> Staff Login</a>
                </div>
                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>
    <main class="main-content">