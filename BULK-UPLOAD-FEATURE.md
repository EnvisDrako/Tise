# Bulk Photo Upload Feature

## ✅ What's New

Added a **Bulk Upload from PC** feature to the gallery management system, allowing admins to upload multiple photos directly from their computer without needing Google Drive or external hosting.

## 🎯 Features

### 1. **Drag & Drop Interface**
- Drag multiple photos directly into the upload area
- Visual feedback with hover effects
- Instant preview of selected photos

### 2. **Browse & Select**
- Click to open file browser
- Select multiple files at once
- Filter shows only image files

### 3. **File Preview**
- Thumbnail preview of all selected photos
- Shows filename for each image
- Remove individual files before upload

### 4. **Progress Tracking**
- Real-time upload progress bar
- Percentage indicator
- Success/error messages

### 5. **Smart Validation**
- Supported formats: JPG, PNG, GIF, WebP
- Maximum file size: 5MB per image
- Automatic error handling for invalid files

## 📁 File Structure

```
public/
├── uploads/
│   ├── .htaccess (security - allows images, blocks PHP)
│   └── gallery/
│       ├── .gitkeep
│       └── [uploaded photos stored here]
└── admin/website/
    ├── gallery.php (updated with bulk upload UI)
    └── upload-gallery-photos.php (upload handler)
```

## 🚀 How to Use

### For Admins:

1. **Navigate to Gallery**
   - Go to `Admin → Website → Gallery`

2. **Select Event**
   - Click "Manage Photos" on any event

3. **Choose Upload Method**
   - Click "Bulk Upload from PC" button

4. **Add Photos**
   - **Option A**: Drag & drop photos into the upload area
   - **Option B**: Click "Browse Files" to select photos

5. **Review & Upload**
   - Preview shows all selected photos
   - Remove any unwanted photos (click × button)
   - Click "Upload Photos" button

6. **Wait for Completion**
   - Progress bar shows upload status
   - Success message appears when done
   - Page auto-refreshes to show new photos

## 🔒 Security Features

### Upload Handler (`upload-gallery-photos.php`)
- ✅ Requires admin authentication
- ✅ Validates file types (images only)
- ✅ Checks file size limits (5MB max)
- ✅ Generates unique filenames (prevents overwrites)
- ✅ Sanitizes input data

### .htaccess Protection
- ✅ Allows image files only
- ✅ Blocks PHP execution in uploads folder
- ✅ Prevents directory listing

## 📊 Technical Details

### Supported File Types
```
- image/jpeg (.jpg, .jpeg)
- image/png (.png)
- image/gif (.gif)
- image/webp (.webp)
```

### File Naming Convention
```
gallery_{EVENT_ID}_{TIMESTAMP}_{UNIQUE_ID}.{extension}

Example: gallery_5_1732123456_abc123def.jpg
```

### Database Integration
- Photos automatically linked to event
- Photo count updated in real-time
- Original filename saved as title
- Image URL stored as relative path

## 🎨 UI Components

### Upload Area
- Dashed border with hover effect
- Drag-over state highlighting
- Large upload icon
- Clear instructions

### File Preview Grid
- Responsive grid layout
- Thumbnail images (120px height)
- Remove button on each preview
- Filename display

### Progress Indicator
- Animated gradient progress bar
- Percentage text
- Success/error messages with icons

## 💡 Benefits Over Google Drive

| Feature | Google Drive | Bulk Upload |
|---------|-------------|-------------|
| Setup Time | Slow (upload, share, convert) | Fast (direct upload) |
| Multiple Files | One at a time | All at once |
| File Management | External service | Your server |
| Link Format | Complex conversion needed | Automatic |
| Privacy | Public sharing required | Server-controlled |
| Speed | Depends on Drive | Direct to server |

## 🔧 Configuration

### Adjust Upload Limits

Edit `upload-gallery-photos.php`:

```php
// Maximum file size (default: 5MB)
$maxFileSize = 5 * 1024 * 1024;

// Allowed file types
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
```

### PHP Settings

Ensure your `php.ini` allows sufficient upload size:

```ini
upload_max_filesize = 10M
post_max_size = 20M
max_file_uploads = 20
```

## 🐛 Troubleshooting

### "Upload failed" error
- Check folder permissions (uploads/gallery should be writable)
- Verify PHP upload limits
- Check available disk space

### Images not displaying
- Verify .htaccess is in uploads folder
- Check file permissions (644 for images)
- Ensure correct path in database

### "File too large" error
- Reduce image file size before upload
- Or increase `$maxFileSize` in upload handler

## 📝 Future Enhancements

Potential improvements:
- [ ] Image compression on upload
- [ ] Automatic thumbnail generation
- [ ] Batch edit (titles, descriptions)
- [ ] Reorder photos with drag & drop
- [ ] Bulk delete option
- [ ] Image cropping/editing
- [ ] EXIF data extraction

## 🔗 Related Files

- `public/admin/website/gallery.php` - Main gallery management
- `public/admin/website/upload-gallery-photos.php` - Upload handler
- `public/admin/website/google-drive-images-guide.php` - Updated guide
- `public/uploads/gallery/` - Storage directory
- `GALLERY-IMPROVEMENTS.md` - Previous improvements

## ✨ Summary

The bulk upload feature makes adding photos to the gallery **10x faster** than using Google Drive links. Simply drag, drop, and upload - no external services needed!
