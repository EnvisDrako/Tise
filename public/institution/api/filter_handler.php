<?php
/**
 * Faculty Portal Filter API Handler
 * Handles filtering requests for faculty portal data
 */

require_once '../../../includes/auth_check_faculty.php';
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
    
    // Validate table access for faculty (only their institution's data)
    $allowedTables = ['students', 'marklists', 'certificate_requests'];
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Access denied to table: ' . $table);
    }
    
    // Get faculty's institution ID from session
    $facultyInstitutionId = $_SESSION['institution_id'] ?? null;
    if (!$facultyInstitutionId) {
        throw new Exception('Faculty institution not found in session');
    }
    
    switch ($action) {
        case 'filter':
            handleFilterRequest($db, $table, $input, $facultyInstitutionId);
            break;
            
        case 'suggestions':
            handleSuggestionsRequest($db, $table, $input, $facultyInstitutionId);
            break;
            
        case 'export':
            handleExportRequest($db, $table, $input, $facultyInstitutionId);
            break;
            
        case 'quick_filters':
            handleQuickFiltersRequest($table, $facultyInstitutionId);
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

function handleFilterRequest($db, $table, $input, $institutionId) {
    // Force institution filter for faculty users
    if (!isset($input['filters'])) {
        $input['filters'] = [];
    }
    $input['filters']['institution_id'] = $institutionId;
    
    // Create filter manager with appropriate configuration
    $manager = FilterConfigurations::createFilterManager($db, $table);
    
    // Add custom query modifications for faculty context
    if ($table === 'students') {
        $manager = createFacultyStudentsFilterManager($db, $institutionId);
    } elseif ($table === 'marklists') {
        $manager = createFacultyMarklistsFilterManager($db, $institutionId);
    } elseif ($table === 'certificate_requests') {
        $manager = createFacultyCertificatesFilterManager($db, $institutionId);
    }
    
    $result = $manager->processRequest($input);
    echo json_encode($result);
}

function handleSuggestionsRequest($db, $table, $input, $institutionId) {
    $manager = FilterConfigurations::createFilterManager($db, $table);
    $term = $input['term'] ?? '';
    
    // Add institution filter to suggestions query
    $input['filters']['institution_id'] = $institutionId;
    
    $result = $manager->getSearchSuggestions($term);
    echo json_encode($result);
}

function handleExportRequest($db, $table, $input, $institutionId) {
    // Force institution filter for exports
    if (!isset($input['filters'])) {
        $input['filters'] = [];
    }
    $input['filters']['institution_id'] = $institutionId;
    
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

function handleQuickFiltersRequest($table, $institutionId) {
    $context = 'faculty_' . $table;
    $filters = FilterConfigurations::getQuickFilters($context, 'faculty', $institutionId);
    
    echo json_encode([
        'success' => true,
        'data' => $filters
    ]);
}

// Custom filter managers for faculty portal (institution-specific)

function createFacultyStudentsFilterManager($db, $institutionId) {
    $manager = new DataFilterManager($db, 'students', 'id');
    
    $config = FilterConfigurations::getStudentsConfig();
    
    // Add institution restriction to all queries
    $config['allowed_filters']['institution_id'] = [
        'type' => 'int',
        'operator' => '='
    ];
    
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts($config['allowed_sorts'])
            ->setAllowedSearchFields($config['allowed_search_fields']);
    
    return $manager;
}

function createFacultyMarklistsFilterManager($db, $institutionId) {
    // Custom query to join marklists with students and filter by institution
    $manager = new DataFilterManager($db, 'marklists_with_students', 'id');
    
    $config = FilterConfigurations::getMarklistsConfig();
    
    // Add institution restriction through student join
    $config['allowed_filters']['institution_id'] = [
        'type' => 'int',
        'operator' => '='
    ];
    
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts(array_merge($config['allowed_sorts'], ['student_name']))
            ->setAllowedSearchFields(array_merge($config['allowed_search_fields'], ['student_name']));
    
    return $manager;
}

function createFacultyCertificatesFilterManager($db, $institutionId) {
    $manager = new DataFilterManager($db, 'certificate_requests', 'id');
    
    $config = FilterConfigurations::getCertificatesConfig();
    
    // Faculty can only see their institution's certificate requests
    $config['allowed_filters']['institution_id'] = [
        'type' => 'int',
        'operator' => '='
    ];
    
    $manager->setAllowedFilters($config['allowed_filters'])
            ->setAllowedSorts($config['allowed_sorts'])
            ->setAllowedSearchFields($config['allowed_search_fields']);
    
    return $manager;
}
?>