<?php
/**
 * Data Filter Manager
 * Handles secure filtering, sorting, and pagination for database queries
 */

class DataFilterManager {
    private $db;
    private $allowedFilters = [];
    private $allowedSorts = [];
    private $allowedSearchFields = [];
    private $tableName;
    private $primaryKey = 'id';
    private $cache = [];
    private $cacheEnabled = true;
    private $cacheTimeout = 300; // 5 minutes
    
    public function __construct($database, $tableName, $primaryKey = 'id') {
        $this->db = $database;
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
    }
    
    /**
     * Set allowed filter fields with their types and validation rules
     */
    public function setAllowedFilters($filters) {
        $this->allowedFilters = $filters;
        return $this;
    }
    
    /**
     * Set allowed sort fields
     */
    public function setAllowedSorts($sorts) {
        $this->allowedSorts = $sorts;
        return $this;
    }
    
    /**
     * Set allowed search fields for full-text search
     */
    public function setAllowedSearchFields($fields) {
        $this->allowedSearchFields = $fields;
        return $this;
    }
    
    /**
     * Enable or disable caching
     */
    public function setCaching($enabled, $timeout = 300) {
        $this->cacheEnabled = $enabled;
        $this->cacheTimeout = $timeout;
        return $this;
    }
    
    /**
     * Process filter request and return filtered data
     */
    public function processRequest($requestData) {
        try {
            // Validate and sanitize input
            $filters = $this->validateFilters($requestData['filters'] ?? []);
            $search = $this->sanitizeSearch($requestData['search'] ?? '');
            $sort = $this->validateSort($requestData['sort'] ?? []);
            $page = max(1, intval($requestData['page'] ?? 1));
            $limit = $this->validateLimit($requestData['limit'] ?? 25);
            
            // Generate cache key
            $cacheKey = $this->generateCacheKey($filters, $search, $sort, $page, $limit);
            
            // Check cache first
            if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
                $cached = $this->cache[$cacheKey];
                if (time() - $cached['timestamp'] < $this->cacheTimeout) {
                    return $cached['data'];
                }
            }
            
            // Build query
            $queryResult = $this->buildQuery($filters, $search, $sort);
            $baseQuery = $queryResult['query'];
            $params = $queryResult['params'];
            
            // Get total count for pagination
            $countQuery = $this->buildCountQuery($filters, $search);
            $totalStmt = $this->db->prepare($countQuery['query']);
            $totalStmt->execute($countQuery['params']);
            $total = $totalStmt->fetchColumn();
            
            // Add pagination
            $offset = ($page - 1) * $limit;
            $baseQuery .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            // Execute query
            $stmt = $this->db->prepare($baseQuery);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate pagination info
            $totalPages = ceil($total / $limit);
            
            $result = [
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => intval($total),
                    'totalPages' => $totalPages,
                    'hasNext' => $page < $totalPages,
                    'hasPrev' => $page > 1
                ],
                'filters_applied' => $filters,
                'search_applied' => $search,
                'sort_applied' => $sort
            ];
            
