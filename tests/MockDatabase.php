<?php
/**
 * Mock Database for Testing
 * Simulates database operations for integration testing
 */

class MockDatabase {
    private $data = [];
    private $lastInsertId = 1;
    
    public function __construct() {
        $this->initializeMockData();
    }
    
    /**
     * Initialize mock data
     */
    private function initializeMockData() {
        $this->data['users'] = [
            [
                'id' => 1,
                'username' => 'admin_test',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'full_name' => 'Test Admin',
                'email' => 'admin@test.com',
                'phone' => '1234567890',
                'is_active' => 1,
                'institution_id' => null
            ],
            [
                'id' => 2,
                'username' => 'faculty_test',
                'password_hash' => password_hash('faculty123', PASSWORD_DEFAULT),
                'role' => 'faculty',
                'full_name' => 'Test Faculty',
                'email' => 'faculty@test.com',
                'phone' => '1234567891',
                'is_active' => 1,
                'institution_id' => 1
            ],
            [
                'id' => 3,
                'username' => 'enquirer_test',
                'password_hash' => password_hash('enquirer123', PASSWORD_DEFAULT),
                'role' => 'enquirer',
                'full_name' => 'Test Enquirer',
                'email' => 'enquirer@test.com',
                'phone' => '1234567892',
                'is_active' => 1,
                'institution_id' => null
            ]
        ];
        
        $this->data['institutions'] = [
            [
                'id' => 1,
                'name' => 'Test Institution 1',
                'code' => 'TEST001',
                'address' => 'Test Address 1',
                'phone' => '1111111111',
                'email' => 'test1@institution.com',
                'is_active' => 1
            ]
        ];
        
        $this->data['enquiries'] = [
            [
                'id' => 1,
                'full_name' => 'Test Enquiry 1',
                'email' => 'enquiry1@test.com',
                'phone' => '9999999991',
                'course_interest' => 'Web Development',
                'message' => 'Test enquiry message 1',
                'status' => 'new',
                'assigned_to_user_id' => null
            ]
        ];
        
        $this->data['students'] = [
            [
                'id' => 1,
                'institution_id' => 1,
                'student_id' => 'STU001',
                'full_name' => 'Test Student 1',
                'email' => 'student1@test.com',
                'phone' => '8888888881',
                'course' => 'Web Development',
                'batch_year' => 2024,
                'is_exam_eligible' => 0,
                'registered_by' => 2
            ]
        ];
        
        $this->data['certificate_requests'] = [];
        $this->data['call_logs'] = [];
        $this->data['marklists'] = [];
    }
    
    /**
     * Simulate prepared statement
     */
    public function prepare($query) {
        return new MockStatement($this, $query);
    }
    
    /**
     * Execute query
     */
    public function query($query) {
        // Simple query execution for basic checks
        return new MockStatement($this, $query);
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->lastInsertId;
    }
    
    /**
     * Get mock data
     */
    public function getData($table) {
        return isset($this->data[$table]) ? $this->data[$table] : [];
    }
    
    /**
     * Insert data
     */
    public function insertData($table, $data) {
        if (!isset($this->data[$table])) {
            $this->data[$table] = [];
        }
        
        $data['id'] = ++$this->lastInsertId;
        $this->data[$table][] = $data;
        
        return true;
    }
    
