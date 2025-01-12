<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php'); // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    die("Unauthorized access.");
}

$username = $_SESSION['user'];

// Update the status of unread notifications to 'read'
$update_sql = "UPDATE notifications SET status = 'read' WHERE username = ? AND status = 'unread'";
$stmt = $conn_login->prepare($update_sql);
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    echo "Notifications marked as read.";
} else {
    echo "Error updating notifications: " . $stmt->error;
}

$stmt->close();
$conn_login->close();
?>