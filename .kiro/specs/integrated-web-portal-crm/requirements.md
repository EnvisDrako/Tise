# Requirements Document

## Introduction

The Integrated Web Portal & CRM system is a comprehensive multi-faceted web application that serves both public users and internal staff through role-based access control. The system consists of a public-facing website for information and enquiries, and a private CRM portal for managing internal operations including student management, enquiry tracking, and institutional administration.

## Glossary

- **System**: The complete Integrated Web Portal & CRM application
- **Public_Website**: The publicly accessible portion of the system for general information and enquiries
- **CRM_Portal**: The private, role-based administrative interface for internal operations
- **Mobile_API**: The JSON-based API endpoint for Android app communication
- **User_Role**: The access level assigned to each user (admin, faculty, enquirer)
- **Enquiry_Record**: A lead or inquiry submitted through the public website
- **Student_Record**: A student's complete profile and academic information created by faculty
- **Institution_Record**: An educational institution managed within the system
- **Call_Log**: A record of phone calls made by enquirers through the mobile app
- **Certificate_Request**: An institution's request for student certificate approval submitted to admin
- **Certificate_Approval_Section**: Admin panel area for managing certificate requests with sorting and download capabilities

## Requirements

### Requirement 1

**User Story:** As a public visitor, I want to access comprehensive information about the institution through detailed pages, so that I can learn about programs, faculty, facilities, and institutional credentials.

#### Acceptance Criteria

1. THE Public_Website SHALL display a home page with institutional overview, mission, and navigation menu
2. THE Public_Website SHALL provide an about us page with detailed institutional history, leadership, and contact information
3. THE Public_Website SHALL display courses and programs page with curriculum details, duration, and faculty information
4. THE Public_Website SHALL show trainers and faculty section with qualifications and experience
5. THE Public_Website SHALL display affiliations and accreditations with official certifications and partnerships
6. THE Public_Website SHALL include a photo and video gallery showcasing facilities and activities
7. THE Public_Website SHALL provide testimonials and success stories from alumni and students
8. THE Public_Website SHALL display a notice board section for announcements, upcoming courses, results, and events
9. THE Public_Website SHALL include a job placement and career support section with placement statistics
10. THE Public_Website SHALL offer a download section for brochures, application forms, and course schedules
11. THE Public_Website SHALL provide a blog and news section for institutional updates
12. THE Public_Website SHALL integrate social media links and contact information

### Requirement 2

**User Story:** As a prospective student, I want to submit my information and documents through an enquiry form and contact the institution, so that faculty can review my details and consider me for admission.

#### Acceptance Criteria

1. THE Public_Website SHALL provide an enquiry form for prospective students to submit their information and documents
2. THE Public_Website SHALL include a contact page with phone numbers, email addresses, and physical address
3. WHEN a prospective student submits an enquiry, THE System SHALL create an Enquiry_Record with their details and uploaded documents
4. THE System SHALL store submitted documents in a secure upload directory
5. THE System SHALL validate all required fields before accepting the enquiry submission

### Requirement 3

**User Story:** As an admin, I want complete control over all system operations and user management, so that I can oversee the entire organization effectively.

#### Acceptance Criteria

1. THE CRM_Portal SHALL provide an admin dashboard with system-wide overview statistics
2. THE System SHALL allow admins to create, view, edit, and delete all user accounts
3. THE System SHALL enable admins to assign specific User_Role permissions to each user
4. THE System SHALL allow admins to view and manage all Enquiry_Records across institutions
5. THE System SHALL enable admins to assign Enquiry_Records to specific enquirer users

### Requirement 4

**User Story:** As an admin, I want to manage institutions and oversee all student records, so that I can maintain organizational structure and academic oversight.

#### Acceptance Criteria

1. THE System SHALL allow admins to create, read, update, and delete Institution_Records
2. THE System SHALL provide access to all Student_Records across all institutions
3. THE System SHALL allow admins to view and manage marklists for any student
4. THE System SHALL maintain audit trails for all administrative actions
5. THE System SHALL enable admins to generate reports across all institutions

### Requirement 5

**User Story:** As an admin, I want a dedicated certificate approval system with sorting and download capabilities, so that I can efficiently manage certificate requests from institutions and maintain proper documentation.

#### Acceptance Criteria

1. THE CRM_Portal SHALL provide a Certificate_Approval_Section accessible only to admins
2. THE System SHALL display all Certificate_Requests submitted by institutions with student details
3. THE System SHALL allow admins to approve or reject Certificate_Requests with comments
4. THE System SHALL enable sorting of approved certificates by institution, date, course, or student name
5. THE System SHALL allow admins to download approved certificates individually or in bulk per institution
6. THE System SHALL maintain a complete audit trail of all certificate approvals and rejections

### Requirement 6

**User Story:** As faculty/institution staff, I want to register qualified students, manage academic records, and request certificates for my institution only, so that I can control admissions and academic processes.

#### Acceptance Criteria

1. THE CRM_Portal SHALL display a faculty dashboard showing only their institution's data
2. THE System SHALL restrict faculty access to Student_Records associated with their institution only
3. THE System SHALL allow faculty to register new students using their credentials for their institution only
4. THE System SHALL allow faculty to mark students as eligible for examinations
5. THE System SHALL enable faculty to upload and manage marklists for their students
6. THE System SHALL allow faculty to submit Certificate_Requests to admin for their students

### Requirement 7

**User Story:** As an enquirer/caller, I want to access my assigned leads through web and mobile interfaces, so that I can efficiently manage my calling responsibilities.

#### Acceptance Criteria

1. THE CRM_Portal SHALL display a list of Enquiry_Records assigned to the logged-in enquirer
2. THE System SHALL provide a web interface for manually updating enquiry status
3. THE Mobile_API SHALL authenticate enquirer users and return assigned phone numbers
4. THE Mobile_API SHALL accept call outcome data and store it as Call_Log records
5. WHEN the mobile app logs a call, THE System SHALL update the corresponding Enquiry_Record status

### Requirement 8

**User Story:** As a system administrator, I want secure role-based access control and data protection, so that sensitive information remains protected and users access only authorized content.

#### Acceptance Criteria

1. THE System SHALL implement a single universal login page for admin, faculty, and enquirer user types
2. THE System SHALL redirect users to appropriate portals based on their User_Role
3. THE System SHALL protect all private portal directories from unauthorized access
4. THE System SHALL store all uploaded files in secure directories with access controls
5. THE System SHALL maintain session security and provide secure logout functionality

### Requirement 9

**User Story:** As a mobile app user (enquirer), I want to make calls and automatically log call details, so that I can efficiently track my calling activities without manual data entry.

#### Acceptance Criteria

1. THE Mobile_API SHALL provide secure authentication for enquirer users
2. THE Mobile_API SHALL return JSON-formatted lists of assigned phone numbers
3. THE System SHALL enable the mobile app to initiate native phone calls
4. THE System SHALL accept call duration data from the phone's call log
5. THE Mobile_API SHALL process and store call outcomes in the Call_Log table