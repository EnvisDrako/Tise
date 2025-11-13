# Admin Portal User Manual

## Integrated Web Portal & CRM System - Administrator Guide

### Table of Contents
1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [User Management](#user-management)
4. [Institution Management](#institution-management)
5. [Enquiry Management](#enquiry-management)
6. [Student Oversight](#student-oversight)
7. [Certificate Approval](#certificate-approval)
8. [Bulk Operations](#bulk-operations)
9. [Data Management](#data-management)
10. [System Monitoring](#system-monitoring)
11. [Reports and Analytics](#reports-and-analytics)

---

## Getting Started

### Accessing the Admin Portal
1. Navigate to your system URL (e.g., `https://your-domain.com`)
2. Click on "Login" or navigate directly to `/login.php`
3. Enter your admin credentials
4. You will be automatically redirected to the admin dashboard

### First Time Setup
After installation, use these default credentials:
- **Username**: `admin`
- **Password**: `admin123` (Change immediately after first login)

**Important**: Change the default password immediately for security.

---

## Dashboard Overview

The admin dashboard provides a comprehensive overview of system-wide statistics and quick access to all administrative functions.

### Key Metrics Displayed
- **Total Users**: Count of all system users by role
- **Active Institutions**: Number of registered institutions
- **Pending Enquiries**: Unassigned or new enquiries requiring attention
- **Certificate Requests**: Pending certificate approvals
- **System Health**: Server status and performance indicators

### Quick Actions
- Create new user accounts
- Add new institutions
- Assign enquiries to staff
- Approve certificate requests
- Generate system reports

### Navigation Menu
- **Dashboard**: System overview and statistics
- **Users**: Manage all user accounts
- **Institutions**: Manage educational institutions
- **Enquiries**: Handle all enquiry management
- **Students**: Oversight of all student records
- **Certificates**: Certificate approval system
- **Bulk Operations**: Mass data operations
- **Data Management**: System data administration
- **Reports**: Analytics and reporting tools

---

## User Management

### Viewing Users
1. Navigate to **Users** from the main menu
2. Use filters to find specific users:
   - **Role Filter**: Admin, Faculty, Enquirer
   - **Institution Filter**: Filter by associated institution
   - **Status Filter**: Active or inactive users
   - **Date Range**: Registration date range
3. Use the search box for quick user lookup by name, email, or username

### Creating New Users
1. Click **"Add New User"** button
2. Fill in required information:
   - **Username**: Unique system identifier
   - **Password**: Secure password (will be hashed)
   - **Full Name**: User's complete name
   - **Email**: Valid email address
   - **Phone**: Contact number
   - **Role**: Select appropriate role (Admin/Faculty/Enquirer)
   - **Institution**: Required for Faculty users
3. Click **"Create User"** to save

### Editing User Information
1. Click the **"Edit"** button next to any user
2. Modify the necessary fields
3. Click **"Update User"** to save changes

### User Role Permissions
- **Admin**: Full system access, can manage all data
- **Faculty**: Institution-specific access, can manage their students
- **Enquirer**: Limited access to assigned enquiries and call logging

### Bulk User Operations
1. Select multiple users using checkboxes
2. Choose from bulk actions:
   - **Activate/Deactivate**: Change user status
   - **Delete**: Remove users (with confirmation)
   - **Export**: Download user data
   - **Send Notifications**: Mass email to selected users

### User Security Features
- **Password Reset**: Force password reset for any user
- **Account Lockout**: View and manage locked accounts
- **Login History**: Review user login activities
- **Session Management**: View active user sessions

---

## Institution Management

### Adding New Institutions
1. Navigate to **Institutions**
2. Click **"Add New Institution"**
3. Enter institution details:
   - **Name**: Full institution name
   - **Code**: Unique institution identifier
   - **Address**: Complete address
   - **Phone**: Primary contact number
   - **Email**: Official email address
   - **Established Date**: Institution founding date
4. Click **"Save Institution"**

### Managing Institution Information
- **Edit**: Update institution details
- **View Students**: See all students from this institution
- **View Faculty**: See assigned faculty members
- **Statistics**: Institution-specific metrics
- **Status**: Activate or deactivate institutions

### Institution Statistics
Each institution displays:
- Total enrolled students
- Active faculty members
- Pending certificate requests
- Recent enquiry conversions

---

## Enquiry Management

### Enquiry Overview
The enquiry management system handles all leads from the public website and tracks their progress through the conversion funnel.

### Viewing Enquiries
1. Navigate to **Enquiries**
2. Use advanced filtering:
   - **Status**: New, Assigned, Contacted, Interested, Not Interested, Enrolled
   - **Assigned To**: Filter by enquirer
   - **Course Interest**: Filter by course type
   - **Date Range**: Enquiry submission date
   - **Source**: How the enquiry was received

### Enquiry Assignment
1. Select unassigned enquiries
2. Choose **"Assign to Enquirer"** from bulk actions
3. Select the enquirer from the dropdown
4. Click **"Assign"** to complete

### Enquiry Status Management
- **New**: Recently submitted, not yet assigned
- **Assigned**: Assigned to an enquirer but not contacted
- **Contacted**: Enquirer has made contact
- **Interested**: Prospect shows interest in enrollment
- **Not Interested**: Prospect declined
- **Enrolled**: Successfully converted to student

### Bulk Enquiry Operations
- **Mass Assignment**: Assign multiple enquiries to enquirers
- **Status Updates**: Change status for multiple enquiries
- **Export Data**: Download enquiry information
- **Delete Old**: Remove old enquiries (with confirmation)
- **Archive**: Move completed enquiries to archive

### Enquiry Analytics
- Conversion rates by enquirer
- Response time statistics
- Course interest trends
- Source effectiveness analysis

---

## Student Oversight

### Student Records Access
As an admin, you have access to all student records across all institutions.

### Viewing Students
1. Navigate to **Students**
2. Use comprehensive filtering:
   - **Institution**: Filter by specific institution
   - **Course**: Filter by course type
   - **Batch Year**: Filter by graduation year
   - **Exam Eligibility**: Eligible or not eligible
   - **Registration Date**: Date range filter

### Student Information Management
- **View Profile**: Complete student information
- **Edit Details**: Update student information
- **Document Access**: View uploaded documents
- **Academic Records**: Access marklists and grades
- **Certificate History**: View certificate requests and approvals

### Exam Eligibility Management
1. Select students using checkboxes
2. Choose **"Mark Exam Eligible"** from bulk actions
3. Confirm the action
4. Students will be updated in bulk

### Student Bulk Operations
- **Export Student Data**: Download comprehensive student information
- **Generate Reports**: Create institution-wise or course-wise reports
- **Bulk Updates**: Mass update student information
- **Archive Graduates**: Move graduated students to archive
- **Delete Records**: Remove student records (with proper authorization)

### Academic Oversight
- Monitor academic progress across institutions
- Identify students requiring attention
- Track graduation rates and success metrics
- Generate academic performance reports

---

## Certificate Approval

### Certificate Request Management
The certificate approval system handles all certificate requests from institutions.

### Viewing Certificate Requests
1. Navigate to **Certificates**
2. Filter requests by:
   - **Institution**: Specific institution requests
   - **Status**: Pending, Approved, Rejected
   - **Request Type**: Completion, Merit, Participation
   - **Date Range**: Request submission date

### Approval Process
1. Click on a certificate request to view details
2. Review student information and eligibility
3. Check academic records and requirements
4. Add approval comments if needed
5. Click **"Approve"** or **"Reject"**

### Bulk Certificate Operations
1. Select multiple certificate requests
2. Choose from bulk actions:
   - **Bulk Approve**: Approve multiple certificates
   - **Bulk Reject**: Reject multiple certificates
   - **Download Certificates**: Generate and download approved certificates
   - **Export Data**: Download certificate request information

### Certificate Generation
- Approved certificates are automatically generated as PDF files
- Certificates include student information, course details, and official signatures
- Generated certificates are stored securely and can be downloaded anytime

### Certificate Tracking
- Complete audit trail of all certificate actions
- Track approval/rejection reasons
- Monitor certificate generation and download history
- Generate certificate statistics and reports

---

## Bulk Operations

### Bulk Operations Dashboard
Access comprehensive bulk operation tools for efficient data management.

### Available Bulk Operations
1. **User Operations**:
   - Bulk user creation from CSV
   - Mass password resets
   - Bulk role assignments
   - User data exports

2. **Enquiry Operations**:
   - Mass enquiry assignments
   - Bulk status updates
   - Enquiry data imports/exports
   - Automated follow-up scheduling

3. **Student Operations**:
   - Bulk student registration
   - Mass exam eligibility updates
   - Student data exports
   - Academic record imports

4. **Certificate Operations**:
   - Bulk certificate approvals
   - Mass certificate generation
   - Certificate data exports
   - Automated certificate delivery

### Using Bulk Operations
1. Navigate to **Bulk Operations**
2. Select the operation type
3. Choose data selection criteria or upload CSV file
4. Review operation preview
5. Confirm and execute operation
6. Monitor operation progress
7. Download operation results

### Bulk Operation Safety Features
- **Preview Mode**: Review changes before execution
- **Rollback Capability**: Undo recent bulk operations
- **Audit Logging**: Complete record of all bulk operations
- **Confirmation Steps**: Multiple confirmations for destructive operations

---

## Data Management

### Data Export and Import
Comprehensive tools for data management and migration.

### Export Options
1. **Complete System Export**: All system data
2. **Selective Export**: Choose specific data types
3. **Filtered Export**: Export based on criteria
4. **Scheduled Exports**: Automated regular exports

### Export Formats
- **CSV**: For spreadsheet applications
- **Excel**: Formatted Excel files
- **PDF**: Formatted reports
- **JSON**: For system integration

### Data Import
1. **User Import**: Bulk user creation from CSV
2. **Student Import**: Mass student registration
3. **Academic Data Import**: Marklists and grades
4. **Institution Import**: Bulk institution setup

### Data Archival
- **Automated Archival**: Based on configurable rules
- **Manual Archival**: Select data for archival
- **Archive Management**: Access and restore archived data
- **Storage Optimization**: Compress archived data

### Data Cleanup
- **Duplicate Detection**: Find and merge duplicate records
- **Data Validation**: Identify and fix data inconsistencies
- **Orphaned Records**: Clean up unlinked data
- **Scheduled Cleanup**: Automated maintenance tasks

---

## System Monitoring

### System Health Dashboard
Monitor system performance and health metrics.

### Key Metrics
- **Server Performance**: CPU, memory, and disk usage
- **Database Performance**: Query performance and connection status
- **User Activity**: Active sessions and user actions
- **Error Rates**: System errors and warnings
- **Security Events**: Login attempts and security alerts

### Performance Monitoring
- **Response Times**: Page load and API response times
- **Database Queries**: Slow query identification
- **File Operations**: Upload/download performance
- **Memory Usage**: Application memory consumption

### Security Monitoring
- **Failed Login Attempts**: Track potential security threats
- **Suspicious Activities**: Unusual user behavior patterns
- **File Access**: Monitor sensitive file access
- **System Changes**: Track configuration modifications

### Alerts and Notifications
- **Email Alerts**: Automated notifications for critical issues
- **Dashboard Alerts**: Real-time system status indicators
- **Log Monitoring**: Automated log analysis and alerting
- **Performance Thresholds**: Alerts when performance degrades

---

## Reports and Analytics

### Available Reports
1. **User Reports**:
   - User activity summaries
   - Login statistics
   - Role distribution analysis
   - User performance metrics

2. **Enquiry Reports**:
   - Conversion rate analysis
   - Source effectiveness reports
   - Enquirer performance metrics
   - Lead quality assessments

3. **Academic Reports**:
   - Student performance analysis
   - Institution comparisons
   - Course completion rates
   - Certificate issuance statistics

4. **System Reports**:
   - Usage statistics
   - Performance metrics
   - Security audit reports
   - Data quality assessments

### Generating Reports
1. Navigate to **Reports**
2. Select report type
3. Configure report parameters:
   - Date range
   - Filters and criteria
   - Output format
4. Generate and download report

### Scheduled Reports
- **Daily Reports**: Automated daily summaries
- **Weekly Reports**: Comprehensive weekly analysis
- **Monthly Reports**: Detailed monthly metrics
- **Custom Schedules**: Configure custom report schedules

### Report Customization
- **Custom Fields**: Add specific data fields
- **Branding**: Add organization logos and branding
- **Formatting**: Customize report layout and styling
- **Distribution**: Automated report distribution via email

---

## Best Practices

### Security Best Practices
1. **Regular Password Updates**: Change passwords regularly
2. **User Access Review**: Regularly review user permissions
3. **Activity Monitoring**: Monitor user activities for anomalies
4. **Backup Verification**: Ensure backups are working properly
5. **Security Updates**: Keep system updated with security patches

### Data Management Best Practices
1. **Regular Backups**: Maintain regular data backups
2. **Data Validation**: Regularly validate data integrity
3. **Archive Old Data**: Archive old records to maintain performance
4. **Monitor Storage**: Keep track of storage usage
5. **Document Changes**: Maintain change logs for important modifications

### Performance Optimization
1. **Regular Maintenance**: Perform regular system maintenance
2. **Monitor Performance**: Keep track of system performance metrics
3. **Optimize Queries**: Review and optimize slow database queries
4. **Clean Up Data**: Remove unnecessary data regularly
5. **Update Software**: Keep all software components updated

---

## Troubleshooting

### Common Issues and Solutions

#### Users Cannot Login
1. Check user account status (active/inactive)
2. Verify password hasn't expired
3. Check for account lockouts
4. Review login attempt logs

#### Slow System Performance
1. Check server resource usage
2. Review database performance
3. Analyze slow queries
4. Check for large file operations

#### Data Export Issues
1. Verify user permissions
2. Check available disk space
3. Review export parameters
4. Check for data corruption

#### Certificate Generation Problems
1. Verify student eligibility
2. Check certificate templates
3. Review approval workflow
4. Check file permissions

### Getting Help
- Check system logs for error messages
- Review user activity logs
- Contact technical support with specific error details
- Use the built-in help system for feature guidance

---

## Appendix

### Keyboard Shortcuts
- **Ctrl + S**: Save current form
- **Ctrl + F**: Open search
- **Ctrl + N**: Create new record (context-dependent)
- **Esc**: Close modal dialogs

### System Limits
- **Maximum file upload size**: 10MB
- **Maximum bulk operation size**: 1000 records
- **Session timeout**: 1 hour of inactivity
- **Password minimum length**: 8 characters

### Contact Information
- **Technical Support**: support@your-domain.com
- **System Administrator**: admin@your-domain.com
- **Emergency Contact**: +1-XXX-XXX-XXXX

---

**End of Admin User Manual**

This manual covers all administrative functions of the Integrated Web Portal & CRM system. For additional help or feature requests, please contact your system administrator.