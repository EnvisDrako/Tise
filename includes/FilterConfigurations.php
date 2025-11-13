<?php
/**
 * Filter Configurations
 * Defines allowed filters, sorts, and search fields for each data type
 */

class FilterConfigurations {
    
    /**
     * Get filter configuration for users table (admin portal)
     */
    public static function getUsersConfig() {
        return [
            'allowed_filters' => [
                'role' => [
                    'type' => 'enum',
                    'values' => ['admin', 'faculty', 'enquirer'],
                    'operator' => '='
                ],
                'institution_id' => [
                    'type' => 'int',
                    'operator' => '='
                ],
                'is_active' => [
                    'type' => 'bool',
                    'operator' => '='
                ],
                'created_at' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'email_domain' => [
                    'type' => 'string',
                    'operator' => 'LIKE'
                ]
            ],
            'allowed_sorts' => [
                'id', 'full_name', 'email', 'role', 'created_at', 'institution_id'
            ],
            'allowed_search_fields' => [
                'full_name', 'email', 'username'
            ]
        ];
    }
    
    /**
     * Get filter configuration for enquiries table
     */
    public static function getEnquiriesConfig() {
        return [
            'allowed_filters' => [
                'status' => [
                    'type' => 'enum',
                    'values' => ['new', 'assigned', 'contacted', 'interested', 'not_interested', 'enrolled'],
                    'operator' => '='
                ],
                'assigned_to_user_id' => [
                    'type' => 'int',
                    'operator' => '='
                ],
                'course_interest' => [
                    'type' => 'string',
                    'operator' => 'LIKE'
                ],
                'created_at' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'source' => [
                    'type' => 'enum',
                    'values' => ['website', 'phone', 'email', 'referral', 'social_media'],
                    'operator' => '='
                ],
                'priority' => [
                    'type' => 'enum',
                    'values' => ['low', 'medium', 'high'],
                    'operator' => '='
                ],
                'follow_up_required' => [
                    'type' => 'bool',
                    'operator' => '='
                ],
                'unassigned' => [
                    'type' => 'bool',
                    'operator' => 'IS_NULL'
                ]
            ],
            'allowed_sorts' => [
                'id', 'full_name', 'phone', 'email', 'status', 'created_at', 'assigned_to_user_id'
            ],
            'allowed_search_fields' => [
                'full_name', 'phone', 'email', 'course_interest', 'message'
            ]
        ];
    }
    
    /**
     * Get filter configuration for students table
     */
    public static function getStudentsConfig() {
        return [
            'allowed_filters' => [
                'institution_id' => [
                    'type' => 'int',
                    'operator' => '='
                ],
                'course' => [
                    'type' => 'string',
                    'operator' => 'LIKE'
                ],
                'batch_year' => [
                    'type' => 'int',
                    'operator' => '='
                ],
                'is_exam_eligible' => [
                    'type' => 'bool',
                    'operator' => '='
                ],
                'created_at' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'age_range' => [
                    'type' => 'array',
                    'operator' => 'BETWEEN'
                ],
                'graduation_status' => [
                    'type' => 'enum',
                    'values' => ['enrolled', 'graduated', 'dropped'],
                    'operator' => '='
                ]
            ],
            'allowed_sorts' => [
                'id', 'student_id', 'full_name', 'course', 'batch_year', 'created_at', 'institution_id'
            ],
            'allowed_search_fields' => [
                'full_name', 'student_id', 'email', 'phone', 'course'
            ]
        ];
    }
    
    /**
     * Get filter configuration for institutions table
     */
    public static function getInstitutionsConfig() {
        return [
            'allowed_filters' => [
                'is_active' => [
                    'type' => 'bool',
                    'operator' => '='
                ],
                'established_date' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'student_count_range' => [
                    'type' => 'array',
                    'operator' => 'BETWEEN'
                ],
                'location' => [
                    'type' => 'string',
                    'operator' => 'LIKE'
                ]
            ],
            'allowed_sorts' => [
                'id', 'name', 'code', 'established_date', 'created_at'
            ],
            'allowed_search_fields' => [
                'name', 'code', 'address', 'phone', 'email'
            ]
        ];
    }
    
