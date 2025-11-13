<?php
/**
 * Full-Text Search Manager
 * Handles advanced search capabilities across multiple tables and fields
 */

class FullTextSearchManager {
    private $db;
    private $searchConfigs = [];
    private $cacheEnabled = true;
    private $cacheTimeout = 300; // 5 minutes
    private $cache = [];
    
    public function __construct($database) {
        $this->db = $database;
        $this->initializeSearchConfigs();
    }
    
    /**
     * Initialize search configurations for different data types
     */
    private function initializeSearchConfigs() {
        $this->searchConfigs = [
            'users' => [
                'table' => 'users_with_institutions',
                'primary_key' => 'id',
                'search_fields' => [
                    'full_name' => ['weight' => 3, 'type' => 'fulltext'],
                    'email' => ['weight' => 2, 'type' => 'exact'],
                    'username' => ['weight' => 2, 'type' => 'exact'],
                    'institution_name' => ['weight' => 1, 'type' => 'like']
                ],
                'result_fields' => [
                    'id', 'full_name', 'email', 'role', 'institution_name', 'is_active'
                ],
                'filters' => ['role', 'is_active', 'institution_id']
            ],
            
            'enquiries' => [
                'table' => 'enquiries_with_assignee',
                'primary_key' => 'id',
                'search_fields' => [
                    'full_name' => ['weight' => 3, 'type' => 'fulltext'],
                    'phone' => ['weight' => 3, 'type' => 'exact'],
                    'email' => ['weight' => 2, 'type' => 'exact'],
                    'course_interest' => ['weight' => 2, 'type' => 'fulltext'],
                    'message' => ['weight' => 1, 'type' => 'fulltext']
                ],
                'result_fields' => [
                    'id', 'full_name', 'phone', 'email', 'course_interest', 'status', 'assigned_to_name'
                ],
                'filters' => ['status', 'assigned_to_user_id', 'priority']
            ],
            
            'students' => [
                'table' => 'students_with_institutions',
                'primary_key' => 'id',
                'search_fields' => [
                    'full_name' => ['weight' => 3, 'type' => 'fulltext'],
                    'student_id' => ['weight' => 3, 'type' => 'exact'],
                    'email' => ['weight' => 2, 'type' => 'exact'],
                    'phone' => ['weight' => 2, 'type' => 'exact'],
                    'course' => ['weight' => 2, 'type' => 'fulltext'],
                    'institution_name' => ['weight' => 1, 'type' => 'like']
                ],
                'result_fields' => [
                    'id', 'student_id', 'full_name', 'email', 'course', 'batch_year', 'institution_name', 'is_exam_eligible'
                ],
                'filters' => ['institution_id', 'course', 'batch_year', 'is_exam_eligible']
            ],
            
            'institutions' => [
                'table' => 'institution_statistics',
                'primary_key' => 'id',
                'search_fields' => [
                    'name' => ['weight' => 3, 'type' => 'fulltext'],
                    'code' => ['weight' => 2, 'type' => 'exact']
                ],
                'result_fields' => [
                    'id', 'name', 'code', 'total_students', 'total_faculty', 'is_active'
                ],
                'filters' => ['is_active']
            ],
            
            'certificates' => [
                'table' => 'certificate_requests_with_details',
                'primary_key' => 'id',
                'search_fields' => [
                    'student_name' => ['weight' => 3, 'type' => 'fulltext'],
                    'student_number' => ['weight' => 3, 'type' => 'exact'],
                    'institution_name' => ['weight' => 2, 'type' => 'like'],
                    'request_type' => ['weight' => 1, 'type' => 'exact']
                ],
                'result_fields' => [
                    'id', 'student_name', 'student_number', 'institution_name', 'request_type', 'status', 'requested_at'
                ],
                'filters' => ['status', 'institution_id', 'request_type']
            ],
            
            'marklists' => [
                'table' => 'marklists_with_students',
                'primary_key' => 'id',
                'search_fields' => [
                    'student_name' => ['weight' => 3, 'type' => 'fulltext'],
                    'student_number' => ['weight' => 3, 'type' => 'exact'],
                    'subject' => ['weight' => 2, 'type' => 'fulltext']
                ],
                'result_fields' => [
                    'id', 'student_name', 'student_number', 'subject', 'marks_obtained', 'total_marks', 'grade', 'exam_date'
                ],
                'filters' => ['student_id', 'subject', 'grade', 'pass_status']
            ],
            
            'call_logs' => [
                'table' => 'call_logs_with_enquiries',
                'primary_key' => 'id',
                'search_fields' => [
                    'enquiry_name' => ['weight' => 3, 'type' => 'fulltext'],
                    'phone' => ['weight' => 3, 'type' => 'exact'],
                    'notes' => ['weight' => 1, 'type' => 'fulltext']
                ],
                'result_fields' => [
                    'id', 'enquiry_name', 'phone', 'call_outcome', 'call_duration', 'call_timestamp', 'follow_up_required'
                ],
                'filters' => ['caller_user_id', 'call_outcome', 'follow_up_required']
            ]
        ];
    }
    
