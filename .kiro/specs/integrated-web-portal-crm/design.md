# Design Document

## Overview

The Integrated Web Portal & CRM system is designed as a unified PHP-based web application with role-based access control. The system serves multiple user types through a single backend with distinct frontend interfaces: a public website, private CRM portals, and a mobile API for Android integration.

## Architecture

### System Architecture Pattern
- **Pattern**: Multi-tier Client-Server Architecture with Role-Based Access Control
- **Backend**: Single PHP application with MySQL database
- **Frontend**: Multiple interfaces served by the same backend
- **API**: RESTful JSON API for mobile app integration
- **Security**: Session-based authentication with role-based authorization

### Technology Stack
- **Server-side**: PHP 7.4+ with MySQL/MariaDB
- **Client-side**: HTML5, CSS3, JavaScript (ES6+)
- **Mobile**: Android (Java/Kotlin) with native API integration
- **Web Server**: Apache with .htaccess configuration
- **File Storage**: Server filesystem with secure upload directories

### Deployment Architecture
```
Web Server (Apache/Nginx)
├── Public Website (/)
├── CRM Portals (/admin/, /institution/, /enquiry/)
├── Mobile API (/api/)
├── Secure Uploads (/uploads/)
└── Shared Backend (/includes/)
```

## Components and Interfaces

### 1. Public Website Component

**Purpose**: Serve public information and collect enquiries

**Key Pages**:
- `index.php` - Home page with institutional overview
- `about.php` - Institutional history, leadership, contact
- `courses.php` - Programs, curriculum, faculty details with filtering
- `trainers.php` - Faculty profiles and qualifications with search
- `affiliations.php` - Certifications and partnerships
- `gallery.php` - Photo and video showcase with categories
- `testimonials.php` - Alumni success stories with filtering
- `notices.php` - Announcements and events with date filtering
- `placements.php` - Career support and statistics
- `downloads.php` - Brochures and forms with categorization
- `blog.php` - News and updates with tags and categories
- `enquiry.php` - Enquiry form submission
- `contact.php` - Contact information and location

**Public Website Filtering Features**:

**Courses Page Filters**:
- Filter by: Course Type, Duration, Level (Beginner/Intermediate/Advanced), Fee Range
- Sort by: Popularity, Duration, Start Date, Alphabetical
- Search: Course Name, Keywords, Faculty Name

**Gallery Filters**:
- Filter by: Category (Events/Facilities/Activities), Date Range, Event Type
- Sort by: Date, Category, Popularity
- Search: Event Name, Description Tags

**Testimonials Filters**:
- Filter by: Course Completed, Graduation Year, Industry, Job Role
- Sort by: Date, Course, Success Level
- Search: Student Name, Company, Course

**Blog/News Filters**:
- Filter by: Category, Date Range, Author, Tags
- Sort by: Date, Popularity, Category
- Search: Title, Content, Tags

**Interface Requirements**:
- Responsive design for mobile and desktop
- SEO-optimized structure
- Fast loading with optimized images
- Social media integration
- Contact form validation
- Advanced search functionality across all content

### 2. Authentication System

**Purpose**: Centralized login and role-based access control

**Components**:
- `login.php` - Universal login interface
- `login_process.php` - Authentication logic and role-based redirection
- `logout.php` - Session termination
- `auth_check_*.php` - Role-specific authorization middleware

**Authentication Flow**:
1. User submits credentials to `login.php`
2. `login_process.php` validates against users table
3. System creates session with user role
4. User redirected to appropriate portal based on role
5. Each protected page includes role-specific auth check

### 3. Admin Portal Component

**Purpose**: Complete system administration and oversight

**Key Features**:
- **Dashboard** (`/admin/index.php`): System-wide statistics and overview with filtering widgets
- **User Management** (`/admin/users.php`): Create, edit, delete all user accounts with advanced filtering
- **Institution Management** (`/admin/institutions.php`): CRUD operations for institutions with search and sort
- **Enquiry Management** (`/admin/enquiries.php`): View all enquiries with comprehensive filtering and assignment
- **Student Oversight** (`/admin/students.php`): Access all student records with multi-criteria filtering
- **Certificate Approval** (`/admin/certificates.php`): Dedicated certificate management with advanced sorting

**Advanced Filtering System for Admin**:

**User Management Filters**:
- Filter by: Role (admin/faculty/enquirer), Institution, Active Status, Registration Date Range
- Sort by: Name, Email, Role, Institution, Created Date, Last Login
- Search: Name, Email, Username (with autocomplete)
- Bulk Actions: Activate/Deactivate, Delete, Export to CSV

**Enquiry Management Filters**:
- Filter by: Status (new/assigned/contacted/interested/not_interested/enrolled), Assigned Enquirer, Course Interest, Date Range, Source
- Sort by: Date Created, Name, Phone, Status, Assigned To, Last Contact
- Search: Name, Phone, Email, Course Interest (with live search)
- Bulk Actions: Assign to Enquirer, Change Status, Export, Delete

