<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System - Simple Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007cba;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #005a8b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 CRM System - Working!</h1>
        
        <p class="success">✅ <strong>Great news!</strong> PHP is working and the website is loading properly.</p>
        
        <h2>🚀 Quick Navigation</h2>
        <p>Try these links to test different parts of the system:</p>
        
        <div>
            <a href="index.php" class="btn">Full Homepage</a>
            <a href="login.php" class="btn">Login Page</a>
            <a href="diagnostic.php" class="btn">Diagnostic Page</a>
            <a href="../setup_local.php" class="btn">Setup Status</a>
        </div>
        
        <h2>📋 System Status</h2>
        
        <?php
        // Quick system checks
        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
        
        // Check if config exists
        if (file_exists('../includes/config.php')) {
            echo "<p class='success'>✅ Configuration file exists</p>";
        } else {
            echo "<p class='error'>❌ Configuration file missing</p>";
        }
        
        // Check if database extensions are available
        if (extension_loaded('pdo_mysql')) {
            echo "<p class='success'>✅ MySQL extension available</p>";
        } else {
            echo "<p class='error'>❌ MySQL extension missing - Install XAMPP for full functionality</p>";
        }
        
        // Check directories
        $dirs = ['../includes', '../database', '../logs', 'uploads'];
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                echo "<p class='success'>✅ Directory exists: $dir</p>";
            } else {
                echo "<p class='warning'>⚠️ Directory missing: $dir</p>";
            }
        }
        ?>
        
        <h2>🔧 If You're Still Having Issues</h2>
        <ol>
            <li><strong>Install XAMPP</strong> - Download from <a href="https://www.apachefriends.org/" target="_blank">apachefriends.org</a></li>
            <li><strong>Move project</strong> to <code>C:\xampp\htdocs\integrated-crm\</code></li>
            <li><strong>Start services</strong> - Apache and MySQL in XAMPP Control Panel</li>
            <li><strong>Access via</strong> <code>http://localhost/integrated-crm/</code></li>
        </ol>
        
        <h2>📞 Default Login Credentials</h2>
        <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">
            <tr style="background: #f0f0f0;">
                <th>Role</th>
                <th>Username</th>
                <th>Password</th>
            </tr>
            <tr>
                <td>Admin</td>
                <td><code>admin</code></td>
                <td><code>admin123</code></td>
            </tr>
        </table>
        
        <p><small><strong>⚠️ Important:</strong> Change the default password after first login!</small></p>
        
        <hr>
        <p><small>Generated at <?php echo date('Y-m-d H:i:s'); ?></small></p>
    </div>
</body>
</html>