<?php
/**
 * Setup Gallery Tables
 */
define('SYSTEM_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Gallery System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <h1>Setup Gallery System</h1>
    
    <?php
    try {
        $db = DatabaseConnection::getInstance();
        $pdo = $db->getConnection();
        
        echo "<div class='section'>";
        echo "<h2>Database Connection</h2>";
        echo "<p class='success'>✓ Connected successfully</p>";
        echo "</div>";
        
        // Create gallery_events table (albums/folders)
        echo "<div class='section'>";
        echo "<h2>Gallery Events Table</h2>";
        
        $eventsSQL = "CREATE TABLE IF NOT EXISTS `gallery_events` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(200) NOT NULL,
            `description` text DEFAULT NULL,
            `event_date` date DEFAULT NULL,
            `thumbnail_url` varchar(255) DEFAULT NULL,
            `drive_folder_id` varchar(100) DEFAULT NULL,
            `category` varchar(50) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `photo_count` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `status` (`status`),
            KEY `category` (`category`),
            KEY `event_date` (`event_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($eventsSQL);
        echo "<p class='success'>✓ Gallery Events table created/verified</p>";
        echo "</div>";
        
        // Create gallery_photos table
        echo "<div class='section'>";
        echo "<h2>Gallery Photos Table</h2>";
        
        $photosSQL = "CREATE TABLE IF NOT EXISTS `gallery_photos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `event_id` int(11) NOT NULL,
            `title` varchar(200) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `image_url` varchar(255) NOT NULL,
            `drive_file_id` varchar(100) DEFAULT NULL,
            `is_thumbnail` tinyint(1) DEFAULT 0,
            `display_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `event_id` (`event_id`),
            KEY `is_thumbnail` (`is_thumbnail`),
            FOREIGN KEY (`event_id`) REFERENCES `gallery_events`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($photosSQL);
        echo "<p class='success'>✓ Gallery Photos table created/verified</p>";
        echo "</div>";
        
        // Check current data
        echo "<div class='section'>";
        echo "<h2>Current Data</h2>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_events");
        $eventsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p><strong>Events/Albums:</strong> {$eventsCount} records</p>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_photos");
        $photosCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p><strong>Photos:</strong> {$photosCount} records</p>";
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2 class='success'>✓ Setup Complete!</h2>";
        echo "<p>Gallery system is ready to use with:</p>";
        echo "<ul>";
        echo "<li>✅ Event/Album management</li>";
        echo "<li>✅ Photo uploads with thumbnails</li>";
        echo "<li>✅ Google Drive folder integration</li>";
        echo "<li>✅ Automatic thumbnail selection</li>";
        echo "</ul>";
        echo "<a href='admin/website/gallery.php' class='btn'>Manage Gallery</a>";
        echo "<a href='admin/website/' class='btn'>Website Dashboard</a>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div class='section'>";
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
</body>
</html>