**Student Management Filters**:
- Filter by: Institution, Course, Batch Year, Exam Eligibility, Registration Date Range
- Sort by: Name, Student ID, Institution, Course, Batch Year, Registration Date
- Search: Name, Student ID, Email, Phone (with instant results)
- Bulk Actions: Mark Exam Eligible, Export by Institution, Generate Reports

**Certificate Approval Interface**:
```php
// Advanced Certificate Management Features
- Filter by: Institution, Request Status, Request Type, Date Range, Approval Status
- Sort by: Institution, Student Name, Request Date, Course, Batch Year, Priority
- Search: Student Name, Student ID, Institution Name (with suggestions)
- Bulk Actions: Approve Selected, Reject Selected, Download Approved Certificates
- Quick Filters: Pending Requests, Approved Today, By Institution
- Export Options: PDF Report, Excel Export, Filtered Results
```

### 4. Faculty Portal Component

**Purpose**: Institution-specific student and academic management

**Key Features**:
- **Dashboard** (`/institution/index.php`): Institution-specific statistics with filtering widgets
- **Student Registration** (`/institution/register_student.php`): Create new student records with validation
- **Student Management** (`/institution/students.php`): View/edit institution's students with advanced filtering
- **Marklist Management** (`/institution/marklists.php`): Upload and manage academic records with sorting
- **Certificate Requests** (`/institution/certificates.php`): Submit and track certificate requests

**Faculty Portal Filtering System**:

**Student Management Filters**:
- Filter by: Course, Batch Year, Exam Eligibility Status, Registration Date Range, Academic Status
- Sort by: Name, Student ID, Course, Batch Year, Registration Date, Last Updated
- Search: Name, Student ID, Email, Phone (with live search)
- Quick Filters: Exam Eligible, Recent Registrations, By Course
- Bulk Actions: Mark Exam Eligible, Export Student List, Generate Reports

**Marklist Management Filters**:
- Filter by: Student, Course, Subject, Exam Date Range, Grade Range
- Sort by: Student Name, Subject, Marks, Grade, Exam Date
- Search: Student Name, Student ID, Subject (with autocomplete)
- Quick Filters: Failed Students, Top Performers, Recent Exams
- Bulk Actions: Export Marklists, Generate Transcripts, Update Grades

**Certificate Request Filters**:
- Filter by: Request Status, Request Type, Submission Date Range, Approval Status
- Sort by: Student Name, Request Date, Status, Certificate Type
- Search: Student Name, Student ID (with suggestions)
- Quick Filters: Pending Requests, Approved Certificates, Recent Submissions
- Bulk Actions: Submit Multiple Requests, Download Approved Certificates

**Access Control**: All data filtered by faculty's institution_id

### 5. Enquirer Portal Component

**Purpose**: Lead management for calling team

**Key Features**:
- **Dashboard** (`/enquiry/index.php`): Assigned enquiry list with filtering and prioritization
- **Status Updates** (`/enquiry/update_status.php`): Manual enquiry status management with history
- **Call History** (`/enquiry/call_logs.php`): View call logs with comprehensive filtering

**Enquirer Portal Filtering System**:

**Assigned Enquiries Filters**:
- Filter by: Status (new/contacted/interested/not_interested), Course Interest, Priority Level, Date Range, Last Contact Date
- Sort by: Priority, Date Assigned, Last Contact, Name, Phone, Status
- Search: Name, Phone, Email, Course Interest (with instant search)
- Quick Filters: High Priority, Overdue Calls, New Assignments, Hot Leads
- Bulk Actions: Update Status, Schedule Follow-up, Mark as Contacted

**Call History Filters**:
- Filter by: Call Outcome, Call Duration Range, Date Range, Follow-up Required
- Sort by: Call Date, Duration, Outcome, Enquiry Name
- Search: Enquiry Name, Phone Number (with autocomplete)
- Quick Filters: Successful Calls, Missed Calls, Follow-up Required, Today's Calls
- Export Options: Call Report, Performance Summary, Daily Activity Log

### 6. Mobile API Component

**Purpose**: JSON API for Android app integration

**Endpoints**:
- `POST /api/login.php` - Authenticate enquirer, return JWT token
- `GET /api/get_numbers.php` - Fetch assigned phone numbers
- `POST /api/log_call.php` - Submit call outcome and duration

**API Response Format**:
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed",
  "timestamp": "2024-01-01T12:00:00Z"
}
```

## Data Models

### Database Schema

**users table**:
```sql
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
    FOREIGN KEY (institution_id) REFERENCES institutions(id)
);
```

**institutions table**:
```sql
CREATE TABLE institutions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    established_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**students table**:
```sql
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
    photo_path VARCHAR(255),
    is_exam_eligible BOOLEAN DEFAULT FALSE,
    registered_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES institutions(id),
    FOREIGN KEY (registered_by) REFERENCES users(id)
);
```

