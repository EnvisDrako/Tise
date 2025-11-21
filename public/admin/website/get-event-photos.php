<?php
/**
 * Get Event Photos - AJAX endpoint
 */
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../../includes/db_connect.php';

header('Content-Type: application/json');

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Event ID required']);
    exit;
}

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM gallery_photos WHERE event_id=? ORDER BY is_thumbnail DESC, display_order ASC, created_at DESC");
    $stmt->execute([$event_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'photos' => $photos
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
