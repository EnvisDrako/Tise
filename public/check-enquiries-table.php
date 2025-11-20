<?php
// Check and create enquiries table if needed
define('SYSTEM_ACCESS', true);

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Enquiries Table Check</h1>";

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'enquiries'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Enquiries table exists</p>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE enquiries");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM enquiries");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>Total enquiries: <strong>{$count}</strong></p>";
        
    } else {
        echo "<p style='color: orange;'>⚠ Enquiries table does not exist</p>";
        echo "<p>Creating table...</p>";
        
        // Create the table
        $sql = "CREATE TABLE IF NOT EXISTS `enquiries` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `full_name` varchar(100) NOT NULL,
            `email` varchar(100) DEFAULT NULL,
            `phone` varchar(20) NOT NULL,
            `course_interest` varchar(100) NOT NULL,
            `message` text DEFAULT NULL,
            `education_level` varchar(50) DEFAULT NULL,
            `experience_level` varchar(50) DEFAULT NULL,
            `preferred_schedule` varchar(50) DEFAULT NULL,
            `documents_path` varchar(255) DEFAULT NULL,
            `status` enum('new','contacted','converted','closed') DEFAULT 'new',
            `assigned_to` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `status` (`status`),
            KEY `assigned_to` (`assigned_to`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Enquiries table created successfully!</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='enquiry.php'>Go to Enquiry Form</a></p>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
}
?>
