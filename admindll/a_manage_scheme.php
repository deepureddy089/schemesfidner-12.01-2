<?php
session_start();
include('../includes/db_login_connection.php');

if (!isset($_SESSION['admin'])) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = "%" . $conn_login->real_escape_string($_POST['query']) . "%";

    // Fetch scheme details
    $sql = "SELECT * FROM schemes WHERE scheme_name LIKE ? OR id LIKE ?";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param('ss', $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $scheme_id = $row['id'];

            // Fetch upvotes and downvotes
            $vote_sql = "SELECT SUM(upvote) AS total_upvotes, SUM(downvote) AS total_downvotes FROM scheme_votes WHERE scheme_id = ?";
            $vote_stmt = $conn_login->prepare($vote_sql);
            $vote_stmt->bind_param('i', $scheme_id);
            $vote_stmt->execute();
            $vote_result = $vote_stmt->get_result();
            $vote_data = $vote_result->fetch_assoc();
            $total_upvotes = $vote_data['total_upvotes'] ?? 0;
            $total_downvotes = $vote_data['total_downvotes'] ?? 0;

            // Fetch number of users who saved the scheme
            $like_sql = "SELECT COUNT(*) AS total_likes FROM scheme_likes WHERE scheme_id = ?";
            $like_stmt = $conn_login->prepare($like_sql);
            $like_stmt->bind_param('i', $scheme_id);
            $like_stmt->execute();
            $like_result = $like_stmt->get_result();
            $like_data = $like_result->fetch_assoc();
            $total_likes = $like_data['total_likes'] ?? 0;

            // Display scheme details
            echo "<div class='scheme-result'>
                    <strong>{$row['scheme_name']}</strong> (ID: {$row['id']})<br>
                    State: {$row['state']}, Age Group: {$row['age_group']}<br>
                    Upvotes: $total_upvotes, Downvotes: $total_downvotes, Saved by: $total_likes users<br>
                    Status: " . ($row['suspended'] == 0 ? "Approved" : ($row['suspended'] == 1 ? "Suspended" : "Draft")) . "<br>
                    <a href='{$row['scheme_link']}' target='_blank'>View Scheme</a> |
                    <a href='edit_scheme.php?id={$row['id']}'>Edit</a> |
                    <a href='#' class='delete-scheme' data-id='{$row['id']}'>Delete</a> |
                    <a href='#' class='suspend-scheme' data-id='{$row['id']}' data-action='" . ($row['suspended'] == 1 ? 'unsuspend' : 'suspend') . "'>" . ($row['suspended'] == 1 ? 'Unsuspend' : 'Suspend') . "</a> |
                    <a href='#' class='approve-scheme' data-id='{$row['id']}' data-action='" . ($row['suspended'] == 2 ? 'approve' : 'disapprove') . "'>" . ($row['suspended'] == 2 ? 'Approve' : 'Disapprove') . "</a>
                  </div>";
        }
    } else {
        echo "No schemes found.";
    }
} elseif (isset($_POST['schemeName'])) {
    // Handle add scheme request
    $schemeName = $_POST['schemeName'];
    $state = $_POST['state'];
    $ageGroup = $_POST['ageGroup'];
    $stateLogo = $_POST['stateLogo'];
    $schemeLink = $_POST['schemeLink'];

    $sql = "INSERT INTO schemes (scheme_name, state, age_group, state_logo, scheme_link) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param('sssss', $schemeName, $state, $ageGroup, $stateLogo, $schemeLink);
    $stmt->execute();

    echo "Scheme added successfully!";
}
?>