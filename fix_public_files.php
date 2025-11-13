<?php
/**
 * Fix Public Files - Add SYSTEM_ACCESS constant
 * This script adds the SYSTEM_ACCESS constant to all public PHP files
 */

$publicFiles = [
    'public/about.php',
    'public/accessibility.php',
    'public/affiliations.php',
    'public/blog.php',
    'public/contact.php',
    'public/courses.php',
    'public/enquiry.php',
    'public/gallery.php',
    'public/privacy-policy.php',
    'public/testimonials.php',
    'public/trainers.php'
];

foreach ($publicFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check if SYSTEM_ACCESS is already defined
        if (strpos($content, "define('SYSTEM_ACCESS'") === false) {
            // Add SYSTEM_ACCESS constant after opening PHP tag
            $content = str_replace('<?php', "<?php\n// Define system access for includes\ndefine('SYSTEM_ACCESS', true);\n", $content);
            
            // Write back to file
            file_put_contents($file, $content);
            echo "Fixed: $file\n";
        } else {
            echo "Already fixed: $file\n";
        }
    } else {
        echo "File not found: $file\n";
    }
}

echo "All public files have been fixed!\n";
?>