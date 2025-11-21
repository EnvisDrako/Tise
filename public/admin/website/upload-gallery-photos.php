<?php
/**
 * Bulk Photo Upload Handler for Gallery
 */
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'uploaded' => []];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

$event_id = $_POST['event_id'] ?? null;

if (!$event_id) {
    $response['message'] = 'Event ID is required';
    echo json_encode($response);
    exit;
}

// Check if files were uploaded
if (!isset($_FILES['photos']) || empty($_FILES['photos']['name'][0])) {
    $response['message'] = 'No files uploaded';
    echo json_encode($response);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = __DIR__ . '/../../uploads/gallery/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Allowed file types
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    $uploadedCount = 0;
    $errors = [];
    
    // Process each uploaded file
    $fileCount = count($_FILES['photos']['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $_FILES['photos']['name'][$i];
        $fileTmpName = $_FILES['photos']['tmp_name'][$i];
        $fileSize = $_FILES['photos']['size'][$i];
        $fileError = $_FILES['photos']['error'][$i];
        $fileType = $_FILES['photos']['type'][$i];
        
        // Skip if no file
        if ($fileError === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        
        // Check for upload errors
        if ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = "$fileName: Upload error (code $fileError)";
            continue;
        }
        
        // Validate file type
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "$fileName: Invalid file type. Only JPG, PNG, GIF, and WebP allowed.";
            continue;
        }
        
        // Validate file size
        if ($fileSize > $maxFileSize) {
            $errors[] = "$fileName: File too large. Maximum size is 5MB.";
            continue;
        }
        
        // Generate unique filename
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = 'gallery_' . $event_id . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $targetPath)) {
            // Create relative URL for database
            $imageUrl = '/uploads/gallery/' . $newFileName;
            
            // Get original filename without extension for title
            $title = pathinfo($fileName, PATHINFO_FILENAME);
            
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO gallery_photos (event_id, title, image_url, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$event_id, $title, $imageUrl]);
            
            $uploadedCount++;
            $response['uploaded'][] = [
                'filename' => $fileName,
                'url' => $imageUrl,
                'id' => $pdo->lastInsertId()
            ];
        } else {
            $errors[] = "$fileName: Failed to move uploaded file";
        }
    }
    
    // Update photo count for the event
    if ($uploadedCount > 0) {
        $stmt = $pdo->prepare("UPDATE gallery_events SET photo_count = (SELECT COUNT(*) FROM gallery_photos WHERE event_id=?) WHERE id=?");
        $stmt->execute([$event_id, $event_id]);
    }
    
    // Prepare response
    if ($uploadedCount > 0) {
        $response['success'] = true;
        $response['message'] = "$uploadedCount photo(s) uploaded successfully!";
        if (!empty($errors)) {
            $response['message'] .= " " . count($errors) . " file(s) had errors.";
            $response['errors'] = $errors;
        }
    } else {
        $response['message'] = 'No photos were uploaded. ' . implode(' ', $errors);
        $response['errors'] = $errors;
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
