<?php
/**
 * Setup Courses and Trainers Tables
 */
define('SYSTEM_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Courses & Trainers</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <h1>Setup Courses & Trainers Tables</h1>
    
    <?php
    try {
        $db = DatabaseConnection::getInstance();
        $pdo = $db->getConnection();
        
        echo "<div class='section'>";
        echo "<h2>Database Connection</h2>";
        echo "<p class='success'>✓ Connected successfully</p>";
        echo "</div>";
        
        // Create courses table
        echo "<div class='section'>";
        echo "<h2>Courses Table</h2>";
        
        $coursesSQL = "CREATE TABLE IF NOT EXISTS `courses` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(200) NOT NULL,
            `description` text NOT NULL,
            `duration` varchar(50) NOT NULL,
            `level` enum('Beginner','Intermediate','Advanced') NOT NULL,
            `fee` varchar(50) NOT NULL,
            `faculty` varchar(100) DEFAULT NULL,
            `features` text DEFAULT NULL,
            `image_url` varchar(255) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($coursesSQL);
        echo "<p class='success'>✓ Courses table created/verified</p>";
        echo "</div>";
        
        // Create trainers table
        echo "<div class='section'>";
        echo "<h2>Trainers Table</h2>";
        
        $trainersSQL = "CREATE TABLE IF NOT EXISTS `trainers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `designation` varchar(100) NOT NULL,
            `specialization` varchar(200) NOT NULL,
            `experience` varchar(50) NOT NULL,
            `qualifications` varchar(200) DEFAULT NULL,
            `bio` text DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `image_url` varchar(255) DEFAULT NULL,
            `linkedin` varchar(255) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($trainersSQL);
        echo "<p class='success'>✓ Trainers table created/verified</p>";
        echo "</div>";
        
        // Check if tables have data
        echo "<div class='section'>";
        echo "<h2>Current Data</h2>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
        $coursesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p><strong>Courses:</strong> {$coursesCount} records</p>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM trainers");
        $trainersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p><strong>Trainers:</strong> {$trainersCount} records</p>";
        echo "</div>";
        
        echo "<div class='section'>";
        echo "<h2 class='success'>✓ Setup Complete!</h2>";
        echo "<p>You can now manage courses and trainers from the admin panel under Website Management.</p>";
        echo "<a href='admin/website/courses.php' class='btn'>Manage Courses</a>";
        echo "<a href='admin/website/trainers.php' class='btn'>Manage Trainers</a>";
        echo "<a href='admin/index.php' class='btn'>Admin Dashboard</a>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div class='section'>";
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
</body>
</html>
