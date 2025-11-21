<?php
// Define system access for includes
define('SYSTEM_ACCESS', true);

$page_title = "Enquiry Form";
$page_description = "Submit your enquiry and documents to get started with our courses and programs";
include '../includes/public_header.php';
include '../includes/db_connect.php';

// This is a PUBLIC page - no authentication required
// Anyone can submit enquiries here

$success_message = '';
$error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course_interest = trim($_POST['course_interest'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $education_level = trim($_POST['education_level'] ?? '');
    $experience_level = trim($_POST['experience_level'] ?? '');
    $preferred_schedule = trim($_POST['preferred_schedule'] ?? '');
    
    $errors = [];
    
    // Server-side validation
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    } elseif (strlen($full_name) < 2) {
        $errors[] = "Full name must be at least 2 characters";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', preg_replace('/\s/', '', $phone))) {
        $errors[] = "Please enter a valid phone number";
    }
    
    if (empty($course_interest)) {
        $errors[] = "Please select a course of interest";
    }
    
    // Message is optional, no validation needed
    
    // Handle file upload
    $documents_path = null;
    if (isset($_FILES['documents']) && $_FILES['documents']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = '../uploads/enquiry_documents/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['documents'];
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error occurred";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File size must be less than 5MB";
        } else {
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_types)) {
                $errors[] = "File type not allowed. Please upload PDF, DOC, DOCX, JPG, JPEG, or PNG files";
            } else {
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $documents_path = 'enquiry_documents/' . $filename;
                } else {
                    $errors[] = "Failed to upload file";
                }
            }
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $db = DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("
                INSERT INTO enquiries (
                    full_name, email, phone, course_interest, 
                    status, created_at
                ) VALUES (?, ?, ?, ?, 'new', NOW())
            ");
            
            // Provide default values for optional fields
            $email = !empty($email) ? $email : 'noemail@provided.com';
            
            $result = $stmt->execute([
                $full_name, $email, $phone, $course_interest
            ]);
            
            if ($result) {
                $success_message = "Thank you for your enquiry! We will contact you within 24 hours.";
                
                // Send email notification (optional - only if mail server is configured)
                try {
                    $to = "admissions@eduportal.com";
                    $subject = "New Enquiry from " . $full_name;
                    $email_message = "
                        New enquiry received:
                        
                        Name: $full_name
                        Email: $email
                        Phone: $phone
                        Course Interest: $course_interest
                    ";
                    
                    $headers = "From: noreply@eduportal.com\r\n";
                    $headers .= "Reply-To: $email\r\n";
                    
                    @mail($to, $subject, $email_message, $headers);
                    // Suppress mail errors - enquiry is saved even if email fails
                } catch (Exception $e) {
                    // Email failed but enquiry is saved - no action needed
                }
                
                // Clear form data after successful submission
                $_POST = [];
            } else {
                $error_message = "Failed to submit enquiry. Please try again.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error occurred. Please try again later.";
            // Log detailed error for debugging
            error_log("Enquiry submission error: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Error Info: " . print_r($e->errorInfo, true));
            
            // Show detailed error in debug mode
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $error_message .= "<br><small>Debug: " . htmlspecialchars($e->getMessage()) . "</small>";
            }
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Submit Your Enquiry</h1>
        <p>Take the first step towards your learning journey</p>
    </div>
</div>

<section class="enquiry-form section">
    <div class="container">
        <?php  if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php  echo $success_message; ?>
            </div>
        <?php  endif; ?>
        
        <?php  if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php  echo $error_message; ?>
            </div>
        <?php  endif; ?>
        
        <div class="info-banner">
            <div class="info-banner-content">
                <i class="fas fa-info-circle"></i>
                <div>
                    <h4>No Registration Required</h4>
                    <p>This enquiry form is open to everyone. Simply fill out your details below and our admissions team will contact you within 24 hours. No account creation or login needed!</p>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <div class="form-section">
                <form id="enquiry-form" method="POST" enctype="multipart/form-data" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   value="<?php  echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
                                   required minlength="2" maxlength="100">
                            <div class="field-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php  echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   maxlength="100">
                            <div class="field-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php  echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                   required maxlength="20" placeholder="+1 234 567 8900">
                            <div class="field-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="course_interest">Course of Interest *</label>
                            <select id="course_interest" name="course_interest" class="form-control" required>
                                <option value="">Select a course</option>
                                <option value="Web Development" <?php  echo ($_POST['course_interest'] ?? '') === 'Web Development' ? 'selected' : ''; ?>>Full Stack Web Development</option>
                                <option value="Data Science" <?php  echo ($_POST['course_interest'] ?? '') === 'Data Science' ? 'selected' : ''; ?>>Data Science & Analytics</option>
                                <option value="Digital Marketing" <?php  echo ($_POST['course_interest'] ?? '') === 'Digital Marketing' ? 'selected' : ''; ?>>Digital Marketing Mastery</option>
                                <option value="Graphic Design" <?php  echo ($_POST['course_interest'] ?? '') === 'Graphic Design' ? 'selected' : ''; ?>>Graphic Design & Visual Arts</option>
                                <option value="Business Analytics" <?php  echo ($_POST['course_interest'] ?? '') === 'Business Analytics' ? 'selected' : ''; ?>>Business Analytics & Intelligence</option>
                                <option value="English Communication" <?php  echo ($_POST['course_interest'] ?? '') === 'English Communication' ? 'selected' : ''; ?>>Business English Communication</option>
                                <option value="Other" <?php  echo ($_POST['course_interest'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other (Please specify in message)</option>
                            </select>
                            <div class="field-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="education_level">Current Education Level</label>
                            <select id="education_level" name="education_level" class="form-control">
                                <option value="">Select education level</option>
                                <option value="High School" <?php  echo ($_POST['education_level'] ?? '') === 'High School' ? 'selected' : ''; ?>>High School</option>
                                <option value="Diploma" <?php  echo ($_POST['education_level'] ?? '') === 'Diploma' ? 'selected' : ''; ?>>Diploma</option>
                                <option value="Bachelor's Degree" <?php  echo ($_POST['education_level'] ?? '') === 'Bachelor\'s Degree' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                <option value="Master's Degree" <?php  echo ($_POST['education_level'] ?? '') === 'Master\'s Degree' ? 'selected' : ''; ?>>Master's Degree</option>
                                <option value="Other" <?php  echo ($_POST['education_level'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="experience_level">Experience Level</label>
                            <select id="experience_level" name="experience_level" class="form-control">
                                <option value="">Select experience level</option>
                                <option value="Beginner" <?php  echo ($_POST['experience_level'] ?? '') === 'Beginner' ? 'selected' : ''; ?>>Beginner (No experience)</option>
                                <option value="Some Experience" <?php  echo ($_POST['experience_level'] ?? '') === 'Some Experience' ? 'selected' : ''; ?>>Some Experience (1-2 years)</option>
                                <option value="Intermediate" <?php  echo ($_POST['experience_level'] ?? '') === 'Intermediate' ? 'selected' : ''; ?>>Intermediate (3-5 years)</option>
                                <option value="Advanced" <?php  echo ($_POST['experience_level'] ?? '') === 'Advanced' ? 'selected' : ''; ?>>Advanced (5+ years)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="preferred_schedule">Preferred Schedule</label>
                        <select id="preferred_schedule" name="preferred_schedule" class="form-control">
                            <option value="">Select preferred schedule</option>
                            <option value="Morning (9 AM - 12 PM)" <?php  echo ($_POST['preferred_schedule'] ?? '') === 'Morning (9 AM - 12 PM)' ? 'selected' : ''; ?>>Morning (9 AM - 12 PM)</option>
                            <option value="Afternoon (1 PM - 4 PM)" <?php  echo ($_POST['preferred_schedule'] ?? '') === 'Afternoon (1 PM - 4 PM)' ? 'selected' : ''; ?>>Afternoon (1 PM - 4 PM)</option>
                            <option value="Evening (5 PM - 8 PM)" <?php  echo ($_POST['preferred_schedule'] ?? '') === 'Evening (5 PM - 8 PM)' ? 'selected' : ''; ?>>Evening (5 PM - 8 PM)</option>
                            <option value="Weekend" <?php  echo ($_POST['preferred_schedule'] ?? '') === 'Weekend' ? 'selected' : ''; ?>>Weekend</option>
                            <option value="Flexible" <?php  echo ($_POST['preferred_schedule'] ?? '') === 'Flexible' ? 'selected' : ''; ?>>Flexible</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="documents">Upload Documents (Optional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="documents" name="documents" class="file-input" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <label for="documents" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span class="file-upload-text">Choose file or drag and drop</span>
                            </label>
                            <div class="file-upload-info">
                                <small>Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 5MB)</small>
                            </div>
                        </div>
                        <div class="field-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address (Full Street/City) <span class="text-danger">*</span></label>
                        <textarea id="address" name="address" class="form-control" rows="3" 
                                required 
                                maxlength="255"
                                placeholder="Enter your full address, including city. This field is compulsory."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        <div class="field-error"></div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Additional Message / Goals (Optional)</label>
                        <textarea id="notes" name="notes" class="form-control" rows="2" 
                                placeholder="Tell us about your specific requirements, questions, or goals (optional)..."
                                maxlength="1000"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        <div class="field-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="terms" name="terms" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="/terms" target="_blank">Terms and Conditions</a> and <a href="/privacy" target="_blank">Privacy Policy</a> *
                        </label>
                        <div class="field-error"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-paper-plane"></i>
                            Submit Enquiry
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i>
                            Reset Form
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="info-section">
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> What Happens Next?</h3>
                    <div class="process-steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Enquiry Review</h4>
                                <p>Our admissions team will review your enquiry within 24 hours</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Personal Consultation</h4>
                                <p>We'll schedule a call to discuss your goals and course options</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Course Enrollment</h4>
                                <p>Complete the enrollment process and start your learning journey</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-phone"></i> Need Immediate Help?</h3>
                    <div class="contact-options">
                        <div class="contact-option">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Call Us</strong>
                                <p>+1 234 567 8900</p>
                            </div>
                        </div>
                        <div class="contact-option">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email Us</strong>
                                <p>admissions@eduportal.com</p>
                            </div>
                        </div>
                        <div class="contact-option">
                            <i class="fas fa-comments"></i>
                            <div>
                                <strong>Live Chat</strong>
                                <p>Available 9 AM - 6 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-star"></i> Why Choose Us?</h3>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check"></i> Industry-expert instructors</li>
                        <li><i class="fas fa-check"></i> Hands-on practical training</li>
                        <li><i class="fas fa-check"></i> Job placement assistance</li>
                        <li><i class="fas fa-check"></i> Flexible learning schedules</li>
                        <li><i class="fas fa-check"></i> Industry-recognized certificates</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.info-banner {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.info-banner-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.info-banner-content i {
    font-size: 2rem;
    opacity: 0.9;
}

.info-banner-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
}

.info-banner-content p {
    margin: 0;
    opacity: 0.95;
    line-height: 1.4;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.form-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    max-width: 1200px;
    margin: 0 auto;
}

.form-section {
    background: white;
    padding: 2.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-control.error {
    border-color: #e74c3c;
}

.field-error {
    color: #e74c3c;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: none;
}

.field-error.show {
    display: block;
}

.file-upload-wrapper {
    position: relative;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    border: 2px dashed #bdc3c7;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload-label:hover {
    border-color: #3498db;
    background: #ecf0f1;
}

.file-upload-label i {
    font-size: 2rem;
    color: #7f8c8d;
    margin-bottom: 0.5rem;
}

.file-upload-text {
    color: #2c3e50;
    font-weight: 500;
}

.file-upload-info {
    margin-top: 0.5rem;
    text-align: center;
}

.file-upload-info small {
    color: #7f8c8d;
}

.character-count {
    text-align: right;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #7f8c8d;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
    line-height: 1.5;
}

.checkbox-label input[type="checkbox"] {
    position: absolute;
    opacity: 0;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #bdc3c7;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
    margin-top: 2px;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: #3498db;
    border-color: #3498db;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark:after {
    content: "✓";
    color: white;
    font-size: 14px;
    font-weight: bold;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
    margin-top: 2rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.info-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.info-card h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-card h3 i {
    color: #3498db;
}

.process-steps {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.step-number {
    width: 30px;
    height: 30px;
    background: #3498db;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.step-content p {
    color: #666;
    line-height: 1.5;
}

.contact-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.contact-option {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.contact-option i {
    width: 40px;
    height: 40px;
    background: #ecf0f1;
    color: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.contact-option strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 0.25rem;
}

.contact-option p {
    color: #666;
    margin: 0;
}

.benefits-list {
    list-style: none;
    padding: 0;
}

.benefits-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: #555;
}

.benefits-list i {
    color: #27ae60;
    width: 16px;
}

@media (max-width: 768px) {
    .form-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .form-section {
        padding: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-large {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('enquiry-form');
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('char-count');
    const fileInput = document.getElementById('documents');
    const fileLabel = document.querySelector('.file-upload-text');
    
    // Character counter for message
    if (messageTextarea && charCount) {
        function updateCharCount() {
            const count = messageTextarea.value.length;
            charCount.textContent = count;
            charCount.style.color = count > 900 ? '#e74c3c' : '#7f8c8d';
        }
        
        messageTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count
    }
    
    // File upload feedback
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
                fileLabel.textContent = `${fileName} (${fileSize} MB)`;
            } else {
                fileLabel.textContent = 'Choose file or drag and drop';
            }
        });
    }
    
    // Enhanced form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(error => {
                error.style.display = 'none';
                error.textContent = '';
            });
            document.querySelectorAll('.form-control').forEach(field => {
                field.classList.remove('error');
            });
            
            requiredFields.forEach(field => {
                const value = field.value.trim();
                const errorDiv = field.parentNode.querySelector('.field-error');
                
                if (!value) {
                    showFieldError(field, errorDiv, 'This field is required');
                    isValid = false;
                } else {
                    // Specific validations
                    if (field.type === 'email' && !isValidEmail(value)) {
                        showFieldError(field, errorDiv, 'Please enter a valid email address');
                        isValid = false;
                    } else if (field.type === 'tel' && !isValidPhone(value)) {
                        showFieldError(field, errorDiv, 'Please enter a valid phone number');
                        isValid = false;
                    } else if (field.id === 'full_name' && value.length < 2) {
                        showFieldError(field, errorDiv, 'Name must be at least 2 characters');
                        isValid = false;
                    } else if (field.id === 'message' && value.length < 10) {
                        showFieldError(field, errorDiv, 'Message must be at least 10 characters');
                        isValid = false;
                    }
                }
            });
            
            // File validation
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                const errorDiv = fileInput.parentNode.querySelector('.field-error');
                
                if (!allowedTypes.includes(file.type)) {
                    showFieldError(fileInput, errorDiv, 'File type not allowed. Please upload PDF, DOC, DOCX, JPG, JPEG, or PNG files');
                    isValid = false;
                } else if (file.size > maxSize) {
                    showFieldError(fileInput, errorDiv, 'File size must be less than 5MB');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = document.querySelector('.form-control.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
    
    function showFieldError(field, errorDiv, message) {
        field.classList.add('error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    function isValidPhone(phone) {
        return /^[\+]?[1-9][\d]{0,15}$/.test(phone.replace(/\s/g, ''));
    }
});
</script>

<?php include '../includes/public_footer.php'; ?>