    /**
     * Update data
     */
    public function updateData($table, $id, $data) {
        if (!isset($this->data[$table])) {
            return false;
        }
        
        foreach ($this->data[$table] as &$row) {
            if ($row['id'] == $id) {
                $row = array_merge($row, $data);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Delete data
     */
    public function deleteData($table, $id) {
        if (!isset($this->data[$table])) {
            return false;
        }
        
        foreach ($this->data[$table] as $key => $row) {
            if ($row['id'] == $id) {
                unset($this->data[$table][$key]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Find data by criteria
     */
    public function findData($table, $criteria) {
        if (!isset($this->data[$table])) {
            return [];
        }
        
        $results = [];
        foreach ($this->data[$table] as $row) {
            $match = true;
            foreach ($criteria as $field => $value) {
                if (!isset($row[$field]) || $row[$field] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = $row;
            }
        }
        
        return $results;
    }
}

class MockStatement {
    private $db;
    private $query;
    private $results = [];
    
    public function __construct($db, $query) {
        $this->db = $db;
        $this->query = $query;
    }
    
    /**
     * Execute statement with parameters
     */
    public function execute($params = []) {
        // Parse basic SQL operations
        $query = strtolower(trim($this->query));
        
        if (strpos($query, 'select') === 0) {
            $this->executeSelect($params);
        } elseif (strpos($query, 'insert') === 0) {
            $this->executeInsert($params);
        } elseif (strpos($query, 'update') === 0) {
            $this->executeUpdate($params);
        } elseif (strpos($query, 'delete') === 0) {
            $this->executeDelete($params);
        }
        
        return true;
    }
    
    /**
     * Fetch single row
     */
    public function fetch($mode = null) {
        return !empty($this->results) ? array_shift($this->results) : false;
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($mode = null) {
        return $this->results;
    }
    
    /**
     * Execute SELECT query
     */
    private function executeSelect($params) {
        // Simple table extraction from query
        if (preg_match('/from\s+(\w+)/i', $this->query, $matches)) {
            $table = $matches[1];
            $this->results = $this->db->getData($table);
            
            // Apply basic WHERE conditions for common patterns
            if (strpos($this->query, 'where') !== false) {
                $this->applyWhereConditions($params);
            }
        }
    }
    
    /**
     * Execute INSERT query
     */
    private function executeInsert($params) {
        if (preg_match('/insert\s+into\s+(\w+)/i', $this->query, $matches)) {
            $table = $matches[1];
            
            // Extract column names and create data array
            $data = [];
            if (preg_match('/\(([^)]+)\)\s+values/i', $this->query, $colMatches)) {
                $columns = array_map('trim', explode(',', $colMatches[1]));
                for ($i = 0; $i < count($params) && $i < count($columns); $i++) {
                    $data[$columns[$i]] = $params[$i];
                }
            }
            
            $this->db->insertData($table, $data);
        }
    }
    
    /**
     * Execute UPDATE query
     */
    private function executeUpdate($params) {
        if (preg_match('/update\s+(\w+)/i', $this->query, $matches)) {
            $table = $matches[1];
            
            // For simplicity, assume first parameter is the ID for WHERE clause
            if (!empty($params)) {
                $id = end($params); // Last parameter is usually the ID
                $data = array_slice($params, 0, -1); // All but last parameter
                
                // This is a simplified update - in real implementation would parse SET clause
                $updateData = ['updated' => true];
                $this->db->updateData($table, $id, $updateData);
            }
        }
    }
    
    /**
     * Execute DELETE query
     */
    private function executeDelete($params) {
        if (preg_match('/delete\s+from\s+(\w+)/i', $this->query, $matches)) {
            $table = $matches[1];
            
            if (!empty($params)) {
                $id = $params[0];
                $this->db->deleteData($table, $id);
            }
        }
    }
    
    /**
     * Apply basic WHERE conditions
     */
    private function applyWhereConditions($params) {
        // This is a simplified implementation
        // In a real mock, you'd parse the WHERE clause properly
        if (!empty($params)) {
            $filteredResults = [];
            foreach ($this->results as $row) {
                // Simple matching logic - this would be more sophisticated in practice
                $match = true;
                foreach ($row as $field => $value) {
                    if (in_array($value, $params)) {
                        $filteredResults[] = $row;
                        break;
                    }
                }
            }
            $this->results = $filteredResults;
        }
    }
}

/**
 * Mock database helper function
 */
function getMockDB() {
    static $mockDb = null;
    if ($mockDb === null) {
        $mockDb = new MockDatabase();
    }
    return $mockDb;
}
?>