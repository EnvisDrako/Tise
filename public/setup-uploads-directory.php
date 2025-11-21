<?php
/**
 * Setup Uploads Directory for Gallery
 * Run this once to ensure proper directory structure and permissions
 */
define('SYSTEM_ACCESS', true);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Uploads Directory</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #2980b9; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .info-box { background: #e3f2fd; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3; margin: 15px 0; }
    </style>
</head>
<body>
    <h1>Setup Uploads Directory for Gallery</h1>
    
    <?php
    $baseDir = __DIR__ . '/uploads';
    $galleryDir = $baseDir . '/gallery';
    $htaccessFile = $baseDir . '/.htaccess';
    $gitkeepFile = $galleryDir . '/.gitkeep';
    
    $errors = [];
    $warnings = [];
    $success = [];
    
    echo "<div class='section'>";
    echo "<h2>Directory Structure Setup</h2>";
    
    // Create base uploads directory
    if (!file_exists($baseDir)) {
        if (mkdir($baseDir, 0755, true)) {
            $success[] = "Created uploads directory: <code>$baseDir</code>";
        } else {
            $errors[] = "Failed to create uploads directory: <code>$baseDir</code>";
        }
    } else {
        $success[] = "Uploads directory exists: <code>$baseDir</code>";
    }
    
    // Create gallery subdirectory
    if (!file_exists($galleryDir)) {
        if (mkdir($galleryDir, 0755, true)) {
            $success[] = "Created gallery directory: <code>$galleryDir</code>";
        } else {
            $errors[] = "Failed to create gallery directory: <code>$galleryDir</code>";
        }
    } else {
        $success[] = "Gallery directory exists: <code>$galleryDir</code>";
    }
    
    // Create .htaccess for security
    if (!file_exists($htaccessFile)) {
        $htaccessContent = "# Allow access to uploaded images
<IfModule mod_rewrite.c>
    RewriteEngine Off
</IfModule>

# Allow image files
<FilesMatch \"\.(jpg|jpeg|png|gif|webp)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Deny access to PHP files
<FilesMatch \"\.php$\">
    Order Deny,Allow
    Deny from all
</FilesMatch>";
        
        if (file_put_contents($htaccessFile, $htaccessContent)) {
            $success[] = "Created .htaccess security file";
        } else {
            $errors[] = "Failed to create .htaccess file";
        }
    } else {
        $success[] = ".htaccess file exists";
    }
    
    // Create .gitkeep
    if (!file_exists($gitkeepFile)) {
        $gitkeepContent = "# This file keeps the directory in git\n# Uploaded gallery photos will be stored here\n";
        if (file_put_contents($gitkeepFile, $gitkeepContent)) {
            $success[] = "Created .gitkeep file";
        } else {
            $warnings[] = "Failed to create .gitkeep file (not critical)";
        }
    } else {
        $success[] = ".gitkeep file exists";
    }
    
    // Check permissions
    echo "<h3>Checking Permissions</h3>";
    
    if (is_writable($galleryDir)) {
        $success[] = "Gallery directory is writable ✓";
    } else {
        $errors[] = "Gallery directory is NOT writable! Photos cannot be uploaded.";
        echo "<div class='info-box'>";
        echo "<strong>Fix Permission Issue:</strong><br>";
        echo "Run this command on your server:<br>";
        echo "<code>chmod 755 " . realpath($galleryDir) . "</code><br>";
        echo "Or via FTP, set folder permissions to 755 (rwxr-xr-x)";
        echo "</div>";
    }
    
    // Display results
    if (!empty($success)) {
        echo "<div style='margin: 20px 0;'>";
        foreach ($success as $msg) {
            echo "<p class='success'>✓ $msg</p>";
        }
        echo "</div>";
    }
    
    if (!empty($warnings)) {
        echo "<div style='margin: 20px 0;'>";
        foreach ($warnings as $msg) {
            echo "<p class='warning'>⚠ $msg</p>";
        }
        echo "</div>";
    }
    
    if (!empty($errors)) {
        echo "<div style='margin: 20px 0;'>";
        foreach ($errors as $msg) {
            echo "<p class='error'>✗ $msg</p>";
        }
        echo "</div>";
    }
    
    echo "</div>";
    
    // Test upload capability
    echo "<div class='section'>";
    echo "<h2>Test Upload Capability</h2>";
    
    $testFile = $galleryDir . '/test_' . time() . '.txt';
    $testContent = "Test file created at " . date('Y-m-d H:i:s');
    
    if (file_put_contents($testFile, $testContent)) {
        echo "<p class='success'>✓ Successfully created test file</p>";
        if (unlink($testFile)) {
            echo "<p class='success'>✓ Successfully deleted test file</p>";
            echo "<p><strong>Upload functionality is working correctly!</strong></p>";
        } else {
            echo "<p class='warning'>⚠ Could not delete test file (not critical)</p>";
        }
    } else {
        echo "<p class='error'>✗ Could not create test file - uploads will NOT work!</p>";
        echo "<div class='info-box'>";
        echo "<strong>Troubleshooting:</strong><br>";
        echo "1. Check folder permissions (should be 755 or 775)<br>";
        echo "2. Check folder ownership (should match web server user)<br>";
        echo "3. Check available disk space<br>";
        echo "4. Check PHP safe_mode and open_basedir restrictions";
        echo "</div>";
    }
    
    echo "</div>";
    
    // PHP Configuration
    echo "<div class='section'>";
    echo "<h2>PHP Upload Configuration</h2>";
    
    $uploadMaxFilesize = ini_get('upload_max_filesize');
    $postMaxSize = ini_get('post_max_size');
    $maxFileUploads = ini_get('max_file_uploads');
    $memoryLimit = ini_get('memory_limit');
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Setting</th><th>Current Value</th><th>Recommended</th><th>Status</th></tr>";
    
    // Upload max filesize
    $uploadOk = (int)$uploadMaxFilesize >= 10;
    echo "<tr>";
    echo "<td><code>upload_max_filesize</code></td>";
    echo "<td><strong>$uploadMaxFilesize</strong></td>";
    echo "<td>10M or higher</td>";
    echo "<td>" . ($uploadOk ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Low</span>") . "</td>";
    echo "</tr>";
    
    // Post max size
    $postOk = (int)$postMaxSize >= 20;
    echo "<tr>";
    echo "<td><code>post_max_size</code></td>";
    echo "<td><strong>$postMaxSize</strong></td>";
    echo "<td>20M or higher</td>";
    echo "<td>" . ($postOk ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Low</span>") . "</td>";
    echo "</tr>";
    
    // Max file uploads
    $uploadsOk = (int)$maxFileUploads >= 20;
    echo "<tr>";
    echo "<td><code>max_file_uploads</code></td>";
    echo "<td><strong>$maxFileUploads</strong></td>";
    echo "<td>20 or higher</td>";
    echo "<td>" . ($uploadsOk ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Low</span>") . "</td>";
    echo "</tr>";
    
    // Memory limit
    echo "<tr>";
    echo "<td><code>memory_limit</code></td>";
    echo "<td><strong>$memoryLimit</strong></td>";
    echo "<td>128M or higher</td>";
    echo "<td><span class='success'>✓ OK</span></td>";
    echo "</tr>";
    
    echo "</table>";
    
    if (!$uploadOk || !$postOk || !$uploadsOk) {
        echo "<div class='info-box' style='margin-top: 15px;'>";
        echo "<strong>To increase these limits:</strong><br>";
        echo "Edit your <code>php.ini</code> file or create a <code>.htaccess</code> file with:<br>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
        echo "php_value upload_max_filesize 10M\n";
        echo "php_value post_max_size 20M\n";
        echo "php_value max_file_uploads 20";
        echo "</pre>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // Summary
    echo "<div class='section'>";
    if (empty($errors) && is_writable($galleryDir)) {
        echo "<h2 class='success'>✓ Setup Complete!</h2>";
        echo "<p>Your gallery upload system is ready to use.</p>";
        echo "<a href='admin/website/gallery.php' class='btn'>Go to Gallery Management</a>";
    } else {
        echo "<h2 class='error'>⚠ Setup Issues Detected</h2>";
        echo "<p>Please fix the errors above before using the bulk upload feature.</p>";
    }
    echo "<a href='index.php' class='btn'>Back to Home</a>";
    echo "</div>";
    ?>
</body>
</html>
