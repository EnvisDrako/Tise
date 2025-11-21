# Gallery System - Setup Instructions

## 🚀 Quick Start

### 1. **Run Setup Script** (One-time)
Visit: `/setup-uploads-directory.php`

This will:
- ✅ Create uploads directory structure
- ✅ Set proper permissions
- ✅ Create security files (.htaccess)
- ✅ Test upload capability
- ✅ Check PHP configuration

### 2. **Access Gallery Admin**
Visit: `/admin/website/gallery.php`

## 📸 Adding Photos - Four Methods

### Method 0: Event Thumbnail Upload (NEW!) 🌟
**Best for**: Event cover images when creating events

1. Click "Create New Event" or "Edit" event
2. In Thumbnail section, choose "Upload from PC" tab
3. Click to select thumbnail image
4. Preview appears instantly
5. Save event

**Advantages:**
- ✅ Upload thumbnail while creating event
- ✅ No need for external hosting
- ✅ Instant preview
- ✅ Easy to change

### Method 1: Bulk Upload from PC (Recommended) ⭐
**Best for**: Multiple photos, local files

1. Click "Manage Photos" on any event
2. Click "Bulk Upload from PC"
3. Drag & drop photos or browse to select
4. Click "Upload Photos"

**Advantages:**
- ✅ Upload many photos at once
- ✅ Drag & drop interface
- ✅ No external services needed
- ✅ Real-time progress tracking
- ✅ Instant preview

### Method 2: Add from URL
**Best for**: Single photos, external hosting

1. Click "Manage Photos" on any event
2. Click "Add from URL"
3. Paste image URL
4. Add title/description (optional)
5. Click "Add Photo"

**Works with:**
- Google Drive (converted format)
- Imgur
- Any direct image URL

### Method 3: Google Drive Links
**Best for**: Photos already on Google Drive

1. Upload image to Google Drive
2. Get shareable link
3. Convert to direct format using Test Tool
4. Use "Add from URL" method

**Important:** Folder links don't work - use individual file links!

## 🔧 Tools Available

### Test Image URL Tool
**Location:** `/admin/website/test-image-url.php`

**Use it to:**
- Test if an image URL works
- Auto-convert Google Drive links
- Verify before adding to gallery

### Google Drive Guide
**Location:** `/admin/website/google-drive-images-guide.php`

**Learn about:**
- How to get shareable links
- Converting to direct format
- Why folder links don't work
- Alternative hosting options

## 📁 Directory Structure

```
public/
├── uploads/
│   ├── .htaccess              # Security (blocks PHP, allows images)
│   └── gallery/
│       ├── .gitkeep
│       └── gallery_*.jpg      # Uploaded photos
│
├── admin/website/
│   ├── gallery.php            # Main gallery management
│   ├── upload-gallery-photos.php  # Upload handler
│   ├── test-image-url.php     # URL testing tool
│   └── google-drive-images-guide.php  # Documentation
│
├── gallery.php                # Public gallery page
├── gallery-event.php          # Public event photos page
└── setup-uploads-directory.php  # One-time setup
```

## 🔒 Security Features

1. **Admin Authentication Required**
   - Only logged-in admins can upload

2. **File Type Validation**
   - Only images allowed (JPG, PNG, GIF, WebP)
   - PHP files blocked

3. **.htaccess Protection**
   - Prevents PHP execution in uploads folder
   - Allows only image files

4. **File Size Limits**
   - Maximum 5MB per image
   - Configurable in upload handler

5. **Unique Filenames**
   - Prevents overwrites
   - Format: `gallery_{EVENT_ID}_{TIMESTAMP}_{UNIQUE_ID}.ext`

## ⚙️ Configuration

### Adjust Upload Limits

Edit `public/admin/website/upload-gallery-photos.php`:

```php
// Line ~30
$maxFileSize = 5 * 1024 * 1024; // Change to 10MB: 10 * 1024 * 1024
```

### PHP Settings (if needed)

Create `.htaccess` in root or edit `php.ini`:

```ini
php_value upload_max_filesize 10M
php_value post_max_size 20M
php_value max_file_uploads 20
```

## 🐛 Troubleshooting

### "Upload failed" error
**Solution:**
1. Run `/setup-uploads-directory.php`
2. Check folder permissions: `chmod 755 public/uploads/gallery`
3. Verify PHP upload limits
4. Check available disk space

### Images not displaying
**Solution:**
1. Check `.htaccess` exists in `uploads/` folder
2. Verify file permissions: `chmod 644` for images
3. Check browser console for errors
4. Verify database has correct path

### "File too large" error
**Solution:**
1. Reduce image file size before upload
2. Or increase `$maxFileSize` in upload handler
3. Check PHP `upload_max_filesize` setting

### Google Drive images not loading
**Solution:**
1. Use Test Tool to verify URL format
2. Check sharing permissions (must be public)
3. Use converted format: `https://drive.google.com/uc?id=FILE_ID`
4. Remember: Folder links don't work!

## 📊 Comparison: Upload Methods

| Feature | Event Thumbnail | Bulk Upload | URL Method | Google Drive |
|---------|----------------|-------------|------------|--------------|
| **Speed** | ⚡ Instant | ⚡ Fast | ⚡ Fast | 🐌 Slow |
| **Multiple Files** | ❌ Single | ✅ Yes | ❌ One at a time | ❌ One at a time |
| **Setup Required** | ✅ One-time | ✅ One-time | ❌ None | 🔧 Complex |
| **External Service** | ❌ No | ❌ No | Depends | ✅ Yes |
| **Privacy** | 🔒 Your server | 🔒 Your server | Depends | 🌐 Public |
| **Best For** | Event covers | Many photos | Single photos | Already on Drive |
| **When to Use** | Creating event | Adding photos | Quick add | Legacy content |

## 🎯 Recommended Workflow

### For New Events:
1. Create event in Gallery Management
2. Use **Bulk Upload** to add all photos at once
3. Set thumbnail from uploaded photos
4. Publish event

### For Single Photos:
1. Use **Add from URL** if already hosted
2. Or use **Bulk Upload** for local files

### For Google Drive Users:
1. Consider using **Bulk Upload** instead
2. If must use Drive, follow the guide carefully
3. Use **Test Tool** to verify links work

## ✅ Checklist

Before using the gallery system:

- [ ] Run `/setup-uploads-directory.php`
- [ ] Verify uploads folder is writable
- [ ] Check PHP upload limits are sufficient
- [ ] Test bulk upload with sample photos
- [ ] Review security settings
- [ ] Bookmark admin tools for easy access

## 🔗 Quick Links

- **Gallery Admin**: `/admin/website/gallery.php`
- **Setup Script**: `/setup-uploads-directory.php`
- **Test Tool**: `/admin/website/test-image-url.php`
- **Drive Guide**: `/admin/website/google-drive-images-guide.php`
- **Public Gallery**: `/gallery.php`

## 📚 Documentation

- `GALLERY-IMPROVEMENTS.md` - CSS and UI improvements
- `BULK-UPLOAD-FEATURE.md` - Detailed bulk upload docs
- `SETUP-INSTRUCTIONS.md` - This file

---

**Need Help?** Check the troubleshooting section or review the Google Drive guide for common issues.
