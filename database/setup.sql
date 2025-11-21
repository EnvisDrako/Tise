-- Integrated Web Portal & CRM Database Setup Script
-- This script creates all tables, indexes, and relationships for the system

-- Create database (uncomment if needed)
CREATE DATABASE integrated_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE integrated_crm;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS call_logs;
DROP TABLE IF EXISTS certificate_requests;
DROP TABLE IF EXISTS marklists;
DROP TABLE IF EXISTS enquiries;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS institutions;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS social_share_analytics;
DROP TABLE IF EXISTS certificate_audit_log;
DROP TABLE IF EXISTS system_exports;
DROP TABLE IF EXISTS scheduled_exports;
DROP TABLE IF EXISTS data_retention_policies;
DROP TABLE IF EXISTS deletion_audit_log;
DROP TABLE IF EXISTS bulk_operations;
DROP TABLE IF EXISTS archived_data;

-- Create institutions table
CREATE TABLE institutions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    established_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'faculty', 'enquirer') NOT NULL,
    institution_id INT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL
);

-- Create students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    institution_id INT NOT NULL,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    course VARCHAR(100),
    batch_year YEAR,
    enrollment_date DATE NULL,   ----  new line 
    status ENUM('active', 'graduated', 'suspended', 'withdrawn') DEFAULT 'active',   ----  new line 
    photo_path VARCHAR(255),
    is_exam_eligible BOOLEAN DEFAULT FALSE,
    registered_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    FOREIGN KEY (registered_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- Create enquiries table
-- Replace the existing CREATE TABLE enquiries statement with this:
CREATE TABLE enquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    
    -- 👇 New Form Fields from your Public Page
    education_level VARCHAR(50) NULL,
    experience_level VARCHAR(50) NULL,
    preferred_schedule VARCHAR(50) NULL,
    
    course_interest VARCHAR(100) NULL,
    address TEXT NOT NULL,
    notes TEXT NULL,
    
    -- 👇 Management & Tracking Fields from your original plan
    status ENUM('new', 'assigned', 'contacted', 'interested', 'not_interested', 'enrolled', 'archived') DEFAULT 'new',
    assigned_to_user_id INT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    source VARCHAR(50) NULL, -- e.g., 'Website', 'Social Media'
    last_contact_date TIMESTAMP NULL,
    
    -- Metadata
    documents_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create marklists table
CREATE TABLE marklists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    marks_obtained INT NOT NULL,
    total_marks INT NOT NULL,
    grade VARCHAR(10),
    exam_date DATE,
    exam_type VARCHAR(50) DEFAULT 'regular',
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- Create certificate_requests table
CREATE TABLE certificate_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    institution_id INT NOT NULL,
    request_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    requested_by INT NOT NULL,
    approved_by INT NULL,
    approval_comments TEXT,
    rejection_reason TEXT,
    certificate_path VARCHAR(255) NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create call_logs table
CREATE TABLE call_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enquiry_id INT NOT NULL,
    caller_user_id INT NOT NULL,
    call_duration INT, -- in seconds
    call_outcome ENUM('answered', 'no_answer', 'busy', 'invalid', 'callback_requested') NOT NULL,
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE NULL,
    notes TEXT,
    call_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE CASCADE,
    FOREIGN KEY (caller_user_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- Create indexes for performance optimization

-- Users table indexes
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_institution ON users(institution_id);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_users_email ON users(email);

-- Students table indexes
CREATE INDEX idx_students_institution_course ON students(institution_id, course);
CREATE INDEX idx_students_batch_eligible ON students(batch_year, is_exam_eligible);
CREATE INDEX idx_students_institution_batch ON students(institution_id, batch_year);
CREATE INDEX idx_students_student_id ON students(student_id);

-- Enquiries table indexes
CREATE INDEX idx_enquiries_status_assigned ON enquiries(status, assigned_to_user_id);
CREATE INDEX idx_enquiries_created_status ON enquiries(created_at, status);
CREATE INDEX idx_enquiries_phone ON enquiries(phone);
CREATE INDEX idx_enquiries_priority_status ON enquiries(priority, status);
CREATE INDEX idx_enquiries_last_contact ON enquiries(last_contact_date);

-- Marklists table indexes
CREATE INDEX idx_marklists_student ON marklists(student_id);
CREATE INDEX idx_marklists_student_subject ON marklists(student_id, subject);
CREATE INDEX idx_marklists_exam_date ON marklists(exam_date);
CREATE INDEX idx_marklists_grade ON marklists(grade);

-- Certificate requests table indexes
CREATE INDEX idx_certificates_institution_status ON certificate_requests(institution_id, status);
CREATE INDEX idx_certificates_requested_at ON certificate_requests(requested_at);
CREATE INDEX idx_certificates_student_status ON certificate_requests(student_id, status);
CREATE INDEX idx_certificates_priority_status ON certificate_requests(priority, status);

-- Call logs table indexes
CREATE INDEX idx_call_logs_caller_timestamp ON call_logs(caller_user_id, call_timestamp);
CREATE INDEX idx_call_logs_enquiry ON call_logs(enquiry_id);
CREATE INDEX idx_call_logs_outcome ON call_logs(call_outcome);
CREATE INDEX idx_call_logs_follow_up ON call_logs(follow_up_required, follow_up_date);

-- Institutions table indexes
CREATE INDEX idx_institutions_code ON institutions(code);
CREATE INDEX idx_institutions_active ON institutions(is_active);

-- Full-text search indexes for better search performance
ALTER TABLE students ADD FULLTEXT(full_name, email, student_id);
ALTER TABLE enquiries ADD FULLTEXT(full_name, email, phone, course_interest);
ALTER TABLE institutions ADD FULLTEXT(name, code);

-- Insert default admin user (password: admin123 - should be changed in production)
INSERT INTO institutions (name, code, address, phone, email, established_date) VALUES
('System Administration', 'SYS_ADMIN', 'System Level', '0000000000', 'admin@system.com', '2024-01-01');

INSERT INTO users (username, password_hash, role, institution_id, full_name, email, phone) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 'System Administrator', 'admin@system.com', '0000000000');

UPDATE users SET password_hash = '$2y$10$GxY9y0vnsovwwc6Ks/kg8uW1Qu7VGI4r.DtdPCTweKexBxN04qAMm' WHERE username = 'admin';

-- Create a view for user management with institution details
CREATE VIEW user_details AS
SELECT 
    u.id,
    u.username,
    u.role,
    u.full_name,
    u.email,
    u.phone,
    u.is_active,
    u.created_at,
    u.last_login,
    i.name as institution_name,
    i.code as institution_code
FROM users u
LEFT JOIN institutions i ON u.institution_id = i.id;

-- Create a view for student details with institution info
CREATE VIEW student_details AS
SELECT 
    s.id,
    s.student_id,
    s.full_name,
    s.email,
    s.phone,
    s.course,
    s.batch_year,
    s.is_exam_eligible,
    s.created_at,
    i.name as institution_name,
    i.code as institution_code,
    u.full_name as registered_by_name
FROM students s
JOIN institutions i ON s.institution_id = i.id
JOIN users u ON s.registered_by = u.id;

-- Newsletter subscribers table for SEO and social media integration
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    source VARCHAR(100) DEFAULT 'website',
    INDEX idx_email (email),
    INDEX idx_subscribed_at (subscribed_at),
    INDEX idx_active (is_active)
);

