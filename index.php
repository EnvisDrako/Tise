<?php
/**
 * Root Index File - Redirects to Public Directory
 * This file redirects users to the public directory where the actual application resides
 */

// Redirect to public directory
header('Location: public/');
exit;
?>