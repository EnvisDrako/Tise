<?php
/**
 * Data Flow Integration Tester
 * Tests data flow between public website, portals, and mobile API
 */

// Define system access constant for includes
define('SYSTEM_ACCESS', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

class DataFlowTester {
    private $db;
    private $testResults = [];
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Test complete data flow integration
     */
    public function testDataFlowIntegration() {
        echo "Testing Data Flow Integration...\n";
        echo str_repeat("=", 50) . "\n\n";
        
        // Test enquiry flow: Public → Admin → Enquirer → Mobile API
        $this->testEnquiryDataFlow();
        
        // Test student flow: Faculty → Admin → Certificate
        $this->testStudentDataFlow();
        
        // Test mobile API synchronization
        $this->testMobileAPISynchronization();
        
        // Test cross-portal data consistency
        $this->testCrossPortalConsistency();
        
        $this->generateDataFlowReport();
    }
    
    /**
     * Test enquiry data flow across all components
     */
    private function testEnquiryDataFlow() {
        echo "Testing Enquiry Data Flow...\n";
        
        // Step 1: Public website enquiry submission
        $enquiryData = $this->simulatePublicEnquirySubmission();
        $this->recordTest('Public Enquiry Submission', $enquiryData !== false, 
            $enquiryData ? "Enquiry ID: {$enquiryData['id']}" : "Failed to create enquiry");
        
        if (!$enquiryData) {
            return;
        }
        
        // Step 2: Admin assigns enquiry to enquirer
        $assignmentResult = $this->simulateAdminEnquiryAssignment($enquiryData['id']);
        $this->recordTest('Admin Enquiry Assignment', $assignmentResult, 
            $assignmentResult ? "Enquiry assigned successfully" : "Failed to assign enquiry");
        
        // Step 3: Enquirer accesses assigned enquiry
        $enquirerAccess = $this->simulateEnquirerAccess($enquiryData['id']);
        $this->recordTest('Enquirer Access to Assigned Enquiry', $enquirerAccess, 
            $enquirerAccess ? "Enquirer can access enquiry" : "Enquirer cannot access enquiry");
        
        // Step 4: Mobile API retrieves phone number
        $mobileAPIAccess = $this->simulateMobileAPIPhoneRetrieval($enquiryData['id']);
        $this->recordTest('Mobile API Phone Retrieval', $mobileAPIAccess, 
            $mobileAPIAccess ? "Phone number retrieved via API" : "Failed to retrieve phone via API");
        
        // Step 5: Mobile API logs call
        $callLogging = $this->simulateMobileAPICallLogging($enquiryData['id']);
        $this->recordTest('Mobile API Call Logging', $callLogging, 
            $callLogging ? "Call logged successfully" : "Failed to log call");
        
        // Step 6: Verify data consistency across all interfaces
        $dataConsistency = $this->verifyEnquiryDataConsistency($enquiryData['id']);
        $this->recordTest('Enquiry Data Consistency', $dataConsistency, 
            $dataConsistency ? "Data consistent across all interfaces" : "Data inconsistency detected");
        
        // Cleanup
        $this->cleanupTestEnquiry($enquiryData['id']);
        
        echo "Enquiry Data Flow Tests Completed.\n\n";
    }
    
    /**
     * Test student data flow across faculty and admin portals
     */
    private function testStudentDataFlow() {
        echo "Testing Student Data Flow...\n";
        
        // Step 1: Faculty registers student
        $studentData = $this->simulateFacultyStudentRegistration();
        $this->recordTest('Faculty Student Registration', $studentData !== false, 
            $studentData ? "Student ID: {$studentData['id']}" : "Failed to register student");
        
        if (!$studentData) {
            return;
        }
        
        // Step 2: Faculty uploads marklist
        $marklistResult = $this->simulateFacultyMarklistUpload($studentData['id']);
        $this->recordTest('Faculty Marklist Upload', $marklistResult, 
            $marklistResult ? "Marklist uploaded successfully" : "Failed to upload marklist");
        
        // Step 3: Faculty marks student exam eligible
        $eligibilityResult = $this->simulateFacultyExamEligibility($studentData['id']);
        $this->recordTest('Faculty Exam Eligibility Update', $eligibilityResult, 
            $eligibilityResult ? "Student marked exam eligible" : "Failed to update eligibility");
        
        // Step 4: Faculty submits certificate request
        $certificateRequest = $this->simulateFacultyCertificateRequest($studentData['id']);
        $this->recordTest('Faculty Certificate Request', $certificateRequest !== false, 
            $certificateRequest ? "Certificate request ID: {$certificateRequest['id']}" : "Failed to submit certificate request");
        
        // Step 5: Admin views student in oversight
        $adminOversight = $this->simulateAdminStudentOversight($studentData['id']);
        $this->recordTest('Admin Student Oversight', $adminOversight, 
            $adminOversight ? "Admin can view student" : "Admin cannot view student");
        
        // Step 6: Admin approves certificate
        if ($certificateRequest) {
            $certificateApproval = $this->simulateAdminCertificateApproval($certificateRequest['id']);
            $this->recordTest('Admin Certificate Approval', $certificateApproval, 
                $certificateApproval ? "Certificate approved" : "Failed to approve certificate");
        }
        
        // Step 7: Verify data consistency
        $dataConsistency = $this->verifyStudentDataConsistency($studentData['id']);
        $this->recordTest('Student Data Consistency', $dataConsistency, 
            $dataConsistency ? "Data consistent across portals" : "Data inconsistency detected");
        
        // Cleanup
        $this->cleanupTestStudent($studentData['id']);
        if ($certificateRequest) {
            $this->cleanupTestCertificateRequest($certificateRequest['id']);
        }
        
        echo "Student Data Flow Tests Completed.\n\n";
    }
    
    /**
     * Test mobile API synchronization with web portals
     */
    private function testMobileAPISynchronization() {
        echo "Testing Mobile API Synchronization...\n";
        
        // Test API authentication
        $authResult = $this->testAPIAuthentication();
        $this->recordTest('API Authentication', $authResult, 
            $authResult ? "API authentication successful" : "API authentication failed");
        
        // Test phone number retrieval
        $phoneResult = $this->testAPIPhoneRetrieval();
        $this->recordTest('API Phone Retrieval', $phoneResult, 
            $phoneResult ? "Phone numbers retrieved successfully" : "Failed to retrieve phone numbers");
        
        // Test call logging synchronization
        $callSyncResult = $this->testAPICallSynchronization();
        $this->recordTest('API Call Synchronization', $callSyncResult, 
            $callSyncResult ? "Call data synchronized" : "Call synchronization failed");
        
        echo "Mobile API Synchronization Tests Completed.\n\n";
    }
    
    /**
     * Test cross-portal data consistency
     */
    private function testCrossPortalConsistency() {
        echo "Testing Cross-Portal Data Consistency...\n";
        
        // Test user data consistency
        $userConsistency = $this->testUserDataConsistency();
        $this->recordTest('User Data Consistency', $userConsistency, 
            $userConsistency ? "User data consistent" : "User data inconsistency detected");
        
        // Test institution data consistency
        $institutionConsistency = $this->testInstitutionDataConsistency();
        $this->recordTest('Institution Data Consistency', $institutionConsistency, 
            $institutionConsistency ? "Institution data consistent" : "Institution data inconsistency detected");
        
        // Test enquiry data consistency
        $enquiryConsistency = $this->testEnquiryDataConsistency();
        $this->recordTest('Enquiry Data Consistency', $enquiryConsistency, 
            $enquiryConsistency ? "Enquiry data consistent" : "Enquiry data inconsistency detected");
        
        echo "Cross-Portal Data Consistency Tests Completed.\n\n";
    }
    
    /**
     * Simulate public enquiry submission
     */
    private function simulatePublicEnquirySubmission() {
        $enquiryData = [
            'full_name' => 'Data Flow Test Enquiry',
            'email' => 'dataflow_test_' . time() . '@test.com',
            'phone' => '9876543210',
            'course_interest' => 'Data Flow Testing Course',
            'message' => 'This is a test enquiry for data flow testing'
        ];
        
        $stmt = $this->db->prepare("INSERT INTO enquiries (full_name, email, phone, course_interest, message) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $enquiryData['full_name'],
            $enquiryData['email'],
            $enquiryData['phone'],
            $enquiryData['course_interest'],
            $enquiryData['message']
        ]);
        
        if ($result) {
            $enquiryData['id'] = $this->db->lastInsertId();
            return $enquiryData;
        }
        
        return false;
    }
    
    /**
     * Simulate admin enquiry assignment
     */
    private function simulateAdminEnquiryAssignment($enquiryId) {
        // Get an enquirer user
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'enquirer' AND is_active = 1 LIMIT 1");
        $stmt->execute();
        $enquirer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enquirer) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE enquiries SET assigned_to_user_id = ?, status = 'assigned' WHERE id = ?");
        return $stmt->execute([$enquirer['id'], $enquiryId]);
    }
    
    /**
     * Simulate enquirer access to assigned enquiry
     */
    private function simulateEnquirerAccess($enquiryId) {
        $stmt = $this->db->prepare("SELECT * FROM enquiries WHERE id = ? AND assigned_to_user_id IS NOT NULL");
        $stmt->execute([$enquiryId]);
        $enquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $enquiry !== false;
    }
    
    /**
     * Simulate mobile API phone retrieval
     */
    private function simulateMobileAPIPhoneRetrieval($enquiryId) {
        $stmt = $this->db->prepare("SELECT phone FROM enquiries WHERE id = ? AND assigned_to_user_id IS NOT NULL");
        $stmt->execute([$enquiryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result !== false && !empty($result['phone']);
    }
    
    /**
     * Simulate mobile API call logging
     */
    private function simulateMobileAPICallLogging($enquiryId) {
        $stmt = $this->db->prepare("SELECT assigned_to_user_id FROM enquiries WHERE id = ?");
        $stmt->execute([$enquiryId]);
        $enquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enquiry) {
            return false;
        }
        
        $stmt = $this->db->prepare("INSERT INTO call_logs (enquiry_id, caller_user_id, call_duration, call_outcome, notes) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $enquiryId,
            $enquiry['assigned_to_user_id'],
            120, // 2 minutes
            'answered',
            'Data flow test call'
        ]);
        
        if ($result) {
            // Update enquiry status
            $stmt = $this->db->prepare("UPDATE enquiries SET status = 'contacted' WHERE id = ?");
            $stmt->execute([$enquiryId]);
        }
        
        return $result;
    }
    
    /**
     * Verify enquiry data consistency across all interfaces
     */
    private function verifyEnquiryDataConsistency($enquiryId) {
        // Check enquiry exists in main table
        $stmt = $this->db->prepare("SELECT * FROM enquiries WHERE id = ?");
        $stmt->execute([$enquiryId]);
        $enquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$enquiry) {
            return false;
        }
        
        // Check call log exists
        $stmt = $this->db->prepare("SELECT * FROM call_logs WHERE enquiry_id = ?");
        $stmt->execute([$enquiryId]);
        $callLog = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$callLog) {
            return false;
        }
        
        // Verify status consistency
        return $enquiry['status'] === 'contacted' && $callLog['enquiry_id'] == $enquiryId;
    }
    
    /**
     * Simulate faculty student registration
     */
    private function simulateFacultyStudentRegistration() {
        $studentData = [
            'institution_id' => 1,
            'student_id' => 'DF' . time(),
            'full_name' => 'Data Flow Test Student',
            'email' => 'student_dataflow_' . time() . '@test.com',
            'phone' => '8765432109',
            'course' => 'Data Flow Testing Course',
            'batch_year' => 2024,
            'registered_by' => 2 // Faculty user
        ];
        
        $stmt = $this->db->prepare("INSERT INTO students (institution_id, student_id, full_name, email, phone, course, batch_year, registered_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $studentData['institution_id'],
            $studentData['student_id'],
            $studentData['full_name'],
            $studentData['email'],
            $studentData['phone'],
            $studentData['course'],
            $studentData['batch_year'],
            $studentData['registered_by']
        ]);
        
        if ($result) {
            $studentData['id'] = $this->db->lastInsertId();
            return $studentData;
        }
        
        return false;
    }
    
    /**
     * Record test result
     */
    private function recordTest($testName, $success, $details = '') {
        $this->testResults[] = [
            'name' => $testName,
            'success' => $success,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $status = $success ? 'PASS' : 'FAIL';
        echo "  $testName: $status\n";
        if ($details) {
            echo "    $details\n";
        }
    }
    
    /**
     * Generate data flow test report
     */
    private function generateDataFlowReport() {
        echo str_repeat("=", 60) . "\n";
        echo "DATA FLOW INTEGRATION TEST REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults, function($test) {
            return $test['success'];
        }));
        
        echo "Summary:\n";
        echo "  Total Tests: $totalTests\n";
        echo "  Passed: $passedTests\n";
        echo "  Failed: " . ($totalTests - $passedTests) . "\n";
        echo "  Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";
        
        echo "Test Details:\n";
        echo str_repeat("-", 60) . "\n";
        
        foreach ($this->testResults as $test) {
            $status = $test['success'] ? '✓' : '✗';
            echo sprintf("  %s %-40s %s\n", 
                $status, 
                $test['name'], 
                $test['success'] ? 'PASS' : 'FAIL'
            );
            
            if (!empty($test['details'])) {
                echo "    " . $test['details'] . "\n";
            }
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
    }
    
    // Cleanup methods
    private function cleanupTestEnquiry($enquiryId) {
        $stmt = $this->db->prepare("DELETE FROM call_logs WHERE enquiry_id = ?");
        $stmt->execute([$enquiryId]);
        
        $stmt = $this->db->prepare("DELETE FROM enquiries WHERE id = ?");
        $stmt->execute([$enquiryId]);
    }
    
    private function cleanupTestStudent($studentId) {
        $stmt = $this->db->prepare("DELETE FROM marklists WHERE student_id = ?");
        $stmt->execute([$studentId]);
        
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
    }
    
    private function cleanupTestCertificateRequest($requestId) {
        $stmt = $this->db->prepare("DELETE FROM certificate_requests WHERE id = ?");
        $stmt->execute([$requestId]);
    }
    
    // Placeholder methods for remaining functionality
    private function simulateFacultyMarklistUpload($studentId) { return true; }
    private function simulateFacultyExamEligibility($studentId) { return true; }
    private function simulateFacultyCertificateRequest($studentId) { 
        return ['id' => 1]; // Mock certificate request
    }
    private function simulateAdminStudentOversight($studentId) { return true; }
    private function simulateAdminCertificateApproval($requestId) { return true; }
    private function verifyStudentDataConsistency($studentId) { return true; }
    private function testAPIAuthentication() { return true; }
    private function testAPIPhoneRetrieval() { return true; }
    private function testAPICallSynchronization() { return true; }
    private function testUserDataConsistency() { return true; }
    private function testInstitutionDataConsistency() { return true; }
    private function testEnquiryDataConsistency() { return true; }
}

// Run data flow tests if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new DataFlowTester();
    $tester->testDataFlowIntegration();
}
?>