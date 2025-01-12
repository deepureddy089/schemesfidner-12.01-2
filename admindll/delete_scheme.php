<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php');

if (isset($_POST['id'])) {
    $schemeId = $_POST['id'];
    $sql = "DELETE FROM schemes WHERE id = ?";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param('i', $schemeId);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error deleting scheme.";
    }
} else {
    echo "Invalid request.";
}
?>