    /**
     * Get filter configuration for certificate requests table
     */
    public static function getCertificatesConfig() {
        return [
            'allowed_filters' => [
                'status' => [
                    'type' => 'enum',
                    'values' => ['pending', 'approved', 'rejected'],
                    'operator' => '='
                ],
                'institution_id' => [
                    'type' => 'int',
                    'operator' => '='
                ],
                'request_type' => [
                    'type' => 'enum',
                    'values' => ['completion', 'transcript', 'degree', 'diploma'],
                    'operator' => '='
                ],
                'requested_at' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'processed_at' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'priority' => [
                    'type' => 'enum',
                    'values' => ['low', 'medium', 'high', 'urgent'],
                    'operator' => '='
                ]
            ],
            'allowed_sorts' => [
                'id', 'requested_at', 'processed_at', 'status', 'institution_id', 'request_type'
            ],
            'allowed_search_fields' => [
                'student_name', 'student_id', 'institution_name', 'request_type'
            ]
        ];
    }
    
    /**
     * Get filter configuration for marklists table
     */
    public static function getMarklistsConfig() {
        return [
            'allowed_filters' => [
                'student_id' => [
                    'type' => 'int',
                    'operator' => '='
                ],
                'subject' => [
                    'type' => 'string',
                    'operator' => 'LIKE'
                ],
                'grade' => [
                    'type' => 'enum',
                    'values' => ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F'],
                    'operator' => '='
                ],
                'marks_range' => [
                    'type' => 'array',
                    'operator' => 'BETWEEN'
                ],
                'exam_date' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'pass_status' => [
                    'type' => 'bool',
                    'operator' => '='
                ]
            ],
            'allowed_sorts' => [
                'id', 'student_id', 'subject', 'marks_obtained', 'grade', 'exam_date'
            ],
            'allowed_search_fields' => [
                'student_name', 'student_id', 'subject'
            ]
        ];
    }
    
    /**
     * Get filter configuration for call logs table
     */
    public static function getCallLogsConfig() {
        return [
            'allowed_filters' => [
                'caller_user_id' => [
                    'type' => 'int',
                    'operator' => '='
                ],
                'call_outcome' => [
                    'type' => 'enum',
                    'values' => ['answered', 'no_answer', 'busy', 'invalid'],
                    'operator' => '='
                ],
                'call_duration_range' => [
                    'type' => 'array',
                    'operator' => 'BETWEEN'
                ],
                'follow_up_required' => [
                    'type' => 'bool',
                    'operator' => '='
                ],
                'call_timestamp' => [
                    'type' => 'string',
                    'operator' => 'DATE_RANGE'
                ],
                'enquiry_status' => [
                    'type' => 'enum',
                    'values' => ['new', 'assigned', 'contacted', 'interested', 'not_interested', 'enrolled'],
                    'operator' => '='
                ]
            ],
            'allowed_sorts' => [
                'id', 'call_timestamp', 'call_duration', 'call_outcome', 'enquiry_id'
            ],
            'allowed_search_fields' => [
                'enquiry_name', 'phone', 'notes'
            ]
        ];
    }
    
    /**
     * Get configuration by table name
     */
    public static function getConfigByTable($tableName) {
        switch ($tableName) {
            case 'users':
                return self::getUsersConfig();
            case 'enquiries':
                return self::getEnquiriesConfig();
            case 'students':
                return self::getStudentsConfig();
            case 'institutions':
                return self::getInstitutionsConfig();
            case 'certificate_requests':
                return self::getCertificatesConfig();
            case 'marklists':
                return self::getMarklistsConfig();
            case 'call_logs':
                return self::getCallLogsConfig();
            default:
                return [
                    'allowed_filters' => [],
                    'allowed_sorts' => ['id'],
                    'allowed_search_fields' => []
                ];
        }
    }
    