            // Cache result
            if ($this->cacheEnabled) {
                $this->cache[$cacheKey] = [
                    'data' => $result,
                    'timestamp' => time()
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("DataFilterManager Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate and sanitize filters
     */
    private function validateFilters($filters) {
        $validatedFilters = [];
        
        foreach ($filters as $field => $value) {
            if (!isset($this->allowedFilters[$field])) {
                continue; // Skip unknown filters
            }
            
            $filterConfig = $this->allowedFilters[$field];
            $validatedValue = $this->validateFilterValue($value, $filterConfig);
            
            if ($validatedValue !== null) {
                $validatedFilters[$field] = $validatedValue;
            }
        }
        
        return $validatedFilters;
    }
    
    /**
     * Validate individual filter value
     */
    private function validateFilterValue($value, $config) {
        $type = $config['type'] ?? 'string';
        
        switch ($type) {
            case 'int':
                return filter_var($value, FILTER_VALIDATE_INT);
                
            case 'float':
                return filter_var($value, FILTER_VALIDATE_FLOAT);
                
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                
            case 'date':
                $date = DateTime::createFromFormat('Y-m-d', $value);
                return $date && $date->format('Y-m-d') === $value ? $value : null;
                
            case 'datetime':
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                return $date && $date->format('Y-m-d H:i:s') === $value ? $value : null;
                
            case 'enum':
                $allowedValues = $config['values'] ?? [];
                return in_array($value, $allowedValues) ? $value : null;
                
            case 'array':
                if (!is_array($value)) return null;
                $allowedValues = $config['values'] ?? [];
                $filtered = array_intersect($value, $allowedValues);
                return !empty($filtered) ? $filtered : null;
                
            case 'string':
            default:
                $sanitized = filter_var($value, FILTER_SANITIZE_STRING);
                $maxLength = $config['max_length'] ?? 255;
                return strlen($sanitized) <= $maxLength ? $sanitized : null;
        }
    }
    
    /**
     * Sanitize search term
     */
    private function sanitizeSearch($search) {
        $sanitized = trim(filter_var($search, FILTER_SANITIZE_STRING));
        return strlen($sanitized) > 0 && strlen($sanitized) <= 255 ? $sanitized : '';
    }
    
    /**
     * Validate sort configuration
     */
    private function validateSort($sort) {
        if (empty($sort['column']) || !in_array($sort['column'], $this->allowedSorts)) {
            return ['column' => null, 'direction' => 'asc'];
        }
        
        $direction = strtolower($sort['direction'] ?? 'asc');
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        
        return [
            'column' => $sort['column'],
            'direction' => $direction
        ];
    }
    
    /**
     * Validate pagination limit
     */
    private function validateLimit($limit) {
        $limit = intval($limit);
        return max(1, min(100, $limit)); // Between 1 and 100
    }
    
    /**
     * Build main query with filters and search
     */
    private function buildQuery($filters, $search, $sort) {
        $baseQuery = "SELECT * FROM {$this->tableName}";
        $whereConditions = [];
        $params = [];
        
        // Add filter conditions
        foreach ($filters as $field => $value) {
            $filterConfig = $this->allowedFilters[$field];
            $condition = $this->buildFilterCondition($field, $value, $filterConfig, $params);
            if ($condition) {
                $whereConditions[] = $condition;
            }
        }
        
        // Add search conditions
        if (!empty($search) && !empty($this->allowedSearchFields)) {
            $searchConditions = [];
            foreach ($this->allowedSearchFields as $field) {
                $searchConditions[] = "{$field} LIKE ?";
                $params[] = "%{$search}%";
            }
            if (!empty($searchConditions)) {
                $whereConditions[] = "(" . implode(" OR ", $searchConditions) . ")";
            }
        }
        
        // Add WHERE clause
        if (!empty($whereConditions)) {
            $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Add sorting
        if ($sort['column']) {
            $baseQuery .= " ORDER BY {$sort['column']} {$sort['direction']}";
        }
        
        return ['query' => $baseQuery, 'params' => $params];
    }
    
    /**
     * Build count query for pagination
     */
    private function buildCountQuery($filters, $search) {
        $baseQuery = "SELECT COUNT(*) FROM {$this->tableName}";
        $whereConditions = [];
        $params = [];
        
        // Add filter conditions (same as main query)
        foreach ($filters as $field => $value) {
            $filterConfig = $this->allowedFilters[$field];
            $condition = $this->buildFilterCondition($field, $value, $filterConfig, $params);
            if ($condition) {
                $whereConditions[] = $condition;
            }
        }
        
        // Add search conditions (same as main query)
        if (!empty($search) && !empty($this->allowedSearchFields)) {
            $searchConditions = [];
            foreach ($this->allowedSearchFields as $field) {
                $searchConditions[] = "{$field} LIKE ?";
                $params[] = "%{$search}%";
            }
            if (!empty($searchConditions)) {
                $whereConditions[] = "(" . implode(" OR ", $searchConditions) . ")";
            }
        }
        
        // Add WHERE clause
        if (!empty($whereConditions)) {
            $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        return ['query' => $baseQuery, 'params' => $params];
    }
    
    /**
     * Build individual filter condition
     */
    private function buildFilterCondition($field, $value, $config, &$params) {
        $type = $config['type'] ?? 'string';
        $operator = $config['operator'] ?? '=';
        
        switch ($operator) {
            case 'LIKE':
                $params[] = "%{$value}%";
                return "{$field} LIKE ?";
                
            case 'IN':
                if (is_array($value) && !empty($value)) {
                    $placeholders = str_repeat('?,', count($value) - 1) . '?';
                    $params = array_merge($params, $value);
                    return "{$field} IN ({$placeholders})";
                }
                return null;
                
            case 'BETWEEN':
                if (is_array($value) && count($value) === 2) {
                    $params[] = $value[0];
                    $params[] = $value[1];
                    return "{$field} BETWEEN ? AND ?";
                }
                return null;
                
            case 'DATE_RANGE':
                if ($value === 'today') {
                    $params[] = date('Y-m-d');
                    $params[] = date('Y-m-d 23:59:59');
                    return "{$field} BETWEEN ? AND ?";
                } elseif ($value === 'last_week') {
                    $params[] = date('Y-m-d', strtotime('-1 week'));
                    $params[] = date('Y-m-d 23:59:59');
                    return "{$field} BETWEEN ? AND ?";
                } elseif ($value === 'last_month') {
                    $params[] = date('Y-m-d', strtotime('-1 month'));
                    $params[] = date('Y-m-d 23:59:59');
                    return "{$field} BETWEEN ? AND ?";
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $params[] = $value;
                    $params[] = $value . ' 23:59:59';
                    return "{$field} BETWEEN ? AND ?";
                }
                return null;
                
            case 'IS_NULL':
                return $value ? "{$field} IS NULL" : "{$field} IS NOT NULL";
                
            case '>=':
            case '<=':
            case '>':
            case '<':
            case '!=':
            case '=':
            default:
                $params[] = $value;
                return "{$field} {$operator} ?";
        }
    }
    
    /**
     * Generate cache key
     */
    private function generateCacheKey($filters, $search, $sort, $page, $limit) {
        $data = [
            'table' => $this->tableName,
            'filters' => $filters,
            'search' => $search,
            'sort' => $sort,
            'page' => $page,
            'limit' => $limit
        ];
        return md5(json_encode($data));
    }
    
    /**
     * Get search suggestions
     */
    public function getSearchSuggestions($term, $limit = 10) {
        if (empty($term) || empty($this->allowedSearchFields)) {
            return ['success' => true, 'data' => []];
        }
        
        try {
            $suggestions = [];
            $term = $this->sanitizeSearch($term);
            
            foreach ($this->allowedSearchFields as $field) {
                $query = "SELECT DISTINCT {$field} as value, {$field} as label 
                         FROM {$this->tableName} 
                         WHERE {$field} LIKE ? 
                         AND {$field} IS NOT NULL 
                         AND {$field} != ''
                         ORDER BY {$field} 
                         LIMIT ?";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute(["%{$term}%", $limit]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $suggestions = array_merge($suggestions, $results);
            }
            
            // Remove duplicates and limit results
            $unique = [];
            $seen = [];
            foreach ($suggestions as $suggestion) {
                $key = strtolower($suggestion['value']);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $unique[] = $suggestion;
                }
            }
            
            return [
                'success' => true,
                'data' => array_slice($unique, 0, $limit)
            ];
            
        } catch (Exception $e) {
            error_log("Search suggestions error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get suggestions'
            ];
        }
    }
    
    /**
     * Export filtered data
     */
    public function exportData($requestData, $format = 'csv') {
        try {
            // Get all data without pagination
            $requestData['page'] = 1;
            $requestData['limit'] = 999999; // Large number to get all results
            
            $result = $this->processRequest($requestData);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            $data = $result['data'];
            
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($data);
                case 'excel':
                    return $this->exportToExcel($data);
                case 'pdf':
                    return $this->exportToPdf($data);
                default:
                    throw new Exception('Unsupported export format');
            }
            
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Export to CSV format
     */
    private function exportToCsv($data) {
        if (empty($data)) {
            return ['success' => false, 'message' => 'No data to export'];
        }
        
        $filename = $this->tableName . '_export_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        
        $file = fopen($filepath, 'w');
        
        // Write headers
        fputcsv($file, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'mimetype' => 'text/csv'
        ];
    }
    
    /**
     * Export to Excel format (requires PhpSpreadsheet)
     */
    private function exportToExcel($data) {
        // This would require PhpSpreadsheet library
        // For now, return CSV as fallback
        return $this->exportToCsv($data);
    }
    
    /**
     * Export to PDF format (requires TCPDF or similar)
     */
    private function exportToPdf($data) {
        // This would require TCPDF or similar library
        // For now, return CSV as fallback
        return $this->exportToCsv($data);
    }
    
    /**
     * Clear cache
     */
    public function clearCache() {
        $this->cache = [];
        return $this;
    }
}
?>