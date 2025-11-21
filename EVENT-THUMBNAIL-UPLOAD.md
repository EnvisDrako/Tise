# Event Thumbnail Upload Feature

## ✨ What's New

You can now upload event thumbnail images directly from your PC when creating or editing gallery events - no need for Google Drive or external hosting!

## 🎯 Features

### Tab-Based Interface
- **Upload from PC**: Select and upload image files directly
- **Use URL**: Paste image URLs (Google Drive, Imgur, etc.)
- Easy switching between methods

### Live Preview
- See thumbnail before saving
- Instant visual feedback
- Remove and change easily

### Smart Validation
- Supported formats: JPG, PNG, GIF, WebP
- Maximum file size: 5MB
- Automatic error messages

## 📖 How to Use

### Creating a New Event

1. **Open Gallery Management**
   - Navigate to `Admin → Website → Gallery`
   - Click "Create New Event/Album"

2. **Fill Event Details**
   - Enter title (required)
   - Add description
   - Set event date
   - Choose category

3. **Upload Thumbnail**
   - In "Thumbnail Image" section
   - **Option A - Upload from PC** (default):
     - Click the upload area
     - Select image from your computer
     - Preview appears instantly
     - Click "Remove" to change
   
   - **Option B - Use URL**:
     - Click "Use URL" tab
     - Paste image URL
     - Works with Google Drive, Imgur, etc.

4. **Save Event**
   - Click "Save Event"
   - Thumbnail is uploaded and saved

### Editing an Existing Event

1. **Open Event**
   - Click "Edit" on any event card

2. **Change Thumbnail**
   - If event has thumbnail, URL tab is shown
   - Switch to "Upload from PC" to replace with new file
   - Or update URL in "Use URL" tab

3. **Save Changes**
   - Click "Save Event"

## 🎨 Interface Details

### Upload Tab
```
┌─────────────────────────────────────┐
│  [Upload from PC] [Use URL]         │
├─────────────────────────────────────┤
│                                     │
│         ☁️ Upload Icon              │
│   Click to select thumbnail image   │
│   JPG, PNG, GIF, WebP - Max 5MB    │
│                                     │
└─────────────────────────────────────┘
```

### After Selection
```
┌─────────────────────────────────────┐
│  [Upload from PC] [Use URL]         │
├─────────────────────────────────────┤
│                                     │
│      [Thumbnail Preview Image]      │
│                                     │
│         [× Remove Button]           │
│                                     │
└─────────────────────────────────────┘
```

### URL Tab
```
┌─────────────────────────────────────┐
│  [Upload from PC] [Use URL]         │
├─────────────────────────────────────┤
│                                     │
│  [Paste image URL or Drive link]    │
│   Paste image URL or Google Drive   │
│                                     │
└─────────────────────────────────────┘
```

## 💡 Benefits

### vs Google Drive URLs
| Feature | Thumbnail Upload | Google Drive URL |
|---------|-----------------|------------------|
| Setup Time | ⚡ Instant | 🐌 Slow (upload, share, convert) |
| Steps Required | 1 click | 5+ steps |
| Link Management | ❌ Not needed | ✅ Required |
| Privacy | 🔒 Your server | 🌐 Public sharing |
| Reliability | ✅ Always works | ⚠️ Depends on Drive |

### vs External Hosting
- No need to upload to Imgur or other services
- No broken links if external service changes
- Full control over your images
- Faster loading (same server)

## 🔧 Technical Details

### File Processing
1. User selects file
2. Client-side validation (type, size)
3. Preview generated using FileReader API
4. On form submit, file uploaded via multipart/form-data
5. Server validates again
6. File saved with unique name
7. Path stored in database

### File Naming Convention
```
event_thumb_{TIMESTAMP}_{UNIQUE_ID}.{extension}

Example: event_thumb_1732123456_abc123def.jpg
```

### Storage Location
```
public/uploads/gallery/event_thumb_*.{jpg|png|gif|webp}
```

### Database Storage
- Stored as relative path: `/uploads/gallery/filename.jpg`
- Same field as URL method (`thumbnail_url`)
- Seamless switching between methods

## 🔒 Security

### Client-Side Validation
- File type check (images only)
- File size check (5MB max)
- Preview before upload

### Server-Side Validation
- MIME type verification
- File size limit enforcement
- Secure filename generation
- Directory traversal prevention

### File System Security
- .htaccess blocks PHP execution
- Only image files accessible
- Unique filenames prevent overwrites
- Proper file permissions (644)

## 🎯 Use Cases

### Perfect For:
- ✅ Creating new events with local images
- ✅ Quick event setup without external tools
- ✅ Events with photos already on your computer
- ✅ Maintaining full control over images

### Consider URL Method For:
- Images already hosted elsewhere
- Google Drive photos (if already uploaded)
- External CDN images
- Shared resources

## 🐛 Troubleshooting

### "File too large" error
**Solution:**
- Reduce image file size
- Use image compression tool
- Or increase limit in `gallery.php` (line ~60)

### Preview not showing
**Solution:**
- Check browser console for errors
- Ensure file is valid image format
- Try different browser
- Clear browser cache

### Upload fails silently
**Solution:**
- Check uploads folder permissions (755)
- Verify PHP upload limits
- Check available disk space
- Review server error logs

### Thumbnail not displaying after save
**Solution:**
- Verify file was uploaded to `/uploads/gallery/`
- Check file permissions (644)
- Verify .htaccess allows image access
- Check database for correct path

## 📝 Code Reference

### Form Handling (PHP)
```php
// File: public/admin/website/gallery.php
// Lines: ~45-75

if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
    // Validate and upload file
    // Generate unique filename
    // Move to uploads directory
    // Store path in database
}
```

### Preview Function (JavaScript)
```javascript
// File: public/admin/website/gallery.php
// Lines: ~580-610

function previewThumbnail(input) {
    // Validate file type and size
    // Generate preview using FileReader
    // Display preview image
}
```

## 🚀 Future Enhancements

Potential improvements:
- [ ] Image cropping before upload
- [ ] Automatic thumbnail generation (multiple sizes)
- [ ] Drag & drop directly on upload area
- [ ] Image compression on upload
- [ ] Batch thumbnail upload for multiple events
- [ ] Image editing (rotate, resize)

## 📚 Related Documentation

- `GALLERY-IMPROVEMENTS.md` - All gallery improvements
- `BULK-UPLOAD-FEATURE.md` - Bulk photo upload docs
- `SETUP-INSTRUCTIONS.md` - Complete setup guide

## ✅ Summary

The event thumbnail upload feature makes creating gallery events **faster and easier** by allowing direct image uploads from your computer. No more external hosting, link conversion, or complex setup - just click, select, and save!

**Key Points:**
- 🎯 Upload directly when creating events
- 👁️ Live preview before saving
- 🔄 Easy switching between upload and URL
- 🔒 Secure and validated
- ⚡ Fast and simple