    /**
     * Create and configure DataFilterManager for a specific table
     */
    public static function createFilterManager($db, $tableName, $primaryKey = 'id') {
        $config = self::getConfigByTable($tableName);
        
        $manager = new DataFilterManager($db, $tableName, $primaryKey);
        $manager->setAllowedFilters($config['allowed_filters'])
                ->setAllowedSorts($config['allowed_sorts'])
                ->setAllowedSearchFields($config['allowed_search_fields']);
        
        return $manager;
    }
    
    /**
     * Get quick filter presets for different contexts
     */
    public static function getQuickFilters($context, $userRole = null, $institutionId = null) {
        $filters = [];
        
        switch ($context) {
            case 'admin_users':
                $filters = [
                    'active_users' => ['is_active' => true],
                    'inactive_users' => ['is_active' => false],
                    'admin_users' => ['role' => 'admin'],
                    'faculty_users' => ['role' => 'faculty'],
                    'enquirer_users' => ['role' => 'enquirer'],
                    'recent_registrations' => ['created_at' => 'last_week']
                ];
                break;
                
            case 'admin_enquiries':
                $filters = [
                    'new_enquiries' => ['status' => 'new'],
                    'assigned_enquiries' => ['status' => 'assigned'],
                    'interested_leads' => ['status' => 'interested'],
                    'unassigned_enquiries' => ['unassigned' => true],
                    'high_priority' => ['priority' => 'high'],
                    'todays_enquiries' => ['created_at' => 'today']
                ];
                break;
                
            case 'admin_students':
                $filters = [
                    'exam_eligible' => ['is_exam_eligible' => true],
                    'not_eligible' => ['is_exam_eligible' => false],
                    'current_batch' => ['batch_year' => date('Y')],
                    'recent_registrations' => ['created_at' => 'last_month']
                ];
                break;
                
            case 'admin_certificates':
                $filters = [
                    'pending_approval' => ['status' => 'pending'],
                    'approved_certificates' => ['status' => 'approved'],
                    'rejected_requests' => ['status' => 'rejected'],
                    'todays_requests' => ['requested_at' => 'today'],
                    'urgent_requests' => ['priority' => 'urgent']
                ];
                break;
                
            case 'faculty_students':
                $filters = [
                    'exam_eligible' => ['is_exam_eligible' => true],
                    'not_eligible' => ['is_exam_eligible' => false],
                    'current_batch' => ['batch_year' => date('Y')],
                    'recent_registrations' => ['created_at' => 'last_month']
                ];
                if ($institutionId) {
                    foreach ($filters as &$filter) {
                        $filter['institution_id'] = $institutionId;
                    }
                }
                break;
                
            case 'faculty_marklists':
                $filters = [
                    'passed_students' => ['pass_status' => true],
                    'failed_students' => ['pass_status' => false],
                    'recent_exams' => ['exam_date' => 'last_month'],
                    'high_grades' => ['grade' => ['A+', 'A']]
                ];
                break;
                
            case 'enquirer_enquiries':
                $filters = [
                    'new_leads' => ['status' => 'new'],
                    'interested_leads' => ['status' => 'interested'],
                    'follow_up_required' => ['follow_up_required' => true],
                    'high_priority' => ['priority' => 'high'],
                    'todays_assignments' => ['created_at' => 'today']
                ];
                break;
                
            case 'enquirer_calls':
                $filters = [
                    'successful_calls' => ['call_outcome' => 'answered'],
                    'missed_calls' => ['call_outcome' => 'no_answer'],
                    'follow_up_required' => ['follow_up_required' => true],
                    'todays_calls' => ['call_timestamp' => 'today'],
                    'long_calls' => ['call_duration_range' => [300, 9999]] // 5+ minutes
                ];
                break;
        }
        
        return $filters;
    }
}
?>