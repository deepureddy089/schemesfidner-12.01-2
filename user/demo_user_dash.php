<?php
// ../user/demo_user_dash.php

// Start the session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include('../includes/db_login_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: user_login.php');
    exit;
}

// Fetch user's first_login status
$username = $_SESSION['user'];
$query = "SELECT first_login FROM users WHERE username = ?";
$stmt = $conn_login->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If first_login is 0, do not show the demo
if ($user['first_login'] == 0) {
    return;
}
?>

<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<div id="demoRundown" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); color: white; z-index: 1000; padding: 20px;">
    <div id="step1" class="demo-step">
        <h2>Welcome to Your Dashboard!</h2>
        <p>Let's take a quick tour of your dashboard:</p>
        <p><strong>Notifications:</strong> Use the "Notifications" button to view your recent notifications.</p>
        <button class="btn btn-primary" onclick="nextStep('step1', 'step2')">Next</button>
    </div>

    <div id="step2" class="demo-step" style="display: none;">
        <h2>Saved Schemes</h2>
        <p><strong>Saved Schemes:</strong> Use the "Saved Schemes" button to view your saved schemes.</p>
        <button class="btn btn-primary" onclick="nextStep('step2', 'step3')">Next</button>
    </div>

    <div id="step3" class="demo-step" style="display: none;">
        <h2>Edit Profile</h2>
        <p><strong>Edit Profile:</strong> Use the "Edit Profile" button to update your profile information.</p>
        <button class="btn btn-primary" onclick="finishDemo()">Finish</button>
    </div>
</div>

<!-- Include Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Function to move to the next step
    function nextStep(currentStepId, nextStepId) {
        // Hide the current step
        document.getElementById(currentStepId).style.display = 'none';

        // Show the next step
        document.getElementById(nextStepId).style.display = 'block';
    }

    // Function to finish the demo
    function finishDemo() {
        // Close the demo rundown
        document.getElementById('demoRundown').style.display = 'none';

        // Update first_login status
        updateFirstLoginStatus();
    }

    // Function to update first_login status
    function updateFirstLoginStatus() {
        fetch('update_first_login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username: '<?php echo $username; ?>' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('First login status updated.');
            } else {
                console.error('Failed to update first login status.');
            }
        });
    }
</script>