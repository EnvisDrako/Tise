<?php
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
$pageTitle = 'Google Drive Images Guide';
renderAdminHeader($pageTitle);
?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fab fa-google-drive"></i> How to Use Google Drive Images</h1>
        <p>Step-by-step guide to using Google Drive images in your courses and trainer profiles</p>
    </div>
    
    <div class="guide-content">
        <div class="guide-section">
            <h2>📤 Step 1: Upload Image to Google Drive</h2>
            <ol>
                <li>Go to <a href="https://drive.google.com" target="_blank">Google Drive</a></li>
                <li>Click <strong>New</strong> → <strong>File upload</strong></li>
                <li>Select your image file (JPG, PNG, etc.)</li>
                <li>Wait for upload to complete</li>
            </ol>
        </div>
        
        <div class="guide-section">
            <h2>🔗 Step 2: Get Shareable Link</h2>
            <ol>
                <li>Right-click on the uploaded image</li>
                <li>Click <strong>Share</strong></li>
                <li>Click <strong>Change to anyone with the link</strong></li>
                <li>Make sure it's set to <strong>Viewer</strong></li>
                <li>Click <strong>Copy link</strong></li>
            </ol>
            <div class="example-box">
                <strong>Example link you'll get:</strong><br>
                <code>https://drive.google.com/file/d/1ABC123xyz456DEF789/view?usp=sharing</code>
            </div>
        </div>
        
        <div class="guide-section">
            <h2>🔧 Step 3: Convert to Direct Image URL</h2>
            <ol>
                <li>From the link, copy the <strong>FILE_ID</strong> (the part between /d/ and /view)</li>
                <li>Use this format: <code>https://drive.google.com/uc?id=FILE_ID</code></li>
            </ol>
            <div class="example-box">
                <strong>Original link:</strong><br>
                <code>https://drive.google.com/file/d/<span class="highlight">1ABC123xyz456DEF789</span>/view?usp=sharing</code>
                <br><br>
                <strong>FILE_ID:</strong> <code class="highlight">1ABC123xyz456DEF789</code>
                <br><br>
                <strong>Final URL to use:</strong><br>
                <code class="success">https://drive.google.com/uc?id=1ABC123xyz456DEF789</code>
            </div>
        </div>
        
        <div class="guide-section">
            <h2>✅ Step 4: Use in Admin Panel</h2>
            <ol>
                <li>Go to <a href="courses.php">Courses</a> or <a href="trainers.php">Trainers</a></li>
                <li>Click <strong>Add New</strong> or <strong>Edit</strong></li>
                <li>Paste the converted URL in the <strong>Image URL</strong> field</li>
                <li>Save the course/trainer</li>
            </ol>
        </div>
        
        <div class="guide-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 style="color: white; border-bottom-color: rgba(255,255,255,0.3);">💡 Easier Option: Bulk Upload from PC</h2>
            <p>For gallery photos, you can now upload directly from your computer!</p>
            <ol style="color: white;">
                <li>Go to <a href="gallery.php" style="color: #ffd700;">Gallery Management</a></li>
                <li>Click <strong>Manage Photos</strong> on any event</li>
                <li>Click <strong>"Bulk Upload from PC"</strong></li>
                <li>Drag & drop multiple photos or browse to select</li>
                <li>Click <strong>Upload Photos</strong></li>
            </ol>
            <p style="margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.2); border-radius: 8px;">
                <strong>✨ Benefits:</strong> No need to upload to Google Drive first! Upload directly from your computer. Supports JPG, PNG, GIF, and WebP up to 5MB each.
            </p>
        </div>
        
        <div class="guide-section tips">
            <h2>💡 Tips & Best Practices</h2>
            <ul>
                <li>✅ Use high-quality images (at least 800x600 pixels)</li>
                <li>✅ Keep file sizes under 2MB for faster loading</li>
                <li>✅ Use JPG for photos, PNG for graphics with transparency</li>
                <li>✅ Make sure the file is set to "Anyone with the link can view"</li>
                <li>⚠️ Don't delete the file from Google Drive or the image will break</li>
                <li>⚠️ If image doesn't show, check sharing permissions</li>
            </ul>
        </div>
        
        <div class="guide-section warning-box">
            <h2>⚠️ Important: Google Drive Folders vs Individual Files</h2>
            <div class="warning-content">
                <h3>Why Folder Links Don't Work Directly</h3>
                <p>Google Drive folder links (like <code>https://drive.google.com/drive/folders/xxxxx</code>) cannot be used to display images directly on websites because:</p>
                <ul>
                    <li>🚫 Folder links require Google account login to view</li>
                    <li>🚫 Cannot be embedded as direct image sources</li>
                    <li>🚫 No API access without authentication</li>
                </ul>
                
                <h3 style="margin-top: 1.5rem;">✅ Solution: Use Individual Image Links</h3>
                <p>For each photo in your gallery event:</p>
                <ol>
                    <li>Open the Google Drive folder</li>
                    <li>Right-click each image individually</li>
                    <li>Get the shareable link for that specific image</li>
                    <li>Convert it to direct URL format (see Step 3 above)</li>
                    <li>Add each image separately in the gallery</li>
                </ol>
                
                <div class="tip-box">
                    <strong>💡 Pro Tip:</strong> While you can store the folder ID for reference, you still need to add each photo individually with its own direct link. The folder ID is just for organization purposes.
                </div>
            </div>
        </div>
        
        <div class="guide-section alternatives">
            <h2>🔄 Alternative Options</h2>
            <div class="alt-grid">
                <div class="alt-card">
                    <h3>Local Upload</h3>
                    <p>Upload images to <code>/public/images/</code> folder on your server</p>
                    <p><strong>Format:</strong> <code>/images/filename.jpg</code></p>
                </div>
                <div class="alt-card">
                    <h3>External URL</h3>
                    <p>Use any direct image URL from the internet</p>
                    <p><strong>Format:</strong> <code>https://example.com/image.jpg</code></p>
                </div>
                <div class="alt-card">
                    <h3>Imgur</h3>
                    <p>Upload to <a href="https://imgur.com" target="_blank">Imgur</a> and use direct link</p>
                    <p><strong>Format:</strong> <code>https://i.imgur.com/xxxxx.jpg</code></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.guide-content {
    max-width: 900px;
    margin: 0 auto;
}

