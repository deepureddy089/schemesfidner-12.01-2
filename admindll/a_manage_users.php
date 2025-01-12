<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php');//Includes connection to Databse

if (isset($_POST['query'])) {
    // Handle search request
    $query = "%" . $conn_login->real_escape_string($_POST['query']) . "%";

    $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ?";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param('ss', $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='user-result'>
                    <strong>{$row['username']}</strong> (Email: {$row['email']})<br>
                    <a href='edit_user.php?id={$row['id']}'>Edit</a> |
                    <a href='suspend_user.php?id={$row['id']}'>Suspend</a>
                  </div>";
        }
    } else {
        echo "No users found.";
    }
}
?>