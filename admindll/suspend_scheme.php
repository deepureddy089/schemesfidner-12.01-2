<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php');//Incldue Databse Connection

if (isset($_POST['id']) && isset($_POST['action'])) {
    $schemeId = $_POST['id'];
    $action = $_POST['action'];
    $suspended = ($action === 'suspend') ? 1 : 0;

    $sql = "UPDATE schemes SET suspended = ? WHERE id = ?";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param('ii', $suspended, $schemeId);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating scheme status.";
    }
} else {
    echo "Invalid request.";
}
?>