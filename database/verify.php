<?php
/**
 * Database Verification Script
 * Checks if all tables and indexes are properly created
 */

// Define system access
define('SYSTEM_ACCESS', true);

// Include configuration and database connection
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

echo "Verifying database setup...\n\n";

try {
    $db = DatabaseConnection::getInstance();
    $connection = $db->getConnection();
    
    // List of expected tables
    $expectedTables = [
        'institutions',
        'users', 
        'students',
        'enquiries',
        'marklists',
        'certificate_requests',
        'call_logs'
    ];
    
    // Check if all tables exist
    echo "Checking tables:\n";
    foreach ($expectedTables as $table) {
        if ($db->tableExists($table)) {
            echo "✓ Table '{$table}' exists\n";
        } else {
            echo "✗ Table '{$table}' missing\n";
        }
    }
    
    // Check if views exist
    echo "\nChecking views:\n";
    $views = ['user_details', 'student_details'];
    foreach ($views as $view) {
        $result = $connection->query("SHOW TABLES LIKE '{$view}'")->fetch();
        if ($result) {
            echo "✓ View '{$view}' exists\n";
        } else {
            echo "✗ View '{$view}' missing\n";
        }
    }
    
    // Check if default admin user exists
    echo "\nChecking default data:\n";
    $adminUser = fetchOne("SELECT username, role FROM users WHERE username = 'admin'");
    if ($adminUser) {
        echo "✓ Default admin user exists (Role: {$adminUser['role']})\n";
    } else {
        echo "✗ Default admin user missing\n";
    }
    
    // Check indexes
    echo "\nChecking some key indexes:\n";
    $indexes = [
        'users' => ['idx_users_role', 'idx_users_institution'],
        'students' => ['idx_students_institution_course', 'idx_students_batch_eligible'],
        'enquiries' => ['idx_enquiries_status_assigned', 'idx_enquiries_created_status']
    ];
    
    foreach ($indexes as $table => $tableIndexes) {
        foreach ($tableIndexes as $index) {
            $result = $connection->query("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}'")->fetch();
            if ($result) {
                echo "✓ Index '{$index}' exists on table '{$table}'\n";
            } else {
                echo "✗ Index '{$index}' missing on table '{$table}'\n";
            }
        }
    }
    
    // Test basic operations
    echo "\nTesting basic operations:\n";
    
    // Test institution count
    $institutionCount = fetchOne("SELECT COUNT(*) as count FROM institutions")['count'];
    echo "✓ Institutions table accessible (Count: {$institutionCount})\n";
    
    // Test user count
    $userCount = fetchOne("SELECT COUNT(*) as count FROM users")['count'];
    echo "✓ Users table accessible (Count: {$userCount})\n";
    
    echo "\nDatabase verification completed!\n";
    
} catch (Exception $e) {
    echo "Verification failed: " . $e->getMessage() . "\n";
    
    if (DEBUG_MODE) {
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    exit(1);
}
?>