**enquiries table**:
```sql
CREATE TABLE enquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    course_interest VARCHAR(100),
    message TEXT,
    status ENUM('new', 'assigned', 'contacted', 'interested', 'not_interested', 'enrolled') DEFAULT 'new',
    assigned_to_user_id INT NULL,
    documents_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to_user_id) REFERENCES users(id)
);
```

**marklists table**:
```sql
CREATE TABLE marklists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    marks_obtained INT NOT NULL,
    total_marks INT NOT NULL,
    grade VARCHAR(10),
    exam_date DATE,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
```

**certificate_requests table**:
```sql
CREATE TABLE certificate_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    institution_id INT NOT NULL,
    request_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_by INT NOT NULL,
    approved_by INT NULL,
    approval_comments TEXT,
    certificate_path VARCHAR(255) NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (institution_id) REFERENCES institutions(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

**call_logs table**:
```sql
CREATE TABLE call_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enquiry_id INT NOT NULL,
    caller_user_id INT NOT NULL,
    call_duration INT, -- in seconds
    call_outcome ENUM('answered', 'no_answer', 'busy', 'invalid') NOT NULL,
    follow_up_required BOOLEAN DEFAULT FALSE,
    notes TEXT,
    call_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enquiry_id) REFERENCES enquiries(id),
    FOREIGN KEY (caller_user_id) REFERENCES users(id)
);
```

## File Structure Implementation

### Directory Organization
```
/
├── public/ (Document root)
│   ├── index.php, about.php, courses.php, etc.
│   ├── login.php, login_process.php, logout.php
│   ├── admin/
│   │   ├── index.php, users.php, institutions.php
│   │   ├── enquiries.php, students.php, certificates.php
│   ├── institution/
│   │   ├── index.php, register_student.php, students.php
│   │   ├── marklists.php, certificates.php
│   ├── enquiry/
│   │   ├── index.php, update_status.php, call_logs.php
│   ├── api/
│   │   ├── login.php, get_numbers.php, log_call.php
│   ├── css/
│   │   ├── public.css, portal.css, responsive.css
│   ├── js/
│   │   ├── public.js, portal.js, api-client.js
│   └── uploads/ (Secured with .htaccess)
│       ├── student_photos/
│       ├── enquiry_documents/
│       ├── marklist_files/
│       └── certificates/
├── includes/ (Outside document root)
│   ├── db_connect.php
│   ├── auth_check_admin.php
│   ├── auth_check_faculty.php
│   ├── auth_check_enquirer.php
│   ├── functions.php
│   └── config.php
└── database/
    └── setup.sql
```

### Security Configuration (.htaccess)
```apache
# Protect includes directory
<Files "includes/*">
    Deny from all
</Files>

# Secure uploads directory
<Directory "uploads">
    Options -Indexes
    <Files "*.php">
        Deny from all
    </Files>
</Directory>

# Clean URLs
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)/?$ $1.php [L,QSA]
```

## Error Handling

### Error Management Strategy
1. **Database Errors**: Centralized error logging with user-friendly messages
2. **Authentication Errors**: Secure error messages without revealing system details
3. **File Upload Errors**: Validation with specific error codes
4. **API Errors**: Standardized JSON error responses
5. **Session Errors**: Automatic logout and redirect to login

### Error Logging Implementation
```php
// includes/error_handler.php
function logError($error, $context = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $error,
        'context' => $context,
        'user_id' => $_SESSION['user_id'] ?? 'anonymous',
        'ip' => $_SERVER['REMOTE_ADDR']
    ];
    error_log(json_encode($logEntry), 3, '/var/log/crm_errors.log');
}
```

## Advanced Filtering and Sorting Implementation

### Frontend Filtering Components

**JavaScript Filtering Framework**:
```javascript
// Advanced filtering system with real-time updates
class DataFilter {
    constructor(tableId, apiEndpoint) {
        this.table = document.getElementById(tableId);
        this.apiEndpoint = apiEndpoint;
        this.filters = {};
        this.sortConfig = { column: null, direction: 'asc' };
        this.searchTerm = '';
        this.pagination = { page: 1, limit: 25 };
    }
    
