<?php
// Diagnostic page to check access
define('SYSTEM_ACCESS', true);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Access Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; margin: 10px 0; border-left: 4px solid #2196F3; }
        a { display: inline-block; margin: 10px 0; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; }
        a:hover { background: #1976D2; }
    </style>
</head>
<body>
    <h1 class="success">✓ Public Access is Working!</h1>
    
    <div class="info">
        <h3>Current Request Info:</h3>
        <p><strong>Request URI:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></p>
        <p><strong>Script Name:</strong> <?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?></p>
        <p><strong>PHP Self:</strong> <?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?></p>
        <p><strong>Server Name:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_NAME']); ?></p>
    </div>
    
    <h2>Test Links:</h2>
    <p><a href="enquiry.php">Test: enquiry.php (direct)</a></p>
    <p><a href="enquiry">Test: enquiry (clean URL)</a></p>
    <p><a href="/Tise/public/enquiry">Test: /Tise/public/enquiry (full path)</a></p>
    <p><a href="index.php">Back to Home</a></p>
    
    <div class="info">
        <h3>Instructions:</h3>
        <p>Click each link above. If you can access the enquiry form without being redirected to login, the issue is fixed!</p>
    </div>
</body>
</html>
