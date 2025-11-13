# Authentication & Enquiry System Overview

## System Architecture

### Public Access (No Login Required)
- **Public Website**: All pages in `/public/` directory are accessible to everyone
- **Enquiry Form**: `/public/enquiry.php` - Anyone can submit enquiries without registration
- **Course Browsing**: All course information, gallery, testimonials, blog are public
- **Contact Information**: Freely accessible to all visitors

### Protected Areas (Login Required)

#### 1. Admin Portal (`/public/admin/`)
- **Access**: Admin users only
- **Authentication**: `includes/auth_check_admin.php`
- **Features**:
  - View all enquiries submitted through public form
  - Assign enquiries to enquirer staff members
  - Manage users, institutions, students
  - System overview and statistics

#### 2. Enquirer Portal (`/public/enquiry/`)
- **Access**: Enquirer staff only
- **Authentication**: `includes/auth_check_enquirer.php`
- **Features**:
  - View assigned enquiries only
  - Update enquiry status and add notes
  - Track call history and follow-ups
  - Personal dashboard with statistics

#### 3. Faculty Portal (`/public/institution/`)
- **Access**: Faculty/Institution users only
- **Authentication**: `includes/auth_check_faculty.php`
- **Features**: Institution management and academic functions

## Enquiry Workflow

### Step 1: Public Submission
1. **Anyone** visits `/public/enquiry.php`
2. Fills out enquiry form with:
   - Personal information (name, email, phone)
   - Course interest and preferences
   - Educational background
   - Optional document upload
3. Form is submitted and stored in database
4. Status: `new`
5. **No login or registration required**

### Step 2: Admin Assignment
1. **Admin** logs in to admin portal
2. Views all enquiries at `/public/admin/enquiries.php`
3. Assigns enquiries to available enquirer staff
4. Status changes to: `assigned`

### Step 3: Enquirer Follow-up
1. **Enquirer staff** logs in to enquirer portal
2. Views assigned enquiries at `/public/enquiry/enquiries.php`
3. Contacts prospects and updates status:
   - `contacted` - Initial contact made
   - `interested` - Prospect shows interest
   - `not_interested` - Prospect not interested
   - `follow_up` - Requires additional follow-up
   - `enrolled` - Successfully enrolled

## User Roles & Permissions

### Admin
- **Full system access**
- Manage all users and enquiries
- Assign enquiries to staff
- View system statistics
- Access: `/public/admin/`

### Enquirer
- **Limited access to assigned enquiries only**
- Cannot see other staff members' enquiries
- Update status and add notes
- Track personal performance
- Access: `/public/enquiry/`

### Faculty
- **Institution-specific access**
- Manage courses and students
- Academic functions
- Access: `/public/institution/`

### Public (No Login)
- **Browse all public content**
- Submit enquiries freely
- Access course information
- View testimonials and gallery

## Security Features

### Authentication
- Session-based authentication
- Role-based access control
- Automatic session timeout
- Secure password handling
- Login attempt monitoring

### Data Protection
- SQL injection prevention
- XSS protection
- File upload validation
- Input sanitization
- Error logging

### Access Control
- Each portal has dedicated auth middleware
- Users redirected to appropriate dashboard
- Unauthorized access attempts logged
- Session validation on each request

## Key Files

### Public Access
- `/public/enquiry.php` - Public enquiry form
- `/public/login.php` - Universal login page
- `/public/logout.php` - Secure logout handler

### Admin Portal
- `/public/admin/index.php` - Admin dashboard
- `/public/admin/enquiries.php` - Enquiry management

### Enquirer Portal
- `/public/enquiry/index.php` - Enquirer dashboard
- `/public/enquiry/enquiries.php` - My assigned enquiries

### Authentication Middleware
- `/includes/auth_check_admin.php` - Admin authentication
- `/includes/auth_check_enquirer.php` - Enquirer authentication
- `/includes/auth_check_faculty.php` - Faculty authentication

## Database Tables

### enquiries
- Stores all public enquiry submissions
- `assigned_to_user_id` - Links to enquirer staff
- `status` - Tracks enquiry progress
- `created_at` - Submission timestamp

### users
- All system users (admin, enquirer, faculty)
- `role` - Determines access level
- `is_active` - Account status

## Benefits of This System

1. **Open Access**: Anyone can submit enquiries without barriers
2. **Organized Workflow**: Clear assignment and tracking system
3. **Role Separation**: Each user type has appropriate access
4. **Accountability**: All actions are logged and tracked
5. **Scalable**: Easy to add more enquirer staff as needed
6. **Secure**: Proper authentication and authorization

## Usage Instructions

### For Visitors
1. Visit the website
2. Browse courses and information freely
3. Submit enquiry through `/enquiry` page
4. Wait for contact from admissions team

### For Admin
1. Login at `/login` with admin credentials
2. Go to "View Enquiries" in admin dashboard
3. Assign new enquiries to available staff
4. Monitor overall system performance

### For Enquirer Staff
1. Login at `/login` with enquirer credentials
2. View assigned enquiries in dashboard
3. Contact prospects and update status
4. Add notes for follow-up tracking

This system ensures that enquiry submission is completely open and accessible while maintaining proper organization and security for internal staff operations.