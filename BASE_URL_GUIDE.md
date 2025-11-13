# BASE_URL Configuration Guide

## Overview

This project now supports dynamic BASE_URL configuration to work seamlessly in both localhost and production environments.

## Configuration

The BASE_URL is automatically detected in `includes/config.php`:

```php
// Auto detect base URL
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    define('BASE_URL', '/integrated-crm/public');
    define('SITE_URL', 'http://localhost/integrated-crm/public');
} else {
    define('BASE_URL', ''); // live server root
    define('SITE_URL', 'https://' . $_SERVER['SERVER_NAME']);
}
```

### Localhost Setup
- **BASE_URL**: `/integrated-crm/public`
- **SITE_URL**: `http://localhost/integrated-crm/public`

### Production Setup
- **BASE_URL**: `` (empty - root directory)
- **SITE_URL**: `https://yourdomain.com`

## Helper Functions

Three helper functions are available in `includes/functions.php`:

### 1. `asset($path)` - For CSS, JS, Images, and Static Files

Use this for all static assets like CSS, JavaScript, images, fonts, etc.

```php
<!-- CSS Files -->
<link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">

<!-- JavaScript Files -->
<script src="<?php echo asset('js/main.js'); ?>"></script>
<script src="<?php echo asset('js/dashboard.js'); ?>"></script>

<!-- Images -->
<img src="<?php echo asset('images/logo.png'); ?>" alt="Logo">
<img src="<?php echo asset('images/hero-bg.jpg'); ?>" alt="Hero">

<!-- Favicons -->
<link rel="icon" href="<?php echo asset('favicon.ico'); ?>">

<!-- Fonts -->
<link rel="stylesheet" href="<?php echo asset('fonts/custom-font.css'); ?>">
```

### 2. `url($path)` - For Page Links and Navigation

Use this for all internal page links and navigation.

```php
<!-- Navigation Links -->
<a href="<?php echo url('/'); ?>">Home</a>
<a href="<?php echo url('about'); ?>">About</a>
<a href="<?php echo url('courses'); ?>">Courses</a>
<a href="<?php echo url('contact'); ?>">Contact</a>

<!-- Form Actions -->
<form action="<?php echo url('login_process.php'); ?>" method="POST">

<!-- Redirects in PHP -->
header('Location: ' . url('dashboard'));
redirect(url('admin/users.php'));

<!-- Admin Portal Links -->
<a href="<?php echo url('admin/'); ?>">Admin Dashboard</a>
<a href="<?php echo url('admin/users.php'); ?>">Manage Users</a>

<!-- Faculty Portal Links -->
<a href="<?php echo url('institution/'); ?>">Faculty Dashboard</a>
<a href="<?php echo url('institution/students.php'); ?>">Students</a>

<!-- Enquiry Portal Links -->
<a href="<?php echo url('enquiry/'); ?>">Enquirer Dashboard</a>
<a href="<?php echo url('enquiry/call_logs.php'); ?>">Call Logs</a>
```

### 3. `currentUrl()` - Get Current Full URL

```php
$current = currentUrl();
// Returns: http://localhost/integrated-crm/public/about.php
```

### 4. `isCurrentPage($path)` - Check Active Page

```php
<!-- Add 'active' class to current page -->
<a href="<?php echo url('about'); ?>" 
   class="nav-link <?php echo isCurrentPage('about') ? 'active' : ''; ?>">
    About
</a>
```

## Usage Examples

### In HTML/PHP Files

```php
<!DOCTYPE html>
<html>
<head>
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/custom.css'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo asset('favicon.ico'); ?>">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <a href="<?php echo url('/'); ?>">Home</a>
        <a href="<?php echo url('about'); ?>">About</a>
        <a href="<?php echo url('contact'); ?>">Contact</a>
    </nav>
    
    <!-- Images -->
    <img src="<?php echo asset('images/logo.png'); ?>" alt="Logo">
    
    <!-- JavaScript -->
    <script src="<?php echo asset('js/jquery.min.js'); ?>"></script>
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
```

### In CSS (Inline Styles)

```php
<style>
.hero-section {
    background-image: url('<?php echo asset('images/hero-bg.jpg'); ?>');
}

.logo {
    background-image: url('<?php echo asset('images/logo.png'); ?>');
}
</style>
```

### In JavaScript (Inline)

```php
<script>
const baseUrl = '<?php echo BASE_URL; ?>';
const apiUrl = '<?php echo url('api/'); ?>';

// AJAX calls
fetch('<?php echo url('api/get_data.php'); ?>')
    .then(response => response.json())
    .then(data => console.log(data));

// Dynamic image loading
const imgSrc = '<?php echo asset('images/dynamic-image.jpg'); ?>';
document.getElementById('myImg').src = imgSrc;
</script>
```

