<?php
/**
 * Test Gallery Images - Debug uploaded images
 */
define('SYSTEM_ACCESS', true);
require_once '../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Gallery Images</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #2c3e50; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
        .image-item { border: 2px solid #ddd; border-radius: 8px; padding: 10px; background: #f9f9f9; }
        .image-item img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
        .image-info { margin-top: 10px; font-size: 0.85rem; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .path { background: #f0f0f0; padding: 5px; border-radius: 3px; font-family: monospace; font-size: 0.8rem; word-break: break-all; }
    </style>
</head>
<body>
    <h1>Gallery Images Test</h1>
    
    <?php
    try {
        $db = DatabaseConnection::getInstance();
        $pdo = $db->getConnection();
        
        // Get all gallery photos
        $stmt = $pdo->query("SELECT gp.*, ge.title as event_title FROM gallery_photos gp LEFT JOIN gallery_events ge ON gp.event_id = ge.id ORDER BY gp.created_at DESC LIMIT 20");
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='test-section'>";
        echo "<h2>Database Photos (" . count($photos) . " found)</h2>";
        
        if (empty($photos)) {
            echo "<p class='error'>No photos found in database</p>";
        } else {
            echo "<div class='image-grid'>";
            foreach ($photos as $photo) {
                $imageUrl = $photo['image_url'];
                
                // Test different path formats
                $testPaths = [
                    'original' => $imageUrl,
                    'with_dot' => (strpos($imageUrl, '/uploads/') === 0 ? '.' . $imageUrl : $imageUrl),
                    'without_slash' => ltrim($imageUrl, '/'),
                ];
                
                echo "<div class='image-item'>";
                echo "<strong>Photo ID: {$photo['id']}</strong><br>";
                echo "<small>Event: " . htmlspecialchars($photo['event_title']) . "</small><br>";
                echo "<div class='path'>DB Path: {$imageUrl}</div>";
                
                // Try to display with different paths
                foreach ($testPaths as $label => $path) {
                    echo "<div style='margin-top: 10px;'>";
                    echo "<small><strong>{$label}:</strong></small><br>";
                    echo "<img src='{$path}' alt='Test' onerror=\"this.style.border='3px solid red'; this.alt='FAILED: {$path}';\" onload=\"this.style.border='3px solid green';\">";
                    echo "<div class='path'>{$path}</div>";
                    echo "</div>";
                }
                
                echo "</div>";
            }
            echo "</div>";
        }
        echo "</div>";
        
        // Check physical files
        echo "<div class='test-section'>";
        echo "<h2>Physical Files in uploads/gallery/</h2>";
        
        $uploadDir = __DIR__ . '/uploads/gallery/';
        if (is_dir($uploadDir)) {
            $files = array_diff(scandir($uploadDir), ['.', '..', '.gitkeep', '.htaccess']);
            
            if (empty($files)) {
                echo "<p class='error'>No files found in uploads/gallery/</p>";
            } else {
                echo "<p class='success'>Found " . count($files) . " files</p>";
                echo "<ul>";
                foreach (array_slice($files, 0, 10) as $file) {
                    $filePath = $uploadDir . $file;
                    $fileSize = filesize($filePath);
                    $readable = is_readable($filePath) ? '✓ Readable' : '✗ Not readable';
                    echo "<li><code>{$file}</code> - " . number_format($fileSize) . " bytes - {$readable}</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p class='error'>Directory uploads/gallery/ does not exist!</p>";
        }
        echo "</div>";
        
        // Check .htaccess
        echo "<div class='test-section'>";
        echo "<h2>.htaccess Check</h2>";
        $htaccessPath = __DIR__ . '/uploads/.htaccess';
        if (file_exists($htaccessPath)) {
            echo "<p class='success'>✓ .htaccess exists</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
            echo htmlspecialchars(file_get_contents($htaccessPath));
            echo "</pre>";
        } else {
            echo "<p class='error'>✗ .htaccess missing in uploads/</p>";
        }
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='test-section'>";
        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
    
    <div class="test-section">
        <h2>Quick Links</h2>
        <p>
            <a href="gallery.php">View Gallery</a> | 
            <a href="admin/website/gallery.php">Admin Gallery</a> | 
            <a href="index.php">Home</a>
        </p>
    </div>
</body>
</html>
