<?php
// update_first_login.php

// Start the session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include('../includes/db_login_connection.php');

// Get the username from the POST data
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];

// Update first_login status to 0
$query = "UPDATE users SET first_login = 0 WHERE username = ?";
$stmt = $conn_login->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();

// Return success response
echo json_encode(['success' => true]);