<?php
/**
 * Certificate Management Functions
 * Backend functions for certificate approval system
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

/**
 * Get filtered certificate requests with pagination and sorting
 */
function getFilteredCertificates($filters = []) {
    global $pdo;
    
    try {
        // Base query with joins
        $baseQuery = "
            SELECT 
                cr.id,
                cr.student_id,
                cr.institution_id,
                cr.request_type,
                cr.status,
                cr.priority,
                cr.requested_at,
                cr.processed_at,
                cr.approval_comments,
                cr.rejection_reason,
                cr.certificate_path,
                s.student_id as student_number,
                s.full_name as student_name,
                s.course,
                s.batch_year,
                i.name as institution_name,
                i.code as institution_code,
                u_req.full_name as requested_by_name,
                u_app.full_name as approved_by_name
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            JOIN users u_req ON cr.requested_by = u_req.id
            LEFT JOIN users u_app ON cr.approved_by = u_app.id
        ";
        
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['institution_id'])) {
            $whereConditions[] = "cr.institution_id = ?";
            $params[] = $filters['institution_id'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "cr.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['request_type'])) {
            $whereConditions[] = "cr.request_type = ?";
            $params[] = $filters['request_type'];
        }
        
        if (!empty($filters['priority'])) {
            $whereConditions[] = "cr.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(cr.requested_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(cr.requested_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(
                s.full_name LIKE ? OR 
                s.student_id LIKE ? OR 
                i.name LIKE ? OR 
                i.code LIKE ? OR
                cr.request_type LIKE ?
            )";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Build WHERE clause
        if (!empty($whereConditions)) {
            $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) FROM (" . $baseQuery . ") as filtered_results";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        // Add sorting
        $sortBy = $filters['sort_by'] ?? 'requested_at';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        
        // Validate sort parameters
        $allowedSortColumns = [
            'requested_at' => 'cr.requested_at',
            'student_name' => 's.full_name',
            'institution_name' => 'i.name',
            'priority' => 'cr.priority',
            'status' => 'cr.status',
            'request_type' => 'cr.request_type'
        ];
        
        if (isset($allowedSortColumns[$sortBy])) {
            $baseQuery .= " ORDER BY " . $allowedSortColumns[$sortBy] . " " . ($sortOrder === 'ASC' ? 'ASC' : 'DESC');
        } else {
            $baseQuery .= " ORDER BY cr.requested_at DESC";
        }
        
        // Add pagination
        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, min(100, (int)($filters['limit'] ?? 25)));
        $offset = ($page - 1) * $limit;
        
        $baseQuery .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        // Execute query
        $stmt = $pdo->prepare($baseQuery);
        $stmt->execute($params);
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $certificates,
            'total' => $totalRecords,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($totalRecords / $limit)
        ];
        
    } catch (PDOException $e) {
        logEvent("Error fetching filtered certificates: " . $e->getMessage(), 'ERROR');
        return [
            'data' => [],
            'total' => 0,
            'page' => 1,
            'limit' => 25,
            'pages' => 0
        ];
    }
}

/**
 * Get certificate statistics for dashboard
 */
function getCertificateStatistics() {
    global $pdo;
    
    try {
        $stats = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'total' => 0
        ];
        
        // Get overall counts
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM certificate_requests 
            GROUP BY status
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $result) {
            $stats[$result['status']] = (int)$result['count'];
            $stats['total'] += (int)$result['count'];
        }
        
        // Get today's approved count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM certificate_requests 
            WHERE status = 'approved' AND DATE(processed_at) = CURDATE()
        ");
        $stmt->execute();
        $stats['approved'] = $stmt->fetchColumn();
        
        // Get today's rejected count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM certificate_requests 
            WHERE status = 'rejected' AND DATE(processed_at) = CURDATE()
        ");
        $stmt->execute();
        $stats['rejected'] = $stmt->fetchColumn();
        
        return $stats;
        
    } catch (PDOException $e) {
        logEvent("Error fetching certificate statistics: " . $e->getMessage(), 'ERROR');
        return [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'total' => 0
        ];
    }
}

/**
 * Get all institutions for filter dropdown
 */
function getAllInstitutions() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, code 
            FROM institutions 
            WHERE is_active = 1 
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error fetching institutions: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Approve a certificate request
 */
