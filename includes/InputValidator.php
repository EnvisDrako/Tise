<?php
/**
 * Comprehensive Input Validation System
 * Provides validation classes for all form inputs and API endpoints
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

class InputValidator {
    private $errors = [];
    private $rules = [];
    private $data = [];
    private $sanitizedData = [];
    
    /**
     * Constructor
     */
    public function __construct($data = []) {
        $this->data = $data;
        $this->sanitizedData = $data;
    }
    
    /**
     * Add validation rule
     */
    public function rule($field, $rules, $customMessage = null) {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        
        $ruleArray = is_array($rules) ? $rules : explode('|', $rules);
        
        foreach ($ruleArray as $rule) {
            $this->rules[$field][] = [
                'rule' => $rule,
                'message' => $customMessage
            ];
        }
        
        return $this;
    }
    
    /**
     * Validate all fields
     */
    public function validate() {
        $this->errors = [];
        
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            
            foreach ($fieldRules as $ruleConfig) {
                $rule = $ruleConfig['rule'];
                $customMessage = $ruleConfig['message'];
                
                if (!$this->validateField($field, $value, $rule, $customMessage)) {
                    break; // Stop on first error for this field
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate individual field
     */
    private function validateField($field, $value, $rule, $customMessage = null) {
        // Parse rule and parameters
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];
        
        $isValid = true;
        $errorMessage = '';
        
        switch ($ruleName) {
            case 'required':
                $isValid = !empty($value) || $value === '0';
                $errorMessage = $customMessage ?: "{$field} is required";
                break;
                
            case 'email':
                if (!empty($value)) {
                    $isValid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                    $errorMessage = $customMessage ?: "{$field} must be a valid email address";
                }
                break;
                
            case 'min':
                if (!empty($value)) {
                    $min = (int)$ruleParams[0];
                    $isValid = strlen($value) >= $min;
                    $errorMessage = $customMessage ?: "{$field} must be at least {$min} characters";
                }
                break;
                
            case 'max':
                if (!empty($value)) {
                    $max = (int)$ruleParams[0];
                    $isValid = strlen($value) <= $max;
                    $errorMessage = $customMessage ?: "{$field} must not exceed {$max} characters";
                }
                break;
                
            case 'numeric':
                if (!empty($value)) {
                    $isValid = is_numeric($value);
                    $errorMessage = $customMessage ?: "{$field} must be numeric";
                }
                break;
                
            case 'integer':
                if (!empty($value)) {
                    $isValid = filter_var($value, FILTER_VALIDATE_INT) !== false;
                    $errorMessage = $customMessage ?: "{$field} must be an integer";
                }
                break;
                
            case 'phone':
                if (!empty($value)) {
                    $isValid = preg_match('/^[0-9+\-\s()]{10,15}$/', $value);
                    $errorMessage = $customMessage ?: "{$field} must be a valid phone number";
                }
                break;
                
            case 'alpha':
                if (!empty($value)) {
                    $isValid = preg_match('/^[a-zA-Z\s]+$/', $value);
                    $errorMessage = $customMessage ?: "{$field} must contain only letters";
                }
                break;
                
            case 'alphanumeric':
                if (!empty($value)) {
                    $isValid = preg_match('/^[a-zA-Z0-9\s]+$/', $value);
                    $errorMessage = $customMessage ?: "{$field} must contain only letters and numbers";
                }
                break;
                
            case 'url':
                if (!empty($value)) {
                    $isValid = filter_var($value, FILTER_VALIDATE_URL) !== false;
                    $errorMessage = $customMessage ?: "{$field} must be a valid URL";
                }
                break;
                
            case 'date':
                if (!empty($value)) {
                    $isValid = strtotime($value) !== false;
                    $errorMessage = $customMessage ?: "{$field} must be a valid date";
                }
                break;
                
            case 'in':
                if (!empty($value)) {
                    $isValid = in_array($value, $ruleParams);
                    $allowedValues = implode(', ', $ruleParams);
                    $errorMessage = $customMessage ?: "{$field} must be one of: {$allowedValues}";
                }
                break;
                
            case 'regex':
                if (!empty($value)) {
                    $pattern = $ruleParams[0];
                    $isValid = preg_match($pattern, $value);
                    $errorMessage = $customMessage ?: "{$field} format is invalid";
                }
                break;
                
            case 'file_required':
                $isValid = isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK;
                $errorMessage = $customMessage ?: "{$field} file is required";
                break;
                
            case 'file_type':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = $ruleParams;
                    $fileExtension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                    $isValid = in_array($fileExtension, $allowedTypes);
                    $allowedTypesStr = implode(', ', $allowedTypes);
                    $errorMessage = $customMessage ?: "{$field} must be one of these file types: {$allowedTypesStr}";
                }
                break;
                
            case 'file_size':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $maxSize = (int)$ruleParams[0] * 1024 * 1024; // Convert MB to bytes
                    $isValid = $_FILES[$field]['size'] <= $maxSize;
                    $errorMessage = $customMessage ?: "{$field} file size must not exceed {$ruleParams[0]}MB";
                }
                break;
                
            case 'password_strength':
                if (!empty($value)) {
                    $isValid = $this->validatePasswordStrength($value);
                    $errorMessage = $customMessage ?: "{$field} must contain at least 8 characters with uppercase, lowercase, number and special character";
                }
                break;
                
            case 'unique':
                if (!empty($value)) {
                    $table = $ruleParams[0];
                    $column = $ruleParams[1] ?? $field;
                    $excludeId = $ruleParams[2] ?? null;
                    $isValid = $this->validateUnique($value, $table, $column, $excludeId);
                    $errorMessage = $customMessage ?: "{$field} already exists";
                }
                break;
                
            case 'exists':
                if (!empty($value)) {
                    $table = $ruleParams[0];
                    $column = $ruleParams[1] ?? 'id';
                    $isValid = $this->validateExists($value, $table, $column);
                    $errorMessage = $customMessage ?: "{$field} does not exist";
                }
                break;
        }
        
        if (!$isValid) {
            $this->errors[$field] = $errorMessage;
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate password strength
     */
    private function validatePasswordStrength($password) {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }
        
        // Must contain uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Must contain lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Must contain number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Must contain special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate unique value in database
     */
    private function validateUnique($value, $table, $column, $excludeId = null) {
        try {
            $pdo = getDB();
            
            $query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
            $params = [$value];
            
            if ($excludeId) {
                $query .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() == 0;
            
        } catch (PDOException $e) {
            logError("Database validation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate value exists in database
     */
    private function validateExists($value, $table, $column) {
        try {
            $pdo = getDB();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
            $stmt->execute([$value]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            logError("Database validation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sanitize input data
     */
    public function sanitize() {
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                // Basic sanitization
                $this->sanitizedData[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $this->sanitizedData[$key] = $this->sanitizeArray($value);
            }
        }
        
        return $this->sanitizedData;
    }
    
    /**
     * Sanitize string value
     */
    private function sanitizeString($value) {
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Convert special characters to HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
    
    /**
     * Sanitize array values
     */
    private function sanitizeArray($array) {
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error message
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Check if field has error
     */
    public function hasError($field) {
        return isset($this->errors[$field]);
    }
    
    /**
     * Get error for specific field
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Get sanitized data
     */
    public function getSanitizedData() {
        return $this->sanitizedData;
    }
    
    /**
     * Get sanitized value for specific field
     */
    public function getSanitized($field, $default = null) {
        return $this->sanitizedData[$field] ?? $default;
    }
}

/**
 * CSRF Token Management
 */
class CSRFProtection {
    private static $tokenName = 'csrf_token';
    private static $tokenExpiry = 3600; // 1 hour
    
    /**
     * Generate CSRF token
     */
    public static function generateToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        $_SESSION[self::$tokenName] = [
            'token' => $token,
            'timestamp' => $timestamp
        ];
        
        return $token;
    }
    
    /**
     * Get current CSRF token
     */
    public static function getToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$tokenName]) || 
            (time() - $_SESSION[self::$tokenName]['timestamp']) > self::$tokenExpiry) {
            return self::generateToken();
        }
        
        return $_SESSION[self::$tokenName]['token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$tokenName])) {
            return false;
        }
        
        $sessionToken = $_SESSION[self::$tokenName];
        
        // Check if token has expired
        if ((time() - $sessionToken['timestamp']) > self::$tokenExpiry) {
            unset($_SESSION[self::$tokenName]);
            return false;
        }
        
        // Validate token
        if (!hash_equals($sessionToken['token'], $token)) {
            logSecurityEvent("CSRF token validation failed", [
                'provided_token' => substr($token, 0, 8) . '...',
                'expected_token' => substr($sessionToken['token'], 0, 8) . '...',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate CSRF hidden input field
     */
    public static function getHiddenField() {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validate CSRF token from request
     */
    public static function validateRequest() {
        $token = $_POST[self::$tokenName] ?? $_GET[self::$tokenName] ?? null;
        
        if (!$token) {
            logSecurityEvent("CSRF token missing from request", [
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown'
            ]);
            return false;
        }
        
        return self::validateToken($token);
    }
}

/**
 * XSS Prevention Helper
 */
class XSSProtection {
    /**
     * Clean output for display
     */
    public static function clean($input, $encoding = 'UTF-8') {
        if (is_array($input)) {
            return array_map([self::class, 'clean'], $input);
        }
        
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, $encoding);
    }
    
    /**
     * Clean for HTML attribute
     */
    public static function attribute($input, $encoding = 'UTF-8') {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, $encoding);
    }
    
    /**
     * Clean for JavaScript context
     */
    public static function javascript($input) {
        return json_encode($input, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
    
    /**
     * Clean for URL context
     */
    public static function url($input) {
        return urlencode($input);
    }
    
    /**
     * Clean for CSS context
     */
    public static function css($input) {
        // Remove potentially dangerous CSS
        $input = preg_replace('/[^a-zA-Z0-9\-_#.]/', '', $input);
        return $input;
    }
    
    /**
     * Strip dangerous HTML tags
     */
    public static function stripDangerousTags($input) {
        $dangerousTags = [
            'script', 'iframe', 'object', 'embed', 'form', 'input', 
            'button', 'select', 'textarea', 'link', 'meta', 'style'
        ];
        
        foreach ($dangerousTags as $tag) {
            $input = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', '', $input);
            $input = preg_replace('/<' . $tag . '[^>]*\/?>/is', '', $input);
        }
        
        return $input;
    }
}

/**
 * File Upload Security
 */
class FileUploadValidator {
    private $allowedTypes;
    private $maxSize;
    private $uploadPath;
    
    public function __construct($allowedTypes = null, $maxSize = null, $uploadPath = null) {
        $this->allowedTypes = $allowedTypes ?: ALLOWED_FILE_TYPES;
        $this->maxSize = $maxSize ?: MAX_FILE_SIZE;
        $this->uploadPath = $uploadPath ?: UPLOAD_PATH;
    }
    
    /**
     * Validate uploaded file
     */
    public function validate($fileField) {
        if (!isset($_FILES[$fileField])) {
            return ['valid' => false, 'error' => 'No file uploaded'];
        }
        
        $file = $_FILES[$fileField];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => $this->getUploadErrorMessage($file['error'])];
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            return ['valid' => false, 'error' => 'File size exceeds maximum allowed size'];
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return ['valid' => false, 'error' => 'File type not allowed'];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!$this->isAllowedMimeType($mimeType, $extension)) {
            return ['valid' => false, 'error' => 'Invalid file type'];
        }
        
        // Check for malicious content
        if ($this->containsMaliciousContent($file['tmp_name'])) {
            logSecurityEvent("Malicious file upload attempt", [
                'filename' => $file['name'],
                'mime_type' => $mimeType,
                'size' => $file['size']
            ]);
            return ['valid' => false, 'error' => 'File contains potentially malicious content'];
        }
        
        return ['valid' => true, 'file' => $file];
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File size exceeds maximum allowed size';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Check if MIME type is allowed for extension
     */
    private function isAllowedMimeType($mimeType, $extension) {
        $allowedMimes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'txt' => ['text/plain']
        ];
        
        if (!isset($allowedMimes[$extension])) {
            return false;
        }
        
        return in_array($mimeType, $allowedMimes[$extension]);
    }
    
    /**
     * Check for malicious content in file
     */
    private function containsMaliciousContent($filePath) {
        // Read first 1KB of file to check for suspicious patterns
        $content = file_get_contents($filePath, false, null, 0, 1024);
        
        // Check for PHP tags
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            return true;
        }
        
        // Check for script tags
        if (preg_match('/<script[^>]*>/i', $content)) {
            return true;
        }
        
        // Check for executable signatures
        $signatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xCA\xFE\xBA\xBE", // Java class file
        ];
        
        foreach ($signatures as $signature) {
            if (strpos($content, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Secure file upload
     */
    public function upload($fileField, $subDirectory = '') {
        $validation = $this->validate($fileField);
        
        if (!$validation['valid']) {
            return $validation;
        }
        
        $file = $validation['file'];
        
        // Generate secure filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $this->generateSecureFilename($file['name'], $extension);
        
        // Create upload directory if it doesn't exist
        $uploadDir = $this->uploadPath . $subDirectory;
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filePath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Set secure permissions
            chmod($filePath, 0644);
            
            return [
                'valid' => true,
                'filename' => $filename,
                'path' => $filePath,
                'size' => $file['size'],
                'original_name' => $file['name']
            ];
        } else {
            return ['valid' => false, 'error' => 'Failed to move uploaded file'];
        }
    }
    
    /**
     * Generate secure filename
     */
    private function generateSecureFilename($originalName, $extension) {
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = substr($basename, 0, 50); // Limit length
        
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
}

/**
 * Global validation helper functions
 */
function validateInput($data, $rules) {
    $validator = new InputValidator($data);
    
    foreach ($rules as $field => $fieldRules) {
        $validator->rule($field, $fieldRules);
    }
    
    $validator->validate();
    
    return [
        'valid' => empty($validator->getErrors()),
        'errors' => $validator->getErrors(),
        'data' => $validator->getSanitizedData()
    ];
}

function sanitizeOutput($input) {
    return XSSProtection::clean($input);
}

function csrfToken() {
    return CSRFProtection::getToken();
}

function csrfField() {
    return CSRFProtection::getHiddenField();
}

function validateCSRF() {
    return CSRFProtection::validateRequest();
}
?>