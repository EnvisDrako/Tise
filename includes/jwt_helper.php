<?php
/**
 * JWT Helper Functions for Mobile API Authentication
 * Simple JWT implementation for API authentication
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

/**
 * Base64 URL encode
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64 URL decode
 */
function base64UrlDecode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

/**
 * Generate JWT token
 */
function generateJWT($payload, $secret = null) {
    $secret = $secret ?: JWT_SECRET;
    
    // Header
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    
    // Add standard claims to payload
    $payload['iat'] = time(); // Issued at
    $payload['exp'] = time() + JWT_EXPIRY; // Expiry
    $payload['iss'] = SITE_URL; // Issuer
    
    $payload = json_encode($payload);
    
    // Create base64 encoded header and payload
    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode($payload);
    
    // Create signature
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
    $base64Signature = base64UrlEncode($signature);
    
    // Create JWT
    return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

/**
 * Verify JWT token
 */
function verifyJWT($jwt, $secret = null) {
    $secret = $secret ?: JWT_SECRET;
    
    // Split the JWT
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) {
        return false;
    }
    
    $header = base64UrlDecode($tokenParts[0]);
    $payload = base64UrlDecode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];
    
    // Verify the signature
    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode($payload);
    $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
    $base64Signature = base64UrlEncode($signature);
    
    if (!hash_equals($base64Signature, $signatureProvided)) {
        return false;
    }
    
    // Decode payload
    $payloadData = json_decode($payload, true);
    
    // Check expiry
    if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
        return false;
    }
    
    return $payloadData;
}

/**
 * Extract JWT from Authorization header
 */
function extractJWTFromHeader() {
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Validate API request and return user data
 */
function validateAPIRequest() {
    $jwt = extractJWTFromHeader();
    
    if (!$jwt) {
        return false;
    }
    
    $payload = verifyJWT($jwt);
    
    if (!$payload) {
        return false;
    }
    
    // Verify user still exists and is active
    $user = fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$payload['user_id']]);
    
    if (!$user) {
        return false;
    }
    
    return [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'full_name' => $user['full_name'],
        'institution_id' => $user['institution_id']
    ];
}

/**
 * Check API rate limiting
 */
function checkAPIRateLimit($userId) {
    // Simple rate limiting using session or database
    // For production, consider using Redis or Memcached
    
    $cacheKey = "api_rate_limit_{$userId}";
    $currentHour = date('Y-m-d-H');
    
    // Use file-based caching for simplicity
    $cacheFile = ERROR_LOG_PATH . "rate_limit_{$userId}_{$currentHour}.cache";
    
    if (file_exists($cacheFile)) {
        $requests = (int)file_get_contents($cacheFile);
        if ($requests >= API_RATE_LIMIT) {
            return false;
        }
        file_put_contents($cacheFile, $requests + 1);
    } else {
        // Clean old cache files
        $pattern = ERROR_LOG_PATH . "rate_limit_{$userId}_*.cache";
        foreach (glob($pattern) as $oldFile) {
            if (filemtime($oldFile) < strtotime('-2 hours')) {
                unlink($oldFile);
            }
        }
        file_put_contents($cacheFile, 1);
    }
    
    return true;
}

/**
 * Send JSON API response
 */
function sendAPIResponse($data, $success = true, $message = '', $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    $response = [
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('c')
    ];
    
    echo json_encode($response);
    exit;
}

/**
 * Send API error response
 */
function sendAPIError($message, $httpCode = 400, $errorCode = null) {
    $data = [];
    if ($errorCode) {
        $data['error_code'] = $errorCode;
    }
    
    sendAPIResponse($data, false, $message, $httpCode);
}

/**
 * Log API request
 */
function logAPIRequest($endpoint, $userId = null, $method = null, $success = true) {
    $method = $method ?: $_SERVER['REQUEST_METHOD'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'method' => $method,
        'user_id' => $userId,
        'ip' => $ip,
        'user_agent' => $userAgent,
        'success' => $success
    ];
    
    $logMessage = json_encode($logData) . PHP_EOL;
    file_put_contents(ERROR_LOG_PATH . 'api_requests.log', $logMessage, FILE_APPEND | LOCK_EX);
}
?>