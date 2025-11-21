<?php
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../../../includes/auth_check_admin.php';
$pageTitle = 'Test Image URL';
renderAdminHeader($pageTitle);
?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-vial"></i> Test Image URL</h1>
        <p>Verify that your image URL works before adding it to the gallery</p>
    </div>
    
    <div class="test-container">
        <div class="input-section">
            <label for="imageUrl">Paste Image URL:</label>
            <input type="text" id="imageUrl" class="form-control" placeholder="https://drive.google.com/uc?id=YOUR_FILE_ID">
            <button onclick="testImage()" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Test Image
            </button>
            <button onclick="convertDriveLink()" class="btn btn-secondary">
                <i class="fas fa-sync"></i> Auto-Convert Drive Link
            </button>
        </div>
        
        <div id="result" class="result-section" style="display:none;">
            <h3>Test Result:</h3>
            <div id="resultContent"></div>
        </div>
        
        <div class="help-section">
            <h3>📖 Quick Guide</h3>
            <div class="help-grid">
                <div class="help-card">
                    <h4>✅ Correct Format</h4>
                    <code>https://drive.google.com/uc?id=FILE_ID</code>
                    <p>This format works for direct image display</p>
                </div>
                <div class="help-card error">
                    <h4>❌ Wrong Format</h4>
                    <code>https://drive.google.com/file/d/FILE_ID/view</code>
                    <p>This is a viewer link, not a direct image link</p>
                </div>
                <div class="help-card error">
                    <h4>❌ Folder Links Don't Work</h4>
                    <code>https://drive.google.com/drive/folders/FOLDER_ID</code>
                    <p>Folder links cannot display images directly</p>
                </div>
            </div>
            <p style="margin-top: 1rem;">
                <a href="google-drive-images-guide.php" class="btn btn-info">
                    <i class="fas fa-book"></i> View Full Guide
                </a>
            </p>
        </div>
    </div>
</div>

<style>
.test-container {
    max-width: 900px;
    margin: 0 auto;
}

.input-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.input-section label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.input-section input {
    width: 100%;
    margin-bottom: 1rem;
}

.input-section button {
    margin-right: 0.5rem;
}

.result-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.result-section h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.test-image-container {
    text-align: center;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 1rem;
}

.test-image-container img {
    max-width: 100%;
    max-height: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #28a745;
    margin-bottom: 1rem;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #dc3545;
    margin-bottom: 1rem;
}

.help-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.help-section h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.help-card {
    background: #e7f5ff;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.help-card.error {
    background: #fff5f5;
    border-left-color: #e74c3c;
}

.help-card h4 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.help-card code {
    display: block;
    background: rgba(0,0,0,0.05);
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    margin: 0.5rem 0;
    word-break: break-all;
}

.help-card p {
    margin: 0.5rem 0 0 0;
    font-size: 0.9rem;
    color: #666;
}
</style>

<script>
function testImage() {
    const url = document.getElementById('imageUrl').value.trim();
    const resultDiv = document.getElementById('result');
    const resultContent = document.getElementById('resultContent');
    
    if (!url) {
        alert('Please enter an image URL');
        return;
    }
    
    resultDiv.style.display = 'block';
    resultContent.innerHTML = '<p>Testing image... <i class="fas fa-spinner fa-spin"></i></p>';
    
    // Create a test image
    const img = new Image();
    
    img.onload = function() {
        resultContent.innerHTML = `
            <div class="success-message">
                <strong><i class="fas fa-check-circle"></i> Success!</strong> Image loaded successfully.
                <br>Dimensions: ${img.width} x ${img.height} pixels
            </div>
            <div class="test-image-container">
                <img src="${url}" alt="Test Image">
            </div>
            <p style="margin-top: 1rem;">
                <strong>✅ This URL is ready to use in your gallery!</strong>
            </p>
        `;
    };
    
    img.onerror = function() {
        resultContent.innerHTML = `
            <div class="error-message">
                <strong><i class="fas fa-times-circle"></i> Error!</strong> Image failed to load.
                <br><br>
                <strong>Possible reasons:</strong>
                <ul style="margin-top: 0.5rem;">
                    <li>The URL format is incorrect</li>
                    <li>The file is not publicly accessible (check sharing settings)</li>
                    <li>The file ID is wrong</li>
                    <li>The file is not an image</li>
                    <li>You're using a folder link instead of a file link</li>
                </ul>
            </div>
            <p><strong>Your URL:</strong> <code style="background: #f8f9fa; padding: 0.5rem; display: block; border-radius: 4px; word-break: break-all;">${url}</code></p>
            <p style="margin-top: 1rem;">
                <a href="google-drive-images-guide.php" class="btn btn-info">
                    <i class="fas fa-book"></i> View Guide for Help
                </a>
            </p>
        `;
    };
    
    img.src = url;
}

function convertDriveLink() {
    const input = document.getElementById('imageUrl');
    let url = input.value.trim();
    
    if (!url) {
        alert('Please paste a Google Drive link first');
        return;
    }
    
    // Check if it's already in correct format
    if (url.includes('drive.google.com/uc?id=')) {
        alert('✅ This link is already in the correct format!');
        return;
    }
    
    // Extract file ID from various formats
    let fileId = null;
    
    // Format: https://drive.google.com/file/d/FILE_ID/view
    if (url.includes('/file/d/')) {
        const match = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/);
        if (match) fileId = match[1];
    }
    
    // Format: https://drive.google.com/open?id=FILE_ID
    if (url.includes('open?id=')) {
        const match = url.match(/id=([a-zA-Z0-9_-]+)/);
        if (match) fileId = match[1];
    }
    
    // Check for folder link
    if (url.includes('/folders/')) {
        alert('❌ This is a folder link! Folder links cannot be used for direct image display.\n\nYou need to:\n1. Open the folder\n2. Right-click each image individually\n3. Get the shareable link for each image\n4. Convert each image link separately');
        return;
    }
    
    if (fileId) {
        const convertedUrl = `https://drive.google.com/uc?id=${fileId}`;
        input.value = convertedUrl;
        alert('✅ Link converted successfully!\n\nClick "Test Image" to verify it works.');
    } else {
        alert('❌ Could not extract file ID from this link.\n\nMake sure you\'re using a Google Drive file link, not a folder link.');
    }
}

// Allow Enter key to test
document.getElementById('imageUrl').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        testImage();
    }
});
</script>

<?php renderAdminFooter(); ?>
