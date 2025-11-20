<?php
// Simple test file to verify public access works
define('SYSTEM_ACCESS', true);

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Access</title></head><body>";
echo "<h1>Public Access Test</h1>";
echo "<p>If you can see this, public pages are accessible!</p>";
echo "<p><a href='enquiry.php'>Go to Enquiry Form</a></p>";
echo "<p><a href='enquiry'>Go to Enquiry (clean URL)</a></p>";
echo "</body></html>";