.guide-section {
    background: white;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.guide-section h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #3498db;
}

.guide-section ol, .guide-section ul {
    margin-left: 1.5rem;
    line-height: 2;
}

.guide-section li {
    margin-bottom: 0.5rem;
}

.example-box {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
    border-left: 4px solid #3498db;
}

.example-box code {
    background: #e9ecef;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    word-break: break-all;
}

.highlight {
    background: #fff3cd;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-weight: bold;
}

.success {
    color: #28a745;
    font-weight: bold;
}

.tips {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.tips h2 {
    color: white;
    border-bottom-color: rgba(255,255,255,0.3);
}

.tips ul {
    list-style: none;
    margin-left: 0;
}

.tips li {
    padding-left: 1.5rem;
    position: relative;
}

.alternatives {
    background: #f8f9fa;
}

.alt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.alt-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.alt-card h3 {
    color: #3498db;
    margin-bottom: 0.5rem;
}

.alt-card code {
    background: #e9ecef;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.warning-box {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    border-left: 5px solid #c92a2a;
}

.warning-box h2 {
    color: white;
    border-bottom-color: rgba(255,255,255,0.3);
}

.warning-box h3 {
    color: white;
    margin-top: 1rem;
    font-size: 1.2rem;
}

.warning-content ul, .warning-content ol {
    margin-left: 1.5rem;
}

.warning-content code {
    background: rgba(0,0,0,0.2);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}

.tip-box {
    background: rgba(255,255,255,0.2);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
    border-left: 4px solid rgba(255,255,255,0.5);
}
</style>

<?php renderAdminFooter(); ?>
