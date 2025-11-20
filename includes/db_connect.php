<?php
/**
 * Database connection management
 * Provides secure database connection with error handling
 */

// Prevent direct access
if (!defined('SYSTEM_ACCESS')) {
    die('Direct access not allowed');
}

// Include configuration
require_once __DIR__ . '/config.php';

if (!class_exists('DatabaseConnection')) {
    class DatabaseConnection {
        private static $instance = null;
        private $connection;
        private $host;
        private $db_name;
        private $username;
        private $password;
    
    private function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        
        $this->connect();
    }
    
    /**
     * Get singleton instance of database connection
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            // Add MySQL specific options if available
            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . DB_CHARSET;
            }
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            $this->logError("Database connection failed: " . $e->getMessage());
            
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    /**
     * Get the database connection
     */
    public function getConnection() {
        // Check if connection is still alive
        if ($this->connection === null) {
            $this->connect();
        }
        
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Query execution failed: " . $e->getMessage() . " | Query: " . $query);
            throw new Exception("Database operation failed");
        }
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch multiple rows
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get last insert ID
     */
    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        try {
            $query = "SELECT 1 FROM {$tableName} LIMIT 1";
            $this->connection->query($query);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Log database errors
     */
    private function logError($message) {
        if (LOG_ERRORS) {
            $logFile = ERROR_LOG_PATH . 'database_errors.log';
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    }
}

/**
 * Helper function to get database connection
 */
if (!function_exists('getDB')) {
    function getDB() {
        return DatabaseConnection::getInstance()->getConnection();
    }
}

/**
 * Helper function to execute query
 */
if (!function_exists('executeQuery')) {
    function executeQuery($query, $params = []) {
        return DatabaseConnection::getInstance()->execute($query, $params);
    }
}

/**
 * Helper function to fetch one row
 */
if (!function_exists('fetchOne')) {
    function fetchOne($query, $params = []) {
        return DatabaseConnection::getInstance()->fetchOne($query, $params);
    }
}

/**
 * Helper function to fetch all rows
 */
if (!function_exists('fetchAll')) {
    function fetchAll($query, $params = []) {
        return DatabaseConnection::getInstance()->fetchAll($query, $params);
    }
}
?>