    // Real-time search with debouncing
    setupSearch() {
        const searchInput = document.getElementById('search');
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.searchTerm = e.target.value;
                this.applyFilters();
            }, 300);
        });
    }
    
    // Multi-select dropdown filters
    addFilter(filterName, value) {
        if (!this.filters[filterName]) {
            this.filters[filterName] = [];
        }
        this.filters[filterName].push(value);
        this.applyFilters();
    }
    
    // Column sorting with visual indicators
    setupColumnSorting() {
        const headers = this.table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.column;
                this.toggleSort(column);
            });
        });
    }
}
```

**Filter UI Components**:
- **Multi-select Dropdowns**: Institution, Status, Course, etc.
- **Date Range Pickers**: Registration dates, exam dates, etc.
- **Number Range Sliders**: Marks, fees, duration
- **Quick Filter Buttons**: Today, This Week, Pending, etc.
- **Advanced Search**: Full-text search with autocomplete
- **Saved Filters**: User can save and reuse filter combinations

### Backend Filtering Implementation

**PHP Filtering Class**:
```php
class DataFilterManager {
    private $db;
    private $allowedFilters = [];
    private $allowedSorts = [];
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Build dynamic WHERE clause from filters
    public function buildFilterQuery($baseQuery, $filters) {
        $whereConditions = [];
        $params = [];
        
        foreach ($filters as $field => $value) {
            if (in_array($field, $this->allowedFilters)) {
                switch ($field) {
                    case 'institution_id':
                        $whereConditions[] = "institution_id = ?";
                        $params[] = $value;
                        break;
                    case 'date_range':
                        $whereConditions[] = "created_at BETWEEN ? AND ?";
                        $params[] = $value['start'];
                        $params[] = $value['end'];
                        break;
                    case 'status':
                        if (is_array($value)) {
                            $placeholders = str_repeat('?,', count($value) - 1) . '?';
                            $whereConditions[] = "status IN ($placeholders)";
                            $params = array_merge($params, $value);
                        }
                        break;
                }
            }
        }
        
        if (!empty($whereConditions)) {
            $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        return ['query' => $baseQuery, 'params' => $params];
    }
    
    // Add sorting with security validation
    public function addSorting($query, $sortColumn, $sortDirection) {
        if (in_array($sortColumn, $this->allowedSorts)) {
            $direction = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY $sortColumn $direction";
        }
        return $query;
    }
}
```

### Database Optimization for Filtering

**Indexing Strategy**:
```sql
-- Indexes for common filter combinations
CREATE INDEX idx_students_institution_course ON students(institution_id, course);
CREATE INDEX idx_students_batch_eligible ON students(batch_year, is_exam_eligible);
CREATE INDEX idx_enquiries_status_assigned ON enquiries(status, assigned_to_user_id);
CREATE INDEX idx_enquiries_created_status ON enquiries(created_at, status);
CREATE INDEX idx_certificates_institution_status ON certificate_requests(institution_id, status);
CREATE INDEX idx_certificates_requested_at ON certificate_requests(requested_at);
CREATE INDEX idx_call_logs_caller_timestamp ON call_logs(caller_user_id, call_timestamp);

-- Full-text search indexes
ALTER TABLE students ADD FULLTEXT(full_name, email, student_id);
ALTER TABLE enquiries ADD FULLTEXT(full_name, email, phone, course_interest);
```

### Export and Reporting Features

**Data Export Options**:
- **CSV Export**: Filtered results with selected columns
- **PDF Reports**: Formatted reports with charts and summaries
- **Excel Export**: Advanced formatting with multiple sheets
- **Print-friendly Views**: Optimized layouts for printing

**Report Generation**:
```php
class ReportGenerator {
    // Generate filtered reports with charts
    public function generateReport($type, $filters, $format = 'pdf') {
        $data = $this->getFilteredData($filters);
        
        switch ($format) {
            case 'pdf':
                return $this->generatePDFReport($data, $type);
            case 'excel':
                return $this->generateExcelReport($data, $type);
            case 'csv':
                return $this->generateCSVReport($data, $type);
        }
    }
    
    // Include charts and statistics
    private function generatePDFReport($data, $type) {
        // Implementation with charts, summaries, and filtered data
    }
}
```

## Testing Strategy

### Testing Approach
1. **Unit Testing**: Core functions and database operations
2. **Integration Testing**: API endpoints and authentication flow
3. **User Acceptance Testing**: Role-based access and workflow validation
4. **Security Testing**: Authentication, authorization, and data protection
5. **Performance Testing**: Database queries and file upload handling

### Test Coverage Areas
- **Authentication System**: Login, logout, role-based redirection
- **CRUD Operations**: All database create, read, update, delete operations
- **File Uploads**: Document and image upload validation
- **API Endpoints**: Mobile app integration points
- **Access Control**: Role-based permission enforcement
- **Data Validation**: Form input sanitization and validation

### Testing Tools and Methods
- **PHP Unit Tests**: For backend logic validation
- **Postman/Insomnia**: For API endpoint testing
- **Browser Testing**: Cross-browser compatibility
- **Mobile Testing**: Android app integration testing
- **Security Scanning**: SQL injection and XSS vulnerability testing