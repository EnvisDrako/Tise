-- Database Views for Advanced Filtering
-- These views provide optimized queries with joins for the filtering system

-- Users with institution information (for admin portal)
CREATE OR REPLACE VIEW users_with_institutions AS
SELECT 
    u.id,
    u.username,
    u.full_name,
    u.email,
    u.phone,
    u.role,
    u.is_active,
    u.created_at,
    u.institution_id,
    i.name as institution_name,
    i.code as institution_code
FROM users u
LEFT JOIN institutions i ON u.institution_id = i.id;

-- Enquiries with assigned user information
CREATE OR REPLACE VIEW enquiries_with_assignee AS
SELECT 
    e.id,
    e.full_name,
    e.email,
    e.phone,
    e.course_interest,
    e.message,
    e.status,
    e.assigned_to_user_id,
    e.documents_path,
    e.created_at,
    u.full_name as assigned_to_name,
    u.email as assigned_to_email,
    CASE 
        WHEN e.status = 'interested' THEN 'high'
        WHEN e.status = 'contacted' THEN 'medium'
        WHEN e.status = 'new' THEN 'medium'
        ELSE 'low'
    END as priority,
    CASE 
        WHEN e.status IN ('contacted', 'interested') AND 
             DATEDIFF(NOW(), e.created_at) > 3 THEN 1
        ELSE 0
    END as follow_up_required,
    (SELECT MAX(call_timestamp) FROM call_logs cl WHERE cl.enquiry_id = e.id) as last_contact
FROM enquiries e
LEFT JOIN users u ON e.assigned_to_user_id = u.id;

-- Students with institution information
CREATE OR REPLACE VIEW students_with_institutions AS
SELECT 
    s.id,
    s.student_id,
    s.full_name,
    s.email,
    s.phone,
    s.address,
    s.course,
    s.batch_year,
    s.photo_path,
    s.is_exam_eligible,
    s.created_at,
    s.institution_id,
    s.registered_by,
    i.name as institution_name,
    i.code as institution_code,
    u.full_name as registered_by_name,
    YEAR(CURDATE()) - s.batch_year as years_since_batch,
    CASE 
        WHEN s.is_exam_eligible = 1 THEN 'eligible'
        ELSE 'not_eligible'
    END as eligibility_status
FROM students s
LEFT JOIN institutions i ON s.institution_id = i.id
LEFT JOIN users u ON s.registered_by = u.id;

-- Certificate requests with detailed information
CREATE OR REPLACE VIEW certificate_requests_with_details AS
SELECT 
    cr.id,
    cr.request_type,
    cr.status,
    cr.requested_at,
    cr.processed_at,
    cr.approval_comments,
    cr.certificate_path,
    cr.student_id,
    cr.institution_id,
    cr.requested_by,
    cr.approved_by,
    s.student_id as student_number,
    s.full_name as student_name,
    s.course as student_course,
    s.batch_year as student_batch,
    i.name as institution_name,
    i.code as institution_code,
    u1.full_name as requested_by_name,
    u2.full_name as approved_by_name,
    CASE 
        WHEN cr.status = 'pending' AND DATEDIFF(NOW(), cr.requested_at) > 7 THEN 'urgent'
        WHEN cr.status = 'pending' AND DATEDIFF(NOW(), cr.requested_at) > 3 THEN 'high'
        WHEN cr.status = 'pending' THEN 'medium'
        ELSE 'low'
    END as priority
FROM certificate_requests cr
LEFT JOIN students s ON cr.student_id = s.id
LEFT JOIN institutions i ON cr.institution_id = i.id
LEFT JOIN users u1 ON cr.requested_by = u1.id
LEFT JOIN users u2 ON cr.approved_by = u2.id;

-- Marklists with student information (for faculty portal)
CREATE OR REPLACE VIEW marklists_with_students AS
SELECT 
    m.id,
    m.subject,
    m.marks_obtained,
    m.total_marks,
    m.grade,
    m.exam_date,
    m.created_at,
    m.student_id,
    m.uploaded_by,
    s.student_id as student_number,
    s.full_name as student_name,
    s.course as student_course,
    s.batch_year as student_batch,
    s.institution_id,
    i.name as institution_name,
    u.full_name as uploaded_by_name,
    ROUND((m.marks_obtained / m.total_marks) * 100, 2) as percentage,
    CASE 
        WHEN m.marks_obtained >= (m.total_marks * 0.9) THEN 1
        ELSE 0
    END as is_distinction,
    CASE 
        WHEN m.marks_obtained >= (m.total_marks * 0.4) THEN 1
        ELSE 0
    END as pass_status
FROM marklists m
LEFT JOIN students s ON m.student_id = s.id
LEFT JOIN institutions i ON s.institution_id = i.id
LEFT JOIN users u ON m.uploaded_by = u.id;