### In PHP Redirects

```php
// Redirect to home
header('Location: ' . url('/'));

// Redirect to login
header('Location: ' . url('login.php'));

// Redirect to admin dashboard
header('Location: ' . url('admin/'));

// Using redirect helper
redirect(url('admin/users.php'));
```

## Migration Checklist

To update existing files to use BASE_URL:

### 1. Update CSS/JS Links
```php
<!-- OLD -->
<link rel="stylesheet" href="/css/style.css">
<script src="/js/main.js"></script>

<!-- NEW -->
<link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
<script src="<?php echo asset('js/main.js'); ?>"></script>
```

### 2. Update Image Sources
```php
<!-- OLD -->
<img src="/images/logo.png" alt="Logo">

<!-- NEW -->
<img src="<?php echo asset('images/logo.png'); ?>" alt="Logo">
```

### 3. Update Navigation Links
```php
<!-- OLD -->
<a href="/about">About</a>
<a href="/admin/">Admin</a>

<!-- NEW -->
<a href="<?php echo url('about'); ?>">About</a>
<a href="<?php echo url('admin/'); ?>">Admin</a>
```

### 4. Update Form Actions
```php
<!-- OLD -->
<form action="/login_process.php" method="POST">

<!-- NEW -->
<form action="<?php echo url('login_process.php'); ?>" method="POST">
```

### 5. Update Background Images in CSS
```php
<!-- OLD -->
<style>
.hero { background-image: url('/images/hero.jpg'); }
</style>

<!-- NEW -->
<style>
.hero { background-image: url('<?php echo asset('images/hero.jpg'); ?>'); }
</style>
```

## Files Already Updated

✅ `includes/config.php` - BASE_URL configuration
✅ `includes/functions.php` - Helper functions added
✅ `includes/public_header.php` - All assets and links updated
✅ `public/index.php` - All assets and links updated

## Files That Need Updating

The following files may still need BASE_URL updates:

### Public Pages
- [ ] `public/about.php`
- [ ] `public/courses.php`
- [ ] `public/contact.php`
- [ ] `public/enquiry.php`
- [ ] `public/login.php`
- [ ] `public/gallery.php`
- [ ] `public/testimonials.php`
- [ ] `public/trainers.php`
- [ ] `public/blog.php`

### Admin Portal
- [ ] All files in `public/admin/`

### Faculty Portal
- [ ] All files in `public/institution/`

### Enquiry Portal
- [ ] All files in `public/enquiry/`

### API
- [ ] All files in `public/api/`

## Testing

### Test on Localhost
1. Access: `http://localhost/integrated-crm/public/`
2. Check that all CSS loads correctly
3. Check that all images display
4. Check that all links work
5. Check that navigation works

### Test on Production
1. Upload to production server
2. Access: `https://yourdomain.com/`
3. Verify all assets load
4. Verify all links work
5. Test all portals (admin, faculty, enquiry)

## Common Issues and Solutions

### Issue: CSS Not Loading
**Solution**: Make sure you're using `asset()` for CSS files:
```php
<link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
```

### Issue: Images Not Displaying
**Solution**: Use `asset()` for image paths:
```php
<img src="<?php echo asset('images/logo.png'); ?>" alt="Logo">
```

### Issue: Links Not Working
**Solution**: Use `url()` for page links:
```php
<a href="<?php echo url('about'); ?>">About</a>
```

### Issue: Background Images Not Showing
**Solution**: Use `asset()` in inline CSS:
```php
<style>
.hero { background-image: url('<?php echo asset('images/hero.jpg'); ?>'); }
</style>
```

## Best Practices

1. **Always use helper functions** - Never hardcode paths
2. **Use `asset()` for files** - CSS, JS, images, fonts
3. **Use `url()` for pages** - Navigation, forms, redirects
4. **Test both environments** - Localhost and production
5. **Update systematically** - One file type at a time

## Quick Reference

| Type | Function | Example |
|------|----------|---------|
| CSS | `asset()` | `asset('css/style.css')` |
| JavaScript | `asset()` | `asset('js/main.js')` |
| Images | `asset()` | `asset('images/logo.png')` |
| Pages | `url()` | `url('about')` |
| Home | `url()` | `url('/')` |
| Admin | `url()` | `url('admin/')` |
| API | `url()` | `url('api/endpoint.php')` |

## Support

If you encounter any issues with BASE_URL configuration:

1. Check that `includes/config.php` is properly configured
2. Verify helper functions exist in `includes/functions.php`
3. Clear browser cache
4. Check server error logs
5. Test with simple echo statements: `<?php echo BASE_URL; ?>`
