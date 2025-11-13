<?php
/**
 * BASE_URL Test Page
 * Use this page to verify BASE_URL configuration is working correctly
 */

define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASE_URL Test Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
        .test-section {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        .test-link {
            display: inline-block;
            padding: 8px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
        .test-link:hover {
            background: #0056b3;
        }
        .test-image {
            max-width: 200px;
            border: 2px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 BASE_URL Configuration Test</h1>
        
        <div class="test-section">
            <h2>✅ Configuration Status</h2>
            <table>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td><strong>BASE_URL</strong></td>
                    <td class="code"><?php echo defined('BASE_URL') ? BASE_URL : 'NOT DEFINED'; ?></td>
                    <td class="<?php echo defined('BASE_URL') ? 'success' : 'error'; ?>">
                        <?php echo defined('BASE_URL') ? '✓ Defined' : '✗ Not Defined'; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>SITE_URL</strong></td>
                    <td class="code"><?php echo defined('SITE_URL') ? SITE_URL : 'NOT DEFINED'; ?></td>
                    <td class="<?php echo defined('SITE_URL') ? 'success' : 'error'; ?>">
                        <?php echo defined('SITE_URL') ? '✓ Defined' : '✗ Not Defined'; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Server Name</strong></td>
                    <td class="code"><?php echo $_SERVER['SERVER_NAME']; ?></td>
                    <td class="info">Info</td>
                </tr>
                <tr>
                    <td><strong>Document Root</strong></td>
                    <td class="code"><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
                    <td class="info">Info</td>
                </tr>
                <tr>
                    <td><strong>Current URL</strong></td>
                    <td class="code"><?php echo function_exists('currentUrl') ? currentUrl() : 'Function not available'; ?></td>
                    <td class="info">Info</td>
                </tr>
            </table>
        </div>

        <div class="test-section">
            <h2>🔗 Helper Functions Test</h2>
            
            <h3>asset() Function</h3>
            <p>Used for CSS, JS, images, and static files:</p>
            <div class="code">
                asset('css/style.css') = <?php echo function_exists('asset') ? asset('css/style.css') : 'Function not available'; ?>
            </div>
            <div class="code">
                asset('images/logo.png') = <?php echo function_exists('asset') ? asset('images/logo.png') : 'Function not available'; ?>
            </div>
            
            <h3>url() Function</h3>
            <p>Used for page links and navigation:</p>
            <div class="code">
                url('/') = <?php echo function_exists('url') ? url('/') : 'Function not available'; ?>
            </div>
            <div class="code">
                url('about') = <?php echo function_exists('url') ? url('about') : 'Function not available'; ?>
            </div>
            <div class="code">
                url('admin/') = <?php echo function_exists('url') ? url('admin/') : 'Function not available'; ?>
            </div>
        </div>

        <div class="test-section">
            <h2>🎨 CSS Test</h2>
            <p>Testing if CSS loads correctly with BASE_URL:</p>
            <link rel="stylesheet" href="<?php echo asset('css/public.css'); ?>">
            <p class="success">If this page has proper styling, CSS is loading correctly!</p>
        </div>

        <div class="test-section">
            <h2>🔗 Navigation Links Test</h2>
            <p>Click these links to test navigation:</p>
            <a href="<?php echo url('/'); ?>" class="test-link">Home</a>
            <a href="<?php echo url('about'); ?>" class="test-link">About</a>
            <a href="<?php echo url('courses'); ?>" class="test-link">Courses</a>
            <a href="<?php echo url('contact'); ?>" class="test-link">Contact</a>
            <a href="<?php echo url('login'); ?>" class="test-link">Login</a>
        </div>

        <div class="test-section">
            <h2>📋 Usage Examples</h2>
            
            <h3>For CSS Files:</h3>
            <div class="code">
&lt;link rel="stylesheet" href="&lt;?php echo asset('css/style.css'); ?&gt;"&gt;
            </div>
            
            <h3>For JavaScript Files:</h3>
            <div class="code">
&lt;script src="&lt;?php echo asset('js/main.js'); ?&gt;"&gt;&lt;/script&gt;
            </div>
            
            <h3>For Images:</h3>
            <div class="code">
&lt;img src="&lt;?php echo asset('images/logo.png'); ?&gt;" alt="Logo"&gt;
            </div>
            
            <h3>For Page Links:</h3>
            <div class="code">
&lt;a href="&lt;?php echo url('about'); ?&gt;"&gt;About&lt;/a&gt;
            </div>
            
            <h3>For Form Actions:</h3>
            <div class="code">
&lt;form action="&lt;?php echo url('login_process.php'); ?&gt;" method="POST"&gt;
            </div>
            
            <h3>For Redirects:</h3>
            <div class="code">
header('Location: ' . url('dashboard'));
            </div>
        </div>

        <div class="test-section">
            <h2>📚 Documentation</h2>
            <p>For complete documentation, see <strong>BASE_URL_GUIDE.md</strong> in the project root.</p>
            <p>To scan for files that need updating, run:</p>
            <div class="code">
php update_base_url.php
            </div>
        </div>

        <div class="test-section">
            <h2>✅ Checklist</h2>
            <ul>
                <li><?php echo defined('BASE_URL') ? '✓' : '✗'; ?> BASE_URL is defined</li>
                <li><?php echo defined('SITE_URL') ? '✓' : '✗'; ?> SITE_URL is defined</li>
                <li><?php echo function_exists('asset') ? '✓' : '✗'; ?> asset() function exists</li>
                <li><?php echo function_exists('url') ? '✓' : '✗'; ?> url() function exists</li>
                <li><?php echo function_exists('currentUrl') ? '✓' : '✗'; ?> currentUrl() function exists</li>
                <li><?php echo function_exists('isCurrentPage') ? '✓' : '✗'; ?> isCurrentPage() function exists</li>
            </ul>
        </div>

        <div class="test-section">
            <h2>🚀 Next Steps</h2>
            <ol>
                <li>Verify all checkmarks above are green (✓)</li>
                <li>Test navigation links work correctly</li>
                <li>Run <code>php update_base_url.php</code> to find files needing updates</li>
                <li>Update remaining files using the helper functions</li>
                <li>Test on both localhost and production</li>
            </ol>
        </div>

        <p style="text-align: center; margin-top: 30px; color: #666;">
            <a href="<?php echo url('/'); ?>" class="test-link">← Back to Home</a>
        </p>
    </div>
</body>
</html>
