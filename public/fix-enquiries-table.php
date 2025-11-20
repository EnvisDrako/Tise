<?php
// Fix enquiries table - add missing columns
define('SYSTEM_ACCESS', true);

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Fix Enquiries Table</h1>";

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Get current columns
    $stmt = $pdo->query("DESCRIBE enquiries");
    $existingColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
    }
    
    echo "<h3>Current Columns:</h3>";
    echo "<p>" . implode(', ', $existingColumns) . "</p>";
    
    // Define required columns
    $requiredColumns = [
        'education_level' => "ALTER TABLE enquiries ADD COLUMN education_level VARCHAR(50) DEFAULT NULL AFTER message",
        'experience_level' => "ALTER TABLE enquiries ADD COLUMN experience_level VARCHAR(50) DEFAULT NULL AFTER education_level",
        'preferred_schedule' => "ALTER TABLE enquiries ADD COLUMN preferred_schedule VARCHAR(50) DEFAULT NULL AFTER experience_level",
        'documents_path' => "ALTER TABLE enquiries ADD COLUMN documents_path VARCHAR(255) DEFAULT NULL AFTER preferred_schedule"
    ];
    
    echo "<h3>Adding Missing Columns:</h3>";
    
    $columnsAdded = 0;
    foreach ($requiredColumns as $column => $sql) {
        if (!in_array($column, $existingColumns)) {
            try {
                $pdo->exec($sql);
                echo "<p style='color: green;'>✓ Added column: <strong>{$column}</strong></p>";
                $columnsAdded++;
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Failed to add {$column}: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>• Column already exists: {$column}</p>";
        }
    }
    
    if ($columnsAdded > 0) {
        echo "<hr>";
        echo "<p style='color: green; font-weight: bold;'>✓ Successfully added {$columnsAdded} column(s)!</p>";
    } else {
        echo "<hr>";
        echo "<p style='color: blue;'>All required columns already exist.</p>";
    }
    
    // Show updated structure
    echo "<h3>Updated Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE enquiries");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<p><a href='enquiry.php' style='display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Test Enquiry Form</a></p>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 40px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #2c3e50;
        border-bottom: 3px solid #3498db;
        padding-bottom: 10px;
    }
    table {
        background: white;
        width: 100%;
        margin: 20px 0;
    }
    th {
        background: #3498db;
        color: white;
        padding: 10px;
        text-align: left;
    }
    td {
        padding: 8px;
    }
    tr:nth-child(even) {
        background: #f9f9f9;
    }
</style>
