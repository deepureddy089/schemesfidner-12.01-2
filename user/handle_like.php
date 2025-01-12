<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php'); // Include the database connection

// Function to return JSON and exit
function returnJson($success, $message = '', $liked = false) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'liked' => $liked
    ]);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    returnJson(false, 'User not logged in.');
}

// Get the username from the session
$username = $_SESSION['user'];

// Get the scheme ID from the POST request
if (!isset($_POST['scheme_id'])) {
    returnJson(false, 'Invalid request.');
}

$scheme_id = intval($_POST['scheme_id']);

// Check if the user has already liked the scheme
$check_sql = "SELECT id FROM scheme_likes WHERE scheme_id = ? AND username = ?";
$stmt = $conn_login->prepare($check_sql);
if (!$stmt) {
    returnJson(false, 'Database error: ' . $conn_login->error);
}
$stmt->bind_param("is", $scheme_id, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User has already liked the scheme, so unlike it
    $delete_sql = "DELETE FROM scheme_likes WHERE scheme_id = ? AND username = ?";
    $stmt = $conn_login->prepare($delete_sql);
    if (!$stmt) {
        returnJson(false, 'Database error: ' . $conn_login->error);
    }
    $stmt->bind_param("is", $scheme_id, $username);
    $stmt->execute();
    returnJson(true, 'Unliked successfully.', false);
} else {
    // User is liking the scheme for the first time
    $insert_sql = "INSERT INTO scheme_likes (scheme_id, username) VALUES (?, ?)";
    $stmt = $conn_login->prepare($insert_sql);
    if (!$stmt) {
        returnJson(false, 'Database error: ' . $conn_login->error);
    }
    $stmt->bind_param("is", $scheme_id, $username);
    $stmt->execute();
    returnJson(true, 'Liked successfully.', true);
}

$stmt->close();
$conn_login->close();
?>