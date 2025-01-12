<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php'); // Include the database connection

// Function to return JSON and exit
function returnJson($success, $message = '', $upvotes = 0, $downvotes = 0) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'upvotes' => $upvotes,
        'downvotes' => $downvotes
    ]);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    returnJson(false, 'User not logged in.');
}

// Get the username from the session
$username = $_SESSION['user'];

// Get the scheme ID and vote type from the POST request
if (!isset($_POST['scheme_id']) || !isset($_POST['vote_type'])) {
    returnJson(false, 'Invalid request.');
}

$scheme_id = intval($_POST['scheme_id']);
$vote_type = $_POST['vote_type'];

// Validate the vote type
if ($vote_type !== 'upvote' && $vote_type !== 'downvote') {
    returnJson(false, 'Invalid vote type.');
}

// Check if the user has already voted
$check_sql = "SELECT upvote, downvote FROM scheme_votes WHERE scheme_id = ? AND username = ?";
$stmt = $conn_login->prepare($check_sql);
if (!$stmt) {
    returnJson(false, 'Database error: ' . $conn_login->error);
}
$stmt->bind_param("is", $scheme_id, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User has already voted, update the vote
    $row = $result->fetch_assoc();
    $current_upvote = $row['upvote'];
    $current_downvote = $row['downvote'];

    if ($vote_type === 'upvote') {
        $new_upvote = !$current_upvote; // Toggle upvote
        $new_downvote = FALSE; // Ensure downvote is false
    } else {
        $new_downvote = !$current_downvote; // Toggle downvote
        $new_upvote = FALSE; // Ensure upvote is false
    }

    $update_sql = "UPDATE scheme_votes SET upvote = ?, downvote = ? WHERE scheme_id = ? AND username = ?";
    $stmt = $conn_login->prepare($update_sql);
    if (!$stmt) {
        returnJson(false, 'Database error: ' . $conn_login->error);
    }
    $stmt->bind_param("iiis", $new_upvote, $new_downvote, $scheme_id, $username);
    $stmt->execute();
} else {
    // User is voting for the first time
    $upvote = ($vote_type === 'upvote') ? TRUE : FALSE;
    $downvote = ($vote_type === 'downvote') ? TRUE : FALSE;

    $insert_sql = "INSERT INTO scheme_votes (scheme_id, username, upvote, downvote) VALUES (?, ?, ?, ?)";
    $stmt = $conn_login->prepare($insert_sql);
    if (!$stmt) {
        returnJson(false, 'Database error: ' . $conn_login->error);
    }
    $stmt->bind_param("isii", $scheme_id, $username, $upvote, $downvote);
    $stmt->execute();
}

// Fetch updated vote counts
$upvote_sql = "SELECT COUNT(*) as upvotes FROM scheme_votes WHERE scheme_id = ? AND upvote = TRUE";
$downvote_sql = "SELECT COUNT(*) as downvotes FROM scheme_votes WHERE scheme_id = ? AND downvote = TRUE";

$stmt = $conn_login->prepare($upvote_sql);
$stmt->bind_param("i", $scheme_id);
$stmt->execute();
$upvote_result = $stmt->get_result();
$upvotes = $upvote_result->fetch_assoc()['upvotes'] ?? 0;

$stmt = $conn_login->prepare($downvote_sql);
$stmt->bind_param("i", $scheme_id);
$stmt->execute();
$downvote_result = $stmt->get_result();
$downvotes = $downvote_result->fetch_assoc()['downvotes'] ?? 0;

// Return the updated vote counts
returnJson(true, '', $upvotes, $downvotes);

$stmt->close();
$conn_login->close();
?>