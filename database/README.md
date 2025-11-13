# Database Setup Instructions

This directory contains the database schema and setup scripts for the Integrated Web Portal & CRM system.

## Files

- `setup.sql` - Complete database schema with tables, indexes, and initial data
- `migrate.php` - PHP script to execute the database setup
- `verify.php` - Script to verify that the database was set up correctly

## Setup Instructions

### 1. Create Database

First, create a MySQL database:

```sql
CREATE DATABASE integrated_crm_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Update Configuration

Edit `includes/config.php` and update the database connection settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'integrated_crm_portal');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Run Migration

Execute the migration script from the command line:

```bash
php database/migrate.php
```

Or run it through a web browser by accessing:
```
http://your-domain/database/migrate.php
```

### 4. Verify Setup

Run the verification script to ensure everything was set up correctly:

```bash
php database/verify.php
```

## Default Admin Account

After setup, a default admin account is created:

- **Username:** admin
- **Password:** admin123
- **Role:** admin

**Important:** Change the default password immediately after first login.

## Database Schema Overview

### Tables

1. **institutions** - Educational institutions in the system
2. **users** - System users (admin, faculty, enquirers)
3. **students** - Student records managed by institutions
4. **enquiries** - Lead management for potential students
5. **marklists** - Student academic records
6. **certificate_requests** - Certificate generation requests
7. **call_logs** - CRM call tracking and follow-ups

### Key Features

- **Security:** Foreign key constraints and proper relationships
- **Performance:** Strategic indexes for common queries
- **Search:** Full-text search capabilities on key fields
- **Audit:** Created/updated timestamps on all records
- **Views:** Pre-built views for common data combinations

## Troubleshooting

### Connection Issues

1. Verify database credentials in `includes/config.php`
2. Ensure MySQL server is running
3. Check that the database exists
4. Verify user permissions

### Migration Errors

1. Check MySQL error logs
2. Ensure sufficient privileges for the database user
3. Verify PHP PDO MySQL extension is installed
4. Check file permissions on the includes directory

### Performance

The schema includes optimized indexes for:
- User role-based queries
- Student filtering by institution and course
- Enquiry status and assignment tracking
- Date-based reporting queries

For large datasets, consider additional indexing based on your specific query patterns.