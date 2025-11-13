<?php
/**
 * Script to fix duplicate SYSTEM_ACCESS definitions in public PHP files
 */

$files = [
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

$fixed = 0;
$errors = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Pattern to find the duplicate SYSTEM_ACCESS at the end (before footer include)
    $pattern = '/\n<\?php\s*\n\/\/ Define system access for includes\s*\ndefine\(\'SYSTEM_ACCESS\',\s*true\);\s*\n\s*include/';
    
    // Replace with just the include
    $newContent = preg_replace($pattern, "\n<?php include", $content);
    
    if ($newContent !== $content) {
        if (file_put_contents($file, $newContent)) {
            echo "✅ Fixed: $file\n";
            $fixed++;
        } else {
            echo "❌ Error writing: $file\n";
            $errors++;
        }
    } else {
        echo "ℹ️  No duplicate found in: $file\n";
    }
}

echo "\n";
echo "===========================================\n";
echo "Summary:\n";
echo "Fixed: $fixed files\n";
echo "Errors: $errors files\n";
echo "===========================================\n";
?>
