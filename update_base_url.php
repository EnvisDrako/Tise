#!/usr/bin/env php
<?php
/**
 * BASE_URL Update Helper Script
 * This script helps identify files that need BASE_URL updates
 */

echo "===========================================\n";
echo "BASE_URL Update Helper Script\n";
echo "===========================================\n\n";

// Directories to scan
$directories = [
    'public',
    'includes'
];

// Patterns to search for
$patterns = [
    'href="/' => 'Links starting with /',
    'src="/' => 'Sources starting with /',
    'action="/' => 'Form actions starting with /',
    'url\(\'/' => 'CSS url() starting with /',
    'url\("/' => 'CSS url() starting with /',
];

$foundIssues = [];

echo "Scanning files for hardcoded paths...\n\n";

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match('/\.(php|html)$/', $file->getFilename())) {
            $filepath = $file->getPathname();
            $content = file_get_contents($filepath);
            
            foreach ($patterns as $pattern => $description) {
                if (preg_match('/' . $pattern . '/', $content, $matches)) {
                    if (!isset($foundIssues[$filepath])) {
                        $foundIssues[$filepath] = [];
                    }
                    $foundIssues[$filepath][] = $description;
                }
            }
        }
    }
}

if (empty($foundIssues)) {
    echo "✅ No hardcoded paths found! All files appear to be using BASE_URL correctly.\n\n";
} else {
    echo "⚠️  Found " . count($foundIssues) . " files with potential hardcoded paths:\n\n";
    
    foreach ($foundIssues as $file => $issues) {
        echo "📄 " . $file . "\n";
        foreach (array_unique($issues) as $issue) {
            echo "   - " . $issue . "\n";
        }
        echo "\n";
    }
    
    echo "\nRecommendations:\n";
    echo "1. Review each file listed above\n";
    echo "2. Replace hardcoded paths with helper functions:\n";
    echo "   - Use asset() for CSS, JS, images\n";
    echo "   - Use url() for page links\n";
    echo "3. Refer to BASE_URL_GUIDE.md for detailed instructions\n\n";
}

// Check if helper functions exist
echo "Checking helper functions...\n";
$functionsFile = 'includes/functions.php';
if (file_exists($functionsFile)) {
    $content = file_get_contents($functionsFile);
    
    $requiredFunctions = ['asset', 'url', 'currentUrl', 'isCurrentPage'];
    $missingFunctions = [];
    
    foreach ($requiredFunctions as $func) {
        if (strpos($content, "function $func(") === false) {
            $missingFunctions[] = $func;
        }
    }
    
    if (empty($missingFunctions)) {
        echo "✅ All helper functions are present\n\n";
    } else {
        echo "❌ Missing helper functions: " . implode(', ', $missingFunctions) . "\n";
        echo "   Please add these functions to includes/functions.php\n\n";
    }
} else {
    echo "❌ includes/functions.php not found\n\n";
}

// Check config.php
echo "Checking configuration...\n";
$configFile = 'includes/config.php';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    
    if (strpos($content, "define('BASE_URL'") !== false) {
        echo "✅ BASE_URL is defined in config.php\n";
    } else {
        echo "❌ BASE_URL is not defined in config.php\n";
        echo "   Please add BASE_URL configuration\n";
    }
    
    if (strpos($content, "define('SITE_URL'") !== false) {
        echo "✅ SITE_URL is defined in config.php\n";
    } else {
        echo "⚠️  SITE_URL is not defined in config.php (optional but recommended)\n";
    }
} else {
    echo "❌ includes/config.php not found\n";
}

echo "\n===========================================\n";
echo "Scan Complete!\n";
echo "===========================================\n";
?>