    /**
     * Perform advanced search across specified data types
     */
    public function search($query, $dataTypes = [], $filters = [], $options = []) {
        try {
            $query = trim($query);
            if (empty($query) || strlen($query) < 2) {
                return [
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters long'
                ];
            }
            
            // Default options
            $options = array_merge([
                'limit' => 50,
                'include_highlights' => true,
                'group_by_type' => true,
                'min_score' => 0.1
            ], $options);
            
            // If no data types specified, search all
            if (empty($dataTypes)) {
                $dataTypes = array_keys($this->searchConfigs);
            }
            
            // Generate cache key
            $cacheKey = $this->generateSearchCacheKey($query, $dataTypes, $filters, $options);
            
            // Check cache
            if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
                $cached = $this->cache[$cacheKey];
                if (time() - $cached['timestamp'] < $this->cacheTimeout) {
                    return $cached['data'];
                }
            }
            
            $results = [];
            $totalResults = 0;
            
            foreach ($dataTypes as $dataType) {
                if (!isset($this->searchConfigs[$dataType])) {
                    continue;
                }
                
                $typeResults = $this->searchDataType($query, $dataType, $filters, $options);
                if (!empty($typeResults)) {
                    if ($options['group_by_type']) {
                        $results[$dataType] = $typeResults;
                    } else {
                        $results = array_merge($results, $typeResults);
                    }
                    $totalResults += count($typeResults);
                }
            }
            
            // Sort results by relevance if not grouped
            if (!$options['group_by_type']) {
                usort($results, function($a, $b) {
                    return $b['_score'] <=> $a['_score'];
                });
                
                // Limit results
                $results = array_slice($results, 0, $options['limit']);
            }
            
            $response = [
                'success' => true,
                'query' => $query,
                'total_results' => $totalResults,
                'results' => $results,
                'data_types_searched' => $dataTypes,
                'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
            ];
            
            // Cache results
            if ($this->cacheEnabled) {
                $this->cache[$cacheKey] = [
                    'data' => $response,
                    'timestamp' => time()
                ];
            }
            
            return $response;
            
        } catch (Exception $e) {
            error_log("FullTextSearch Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Search within a specific data type
     */
    private function searchDataType($query, $dataType, $filters, $options) {
        $config = $this->searchConfigs[$dataType];
        $searchFields = $config['search_fields'];
        $resultFields = $config['result_fields'];
        
        // Build search conditions
        $searchConditions = [];
        $searchParams = [];
        $scoreCalculations = [];
        
        foreach ($searchFields as $field => $fieldConfig) {
            $weight = $fieldConfig['weight'];
            $type = $fieldConfig['type'];
            
            switch ($type) {
                case 'fulltext':
                    // Use MySQL MATCH AGAINST for full-text search
                    $searchConditions[] = "MATCH({$field}) AGAINST(? IN BOOLEAN MODE)";
                    $searchParams[] = $this->prepareFullTextQuery($query);
                    $scoreCalculations[] = "({$weight} * MATCH({$field}) AGAINST(? IN BOOLEAN MODE))";
                    $searchParams[] = $this->prepareFullTextQuery($query);
                    break;
                    
                case 'exact':
                    $searchConditions[] = "{$field} = ?";
                    $searchParams[] = $query;
                    $scoreCalculations[] = "({$weight} * CASE WHEN {$field} = ? THEN 1 ELSE 0 END)";
                    $searchParams[] = $query;
                    break;
                    
                case 'like':
                    $searchConditions[] = "{$field} LIKE ?";
                    $searchParams[] = "%{$query}%";
                    $scoreCalculations[] = "({$weight} * CASE WHEN {$field} LIKE ? THEN 0.5 ELSE 0 END)";
                    $searchParams[] = "%{$query}%";
                    break;
            }
        }
        
        if (empty($searchConditions)) {
            return [];
        }
        
        // Build the query
        $selectFields = implode(', ', $resultFields);
        $scoreCalculation = implode(' + ', $scoreCalculations);
        
        $sql = "SELECT {$selectFields}, 
                       ({$scoreCalculation}) as _score,
                       '{$dataType}' as _type
                FROM {$config['table']} 
                WHERE (" . implode(' OR ', $searchConditions) . ")";
        
        // Add filters
        $filterParams = [];
        if (!empty($filters)) {
            $filterConditions = [];
            foreach ($filters as $field => $value) {
                if (in_array($field, $config['filters'])) {
                    $filterConditions[] = "{$field} = ?";
                    $filterParams[] = $value;
                }
            }
            if (!empty($filterConditions)) {
                $sql .= " AND " . implode(' AND ', $filterConditions);
            }
        }
        
        // Add score filter and ordering
        $sql .= " HAVING _score >= ? ORDER BY _score DESC LIMIT ?";
        
        $allParams = array_merge($searchParams, $filterParams, [$options['min_score'], $options['limit']]);
        
        // Execute query
        $stmt = $this->db->prepare($sql);
        $stmt->execute($allParams);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add highlights if requested
        if ($options['include_highlights']) {
            foreach ($results as &$result) {
                $result['_highlights'] = $this->generateHighlights($result, $query, $searchFields);
            }
        }
        
        return $results;
    }
    
    /**
     * Prepare full-text query for MySQL MATCH AGAINST
     */
    private function prepareFullTextQuery($query) {
        // Split query into words
        $words = preg_split('/\s+/', trim($query));
        $words = array_filter($words, function($word) {
            return strlen($word) >= 2; // MySQL full-text minimum word length
        });
        
        if (empty($words)) {
            return $query;
        }
        
        // Add boolean operators for better matching
        $processedWords = [];
        foreach ($words as $word) {
            // Escape special characters
            $word = preg_replace('/[+\-><()~*\"@]/', '', $word);
            if (!empty($word)) {
                $processedWords[] = "+{$word}*"; // Require word with wildcard
            }
        }
        
        return implode(' ', $processedWords);
    }
    
    /**
     * Generate highlights for search results
     */
    private function generateHighlights($result, $query, $searchFields) {
        $highlights = [];
        $words = preg_split('/\s+/', strtolower(trim($query)));
        
        foreach ($searchFields as $field => $config) {
            if (isset($result[$field]) && !empty($result[$field])) {
                $text = $result[$field];
                $highlightedText = $this->highlightText($text, $words);
                
                if ($highlightedText !== $text) {
                    $highlights[$field] = $highlightedText;
                }
            }
        }
        
        return $highlights;
    }
    
    /**
     * Highlight search terms in text
     */
    private function highlightText($text, $words) {
        $highlightedText = $text;
        
        foreach ($words as $word) {
            if (strlen($word) >= 2) {
                $pattern = '/(' . preg_quote($word, '/') . ')/i';
                $highlightedText = preg_replace($pattern, '<mark>$1</mark>', $highlightedText);
            }
        }
        
        return $highlightedText;
    }
    
    /**
     * Get search suggestions based on partial query
     */
    public function getSuggestions($query, $dataTypes = [], $limit = 10) {
        try {
            $query = trim($query);
            if (strlen($query) < 2) {
                return ['success' => true, 'suggestions' => []];
            }
            
            if (empty($dataTypes)) {
                $dataTypes = array_keys($this->searchConfigs);
            }
            
            $suggestions = [];
            
            foreach ($dataTypes as $dataType) {
                if (!isset($this->searchConfigs[$dataType])) {
                    continue;
                }
                
                $config = $this->searchConfigs[$dataType];
                $typeSuggestions = $this->getDataTypeSuggestions($query, $dataType, $config, $limit);
                $suggestions = array_merge($suggestions, $typeSuggestions);
            }
            
            // Remove duplicates and sort by relevance
            $uniqueSuggestions = [];
            $seen = [];
            
            foreach ($suggestions as $suggestion) {
                $key = strtolower($suggestion['text']);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniqueSuggestions[] = $suggestion;
                }
            }
            
            // Sort by score and limit
            usort($uniqueSuggestions, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            return [
                'success' => true,
                'suggestions' => array_slice($uniqueSuggestions, 0, $limit)
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
     * Get suggestions for a specific data type
     */
    private function getDataTypeSuggestions($query, $dataType, $config, $limit) {
        $suggestions = [];
        
        foreach ($config['search_fields'] as $field => $fieldConfig) {
            $weight = $fieldConfig['weight'];
            
            $sql = "SELECT DISTINCT {$field} as text, 
                           '{$dataType}' as type,
                           '{$field}' as field,
                           {$weight} as score
                    FROM {$config['table']} 
                    WHERE {$field} LIKE ? 
                    AND {$field} IS NOT NULL 
                    AND {$field} != ''
                    ORDER BY {$field} 
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["%{$query}%", $limit]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $suggestions = array_merge($suggestions, $results);
        }
        
        return $suggestions;
    }
    
    /**
     * Generate cache key for search
     */
    private function generateSearchCacheKey($query, $dataTypes, $filters, $options) {
        $data = [
            'query' => $query,
            'types' => $dataTypes,
            'filters' => $filters,
            'options' => $options
        ];
        return 'search_' . md5(json_encode($data));
    }
    
    /**
     * Clear search cache
     */
    public function clearCache() {
        $this->cache = [];
        return $this;
    }
    
    /**
     * Get search statistics
     */
    public function getSearchStats() {
        $stats = [];
        
        foreach ($this->searchConfigs as $dataType => $config) {
            try {
                $sql = "SELECT COUNT(*) as total_records FROM {$config['table']}";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stats[$dataType] = [
                    'total_records' => intval($result['total_records']),
                    'searchable_fields' => count($config['search_fields']),
                    'table' => $config['table']
                ];
            } catch (Exception $e) {
                $stats[$dataType] = [
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $stats;
    }
}
?>