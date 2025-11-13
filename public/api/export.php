<?php
/**
 * Global Export API
 * Handles data export in multiple formats
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/ExportManager.php';
require_once '../../includes/DataFilterManager.php';
require_once '../../includes/FilterConfigurations.php';

header('Content-Type: application/json');

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? $input['action'] ?? 'export';
    
    switch ($action) {
        case 'export':
            handleExportRequest($db, $input);
            break;
            
        case 'stats':
            handleStatsRequest();
            break;
            
        case 'cleanup':
            handleCleanupRequest();
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

function handleExportRequest($db, $input) {
    // Validate required parameters
    $table = $input['table'] ?? '';
    $format = $input['format'] ?? 'csv';
    
    if (empty($table)) {
        throw new Exception('Table parameter is required');
    }
    
    // Get data using filter manager
    $filterManager = FilterConfigurations::createFilterManager($db, $table);
    
    // Prepare filter request
    $filterRequest = [
        'filters' => $input['filters'] ?? [],
        'search' => $input['search'] ?? '',
        'sort' => $input['sort'] ?? [],
        'page' => 1,
        'limit' => 10000 // Large limit for export
    ];
    
    $filterResult = $filterManager->processRequest($filterRequest);
    
    if (!$filterResult['success']) {
        throw new Exception('Failed to retrieve data: ' . $filterResult['message']);
    }
    
    $data = $filterResult['data'];
    
    if (empty($data)) {
        throw new Exception('No data found to export');
    }
    
    // Initialize export manager
    $exportManager = new ExportManager($db);
    
    // Prepare export options
    $exportOptions = array_merge([
        'filename' => $table . '_export_' . date('Y-m-d_H-i-s'),
        'include_headers' => true,
        'include_metadata' => true,
        'compress' => $input['compress'] ?? false,
        'filters_applied' => $filterRequest['filters'],
        'total_records' => $filterResult['pagination']['total']
    ], $input['options'] ?? []);
    
    // Perform export
    $exportResult = $exportManager->exportData($data, $format, $exportOptions);
    
    if ($exportResult['success']) {
        // Send file for download
        $filepath = $exportResult['filepath'];
        $filename = $exportResult['filename'];
        $mimetype = $exportResult['mimetype'];
        
        // Set headers for file download
        header('Content-Type: ' . $mimetype);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        // Output file and clean up
        readfile($filepath);
        unlink($filepath);
        exit;
    } else {
        echo json_encode($exportResult);
    }
}

function handleStatsRequest() {
    $exportManager = new ExportManager(null);
    $stats = $exportManager->getExportStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function handleCleanupRequest() {
    $exportManager = new ExportManager(null);
    $cleaned = $exportManager->cleanupTempFiles();
    
    echo json_encode([
        'success' => true,
        'message' => "Cleaned up {$cleaned} temporary files"
    ]);
}
?>