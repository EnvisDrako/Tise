# Faculty Portal User Manual

## Integrated Web Portal & CRM System - Faculty Guide

### Table of Contents
1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Student Registration](#student-registration)
4. [Student Management](#student-management)
5. [Marklist Management](#marklist-management)
6. [Certificate Requests](#certificate-requests)
7. [Reports and Analytics](#reports-and-analytics)
8. [Profile Management](#profile-management)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Getting Started

### Accessing the Faculty Portal
1. Navigate to your system URL (e.g., `https://your-domain.com`)
2. Click on "Login" or navigate directly to `/login.php`
3. Enter your faculty credentials
4. You will be automatically redirected to the faculty dashboard at `/institution/`

### Understanding Your Access Level
As a faculty member, you have access to:
- Students from your institution only
- Academic records for your students
- Certificate request submission for your students
- Institution-specific reports and analytics

**Note**: You cannot access students or data from other institutions.

---

## Dashboard Overview

The faculty dashboard provides an overview of your institution's key metrics and quick access to common tasks.

### Key Metrics Displayed
- **Total Students**: Number of students registered under your institution
- **Exam Eligible Students**: Students marked as eligible for examinations
- **Pending Certificates**: Certificate requests awaiting admin approval
- **Recent Registrations**: Newly registered students
- **Course Distribution**: Breakdown of students by course

### Quick Actions Panel
- **Register New Student**: Add a new student to your institution
- **Upload Marklists**: Add academic records for students
- **Submit Certificate Request**: Request certificates for eligible students
- **Generate Reports**: Create student and academic reports

### Navigation Menu
- **Dashboard**: Institution overview and statistics
- **Register Student**: Add new student records
- **Students**: Manage your institution's students
- **Marklists**: Academic record management
- **Certificates**: Certificate request management
- **Reports**: Institution-specific analytics

---

## Student Registration

### Registering New Students
1. Navigate to **Register Student** from the main menu
2. Fill in the student registration form:

#### Personal Information
- **Student ID**: Unique identifier (auto-generated or manual)
- **Full Name**: Student's complete name
- **Email**: Student's email address
- **Phone**: Contact number
- **Address**: Complete address

#### Academic Information
- **Course**: Select from available courses
- **Batch Year**: Year of enrollment/graduation
- **Specialization**: Course specialization (if applicable)

#### Documents
- **Photo**: Upload student photograph (JPG/PNG, max 2MB)
- **Documents**: Upload relevant documents (PDF/DOC, max 5MB each)

3. Click **"Register Student"** to save

### Student ID Generation
- **Auto-generated**: System creates unique ID based on institution code and sequence
- **Manual Entry**: Enter custom student ID (must be unique)
- **Format**: Typically follows pattern like "INST001-2024-001"

### Document Upload Guidelines
- **Photo Requirements**: Clear, passport-size photo in JPG or PNG format
- **Document Types**: Academic transcripts, ID proof, certificates
- **File Size Limits**: Photos max 2MB, documents max 5MB
- **Security**: All uploads are scanned for security threats

### Validation and Error Handling
- **Duplicate Check**: System prevents duplicate student IDs and email addresses
- **Required Fields**: All mandatory fields must be completed
- **Format Validation**: Email and phone number format validation
- **File Validation**: Uploaded files are checked for type and size

---

## Student Management

### Viewing Your Students
1. Navigate to **Students** from the main menu
2. View all students registered under your institution
3. Use filtering and search options to find specific students

### Advanced Filtering Options
- **Course Filter**: Filter by specific courses
- **Batch Year**: Filter by enrollment/graduation year
- **Exam Eligibility**: Show only eligible or non-eligible students
- **Registration Date**: Filter by registration date range
- **Status**: Active or inactive students

### Search Functionality
- **Quick Search**: Search by name, student ID, or email
- **Advanced Search**: Multi-criteria search with filters
- **Real-time Results**: Search results update as you type

### Student Profile Management
Click on any student to access their complete profile:

#### Personal Information Tab
- View and edit personal details
- Update contact information
- Manage address information

#### Academic Information Tab
- View course and batch details
- Update academic status
- Manage specialization information

#### Documents Tab
- View uploaded documents
- Download student documents
- Upload additional documents

#### Academic Records Tab
- View all marklists and grades
- Access academic history
- Generate academic transcripts

### Bulk Student Operations
Select multiple students using checkboxes to perform bulk operations:

#### Available Bulk Actions
- **Mark Exam Eligible**: Set exam eligibility for multiple students
- **Export Student Data**: Download student information in various formats
- **Generate Reports**: Create reports for selected students
- **Send Notifications**: Email notifications to selected students
- **Update Status**: Change status for multiple students

#### Exam Eligibility Management
1. Select students using checkboxes
2. Click **"Mark Exam Eligible"** button
3. Confirm the action in the popup dialog
4. Students will be updated and can now have certificates requested

---

## Marklist Management

### Understanding Marklists
Marklists are academic records that contain:
- Subject-wise marks and grades
- Examination details
- Assessment information
- Grade point calculations

### Uploading Marklists
1. Navigate to **Marklists** from the main menu
2. Click **"Upload New Marklist"**
3. Select the student from dropdown
4. Fill in marklist details:

#### Examination Information
- **Exam Type**: Semester, Annual, Supplementary
- **Exam Date**: Date of examination
- **Academic Year**: Relevant academic year

#### Subject Details
- **Subject Name**: Name of the subject
- **Subject Code**: Unique subject identifier
- **Marks Obtained**: Marks scored by student
- **Total Marks**: Maximum marks for the subject
- **Grade**: Letter grade (A, B, C, etc.)

#### Additional Information
- **Remarks**: Any additional comments
- **Internal Assessment**: Internal marks if applicable
- **Practical Marks**: Practical examination marks

### Bulk Marklist Upload
For uploading multiple marklists:
1. Download the CSV template
2. Fill in marklist data following the template format
3. Upload the completed CSV file
4. Review and confirm the import

### Marklist Validation
- **Grade Calculation**: Automatic grade calculation based on marks
- **Data Validation**: Checks for valid marks and grade ranges
- **Duplicate Prevention**: Prevents duplicate entries for same subject/exam
- **Completeness Check**: Ensures all required fields are filled

### Viewing and Managing Marklists
- **Filter by Student**: View marklists for specific students
- **Filter by Subject**: View specific subject performance
- **Filter by Exam Date**: View marklists from specific time periods
- **Export Options**: Download marklists in PDF or Excel format

### Academic Transcript Generation
1. Select student from the list
2. Choose **"Generate Transcript"** option
3. Select subjects and exams to include
4. Download the formatted academic transcript

---

## Certificate Requests

### Understanding Certificate Types
Available certificate types:
- **Course Completion Certificate**: For successfully completed courses
- **Merit Certificate**: For outstanding academic performance
- **Participation Certificate**: For course participation
- **Custom Certificate**: Institution-specific certificates

### Submitting Certificate Requests
1. Navigate to **Certificates** from the main menu
2. Click **"Submit New Request"**
3. Fill in certificate request details:

#### Student Selection
- **Student**: Select from eligible students dropdown
- **Eligibility Check**: System verifies student eligibility automatically

#### Certificate Details
- **Certificate Type**: Choose appropriate certificate type
- **Course**: Select relevant course
- **Completion Date**: Date of course completion
- **Grade/Performance**: Academic performance level

#### Additional Information
- **Special Mentions**: Any special achievements or mentions
- **Custom Text**: Additional text for custom certificates
- **Priority Level**: Normal or urgent processing

### Certificate Request Validation
- **Eligibility Check**: Student must be marked as exam eligible
- **Academic Requirements**: Minimum academic requirements must be met
- **Document Verification**: Required documents must be uploaded
- **Approval Workflow**: Requests go through admin approval process

### Tracking Certificate Requests
- **Status Tracking**: Monitor request status (Pending, Approved, Rejected)
- **Approval Comments**: View admin comments on requests
- **Download Certificates**: Download approved certificates
- **Request History**: View complete request history

### Bulk Certificate Requests
For multiple certificate requests:
1. Select eligible students using checkboxes
2. Choose **"Bulk Certificate Request"**
3. Select certificate type and details
4. Submit bulk request for admin approval

### Certificate Status Management
- **Pending**: Request submitted, awaiting admin review
- **Under Review**: Admin is reviewing the request
- **Approved**: Certificate approved and generated
- **Rejected**: Request rejected with admin comments
- **Generated**: Certificate file is ready for download

---

## Reports and Analytics

### Available Reports
Access comprehensive reports about your institution's performance:

#### Student Reports
- **Enrollment Statistics**: Student enrollment trends
- **Course Distribution**: Students by course and batch
- **Performance Analysis**: Academic performance metrics
- **Completion Rates**: Course completion statistics

#### Academic Reports
- **Grade Distribution**: Grade analysis across subjects
- **Subject Performance**: Subject-wise performance analysis
- **Exam Statistics**: Examination performance metrics
- **Improvement Tracking**: Student progress over time

#### Certificate Reports
- **Certificate Requests**: Summary of certificate requests
- **Approval Rates**: Certificate approval statistics
- **Processing Times**: Average processing time analysis
- **Certificate Distribution**: Types of certificates issued

### Generating Reports
1. Navigate to **Reports** from the main menu
2. Select report type from available options
3. Configure report parameters:
   - **Date Range**: Select time period for analysis
   - **Filters**: Apply specific filters (course, batch, etc.)
   - **Format**: Choose output format (PDF, Excel, CSV)
4. Click **"Generate Report"** to create and download

### Report Customization
- **Custom Date Ranges**: Select specific time periods
- **Filter Options**: Apply multiple filters for targeted analysis
- **Chart Types**: Choose from various chart and graph options
- **Branding**: Reports include institution branding and logos

### Scheduled Reports
Set up automated report generation:
1. Configure report parameters
2. Set schedule (daily, weekly, monthly)
3. Add email recipients
4. Reports will be automatically generated and emailed

---

## Profile Management

### Managing Your Profile
Access your profile settings to update personal information:

#### Personal Information
- **Full Name**: Update your display name
- **Email**: Change email address (requires verification)
- **Phone**: Update contact number
- **Department**: Update department information

#### Security Settings
- **Change Password**: Update your login password
- **Security Questions**: Set up security questions
- **Two-Factor Authentication**: Enable 2FA for enhanced security
- **Login Notifications**: Receive email notifications for logins

#### Preferences
- **Dashboard Layout**: Customize dashboard appearance
- **Notification Settings**: Configure email and system notifications
- **Language**: Select preferred language (if multiple languages supported)
- **Time Zone**: Set your local time zone

### Institution Information
View your institution details (read-only):
- Institution name and code
- Contact information
- Establishment date
- Current status

---

## Best Practices

### Student Registration Best Practices
1. **Complete Information**: Always fill in all required fields
2. **Verify Details**: Double-check student information before saving
3. **Document Quality**: Upload clear, readable documents
4. **Unique IDs**: Ensure student IDs are unique and follow institution format
5. **Regular Updates**: Keep student information updated

### Academic Record Management
1. **Timely Updates**: Upload marklists promptly after examinations
2. **Accuracy**: Verify marks and grades before submission
3. **Backup Records**: Keep backup copies of academic records
4. **Regular Review**: Periodically review and verify academic data
5. **Grade Consistency**: Maintain consistent grading standards

### Certificate Request Guidelines
1. **Eligibility Verification**: Ensure students meet all requirements
2. **Complete Documentation**: Verify all required documents are uploaded
3. **Accurate Information**: Double-check all certificate details
4. **Timely Submission**: Submit requests well before deadlines
5. **Follow-up**: Monitor request status and respond to admin queries

### Security Best Practices
1. **Strong Passwords**: Use complex passwords and change them regularly
2. **Secure Logout**: Always log out when finished
3. **Access Control**: Don't share login credentials
4. **Regular Monitoring**: Review your account activity regularly
5. **Report Issues**: Report any suspicious activity immediately

---

## Troubleshooting

### Common Issues and Solutions

#### Cannot Register New Student
**Possible Causes and Solutions**:
- **Duplicate Student ID**: Check if student ID already exists, use different ID
- **Invalid Email Format**: Ensure email address is in correct format
- **File Upload Issues**: Check file size and format requirements
- **Required Fields**: Ensure all mandatory fields are completed

#### Marklist Upload Problems
**Possible Causes and Solutions**:
- **Student Not Found**: Verify student is registered in your institution
- **Invalid Marks**: Check marks are within valid range (0 to total marks)
- **Duplicate Entry**: Check if marklist already exists for same subject/exam
- **File Format Issues**: Ensure CSV file follows the correct template format

#### Certificate Request Issues
**Possible Causes and Solutions**:
- **Student Not Eligible**: Student must be marked as exam eligible first
- **Missing Requirements**: Check if all academic requirements are met
- **Incomplete Information**: Ensure all required fields are filled
- **Document Issues**: Verify all required documents are uploaded

#### Login and Access Problems
**Possible Causes and Solutions**:
- **Incorrect Credentials**: Verify username and password
- **Account Locked**: Contact admin if account is locked
- **Session Expired**: Log in again if session has timed out
- **Browser Issues**: Clear browser cache and cookies

#### Performance Issues
**Possible Causes and Solutions**:
- **Slow Loading**: Check internet connection, try refreshing page
- **File Upload Slow**: Large files may take time, ensure stable connection
- **Search Not Working**: Try clearing search filters and search again
- **Report Generation Slow**: Large reports may take time to generate

### Getting Help
1. **Check Error Messages**: Read error messages carefully for specific guidance
2. **Review User Manual**: Refer to relevant sections of this manual
3. **Contact Admin**: Contact your system administrator for technical issues
4. **System Status**: Check if there are any system-wide issues
5. **Browser Compatibility**: Ensure you're using a supported browser

### System Requirements
- **Supported Browsers**: Chrome 80+, Firefox 75+, Safari 13+, Edge 80+
- **Internet Connection**: Stable broadband connection recommended
- **File Formats**: PDF, DOC, DOCX for documents; JPG, PNG for images
- **JavaScript**: Must be enabled in browser
- **Cookies**: Must be enabled for proper session management

---

## Appendix

### Keyboard Shortcuts
- **Ctrl + S**: Save current form
- **Ctrl + F**: Open search functionality
- **Ctrl + N**: Create new record (where applicable)
- **Esc**: Close modal dialogs and popups
- **Tab**: Navigate between form fields

### File Format Guidelines
#### Student Photos
- **Formats**: JPG, JPEG, PNG
- **Size**: Maximum 2MB
- **Dimensions**: Minimum 200x200 pixels, recommended 400x400 pixels
- **Quality**: Clear, passport-style photo

#### Documents
- **Formats**: PDF, DOC, DOCX
- **Size**: Maximum 5MB per file
- **Quality**: Clear, readable scanned documents
- **Naming**: Use descriptive filenames

#### Marklist CSV Format
```csv
Student_ID,Subject_Code,Subject_Name,Marks_Obtained,Total_Marks,Grade,Exam_Date
STU001,CS101,Computer Science,85,100,A,2024-05-15
STU002,CS101,Computer Science,78,100,B,2024-05-15
```

### Contact Information
- **Technical Support**: faculty-support@your-domain.com
- **Academic Queries**: academic@your-domain.com
- **System Administrator**: admin@your-domain.com
- **Help Desk**: +1-XXX-XXX-XXXX (Extension: Faculty Support)

### Frequently Asked Questions

**Q: Can I register students from other institutions?**
A: No, you can only register and manage students from your own institution.

**Q: How long does certificate approval take?**
A: Certificate approval typically takes 3-5 business days, depending on admin workload.

**Q: Can I edit student information after registration?**
A: Yes, you can edit most student information except the student ID.

**Q: What happens if I upload incorrect marklist data?**
A: You can edit or delete marklist entries before they are finalized.

**Q: Can I download student data for backup?**
A: Yes, you can export student data in various formats for backup purposes.

---

**End of Faculty User Manual**

This manual covers all faculty functions of the Integrated Web Portal & CRM system. For additional help or feature requests, please contact your system administrator or technical support team.