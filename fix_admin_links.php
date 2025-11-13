<?php
/**
 * Fix Admin Portal Links
 * Updates all href and onclick links to use BASE_URL
 */

$adminFiles = [
    'public/admin/index.php',
    'public/admin/users.php',
    'public/admin/institutions.php',
    'public/admin/enquiries.php',
    'public/admin/students.php',
    'public/admin/certificates.php',
    'public/admin/data_management.php',
    'public/admin/bulk_operations.php',
    'public/admin/system_monitoring.php',
    'public/admin/security_settings.php',
    'public/admin/backup_management.php',
    'public/admin/downloads.php'
];

$fixed = 0;
$errors = 0;

foreach ($adminFiles as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix onclick window.location.href
    $content = preg_replace(
        "/onclick=\"window\.location\.href='([^']+)'/",
        "onclick=\"window.location.href='<?php echo url('admin/$1'); ?>'\"",
        $content
    );
    
    // Fix href links (but not external links or #)
    $content = preg_replace(
        "/href=\"(?!http|#|<\?php)([a-zA-Z0-9_\-\.]+\.php)/",
        "href=\"<?php echo url('admin/$1'); ?>",
        $content
    );
    
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ Fixed: $file\n";
            $fixed++;
        } else {
            echo "❌ Error writing: $file\n";
            $errors++;
        }
    } else {
        echo "ℹ️  No changes needed: $file\n";
    }
}

echo "\n";
echo "===========================================\n";
echo "Summary:\n";
echo "Fixed: $fixed files\n";
echo "Errors: $errors files\n";
echo "===========================================\n";
?>
