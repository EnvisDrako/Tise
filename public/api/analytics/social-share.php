<?php
// Social share analytics tracking endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }
    
    $platform = $input['platform'] ?? 'unknown';
    $url = $input['url'] ?? '';
    $title = $input['title'] ?? '';
    $timestamp = $input['timestamp'] ?? date('c');
    
    // Include database connection
    require_once '../../../includes/db_connect.php';
    
    // Insert social share tracking
    $stmt = $pdo->prepare("
        INSERT INTO social_share_analytics 
        (platform, shared_url, page_title, shared_at, ip_address, user_agent, referrer) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    
    $stmt->execute([
        $platform,
        $url,
        $title,
        $timestamp,
        $ipAddress,
        $userAgent,
        $referrer
    ]);
    
    // Log the share event
    error_log("Social share tracked: $platform - $title - $url");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Share tracked successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Social share analytics database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>