<?php
/**
 * Enquirer Portal Filter API Handler
 * Handles filtering requests for enquirer portal data
 */

require_once '../../../includes/auth_check_enquirer.php';
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
    
    // Validate table access for enquirer (only their assigned data)
    $allowedTables = ['enquiries', 'call_logs'];
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Access denied to table: ' . $table);
    }
    
    // Get enquirer's user ID from session
    $enquirerUserId = $_SESSION['user_id'] ?? null;
    if (!$enquirerUserId) {
        throw new Exception('Enquirer user ID not found in session');
    }
    
    switch ($action) {
        case 'filter':
            handleFilterRequest($db, $table, $input, $enquirerUserId);
            break;
            
        case 'suggestions':
            handleSuggestionsRequest($db, $table, $input, $enquirerUserId);
            break;
            
        case 'export':
            handleExportRequest($db, $table, $input, $enquirerUserId);
            break;
            
        case 'quick_filters':
            handleQuickFiltersRequest($table, $enquirerUserId);
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

function handleFilterRequest($db, $table, $input, $userId) {
    // Force user-specific filter for enquirer users
    if (!isset($input['filters'])) {
        $input['filters'] = [];
    }
    
    if ($table === 'enquiries') {
        $input['filters']['assigned_to_user_id'] = $userId;
    } elseif ($table === 'call_logs') {
        $input['filters']['caller_user_id'] = $userId;
    }
    
    // Create filter manager with appropriate configuration
    $manager = FilterConfigurations::createFilterManager($db, $table);
    
    // Add custom query modifications for enquirer context
    if ($table === 'enquiries') {
        $manager = createEnquirerEnquiriesFilterManager($db, $userId);
    } elseif ($table === 'call_logs') {
        $manager = createEnquirerCallLogsFilterManager($db, $userId);
    }
    
    $result = $manager->processRequest($input);
    echo json_encode($result);
}

function handleSuggestionsRequest($db, $table, $input, $userId) {
    $manager = FilterConfigurations::createFilterManager($db, $table);
    $term = $input['term'] ?? '';
    
    // Add user filter to suggestions query
    if ($table === 'enquiries') {
        $input['filters']['assigned_to_user_id'] = $userId;
    } elseif ($table === 'call_logs') {
        $input['filters']['caller_user_id'] = $userId;
    }
    
    $result = $manager->getSearchSuggestions($term);
    echo json_encode($result);
}

function handleExportRequest($db, $table, $input, $userId) {
    // Force user-specific filter for exports
    if (!isset($input['filters'])) {
        $input['filters'] = [];
    }
    
    if ($table === 'enquiries') {
        $input['filters']['assigned_to_user_id'] = $userId;
    } elseif ($table === 'call_logs') {
        $input['filters']['caller_user_id'] = $userId;
    }
    
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

function handleQuickFiltersRequest($table, $userId) {
    $context = 'enquirer_' . $table;
    $filters = FilterConfigurations::getQuickFilters($context, 'enquirer');
    
    echo json_encode([
        'success' => true,
        'data' => $filters
    ]);
}

// Custom filter managers for enquirer portal (user-specific)

function createEnquirerEnquiriesFilterManager($db, $userId) {
    $manager = new DataFilterManager($db, 'enquiries', 'id');
    
    $config = FilterConfigurations::getEnquiriesConfig();
    
    // Add user restriction to all queries
    $config['allowed_filters']['assigned_to_user_id'] = [
        'type' => 'int',
        'operator' => '='
    ];
    
    // Add priority and follow-up fields for enquirer workflow
    $config['allowed_filters']['priority'] = [
        'type' => 'enum',
        'values' => ['low', 'medium', 'high'],
        'operator' => '='
    ];
    
    $config['allowed_filters']['follow_up_required'] = [
        'type' => 'bool',
        'operator' => '='
    ];
    
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts($config['allowed_sorts'])
            ->setAllowedSearchFields($config['allowed_search_fields']);
    
    return $manager;
}

function createEnquirerCallLogsFilterManager($db, $userId) {
    // Custom query to join call logs with enquiries for better context
    $manager = new DataFilterManager($db, 'call_logs_with_enquiries', 'id');
    
    $config = FilterConfigurations::getCallLogsConfig();
    
    // Add user restriction
    $config['allowed_filters']['caller_user_id'] = [
        'type' => 'int',
        'operator' => '='
    ];
    
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts(array_merge($config['allowed_sorts'], ['enquiry_name']))
            ->setAllowedSearchFields(array_merge($config['allowed_search_fields'], ['enquiry_name']));
    
    return $manager;
}
?>