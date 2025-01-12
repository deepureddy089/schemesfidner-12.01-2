<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php');//Include Database connection

if (isset($_POST['query'])) {
    $query = "%" . $conn_login->real_escape_string($_POST['query']) . "%";

    $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ?";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param('ss', $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='email-user-result'>{$row['username']} ({$row['email']})</div>";
        }
    } else {
        echo "No users found.";
    }
}
?>