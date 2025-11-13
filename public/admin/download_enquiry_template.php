<?php
/**
 * Download Enquiry CSV Template
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="enquiry_import_template.csv"');
header('Cache-Control: no-cache, must-revalidate');

$output = fopen('php://output', 'w');

// CSV headers
fputcsv($output, [
    'Full Name',
    'Email',
    'Phone',
    'Course Interest',
    'Message',
    'Status',
    'Assigned To User ID'
]);

// Sample data row
fputcsv($output, [
    'John Doe',
    'john.doe@example.com',
    '+1234567890',
    'Computer Science',
    'Interested in learning more about the program',
    'new',
    ''
]);

// Another sample row
fputcsv($output, [
    'Jane Smith',
    'jane.smith@example.com',
    '+0987654321',
    'Business Administration',
    'Would like to schedule a campus visit',
    'new',
    ''
]);

fclose($output);
exit;