CREATE TABLE social_share_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    platform VARCHAR(50) NOT NULL,
    shared_url TEXT NOT NULL,
    page_title VARCHAR(255),
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer TEXT,
    INDEX idx_platform (platform),
    INDEX idx_shared_at (shared_at),
    INDEX idx_ip_address (ip_address)
);
-- Certificate audit log table for tracking all certificate actions
CREATE TABLE certificate_audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    certificate_id INT NOT NULL,
    action ENUM('created', 'approved', 'rejected', 'downloaded', 'modified') NOT NULL,
    details TEXT,
    performed_by INT NOT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (certificate_id) REFERENCES certificate_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_certificate_id (certificate_id),
    INDEX idx_performed_by (performed_by),
    INDEX idx_performed_at (performed_at),
    INDEX idx_action (action)
);

-- System exports table for tracking all export operations
CREATE TABLE system_exports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_type VARCHAR(50) NOT NULL,
    format VARCHAR(20) NOT NULL,
    filters JSON,
    date_from DATE NULL,
    date_to DATE NULL,
    options JSON,
    status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    filename VARCHAR(255) NULL,
    file_path VARCHAR(500) NULL,
    file_size BIGINT NULL,
    record_count INT NULL,
    error_message TEXT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_exports_status (status),
    INDEX idx_exports_created (created_at),
    INDEX idx_exports_data_type (data_type),
    INDEX idx_exports_created_by (created_by)
);

-- Scheduled exports table for recurring export jobs
CREATE TABLE scheduled_exports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_type VARCHAR(50) NOT NULL,
    format VARCHAR(20) NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly') NOT NULL,
    run_time TIME NOT NULL,
    filters JSON,
    email_address VARCHAR(100) NOT NULL,
    compress BOOLEAN DEFAULT FALSE,
    next_run TIMESTAMP NOT NULL,
    last_run TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_scheduled_next_run (next_run),
    INDEX idx_scheduled_active (is_active),
    INDEX idx_scheduled_created_by (created_by)
);

