# Bulk Import Guide - Students & Enquiries

## Overview
The system now supports both manual entry and CSV bulk import for Students and Enquiries.

## Features Added

### 1. Add Enquiries
**Location:** `admin/add_enquiry.php`

**Manual Entry:**
- Full Name (required)
- Email (required)
- Phone
- Course Interest
- Message
- Status (new/assigned/contacted/interested/not_interested/enrolled)
- Assign to Enquirer Staff

**CSV Import:**
- Download template: `admin/download_enquiry_template.php`
- CSV Columns (in order):
  1. Full Name (required)
  2. Email (required)
  3. Phone
  4. Course Interest
  5. Message
  6. Status
  7. Assigned To User ID

### 2. Add Students
**Location:** `admin/add_student.php`

**Manual Entry:**
- Student ID (required, unique)
- Full Name (required)
- Email
- Phone
- Course
- Batch Year
- Institution (required)
- Enrollment Date
- Status (active/graduated/suspended/withdrawn)

**CSV Import:**
- Download template: `admin/download_student_template.php`
- CSV Columns (in order):
  1. Student ID (required, unique)
  2. Full Name (required)
  3. Email
  4. Phone
  5. Course
  6. Batch Year
  7. Institution ID (required)
  8. Enrollment Date (YYYY-MM-DD)
  9. Status

## How to Use

### Manual Entry
1. Navigate to Admin Portal
2. Click "Enquiries" or "Students" in navigation
3. Select "Add Enquiry" or "Add Student" from submenu
4. Fill in the form
5. Click "Add" button

### CSV Import
1. Navigate to the Add page (Enquiry or Student)
2. Click "Download CSV Template" button
3. Open the template in Excel or any spreadsheet software
4. Fill in your data (keep the header row)
5. Save as CSV file
6. Upload the CSV file using the import form
7. Click "Import" button

## CSV Format Tips

### For Enquiries:
```csv
Full Name,Email,Phone,Course Interest,Message,Status,Assigned To User ID
John Doe,john@example.com,+1234567890,Computer Science,Interested in program,new,
Jane Smith,jane@example.com,+0987654321,Business Admin,Campus visit request,new,
```

### For Students:
```csv
Student ID,Full Name,Email,Phone,Course,Batch Year,Institution ID,Enrollment Date,Status
STU001,John Doe,john@example.com,+1234567890,Computer Science,2023-2024,1,2023-09-01,active
STU002,Jane Smith,jane@example.com,+0987654321,Business Admin,2023-2024,1,2023-09-01,active
```

## Important Notes

1. **CSV Headers:** First row must contain column headers
2. **Required Fields:** Must be filled for each row
3. **Institution ID:** Check the available institutions table on the import page
4. **User ID:** Check the available enquirer staff table on the import page
5. **Duplicate Check:** Student IDs must be unique (system will skip duplicates)
6. **Error Handling:** Import will continue even if some rows fail, showing error summary
7. **Date Format:** Use YYYY-MM-DD format for dates
8. **Status Values:** Use exact values as shown in the guide

## Navigation Updates

The admin navigation now includes dropdown submenus:
- **Enquiries** → View All / Add Enquiry
- **Students** → View All / Add Student

## Access

Only Admin users can access these features. The pages are protected by admin authentication.

## Logging

All import and manual entry actions are logged with:
- User who performed the action
- Number of records imported
- Timestamp
- Any errors encountered
