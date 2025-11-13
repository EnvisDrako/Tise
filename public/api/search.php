<?php
/**
 * Global Search API
 * Provides full-text search across all data types
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/FullTextSearchManager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? $input['action'] ?? 'search';
    
    // Initialize search manager
    $searchManager = new FullTextSearchManager($db);
    
    switch ($action) {
        case 'search':
            handleSearchRequest($searchManager, $input);
            break;
            
        case 'suggestions':
            handleSuggestionsRequest($searchManager, $input);
            break;
            
        case 'stats':
            handleStatsRequest($searchManager);
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

function handleSearchRequest($searchManager, $input) {
    // Validate required parameters
    $query = $input['query'] ?? '';
    if (empty($query)) {
        throw new Exception('Search query is required');
    }
    
    // Get optional parameters
    $dataTypes = $input['data_types'] ?? [];
    $filters = $input['filters'] ?? [];
    $options = $input['options'] ?? [];
    
    // Perform search
    $result = $searchManager->search($query, $dataTypes, $filters, $options);
    
    echo json_encode($result);
}

function handleSuggestionsRequest($searchManager, $input) {
    $query = $input['query'] ?? '';
    $dataTypes = $input['data_types'] ?? [];
    $limit = intval($input['limit'] ?? 10);
    
    $result = $searchManager->getSuggestions($query, $dataTypes, $limit);
    
    echo json_encode($result);
}

function handleStatsRequest($searchManager) {
    $stats = $searchManager->getSearchStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}
?>