-- Data retention policies table
CREATE TABLE data_retention_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_type VARCHAR(50) NOT NULL UNIQUE,
    retention_days INT NOT NULL,
    archive_before_delete BOOLEAN DEFAULT TRUE,
    archive_location VARCHAR(255) NULL,
    auto_delete BOOLEAN DEFAULT FALSE,
    confirmation_required BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_retention_data_type (data_type)
);

-- Deletion audit log table
CREATE TABLE deletion_audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    operation_type ENUM('delete', 'archive', 'restore') NOT NULL,
    data_type VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    record_data JSON,
    reason TEXT,
    performed_by INT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rollback_data JSON NULL,
    can_rollback BOOLEAN DEFAULT FALSE,
    rollback_expires_at TIMESTAMP NULL,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_audit_data_type (data_type),
    INDEX idx_audit_performed_by (performed_by),
    INDEX idx_audit_performed_at (performed_at),
    INDEX idx_audit_rollback (can_rollback, rollback_expires_at)
);

-- Bulk operations table for tracking long-running operations
CREATE TABLE bulk_operations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    operation_type ENUM('delete', 'archive', 'export', 'update') NOT NULL,
    data_type VARCHAR(50) NOT NULL,
    filters JSON,
    total_records INT NOT NULL,
    processed_records INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    result_data JSON NULL,
    error_message TEXT NULL,
    started_by INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    can_cancel BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (started_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_bulk_status (status),
    INDEX idx_bulk_started_by (started_by),
    INDEX idx_bulk_started_at (started_at),
    INDEX idx_bulk_data_type (data_type)
);

-- Archive storage table for compressed archived data
CREATE TABLE archived_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_type VARCHAR(50) NOT NULL,
    original_record_id INT NOT NULL,
    compressed_data LONGBLOB,
    metadata JSON,
    archived_by INT NOT NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    storage_location VARCHAR(255) NULL,
    compression_type VARCHAR(20) DEFAULT 'gzip',
    original_size BIGINT NULL,
    compressed_size BIGINT NULL,
    FOREIGN KEY (archived_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_archived_data_type (data_type),
    INDEX idx_archived_record_id (original_record_id),
    INDEX idx_archived_by (archived_by),
    INDEX idx_archived_at (archived_at),
    INDEX idx_archived_expires (expires_at)
);

-- Insert default data retention policies
INSERT INTO data_retention_policies (data_type, retention_days, archive_before_delete, auto_delete, confirmation_required, created_by) VALUES
('users', 2555, TRUE, FALSE, TRUE, 1),  -- 7 years for users
('students', 3650, TRUE, FALSE, TRUE, 1),  -- 10 years for students
('enquiries', 1095, TRUE, TRUE, FALSE, 1),  -- 3 years for enquiries
('call_logs', 730, TRUE, TRUE, FALSE, 1),  -- 2 years for call logs
('certificates', 3650, TRUE, FALSE, TRUE, 1),  -- 10 years for certificates
('marklists', 3650, TRUE, FALSE, TRUE, 1),  -- 10 years for marklists
('system_exports', 30, FALSE, TRUE, FALSE, 1);  -- 30 days for exports

-- Additional indexes for better performance on large datasets
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_users_role_institution ON users(role, institution_id);
CREATE INDEX idx_students_created_at ON students(created_at);
CREATE INDEX idx_enquiries_created_at ON enquiries(created_at);
CREATE INDEX idx_certificates_created_at ON certificate_requests(requested_at);
CREATE INDEX idx_call_logs_timestamp ON call_logs(call_timestamp);
CREATE INDEX idx_marklists_created_at ON marklists(created_at);

-- Create views for common data management queries
CREATE VIEW v_export_summary AS
SELECT 
    data_type,
    format,
    COUNT(*) as total_exports,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_exports,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_exports,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_exports,
    AVG(file_size) as avg_file_size,
    SUM(file_size) as total_file_size
FROM system_exports
GROUP BY data_type, format;

CREATE VIEW v_retention_status AS
SELECT 
    drp.data_type,
    drp.retention_days,
    drp.auto_delete,
    COUNT(CASE 
        WHEN drp.data_type = 'users' AND DATEDIFF(NOW(), u.created_at) > drp.retention_days THEN 1
        WHEN drp.data_type = 'students' AND DATEDIFF(NOW(), s.created_at) > drp.retention_days THEN 1
        WHEN drp.data_type = 'enquiries' AND DATEDIFF(NOW(), e.created_at) > drp.retention_days THEN 1
        WHEN drp.data_type = 'call_logs' AND DATEDIFF(NOW(), cl.call_timestamp) > drp.retention_days THEN 1
        WHEN drp.data_type = 'certificates' AND DATEDIFF(NOW(), cr.requested_at) > drp.retention_days THEN 1
        WHEN drp.data_type = 'marklists' AND DATEDIFF(NOW(), m.created_at) > drp.retention_days THEN 1
        WHEN drp.data_type = 'system_exports' AND DATEDIFF(NOW(), se.created_at) > drp.retention_days THEN 1
    END) as records_to_process
