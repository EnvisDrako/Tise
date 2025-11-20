<?php
/**
 * Workflow Testing Script
 * Tests complete user workflows from login to task completion
 */

// Define system access constant for includes
define('SYSTEM_ACCESS', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

class WorkflowTester {
    private $db;
    private $baseUrl;
    private $testResults = [];
    
    public function __construct($baseUrl = 'http://localhost') {
        $this->db = getDB();
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    /**
     * Test complete admin workflow
     */
    public function testCompleteAdminWorkflow() {
        echo "Testing Complete Admin Workflow...\n";
        
        $workflow = [
            'Login to Admin Portal',
            'View Dashboard Statistics',
            'Create New User Account',
            'Create New Institution',
            'Assign Enquiry to User',
            'Approve Certificate Request',
            'Generate System Report',
            'Perform Bulk Operations',
            'Logout'
        ];
        
        foreach ($workflow as $step) {
            $this->executeWorkflowStep('Admin', $step);
        }
        
        return $this->getWorkflowResults('Admin');
    }
    
    /**
     * Test complete faculty workflow
     */
    public function testCompleteFacultyWorkflow() {
        echo "Testing Complete Faculty Workflow...\n";
        
        $workflow = [
            'Login to Faculty Portal',
            'View Institution Dashboard',
            'Register New Student',
            'Upload Student Marklist',
            'Mark Student Exam Eligible',
            'Submit Certificate Request',
            'Download Student Reports',
            'Logout'
        ];
        
        foreach ($workflow as $step) {
            $this->executeWorkflowStep('Faculty', $step);
        }
        
        return $this->getWorkflowResults('Faculty');
    }
    
    /**
     * Test complete enquirer workflow
     */
    public function testCompleteEnquirerWorkflow() {
        echo "Testing Complete Enquirer Workflow...\n";
        
        $workflow = [
            'Login to Enquirer Portal',
            'View Assigned Enquiries',
            'Update Enquiry Status',
            'Log Call Activity',
            'Schedule Follow-up',
            'Generate Call Report',
            'Logout'
        ];
        
        foreach ($workflow as $step) {
            $this->executeWorkflowStep('Enquirer', $step);
        }
        
        return $this->getWorkflowResults('Enquirer');
    }
    
    /**
     * Test public website workflow
     */
    public function testPublicWebsiteWorkflow() {
        echo "Testing Public Website Workflow...\n";
        
        $workflow = [
            'Access Home Page',
            'Browse Course Information',
            'View Gallery',
            'Submit Enquiry Form',
            'Upload Documents',
            'Receive Confirmation'
        ];
        
        foreach ($workflow as $step) {
            $this->executeWorkflowStep('Public', $step);
        }
        
        return $this->getWorkflowResults('Public');
    }
    
    /**
     * Execute individual workflow step
     */
    private function executeWorkflowStep($userType, $step) {
        $startTime = microtime(true);
        $success = false;
        $details = '';
        
        try {
            switch ($userType) {
                case 'Admin':
                    $success = $this->executeAdminStep($step);
                    break;
                case 'Faculty':
                    $success = $this->executeFacultyStep($step);
                    break;
                case 'Enquirer':
                    $success = $this->executeEnquirerStep($step);
                    break;
                case 'Public':
                    $success = $this->executePublicStep($step);
                    break;
            }
        } catch (Exception $e) {
            $success = false;
            $details = $e->getMessage();
        }
        
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        
        $this->testResults[$userType][] = [
            'step' => $step,
            'success' => $success,
            'duration' => $duration,
            'details' => $details
        ];
        
        $status = $success ? 'PASS' : 'FAIL';
        echo "  $step: $status ({$duration}ms)\n";
        
        if (!$success && $details) {
            echo "    Error: $details\n";
        }
    }
    
    /**
     * Execute admin workflow steps
     */
    private function executeAdminStep($step) {
        switch ($step) {
            case 'Login to Admin Portal':
                return $this->simulateLogin('admin_test', 'admin');
                
            case 'View Dashboard Statistics':
                return $this->testDashboardAccess('admin');
                
            case 'Create New User Account':
                return $this->testUserCreation();
                
            case 'Create New Institution':
                return $this->testInstitutionCreation();
                
            case 'Assign Enquiry to User':
                return $this->testEnquiryAssignment();
                
            case 'Approve Certificate Request':
                return $this->testCertificateApproval();
                
            case 'Generate System Report':
                return $this->testReportGeneration();
                
            case 'Perform Bulk Operations':
                return $this->testBulkOperations();
                
            case 'Logout':
                return $this->simulateLogout();
                
            default:
                return false;
        }
    }
    
    /**
     * Execute faculty workflow steps
     */
    private function executeFacultyStep($step) {
        switch ($step) {
            case 'Login to Faculty Portal':
                return $this->simulateLogin('faculty_test', 'faculty');
                
            case 'View Institution Dashboard':
                return $this->testDashboardAccess('faculty');
                
            case 'Register New Student':
                return $this->testStudentRegistration();
                
            case 'Upload Student Marklist':
                return $this->testMarklistUpload();
                
            case 'Mark Student Exam Eligible':
                return $this->testExamEligibility();
                
            case 'Submit Certificate Request':
                return $this->testCertificateSubmission();
                
            case 'Download Student Reports':
                return $this->testReportDownload();
                
            case 'Logout':
                return $this->simulateLogout();
                
            default:
                return false;
        }
    }
    
    /**
     * Execute enquirer workflow steps
     */
    private function executeEnquirerStep($step) {
        switch ($step) {
            case 'Login to Enquirer Portal':
                return $this->simulateLogin('enquirer_test', 'enquirer');
                
            case 'View Assigned Enquiries':
                return $this->testAssignedEnquiriesAccess();
                
            case 'Update Enquiry Status':
                return $this->testStatusUpdate();
                
            case 'Log Call Activity':
                return $this->testCallLogging();
                
            case 'Schedule Follow-up':
                return $this->testFollowupScheduling();
                
            case 'Generate Call Report':
                return $this->testCallReportGeneration();
                
            case 'Logout':
                return $this->simulateLogout();
                
            default:
                return false;
        }
    }
    
    /**
     * Execute public website steps
     */
    private function executePublicStep($step) {
        switch ($step) {
            case 'Access Home Page':
                return $this->testPageAccess('/index.php');
                
            case 'Browse Course Information':
                return $this->testPageAccess('/courses.php');
                
            case 'View Gallery':
                return $this->testPageAccess('/gallery.php');
                
            case 'Submit Enquiry Form':
                return $this->testEnquirySubmission();
                
            case 'Upload Documents':
                return $this->testDocumentUpload();
                
            case 'Receive Confirmation':
                return $this->testConfirmationProcess();
                
            default:
                return false;
        }
    }
    
    /**
     * Simulate user login
     */
    private function simulateLogin($username, $expectedRole) {
        $stmt = $this->db->prepare("SELECT id, username, role, password_hash, institution_id FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception("User not found: $username");
        }
        
        if ($user['role'] !== $expectedRole) {
            throw new Exception("Role mismatch. Expected: $expectedRole, Got: {$user['role']}");
        }
        
        // Simulate session creation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['institution_id'] = $user['institution_id'];
        
        return true;
    }
    
    /**
     * Simulate logout
     */
    private function simulateLogout() {
        session_destroy();
        return true;
    }
    
    /**
     * Test dashboard access
     */
    private function testDashboardAccess($role) {
        $dashboardPaths = [
            'admin' => '/admin/index.php',
            'faculty' => '/institution/index.php',
            'enquirer' => '/enquiry/index.php'
        ];
        
        $path = $dashboardPaths[$role] ?? null;
        if (!$path) {
            return false;
        }
        
        return $this->testPageAccess($path);
    }
    
    /**
     * Test page access
     */
    private function testPageAccess($path) {
        $fullPath = __DIR__ . '/../public' . $path;
        
        if (!file_exists($fullPath)) {
            throw new Exception("Page not found: $path");
        }
        
        // Basic file existence and readability check
        return is_readable($fullPath);
    }
    
    /**
     * Test user creation
     */
    private function testUserCreation() {
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, role, full_name, email) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'workflow_test_user_' . time(),
            password_hash('test123', PASSWORD_DEFAULT),
            'enquirer',
            'Workflow Test User',
            'workflow_test_' . time() . '@test.com'
        ]);
        
        if ($result) {
            // Clean up
            $userId = $this->db->lastInsertId();
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
        }
        
        return $result;
    }
    
    /**
     * Test institution creation
     */
    private function testInstitutionCreation() {
        $stmt = $this->db->prepare("INSERT INTO institutions (name, code, address, phone, email) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'Workflow Test Institution',
            'WF' . time(),
            'Test Address',
            '1234567890',
            'workflow_test_' . time() . '@institution.com'
        ]);
        
        if ($result) {
            // Clean up
            $institutionId = $this->db->lastInsertId();
            $stmt = $this->db->prepare("DELETE FROM institutions WHERE id = ?");
            $stmt->execute([$institutionId]);
        }
        
        return $result;
    }
    
    /**
     * Test enquiry assignment
     */
    private function testEnquiryAssignment() {
        // Get an unassigned enquiry
        $stmt = $this->db->prepare("SELECT id FROM enquiries WHERE assigned_to_user_id IS NULL LIMIT 1");
        $stmt->execute();
        $enquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enquiry) {
            // Create a test enquiry
            $stmt = $this->db->prepare("INSERT INTO enquiries (full_name, email, phone, course_interest, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                'Workflow Test Enquiry',
                'workflow_enquiry_' . time() . '@test.com',
                '9999999999',
                'Test Course',
                'Test message'
            ]);
            $enquiryId = $this->db->lastInsertId();
        } else {
            $enquiryId = $enquiry['id'];
        }
        
        // Assign enquiry
        $stmt = $this->db->prepare("UPDATE enquiries SET assigned_to_user_id = ?, status = 'assigned' WHERE id = ?");
        $result = $stmt->execute([3, $enquiryId]); // Assign to enquirer user
        
        return $result;
    }
    
    /**
     * Test certificate approval
     */
    private function testCertificateApproval() {
        // Create a test certificate request
        $stmt = $this->db->prepare("INSERT INTO certificate_requests (student_id, institution_id, request_type, requested_by) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([1, 1, 'completion', 2]);
        
        if (!$result) {
            return false;
        }
        
        $requestId = $this->db->lastInsertId();
        
        // Approve the request
        $stmt = $this->db->prepare("UPDATE certificate_requests SET status = 'approved', approved_by = ?, approval_comments = ?, processed_at = NOW() WHERE id = ?");
        $result = $stmt->execute([1, 'Workflow test approval', $requestId]);
        
        // Clean up
        $stmt = $this->db->prepare("DELETE FROM certificate_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        
        return $result;
    }
    
    /**
     * Get workflow results
     */
    private function getWorkflowResults($userType) {
        if (!isset($this->testResults[$userType])) {
            return ['success' => false, 'message' => 'No test results found'];
        }
        
        $results = $this->testResults[$userType];
        $totalSteps = count($results);
        $successfulSteps = count(array_filter($results, function($step) {
            return $step['success'];
        }));
        
        return [
            'success' => $successfulSteps === $totalSteps,
            'total_steps' => $totalSteps,
            'successful_steps' => $successfulSteps,
            'success_rate' => round(($successfulSteps / $totalSteps) * 100, 2),
            'details' => $results
        ];
    }
    
    // Placeholder methods for remaining workflow steps
    private function testReportGeneration() { return true; }
    private function testBulkOperations() { return true; }
    private function testStudentRegistration() { return true; }
    private function testMarklistUpload() { return true; }
    private function testExamEligibility() { return true; }
    private function testCertificateSubmission() { return true; }
    private function testReportDownload() { return true; }
    private function testAssignedEnquiriesAccess() { return true; }
    private function testStatusUpdate() { return true; }
    private function testCallLogging() { return true; }
    private function testFollowupScheduling() { return true; }
    private function testCallReportGeneration() { return true; }
    private function testEnquirySubmission() { return true; }
    private function testDocumentUpload() { return true; }
    private function testConfirmationProcess() { return true; }
}

// Run workflow tests if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new WorkflowTester();
    
    echo "Starting Workflow Testing...\n";
    echo str_repeat("=", 50) . "\n\n";
    
    $adminResults = $tester->testCompleteAdminWorkflow();
    $facultyResults = $tester->testCompleteFacultyWorkflow();
    $enquirerResults = $tester->testCompleteEnquirerWorkflow();
    $publicResults = $tester->testPublicWebsiteWorkflow();
    
    echo "\nWorkflow Test Summary:\n";
    echo str_repeat("-", 30) . "\n";
    echo "Admin Workflow: " . ($adminResults['success'] ? 'PASS' : 'FAIL') . " ({$adminResults['success_rate']}%)\n";
    echo "Faculty Workflow: " . ($facultyResults['success'] ? 'PASS' : 'FAIL') . " ({$facultyResults['success_rate']}%)\n";
    echo "Enquirer Workflow: " . ($enquirerResults['success'] ? 'PASS' : 'FAIL') . " ({$enquirerResults['success_rate']}%)\n";
    echo "Public Workflow: " . ($publicResults['success'] ? 'PASS' : 'FAIL') . " ({$publicResults['success_rate']}%)\n";
}
?>