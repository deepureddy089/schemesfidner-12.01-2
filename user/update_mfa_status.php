<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$username = $_SESSION['user'];
$mfa_status = $_POST['mfa_status'];

// Update MFA status in the database
$update_sql = "UPDATE users SET mfa_toggle = ? WHERE username = ?";
$stmt = $conn_login->prepare($update_sql);
$stmt->bind_param("is", $mfa_status, $username);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'MFA status updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update MFA status']);
}

$stmt->close();
$conn_login->close();
?>