<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php'); // Include database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    die("Unauthorized access.");
}
//kyujy

// Check if the required POST data is set
if (isset($_POST['id']) && isset($_POST['action'])) {
    $schemeId = $_POST['id'];
    $action = $_POST['action'];

    // Validate the action
    if ($action !== 'approve' && $action !== 'disapprove') {
        die("Invalid action.");
    }

    // Set the suspended value based on the action
    $suspendedValue = ($action === 'approve') ? 0 : 2; // 0 = Approved, 2 = Draft

    // Update the scheme's suspended status
    $sql = "UPDATE schemes SET suspended = ? WHERE id = ?";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param('ii', $suspendedValue, $schemeId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating scheme status.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>