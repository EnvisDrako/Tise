<?php
/**
 * Download Student CSV Template
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../includes/auth_check_admin.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="student_import_template.csv"');
header('Cache-Control: no-cache, must-revalidate');

$output = fopen('php://output', 'w');

// CSV headers
fputcsv($output, [
    'Student ID',
    'Full Name',
    'Email',
    'Phone',
    'Course',
    'Batch Year',
    'Institution ID',
    'Enrollment Date',
    'Status'
]);

// Sample data row
fputcsv($output, [
    'STU001',
    'John Doe',
    'john.doe@example.com',
    '+1234567890',
    'Computer Science',
    '2023-2024',
    '1',
    date('Y-m-d'),
    'active'
]);

// Another sample row
fputcsv($output, [
    'STU002',
    'Jane Smith',
    'jane.smith@example.com',
    '+0987654321',
    'Business Administration',
    '2023-2024',
    '1',
    date('Y-m-d'),
    'active'
]);

fclose($output);
exit;