-- Call logs with enquiry information (for enquirer portal)
CREATE OR REPLACE VIEW call_logs_with_enquiries AS
SELECT 
    cl.id,
    cl.call_duration,
    cl.call_outcome,
    cl.follow_up_required,
    cl.notes,
    cl.call_timestamp,
    cl.enquiry_id,
    cl.caller_user_id,
    e.full_name as enquiry_name,
    e.phone,
    e.email as enquiry_email,
    e.course_interest,
    e.status as enquiry_status,
    u.full_name as caller_name,
    CASE 
        WHEN cl.call_outcome = 'answered' AND cl.call_duration > 60 THEN 'successful'
        WHEN cl.call_outcome = 'answered' THEN 'brief'
        WHEN cl.call_outcome = 'no_answer' THEN 'missed'
        WHEN cl.call_outcome = 'busy' THEN 'busy'
        ELSE 'failed'
    END as call_quality,
    CASE 
        WHEN cl.follow_up_required = 1 AND 
             DATEDIFF(NOW(), cl.call_timestamp) > 1 THEN 'overdue'
        WHEN cl.follow_up_required = 1 THEN 'pending'
        ELSE 'none'
    END as follow_up_status
FROM call_logs cl
LEFT JOIN enquiries e ON cl.enquiry_id = e.id
LEFT JOIN users u ON cl.caller_user_id = u.id;

-- Institution statistics view (for admin dashboard)
CREATE OR REPLACE VIEW institution_statistics AS
SELECT 
    i.id,
    i.name,
    i.code,
    i.is_active,
    i.created_at,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(DISTINCT u.id) as total_faculty,
    COUNT(DISTINCT cr.id) as total_certificate_requests,
    COUNT(DISTINCT CASE WHEN cr.status = 'pending' THEN cr.id END) as pending_certificates,
    COUNT(DISTINCT CASE WHEN s.is_exam_eligible = 1 THEN s.id END) as eligible_students,
    COUNT(DISTINCT CASE WHEN s.batch_year = YEAR(CURDATE()) THEN s.id END) as current_batch_students
FROM institutions i
LEFT JOIN students s ON i.id = s.institution_id
LEFT JOIN users u ON i.id = u.institution_id AND u.role = 'faculty'
LEFT JOIN certificate_requests cr ON i.id = cr.institution_id
GROUP BY i.id, i.name, i.code, i.is_active, i.created_at;

-- Enquirer performance statistics
CREATE OR REPLACE VIEW enquirer_performance AS
SELECT 
    u.id as user_id,
    u.full_name as enquirer_name,
    COUNT(DISTINCT e.id) as total_assigned_enquiries,
    COUNT(DISTINCT CASE WHEN e.status = 'interested' THEN e.id END) as interested_leads,
    COUNT(DISTINCT CASE WHEN e.status = 'enrolled' THEN e.id END) as converted_leads,
    COUNT(DISTINCT cl.id) as total_calls_made,
    COUNT(DISTINCT CASE WHEN cl.call_outcome = 'answered' THEN cl.id END) as successful_calls,
    AVG(cl.call_duration) as avg_call_duration,
    ROUND(
        (COUNT(DISTINCT CASE WHEN e.status = 'enrolled' THEN e.id END) * 100.0) / 
        NULLIF(COUNT(DISTINCT e.id), 0), 2
    ) as conversion_rate,
    ROUND(
        (COUNT(DISTINCT CASE WHEN cl.call_outcome = 'answered' THEN cl.id END) * 100.0) / 
        NULLIF(COUNT(DISTINCT cl.id), 0), 2
    ) as call_success_rate
FROM users u
LEFT JOIN enquiries e ON u.id = e.assigned_to_user_id
LEFT JOIN call_logs cl ON u.id = cl.caller_user_id
WHERE u.role = 'enquirer'
GROUP BY u.id, u.full_name;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_enquiries_status_assigned ON enquiries(status, assigned_to_user_id);
CREATE INDEX IF NOT EXISTS idx_enquiries_created_status ON enquiries(created_at, status);
CREATE INDEX IF NOT EXISTS idx_students_institution_course ON students(institution_id, course);
CREATE INDEX IF NOT EXISTS idx_students_batch_eligible ON students(batch_year, is_exam_eligible);
CREATE INDEX IF NOT EXISTS idx_certificates_institution_status ON certificate_requests(institution_id, status);
CREATE INDEX IF NOT EXISTS idx_certificates_requested_at ON certificate_requests(requested_at);
CREATE INDEX IF NOT EXISTS idx_call_logs_caller_timestamp ON call_logs(caller_user_id, call_timestamp);
CREATE INDEX IF NOT EXISTS idx_call_logs_enquiry_outcome ON call_logs(enquiry_id, call_outcome);
CREATE INDEX IF NOT EXISTS idx_marklists_student_subject ON marklists(student_id, subject);
CREATE INDEX IF NOT EXISTS idx_marklists_exam_date ON marklists(exam_date);

-- Full-text search indexes
ALTER TABLE students ADD FULLTEXT(full_name, email, student_id);
ALTER TABLE enquiries ADD FULLTEXT(full_name, email, phone, course_interest);
ALTER TABLE users ADD FULLTEXT(full_name, email, username);
ALTER TABLE institutions ADD FULLTEXT(name, code, address);
ALTER TABLE marklists ADD FULLTEXT(subject);
ALTER TABLE call_logs ADD FULLTEXT(notes);