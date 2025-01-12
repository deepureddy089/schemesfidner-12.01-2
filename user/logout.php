<?php
include('../includes/session_start.php'); // Include the session start file

// Destroy the session
session_destroy();

// Redirect to the home page
header('Location: ../');
exit;
?>