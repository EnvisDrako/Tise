<?php
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';

echo "Testing redirect...<br>";
echo "BASE_URL: " . BASE_URL . "<br>";
echo "Redirecting to admin in 2 seconds...<br>";

// Flush output
flush();
sleep(2);

// Try redirect
header('Location: ' . BASE_URL . '/admin/');
exit;
?>
