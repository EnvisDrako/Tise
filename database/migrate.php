<?php
/**
 * Database Migration Script
 * Run this script to set up the database schema
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include configuration and database connection
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

echo "Starting database migration...\n";

try {
    // Read the setup SQL file
    $sqlFile = __DIR__ . '/setup.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Setup SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception("Failed to read SQL file");
    }
    
    // Get database connection
    $db = DatabaseConnection::getInstance();
    $connection = $db->getConnection();
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    // Execute each statement
    $connection->beginTransaction();
    
    foreach ($statements as $statement) {
        if (trim($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $connection->exec($statement);
        }
    }
    
    $connection->commit();
    
    echo "Database migration completed successfully!\n";
    echo "Default admin user created:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Please change the default password after first login.\n";
    
} catch (Exception $e) {
    if (isset($connection)) {
        $connection->rollback();
    }
    
    echo "Migration failed: " . $e->getMessage() . "\n";
    
    if (DEBUG_MODE) {
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    exit(1);
}
?>