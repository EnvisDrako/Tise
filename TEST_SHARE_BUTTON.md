# Testing Share Button

## How to Test:

1. **Open the website** in your browser
2. **Scroll to the footer**
3. **Click the "Share" button** (with share icon)
4. **Modal should open** with dark background
5. **You should see**:
   - White text: "Share This Page"
   - 6 colored buttons:
     - Facebook (blue)
     - Twitter (light blue)
     - LinkedIn (professional blue)
     - WhatsApp (green)
     - Email (red)
     - Copy Link (gray)

## Testing Each Button:

### Facebook:
- Click the Facebook button
- Should open: `https://www.facebook.com/sharer/sharer.php?u=[YOUR_URL]`
- Facebook share dialog should appear

### Twitter:
- Click the Twitter button
- Should open: `https://twitter.com/intent/tweet?url=[YOUR_URL]&text=[PAGE_TITLE]`
- Twitter compose dialog should appear

### LinkedIn:
- Click the LinkedIn button
- Should open: `https://www.linkedin.com/sharing/share-offsite/?url=[YOUR_URL]`
- LinkedIn share dialog should appear

### WhatsApp:
- Click the WhatsApp button
- Should open: `https://wa.me/?text=[PAGE_TITLE] [URL]`
- WhatsApp should open (desktop app or web)

### Email:
- Click the Email button
- Should open: `mailto:?subject=[PAGE_TITLE]&body=[DESCRIPTION]`
- Your default email client should open

### Copy Link:
- Click the Copy Link button
- Button text should change to "Copied!" for 2 seconds
- URL should be copied to clipboard
- Try pasting (Ctrl+V) to verify

## If Buttons Don't Work:

### Check Browser Console:
1. Press F12 to open Developer Tools
2. Go to Console tab
3. Click the Share button
4. Look for any errors in red

### Common Issues:

**Modal doesn't open:**
- Check if JavaScript file is loaded
- Check browser console for errors
- Verify `seo-social.js` is included in footer

**Buttons don't have links:**
- The `updateShareLinks()` function should run when modal opens
- Check if `href` attributes are being set
- Inspect the buttons in Developer Tools (F12 > Elements)

**Copy Link doesn't work:**
- Requires HTTPS or localhost
- Check if clipboard API is available
- Try the fallback method

## Quick Fix:

If nothing works, try:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+Shift+R or Ctrl+F5)
3. Check if JavaScript is enabled
4. Try in incognito/private mode

## Manual Test:

Open browser console (F12) and run:
```javascript
// Test if function exists
console.log(typeof initializeSocialSharing);

// Test if modal exists
console.log(document.getElementById('social-share-modal'));

// Test if button exists
console.log(document.getElementById('share-page-btn'));

// Manually open modal
document.getElementById('social-share-modal').classList.add('active');
```

If any of these return `null` or `undefined`, there's a loading issue.
