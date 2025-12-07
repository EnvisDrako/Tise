<?php
/**
 * Test Homepage Data - Debug notifications and courses
 */
define('SYSTEM_ACCESS', true);
require_once '../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Homepage Data</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #2c3e50; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Homepage Data Test</h1>
    
    <?php
    try {
        $db = DatabaseConnection::getInstance();
        $pdo = $db->getConnection();
        
        echo "<div class='section'>";
        echo "<h2>Database Connection</h2>";
        echo "<p class='success'>✓ Connected successfully</p>";
        echo "</div>";
        
        // Check notifications table
        echo "<div class='section'>";
        echo "<h2>Notifications Table</h2>";
        
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✓ Notifications table exists</p>";
            
            // Get all notifications
            $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
            $allNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Total notifications:</strong> " . count($allNotifications) . "</p>";
            
            // Get active notifications
            $stmt = $pdo->query("SELECT * FROM notifications WHERE status='active' ORDER BY created_at DESC LIMIT 3");
            $activeNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Active notifications (for homepage):</strong> " . count($activeNotifications) . "</p>";
            
            if (!empty($allNotifications)) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Category</th><th>Created</th></tr>";
                foreach ($allNotifications as $notif) {
                    echo "<tr>";
                    echo "<td>{$notif['id']}</td>";
                    echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
                    echo "<td>{$notif['status']}</td>";
                    echo "<td>" . htmlspecialchars($notif['category'] ?? 'N/A') . "</td>";
                    echo "<td>{$notif['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='error'>✗ No notifications found in database</p>";
                echo "<p>Go to <a href='admin/website/notifications.php'>Admin → Notifications</a> to add some.</p>";
            }
        } else {
            echo "<p class='error'>✗ Notifications table does not exist!</p>";
            echo "<p>Run the setup script to create the table.</p>";
        }
        echo "</div>";
        
        // Check courses table
        echo "<div class='section'>";
        echo "<h2>Courses Table</h2>";
        
        $stmt = $pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY created_at DESC LIMIT 3");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Active courses (for homepage):</strong> " . count($courses) . "</p>";
        
        if (!empty($courses)) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Title</th><th>Duration</th><th>Level</th><th>Status</th></tr>";
            foreach ($courses as $course) {
                echo "<tr>";
                echo "<td>{$course['id']}</td>";
                echo "<td>" . htmlspecialchars($course['title']) . "</td>";
                echo "<td>" . htmlspecialchars($course['duration'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($course['level'] ?? 'N/A') . "</td>";
                echo "<td>{$course['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>✗ No active courses found</p>";
        }
        echo "</div>";
        
        // Show the actual query being used
        echo "<div class='section'>";
        echo "<h2>Query Being Used on Homepage</h2>";
        echo "<div class='code'>";
        echo "SELECT * FROM notifications WHERE status='active' ORDER BY created_at DESC LIMIT 3";
        echo "</div>";
        echo "<p>This query returns: <strong>" . count($activeNotifications) . " notifications</strong></p>";
        
        if (count($activeNotifications) > 0) {
            echo "<p class='success'>✓ Notifications should appear on homepage</p>";
        } else {
            echo "<p class='error'>✗ No active notifications - section will be hidden</p>";
            echo "<p><strong>To fix:</strong> Add notifications in admin panel and make sure status is 'active'</p>";
        }
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div class='section'>";
        echo "<p class='error'>✗ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
    
    <div class="section">
        <h2>Quick Links</h2>
        <p>
            <a href="index.php">View Homepage</a> | 
            <a href="admin/website/notifications.php">Manage Notifications</a> | 
            <a href="admin/website/courses.php">Manage Courses</a>
        </p>
    </div>
</body>
</html>