function approveCertificate($certificateId, $comments = '') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get certificate request details
        $stmt = $pdo->prepare("
            SELECT cr.*, s.full_name as student_name, s.student_id, s.course, i.name as institution_name
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            WHERE cr.id = ? AND cr.status = 'pending'
        ");
        $stmt->execute([$certificateId]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certificate) {
            throw new Exception("Certificate request not found or already processed");
        }
        
        // Generate certificate file (placeholder - would integrate with actual certificate generation)
        $certificatePath = generateCertificateFile($certificate);
        
        // Update certificate request
        $stmt = $pdo->prepare("
            UPDATE certificate_requests 
            SET status = 'approved', 
                approved_by = ?, 
                approval_comments = ?, 
                certificate_path = ?,
                processed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([getCurrentUserId(), $comments, $certificatePath, $certificateId]);
        
        // Log the approval action with enhanced details
        logCertificateActionDetailed($certificateId, 'approved', $comments);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Certificate approved successfully',
            'certificate_path' => $certificatePath
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error approving certificate {$certificateId}: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Reject a certificate request
 */
function rejectCertificate($certificateId, $reason) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check if certificate exists and is pending
        $stmt = $pdo->prepare("
            SELECT id FROM certificate_requests 
            WHERE id = ? AND status = 'pending'
        ");
        $stmt->execute([$certificateId]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Certificate request not found or already processed");
        }
        
        // Update certificate request
        $stmt = $pdo->prepare("
            UPDATE certificate_requests 
            SET status = 'rejected', 
                approved_by = ?, 
                rejection_reason = ?, 
                processed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([getCurrentUserId(), $reason, $certificateId]);
        
        // Log the rejection action with enhanced details
        logCertificateActionDetailed($certificateId, 'rejected', $reason);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Certificate rejected successfully'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error rejecting certificate {$certificateId}: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Bulk approve certificate requests
 */
function bulkApproveCertificates($certificateIds, $comments = '') {
    global $pdo;
    
    try {
        $ids = explode(',', $certificateIds);
        $ids = array_filter(array_map('intval', $ids));
        
        if (empty($ids)) {
            throw new Exception("No valid certificate IDs provided");
        }
        
        $pdo->beginTransaction();
        
        $successCount = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            $result = approveCertificate($id, $comments);
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = "Certificate {$id}: " . $result['message'];
            }
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'count' => $successCount,
            'errors' => $errors,
            'message' => "{$successCount} certificates approved successfully"
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error in bulk certificate approval: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Bulk reject certificate requests
 */
function bulkRejectCertificates($certificateIds, $reason) {
    global $pdo;
    
    try {
        $ids = explode(',', $certificateIds);
        $ids = array_filter(array_map('intval', $ids));
        
        if (empty($ids)) {
            throw new Exception("No valid certificate IDs provided");
        }
        
        $pdo->beginTransaction();
        
        $successCount = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            $result = rejectCertificate($id, $reason);
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = "Certificate {$id}: " . $result['message'];
            }
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'count' => $successCount,
            'errors' => $errors,
            'message' => "{$successCount} certificates rejected successfully"
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error in bulk certificate rejection: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Generate certificate file (placeholder implementation)
 */
function generateCertificateFile($certificateData) {
    // Create certificates directory if it doesn't exist
    $certificateDir = __DIR__ . '/../uploads/certificates/';
    if (!is_dir($certificateDir)) {
        mkdir($certificateDir, 0755, true);
    }
    
    // Generate unique filename
    $filename = 'certificate_' . $certificateData['id'] . '_' . time() . '.pdf';
    $filePath = $certificateDir . $filename;
    
    // Placeholder: In a real implementation, this would generate an actual PDF certificate
    // For now, create a simple text file as placeholder
    $certificateContent = "CERTIFICATE OF COMPLETION\n\n";
    $certificateContent .= "This is to certify that\n\n";
    $certificateContent .= strtoupper($certificateData['student_name']) . "\n\n";
    $certificateContent .= "Student ID: " . $certificateData['student_id'] . "\n";
    $certificateContent .= "Course: " . $certificateData['course'] . "\n";
    $certificateContent .= "Institution: " . $certificateData['institution_name'] . "\n";
    $certificateContent .= "Certificate Type: " . ucfirst($certificateData['request_type']) . "\n\n";
    $certificateContent .= "Date: " . date('F j, Y') . "\n";
    
    file_put_contents($filePath, $certificateContent);
    
    return 'uploads/certificates/' . $filename;
}

/**
 * Download certificate file
 */
function downloadCertificate($certificateId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT cr.certificate_path, s.full_name as student_name, cr.request_type
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            WHERE cr.id = ? AND cr.status = 'approved' AND cr.certificate_path IS NOT NULL
        ");
        $stmt->execute([$certificateId]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certificate) {
            throw new Exception("Certificate not found or not approved");
        }
        
        $filePath = __DIR__ . '/../' . $certificate['certificate_path'];
        
        if (!file_exists($filePath)) {
            throw new Exception("Certificate file not found");
        }
        
        // Set headers for download
        $filename = sanitizeInput($certificate['student_name']) . '_' . $certificate['request_type'] . '_certificate.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Log the download action
        logCertificateActionDetailed($certificateId, 'downloaded', 'Certificate downloaded by admin');
        
        readfile($filePath);
        exit;
        
    } catch (Exception $e) {
        logEvent("Error downloading certificate {$certificateId}: " . $e->getMessage(), 'ERROR');
        header('HTTP/1.1 404 Not Found');
        echo "Certificate not found";
        exit;
    }
}

/**
 * Bulk download certificates as ZIP
 */
function bulkDownloadCertificates($certificateIds, $institutionId = null) {
    global $pdo;
    
    try {
        $ids = explode(',', $certificateIds);
        $ids = array_filter(array_map('intval', $ids));
        
        if (empty($ids)) {
            throw new Exception("No valid certificate IDs provided");
        }
        
        // Get certificate files
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT 
                cr.id,
                cr.certificate_path, 
                s.full_name as student_name, 
                s.student_id,
                cr.request_type,
                i.name as institution_name
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            WHERE cr.id IN ({$placeholders}) 
            AND cr.status = 'approved' 
            AND cr.certificate_path IS NOT NULL
        ");
        $stmt->execute($ids);
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($certificates)) {
            throw new Exception("No approved certificates found");
        }
        
        // Create ZIP file
        $zip = new ZipArchive();
        $zipFilename = 'certificates_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipFilename;
        
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Cannot create ZIP file");
        }
        
        foreach ($certificates as $cert) {
            $filePath = __DIR__ . '/../' . $cert['certificate_path'];
            if (file_exists($filePath)) {
                $archiveName = sanitizeInput($cert['student_name']) . '_' . 
                              $cert['student_id'] . '_' . 
                              $cert['request_type'] . '_certificate.pdf';
                $zip->addFile($filePath, $archiveName);
            }
        }
        
        $zip->close();
        
        // Set headers for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        
        readfile($zipPath);
        unlink($zipPath); // Clean up temp file
        exit;
        
    } catch (Exception $e) {
        logEvent("Error in bulk certificate download: " . $e->getMessage(), 'ERROR');
        header('HTTP/1.1 500 Internal Server Error');
        echo "Error creating download";
        exit;
    }
}

/**
 * Log certificate actions for audit trail
 */
function logCertificateAction($certificateId, $action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO certificate_audit_log (
                certificate_id, 
                action, 
                details, 
                performed_by, 
                performed_at
            ) VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$certificateId, $action, $details, getCurrentUserId()]);
        
    } catch (PDOException $e) {
        // Log audit logging failure
        logEvent("Failed to log certificate action: " . $e->getMessage(), 'ERROR');
    }
}

