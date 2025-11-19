# Taset Academy - Logo & Image Requirements

## Summary of Changes Made

✅ **Site Name Updated**: Changed from "EduPortal" to "Taset Academy" throughout the system
✅ **Image Folders Created**: Organized structure for logos, icons, and banners
✅ **Header Updated**: Logo placeholder with fallback to text
✅ **Footer Updated**: Logo placeholder with fallback to text
✅ **Configuration Updated**: SITE_NAME constant set to "Taset Academy"

## Required Files Checklist

### Priority 1 - Essential Files

#### Main Logo Files (Place in: `public/images/logos/`)
- [ ] **logo.png** - Main logo for light backgrounds
  - Recommended size: 300x80px
  - Format: PNG with transparent background
  - Used in: Header navigation, admin portal
  
- [ ] **logo-white.png** - Logo for dark backgrounds
  - Recommended size: 300x80px
  - Format: PNG with transparent background
  - Used in: Footer, dark theme areas

#### Favicon Files (Place in: `public/images/icons/`)
- [ ] **favicon.ico** - Standard favicon
  - Sizes: 16x16, 32x32, 48x48 (multi-resolution)
  - Format: ICO file
  
- [ ] **favicon-16x16.png** - Small favicon
  - Size: 16x16px
  - Format: PNG
  
- [ ] **favicon-32x32.png** - Standard favicon
  - Size: 32x32px
  - Format: PNG
  
- [ ] **apple-touch-icon.png** - iOS home screen icon
  - Size: 180x180px
  - Format: PNG

### Priority 2 - Social Media & SEO

#### Social Sharing (Place in: `public/images/`)
- [ ] **og-default.jpg** - Default social media preview image
  - Size: 1200x630px
  - Format: JPG (optimized)
  - Used when: Sharing pages on Facebook, LinkedIn, Twitter

#### Square Logo (Place in: `public/images/logos/`)
- [ ] **logo-square.png** - Square version for social media
  - Size: 512x512px
  - Format: PNG with transparent background

### Priority 3 - Website Content

#### Banner Images (Place in: `public/images/banners/`)
- [ ] **hero-banner.jpg** - Homepage hero section
  - Size: 1920x600px
  - Format: JPG (optimized)
  
- [ ] **about-banner.jpg** - About page banner
- [ ] **courses-banner.jpg** - Courses page banner
- [ ] **contact-banner.jpg** - Contact page banner

#### Certification Logos (Place in: `public/images/`)
- [ ] **cert-iso.png** - ISO certification badge
- [ ] **cert-accredited.png** - Accreditation badge

## File Upload Instructions

### Method 1: Direct Upload
1. Navigate to `D:\xampp\htdocs\integrated-crm\public\images\`
2. Place files in appropriate subfolders:
   - Logos → `logos/`
   - Icons/Favicons → `icons/`
   - Banners → `banners/`
3. Ensure exact file names as specified above

### Method 2: FTP Upload (for production)
1. Connect to server via FTP
2. Navigate to `/public/images/`
3. Upload files to respective folders

## Design Specifications

### Logo Design Guidelines
- **Style**: Clean, professional, educational
- **Colors**: Should work on both light and dark backgrounds
- **Text**: "Taset Academy" should be readable
- **Format**: Vector-based (provide PNG exports)
- **Variations needed**:
  - Full color on white/light background
  - White/light version for dark backgrounds
  - Square version for social media

### Color Palette (Suggested)
- Primary: #3498db (Blue)
- Secondary: #2ecc71 (Green)
- Accent: #e74c3c (Red)
- Text: #2c3e50 (Dark Gray)
- Background: #ffffff (White)

### Typography (Suggested)
- Headings: Bold, modern sans-serif
- Body: Clean, readable sans-serif
- Suggested fonts: Montserrat, Poppins, Open Sans, Roboto

## Current Fallback Behavior

Until logo files are provided, the system will display:
- **Header**: 🎓 Taset Academy (graduation cap icon + text)
- **Footer**: 🎓 Taset Academy (graduation cap icon + text)

This ensures the website remains fully functional and branded correctly.

## Contact Information Updated

The following contact details are currently set as placeholders:
- **Email**: info@tasetacademy.com
- **Phone**: +1 234 567 8900
- **Address**: 123 Education Street, Knowledge City
- **Social Media**: @tasetacademy (all platforms)

Please provide actual contact information to update these placeholders.

## Testing After Upload

After uploading logo files:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Visit homepage to verify header logo
3. Scroll to footer to verify footer logo
4. Check favicon in browser tab
5. Test social media sharing preview

## Need Help?

If you need assistance with:
- Image optimization
- File format conversion
- Logo design recommendations
- Technical implementation

Contact the development team for support.

---

**Last Updated**: November 18, 2025
**Status**: Awaiting logo files from client
