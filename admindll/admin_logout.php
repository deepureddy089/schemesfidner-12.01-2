<?php
// Start the session at the beginning
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the home page or login page
header('Location: admin_login.php');  // Adjust the URL according to your project structure
exit;
?>