/**
 * Get certificate request details for modal view
 */
function getCertificateDetails($certificateId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                cr.*,
                s.student_id as student_number,
                s.full_name as student_name,
                s.email as student_email,
                s.phone as student_phone,
                s.course,
                s.batch_year,
                i.name as institution_name,
                i.code as institution_code,
                i.email as institution_email,
                u_req.full_name as requested_by_name,
                u_app.full_name as approved_by_name
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            JOIN users u_req ON cr.requested_by = u_req.id
            LEFT JOIN users u_app ON cr.approved_by = u_app.id
            WHERE cr.id = ?
        ");
        $stmt->execute([$certificateId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error fetching certificate details: " . $e->getMessage(), 'ERROR');
        return null;
    }
}

/**
 * Export filtered certificates to various formats
 */
function exportCertificates($filters, $format = 'csv') {
    // Remove pagination for export
    unset($filters['page']);
    $filters['limit'] = 10000; // Large limit for export
    
    $certificateData = getFilteredCertificates($filters);
    $certificates = $certificateData['data'];
    
    switch ($format) {
        case 'csv':
            exportCertificatesCSV($certificates);
            break;
        case 'excel':
            exportCertificatesExcel($certificates);
            break;
        case 'pdf':
            exportCertificatesPDF($certificates);
            break;
        default:
            exportCertificatesCSV($certificates);
    }
}

/**
 * Export certificates to CSV
 */
function exportCertificatesCSV($certificates) {
    $filename = 'certificate_requests_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, [
        'ID',
        'Student Name',
        'Student ID',
        'Institution',
        'Course',
        'Request Type',
        'Priority',
        'Status',
        'Requested Date',
        'Processed Date',
        'Requested By',
        'Approved By',
        'Comments/Reason'
    ]);
    
    // CSV data
    foreach ($certificates as $cert) {
        fputcsv($output, [
            $cert['id'],
            $cert['student_name'],
            $cert['student_number'],
            $cert['institution_name'],
            $cert['course'],
            ucfirst($cert['request_type']),
            ucfirst($cert['priority']),
            ucfirst($cert['status']),
            date('Y-m-d H:i:s', strtotime($cert['requested_at'])),
            $cert['processed_at'] ? date('Y-m-d H:i:s', strtotime($cert['processed_at'])) : '',
            $cert['requested_by_name'],
            $cert['approved_by_name'] ?? '',
            $cert['approval_comments'] ?: $cert['rejection_reason'] ?: ''
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * Render certificate details modal content
 */
function renderCertificateDetailsModal($certificate) {
    echo '<div class="alert alert-info">Certificate details for: ' . htmlspecialchars($certificate['student_name']) . '</div>';
}

/**
 * Get certificate audit trail
 */
function getCertificateAuditTrail($certificateId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                cal.action,
                cal.details,
                cal.performed_at,
                u.full_name as performed_by_name
            FROM certificate_audit_log cal
            JOIN users u ON cal.performed_by = u.id
            WHERE cal.certificate_id = ?
            ORDER BY cal.performed_at DESC
        ");
        $stmt->execute([$certificateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        logEvent("Error fetching certificate audit trail: " . $e->getMessage(), 'ERROR');
        return [];
    }
}

/**
 * Enhanced certificate approval with notification system
 */
function approveCertificateWithNotification($certificateId, $comments = '') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get certificate request details
        $stmt = $pdo->prepare("
            SELECT 
                cr.*, 
                s.full_name as student_name, 
                s.student_id, 
                s.course, 
                s.email as student_email,
                i.name as institution_name,
                i.email as institution_email,
                u.full_name as requested_by_name,
                u.email as requested_by_email
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            JOIN users u ON cr.requested_by = u.id
            WHERE cr.id = ? AND cr.status = 'pending'
        ");
        $stmt->execute([$certificateId]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certificate) {
            throw new Exception("Certificate request not found or already processed");
        }
        
        // Generate certificate file
        $certificatePath = generateCertificateFile($certificate);
        
        // Update certificate request
        $stmt = $pdo->prepare("
            UPDATE certificate_requests 
            SET status = 'approved', 
                approved_by = ?, 
                approval_comments = ?, 
                certificate_path = ?,
                processed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([getCurrentUserId(), $comments, $certificatePath, $certificateId]);
        
        // Log the approval action with IP and user agent
        logCertificateActionDetailed($certificateId, 'approved', $comments);
        
        // Send notification email (if email system is configured)
        sendCertificateApprovalNotification($certificate, $comments);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Certificate approved successfully',
            'certificate_path' => $certificatePath
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error approving certificate {$certificateId}: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Enhanced certificate rejection with notification system
 */
function rejectCertificateWithNotification($certificateId, $reason) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get certificate request details for notification
        $stmt = $pdo->prepare("
            SELECT 
                cr.*, 
                s.full_name as student_name, 
                s.student_id, 
                s.course,
                i.name as institution_name,
                u.full_name as requested_by_name,
                u.email as requested_by_email
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            JOIN users u ON cr.requested_by = u.id
            WHERE cr.id = ? AND cr.status = 'pending'
        ");
        $stmt->execute([$certificateId]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certificate) {
            throw new Exception("Certificate request not found or already processed");
        }
        
        // Update certificate request
        $stmt = $pdo->prepare("
            UPDATE certificate_requests 
            SET status = 'rejected', 
                approved_by = ?, 
                rejection_reason = ?, 
                processed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([getCurrentUserId(), $reason, $certificateId]);
        
        // Log the rejection action with details
        logCertificateActionDetailed($certificateId, 'rejected', $reason);
        
        // Send notification email
        sendCertificateRejectionNotification($certificate, $reason);
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Certificate rejected successfully'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error rejecting certificate {$certificateId}: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Enhanced audit logging with IP and user agent
 */
function logCertificateActionDetailed($certificateId, $action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO certificate_audit_log (
                certificate_id, 
                action, 
                details, 
                performed_by, 
                performed_at,
                ip_address,
                user_agent
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?)
        ");
        $stmt->execute([
            $certificateId, 
            $action, 
            $details, 
            getCurrentUserId(),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
    } catch (PDOException $e) {
        logEvent("Failed to log certificate action: " . $e->getMessage(), 'ERROR');
    }
}

/**
 * Send certificate approval notification email
 */
function sendCertificateApprovalNotification($certificate, $comments = '') {
    // Placeholder for email notification system
    // In a real implementation, this would send an email to the institution
    
    $subject = "Certificate Request Approved - " . $certificate['student_name'];
    $message = "
        Dear " . $certificate['requested_by_name'] . ",
        
        The certificate request for student " . $certificate['student_name'] . " 
        (ID: " . $certificate['student_id'] . ") has been approved.
        
        Certificate Type: " . ucfirst($certificate['request_type']) . "
        Course: " . $certificate['course'] . "
        Institution: " . $certificate['institution_name'] . "
        
        " . ($comments ? "Admin Comments: " . $comments : "") . "
        
        You can download the certificate from the admin portal.
        
        Best regards,
        Certificate Management System
    ";
    
    // Log the notification attempt
    logEvent("Certificate approval notification sent for certificate ID: " . $certificate['id'], 'INFO');
    
    // In production, implement actual email sending here
    // mail($certificate['requested_by_email'], $subject, $message);
}

/**
 * Send certificate rejection notification email
 */
function sendCertificateRejectionNotification($certificate, $reason) {
    // Placeholder for email notification system
    
    $subject = "Certificate Request Rejected - " . $certificate['student_name'];
    $message = "
        Dear " . $certificate['requested_by_name'] . ",
        
        The certificate request for student " . $certificate['student_name'] . " 
        (ID: " . $certificate['student_id'] . ") has been rejected.
        
        Certificate Type: " . ucfirst($certificate['request_type']) . "
        Course: " . $certificate['course'] . "
        Institution: " . $certificate['institution_name'] . "
        
        Rejection Reason: " . $reason . "
        
        Please review the requirements and resubmit if necessary.
        
        Best regards,
        Certificate Management System
    ";
    
    // Log the notification attempt
    logEvent("Certificate rejection notification sent for certificate ID: " . $certificate['id'], 'INFO');
    
    // In production, implement actual email sending here
    // mail($certificate['requested_by_email'], $subject, $message);
}

/**
 * Bulk delete certificate requests with confirmation
 */
function bulkDeleteCertificates($certificateIds, $confirm = false) {
    global $pdo;
    
    try {
        if (!$confirm) {
            return [
                'success' => false,
                'message' => 'Confirmation required for bulk deletion'
            ];
        }
        
        $ids = explode(',', $certificateIds);
        $ids = array_filter(array_map('intval', $ids));
        
        if (empty($ids)) {
            throw new Exception("No valid certificate IDs provided");
        }
        
        $pdo->beginTransaction();
        
        // Create backup before deletion
        $backupData = [];
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT 
                cr.*,
                s.full_name as student_name,
                s.student_id,
                i.name as institution_name
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            WHERE cr.id IN ({$placeholders})
        ");
        $stmt->execute($ids);
        $backupData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Save backup to file
        $backupFile = 'deleted_certificates_' . date('Y-m-d_H-i-s') . '.json';
        $backupPath = __DIR__ . '/../uploads/backups/';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        file_put_contents($backupPath . $backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
        
        // Delete certificate files
        foreach ($backupData as $cert) {
            if (!empty($cert['certificate_path'])) {
                $filePath = __DIR__ . '/../' . $cert['certificate_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        // Delete audit log entries
        $stmt = $pdo->prepare("DELETE FROM certificate_audit_log WHERE certificate_id IN ({$placeholders})");
        $stmt->execute($ids);
        
        // Delete certificate requests
        $stmt = $pdo->prepare("DELETE FROM certificate_requests WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        
        $deletedCount = $stmt->rowCount();
        
        // Log the bulk deletion
        logEvent("Bulk deletion of {$deletedCount} certificate requests by admin ID: " . getCurrentUserId(), 'INFO');
        
        $pdo->commit();
        
        return [
            'success' => true,
            'count' => $deletedCount,
            'backup_file' => $backupFile,
            'message' => "{$deletedCount} certificate requests deleted successfully"
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error in bulk certificate deletion: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Perform scheduled cleanup of old certificate records
 */
function performScheduledCleanup($retentionDays = 365) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $cutoffDate = date('Y-m-d', strtotime("-{$retentionDays} days"));
        
        // Get records to be archived
        $stmt = $pdo->prepare("
            SELECT 
                cr.*,
                s.full_name as student_name,
                s.student_id,
                i.name as institution_name
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            WHERE cr.processed_at IS NOT NULL 
            AND DATE(cr.processed_at) < ?
        ");
        $stmt->execute([$cutoffDate]);
        $recordsToArchive = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($recordsToArchive)) {
            return [
                'success' => true,
                'archived_count' => 0,
                'deleted_count' => 0,
                'message' => 'No records found for cleanup'
            ];
        }
        
        // Create archive
        $archiveFile = 'certificate_archive_' . date('Y-m-d_H-i-s') . '.json';
        $archivePath = __DIR__ . '/../uploads/archives/';
        if (!is_dir($archivePath)) {
            mkdir($archivePath, 0755, true);
        }
        
        // Compress and save archive
        $archiveData = [
            'created_at' => date('Y-m-d H:i:s'),
            'retention_days' => $retentionDays,
            'cutoff_date' => $cutoffDate,
            'records' => $recordsToArchive
        ];
        
        $compressedData = gzcompress(json_encode($archiveData, JSON_PRETTY_PRINT));
        file_put_contents($archivePath . $archiveFile . '.gz', $compressedData);
        
        // Move certificate files to archive directory
        $certificateArchivePath = $archivePath . 'certificates/';
        if (!is_dir($certificateArchivePath)) {
            mkdir($certificateArchivePath, 0755, true);
        }
        
        foreach ($recordsToArchive as $record) {
            if (!empty($record['certificate_path'])) {
                $sourcePath = __DIR__ . '/../' . $record['certificate_path'];
                $archiveFileName = 'archived_' . basename($record['certificate_path']);
                $destinationPath = $certificateArchivePath . $archiveFileName;
                
                if (file_exists($sourcePath)) {
                    copy($sourcePath, $destinationPath);
                    unlink($sourcePath);
                }
            }
        }
        
        // Delete from database
        $ids = array_column($recordsToArchive, 'id');
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        // Delete audit logs
        $stmt = $pdo->prepare("DELETE FROM certificate_audit_log WHERE certificate_id IN ({$placeholders})");
        $stmt->execute($ids);
        
        // Delete certificate requests
        $stmt = $pdo->prepare("DELETE FROM certificate_requests WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        
        $deletedCount = $stmt->rowCount();
        
        // Log the cleanup operation
        logEvent("Scheduled cleanup completed: {$deletedCount} records archived and deleted (retention: {$retentionDays} days)", 'INFO');
        
        $pdo->commit();
        
        return [
            'success' => true,
            'archived_count' => count($recordsToArchive),
            'deleted_count' => $deletedCount,
            'archive_file' => $archiveFile . '.gz',
            'message' => "Cleanup completed successfully"
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error in scheduled cleanup: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Archive certificate records based on status and date
 */
function archiveCertificates($status, $archiveDate, $createBackup = false) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Build query based on status
        $whereClause = "WHERE DATE(cr.processed_at) < ?";
        $params = [$archiveDate];
        
        if ($status !== 'all') {
            $whereClause .= " AND cr.status = ?";
            $params[] = $status;
        }
        
        // Get records to archive
        $stmt = $pdo->prepare("
            SELECT 
                cr.*,
                s.full_name as student_name,
                s.student_id,
                i.name as institution_name
            FROM certificate_requests cr
            JOIN students s ON cr.student_id = s.id
            JOIN institutions i ON cr.institution_id = i.id
            {$whereClause}
        ");
        $stmt->execute($params);
        $recordsToArchive = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($recordsToArchive)) {
            return [
                'success' => true,
                'archived_count' => 0,
                'message' => 'No records found for archiving'
            ];
        }
        
        $archivePath = __DIR__ . '/../uploads/archives/';
        if (!is_dir($archivePath)) {
            mkdir($archivePath, 0755, true);
        }
        
        if ($createBackup) {
            // Create backup before archiving
            $backupFile = 'certificate_backup_' . date('Y-m-d_H-i-s') . '.json';
            $backupData = [
                'created_at' => date('Y-m-d H:i:s'),
                'archive_criteria' => [
                    'status' => $status,
                    'archive_date' => $archiveDate
                ],
                'records' => $recordsToArchive
            ];
            
            file_put_contents($archivePath . $backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
        }
        
        // Create compressed archive
        $archiveFile = 'certificate_archive_' . $status . '_' . date('Y-m-d_H-i-s') . '.json.gz';
        $archiveData = [
            'created_at' => date('Y-m-d H:i:s'),
            'status_filter' => $status,
            'archive_date' => $archiveDate,
            'records' => $recordsToArchive
        ];
        
        $compressedData = gzcompress(json_encode($archiveData, JSON_PRETTY_PRINT));
        file_put_contents($archivePath . $archiveFile, $compressedData);
        
        // Archive certificate files
        $certificateArchivePath = $archivePath . 'certificates/';
        if (!is_dir($certificateArchivePath)) {
            mkdir($certificateArchivePath, 0755, true);
        }
        
        foreach ($recordsToArchive as $record) {
            if (!empty($record['certificate_path'])) {
                $sourcePath = __DIR__ . '/../' . $record['certificate_path'];
                $archiveFileName = 'archived_' . date('Y-m-d_') . basename($record['certificate_path']);
                $destinationPath = $certificateArchivePath . $archiveFileName;
                
                if (file_exists($sourcePath)) {
                    copy($sourcePath, $destinationPath);
                }
            }
        }
        
        // Update records to archived status instead of deleting
        $ids = array_column($recordsToArchive, 'id');
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        // Add archived status to certificate_requests table if not exists
        try {
            $pdo->exec("ALTER TABLE certificate_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'pending'");
        } catch (PDOException $e) {
            // Column might already be modified
        }
        
        // Update status to archived
        $stmt = $pdo->prepare("UPDATE certificate_requests SET status = 'archived' WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        
        $archivedCount = $stmt->rowCount();
        
        // Log the archive operation
        logEvent("Certificate archive completed: {$archivedCount} records archived (status: {$status}, date: {$archiveDate})", 'INFO');
        
        $pdo->commit();
        
        return [
            'success' => true,
            'archived_count' => $archivedCount,
            'archive_file' => $archiveFile,
            'backup_file' => $createBackup ? $backupFile : null,
            'message' => "Archive completed successfully"
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        logEvent("Error in certificate archiving: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Enhanced export to Excel format
 */
function exportCertificatesExcel($certificates) {
    // For a full implementation, you would use a library like PhpSpreadsheet
    // For now, we'll create a CSV with Excel-friendly formatting
    
    $filename = 'certificate_requests_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Enhanced CSV headers
    fputcsv($output, [
        'Certificate ID',
        'Student Name',
        'Student ID',
        'Institution Name',
        'Institution Code',
        'Course',
        'Batch Year',
        'Request Type',
        'Priority Level',
        'Current Status',
        'Requested Date',
        'Processed Date',
        'Requested By',
        'Approved/Rejected By',
        'Processing Time (Days)',
        'Comments/Reason',
        'Certificate Available'
    ]);
    
    // Enhanced CSV data with calculations
    foreach ($certificates as $cert) {
        $processingTime = '';
        if ($cert['processed_at']) {
            $requestDate = new DateTime($cert['requested_at']);
            $processedDate = new DateTime($cert['processed_at']);
            $processingTime = $processedDate->diff($requestDate)->days;
        }
        
        fputcsv($output, [
            $cert['id'],
            $cert['student_name'],
            $cert['student_number'],
            $cert['institution_name'],
            $cert['institution_code'],
            $cert['course'],
            $cert['batch_year'],
            ucfirst($cert['request_type']),
            ucfirst($cert['priority']),
            ucfirst($cert['status']),
            date('Y-m-d H:i:s', strtotime($cert['requested_at'])),
            $cert['processed_at'] ? date('Y-m-d H:i:s', strtotime($cert['processed_at'])) : '',
            $cert['requested_by_name'],
            $cert['approved_by_name'] ?? '',
            $processingTime,
            $cert['approval_comments'] ?: $cert['rejection_reason'] ?: '',
            !empty($cert['certificate_path']) ? 'Yes' : 'No'
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * Export certificates to PDF report
 */
function exportCertificatesPDF($certificates) {
    // For a full implementation, you would use a library like TCPDF or FPDF
    // For now, we'll create an HTML report that can be printed as PDF
    
    $filename = 'certificate_report_' . date('Y-m-d_H-i-s') . '.html';
    
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificate Requests Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .summary { background: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .status-pending { background-color: #fff3cd; }
        .status-approved { background-color: #d1edff; }
        .status-rejected { background-color: #f8d7da; }
        .priority-high { color: #dc3545; font-weight: bold; }
        .priority-medium { color: #fd7e14; }
        .priority-low { color: #6c757d; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Certificate Requests Report</h1>
        <p>Generated on: ' . date('F j, Y g:i A') . '</p>
        <p>Total Records: ' . count($certificates) . '</p>
    </div>
    
    <div class="summary">
        <h3>Summary Statistics</h3>';
    
    // Calculate summary statistics
    $statusCounts = array_count_values(array_column($certificates, 'status'));
    $priorityCounts = array_count_values(array_column($certificates, 'priority'));
    
    echo '<p><strong>Status Breakdown:</strong> ';
    foreach ($statusCounts as $status => $count) {
        echo ucfirst($status) . ': ' . $count . ' | ';
    }
    echo '</p>';
    
    echo '<p><strong>Priority Breakdown:</strong> ';
    foreach ($priorityCounts as $priority => $count) {
        echo ucfirst($priority) . ': ' . $count . ' | ';
    }
    echo '</p></div>';
    
    echo '<table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Institution</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Requested</th>
                <th>Processed</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($certificates as $cert) {
        $statusClass = 'status-' . $cert['status'];
        $priorityClass = 'priority-' . $cert['priority'];
        
        echo '<tr class="' . $statusClass . '">
            <td>' . htmlspecialchars($cert['id']) . '</td>
            <td>
                <strong>' . htmlspecialchars($cert['student_name']) . '</strong><br>
                <small>' . htmlspecialchars($cert['student_number']) . '</small>
            </td>
            <td>' . htmlspecialchars($cert['institution_name']) . '</td>
            <td>' . ucfirst(htmlspecialchars($cert['request_type'])) . '</td>
            <td class="' . $priorityClass . '">' . ucfirst(htmlspecialchars($cert['priority'])) . '</td>
            <td>' . ucfirst(htmlspecialchars($cert['status'])) . '</td>
            <td>' . date('M j, Y', strtotime($cert['requested_at'])) . '</td>
            <td>' . ($cert['processed_at'] ? date('M j, Y', strtotime($cert['processed_at'])) : '-') . '</td>
        </tr>';
    }
    
    echo '</tbody></table>
    
    <div class="no-print">
        <p><em>This report can be printed or saved as PDF using your browser\'s print function.</em></p>
    </div>
</body>
</html>';
    
    exit;
}