FROM data_retention_policies drp
LEFT JOIN users u ON drp.data_type = 'users'
LEFT JOIN students s ON drp.data_type = 'students'
LEFT JOIN enquiries e ON drp.data_type = 'enquiries'
LEFT JOIN call_logs cl ON drp.data_type = 'call_logs'
LEFT JOIN certificate_requests cr ON drp.data_type = 'certificates'
LEFT JOIN marklists m ON drp.data_type = 'marklists'
LEFT JOIN system_exports se ON drp.data_type = 'system_exports'
GROUP BY drp.data_type, drp.retention_days, drp.auto_delete;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE CleanupExpiredExports()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE export_id INT;
    DECLARE file_path VARCHAR(500);
    
    DECLARE cur CURSOR FOR 
        SELECT id, file_path 
        FROM system_exports 
        WHERE status = 'completed' 
        AND (expires_at IS NULL OR expires_at < NOW())
        AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    cleanup_loop: LOOP
        FETCH cur INTO export_id, file_path;
        IF done THEN
            LEAVE cleanup_loop;
        END IF;
        
        -- Delete the export record
        DELETE FROM system_exports WHERE id = export_id;
        
        -- Note: File deletion would need to be handled by the application
        -- as MySQL cannot directly delete files from the filesystem
        
    END LOOP;
    
    CLOSE cur;
END //

CREATE PROCEDURE GetDataForRetention(IN data_type_param VARCHAR(50))
BEGIN
    DECLARE retention_days_val INT;
    
    SELECT retention_days INTO retention_days_val
    FROM data_retention_policies 
    WHERE data_type = data_type_param;
    
    CASE data_type_param
        WHEN 'users' THEN
            SELECT * FROM users 
            WHERE DATEDIFF(NOW(), created_at) > retention_days_val
            AND is_active = 0;
            
        WHEN 'students' THEN
            SELECT * FROM students 
            WHERE DATEDIFF(NOW(), created_at) > retention_days_val;
            
        WHEN 'enquiries' THEN
            SELECT * FROM enquiries 
            WHERE DATEDIFF(NOW(), created_at) > retention_days_val
            AND status IN ('not_interested', 'enrolled');
            
        WHEN 'call_logs' THEN
            SELECT * FROM call_logs 
            WHERE DATEDIFF(NOW(), call_timestamp) > retention_days_val;
            
        WHEN 'certificates' THEN
            SELECT * FROM certificate_requests 
            WHERE DATEDIFF(NOW(), requested_at) > retention_days_val
            AND status IN ('approved', 'rejected');
            
        WHEN 'marklists' THEN
            SELECT * FROM marklists 
            WHERE DATEDIFF(NOW(), created_at) > retention_days_val;
            
        WHEN 'system_exports' THEN
            SELECT * FROM system_exports 
            WHERE DATEDIFF(NOW(), created_at) > retention_days_val;
    END CASE;
END //

DELIMITER ;

-- Create triggers for audit logging
DELIMITER //

CREATE TRIGGER audit_user_delete 
BEFORE DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO deletion_audit_log (operation_type, data_type, record_id, record_data, performed_by)
    VALUES ('delete', 'users', OLD.id, JSON_OBJECT(
        'username', OLD.username,
        'full_name', OLD.full_name,
        'email', OLD.email,
        'role', OLD.role,
        'institution_id', OLD.institution_id,
        'created_at', OLD.created_at
    ), @current_user_id);
END //

CREATE TRIGGER audit_student_delete 
BEFORE DELETE ON students
FOR EACH ROW
BEGIN
    INSERT INTO deletion_audit_log (operation_type, data_type, record_id, record_data, performed_by)
    VALUES ('delete', 'students', OLD.id, JSON_OBJECT(
        'student_id', OLD.student_id,
        'full_name', OLD.full_name,
        'email', OLD.email,
        'course', OLD.course,
        'batch_year', OLD.batch_year,
        'institution_id', OLD.institution_id,
        'created_at', OLD.created_at
    ), @current_user_id);
END //

CREATE TRIGGER audit_enquiry_delete 
BEFORE DELETE ON enquiries
FOR EACH ROW
BEGIN
    INSERT INTO deletion_audit_log (operation_type, data_type, record_id, record_data, performed_by)
    VALUES ('delete', 'enquiries', OLD.id, JSON_OBJECT(
        'full_name', OLD.full_name,
        'email', OLD.email,
        'phone', OLD.phone,
        'course_interest', OLD.course_interest,
        'status', OLD.status,
        'created_at', OLD.created_at
    ), @current_user_id);
END //

DELIMITER ;
