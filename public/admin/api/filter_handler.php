<?php
/**
 * Admin Portal Filter API Handler
 * Handles filtering requests for admin portal data
 */

require_once '../../../includes/auth_check_admin.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/DataFilterManager.php';
require_once '../../../includes/FilterConfigurations.php';

header('Content-Type: application/json');

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? $input['action'] ?? '';
    $table = $_GET['table'] ?? $input['table'] ?? '';
    
    if (empty($action) || empty($table)) {
        throw new Exception('Missing required parameters: action and table');
    }
    
    // Validate table access for admin
    $allowedTables = ['users', 'enquiries', 'students', 'institutions', 'certificate_requests'];
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Access denied to table: ' . $table);
    }
    
    switch ($action) {
        case 'filter':
            handleFilterRequest($db, $table, $input);
            break;
            
        case 'suggestions':
            handleSuggestionsRequest($db, $table, $input);
            break;
            
        case 'export':
            handleExportRequest($db, $table, $input);
            break;
            
        case 'quick_filters':
            handleQuickFiltersRequest($table);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleFilterRequest($db, $table, $input) {
    // Create filter manager with appropriate configuration
    $manager = FilterConfigurations::createFilterManager($db, $table);
    
    // Add custom query modifications for admin context
    if ($table === 'users') {
        // Join with institutions for user management
        $manager = createUsersFilterManager($db);
    } elseif ($table === 'enquiries') {
        // Join with assigned users for enquiry management
        $manager = createEnquiriesFilterManager($db);
    } elseif ($table === 'students') {
        // Join with institutions for student management
        $manager = createStudentsFilterManager($db);
    } elseif ($table === 'certificate_requests') {
        // Join with students and institutions
        $manager = createCertificatesFilterManager($db);
    }
    
    $result = $manager->processRequest($input);
    echo json_encode($result);
}

function handleSuggestionsRequest($db, $table, $input) {
    $manager = FilterConfigurations::createFilterManager($db, $table);
    $term = $input['term'] ?? '';
    
    $result = $manager->getSearchSuggestions($term);
    echo json_encode($result);
}

function handleExportRequest($db, $table, $input) {
    $manager = FilterConfigurations::createFilterManager($db, $table);
    $format = $input['format'] ?? 'csv';
    
    $result = $manager->exportData($input, $format);
    
    if ($result['success']) {
        // Send file for download
        $filepath = $result['filepath'];
        $filename = $result['filename'];
        $mimetype = $result['mimetype'];
        
        header('Content-Type: ' . $mimetype);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        unlink($filepath); // Clean up temp file
        exit;
    } else {
        echo json_encode($result);
    }
}

function handleQuickFiltersRequest($table) {
    $context = 'admin_' . $table;
    $filters = FilterConfigurations::getQuickFilters($context);
    
    echo json_encode([
        'success' => true,
        'data' => $filters
    ]);
}

// Custom filter managers with joins for admin portal

function createUsersFilterManager($db) {
    $manager = new DataFilterManager($db, 'users_with_institutions', 'id');
    
    // Custom query with institution join
    $config = FilterConfigurations::getUsersConfig();
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts(array_merge($config['allowed_sorts'], ['institution_name']))
            ->setAllowedSearchFields(array_merge($config['allowed_search_fields'], ['institution_name']));
    
    return $manager;
}

function createEnquiriesFilterManager($db) {
    $manager = new DataFilterManager($db, 'enquiries_with_assignee', 'id');
    
    $config = FilterConfigurations::getEnquiriesConfig();
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts(array_merge($config['allowed_sorts'], ['assigned_to_name']))
            ->setAllowedSearchFields(array_merge($config['allowed_search_fields'], ['assigned_to_name']));
    
    return $manager;
}

function createStudentsFilterManager($db) {
    $manager = new DataFilterManager($db, 'students_with_institutions', 'id');
    
    $config = FilterConfigurations::getStudentsConfig();
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts(array_merge($config['allowed_sorts'], ['institution_name']))
            ->setAllowedSearchFields(array_merge($config['allowed_search_fields'], ['institution_name']));
    
    return $manager;
}

function createCertificatesFilterManager($db) {
    $manager = new DataFilterManager($db, 'certificate_requests_with_details', 'id');
    
    $config = FilterConfigurations::getCertificatesConfig();
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts($config['allowed_sorts'])
            ->setAllowedSearchFields($config['allowed_search_fields']);
    
    return $manager;
}
?>