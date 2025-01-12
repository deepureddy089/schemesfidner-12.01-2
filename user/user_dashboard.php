<?php
include('../includes/session_start.php'); // Include the session start file
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../includes/db_login_connection.php'); // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: user_login.php');
    exit;
}

$username = $_SESSION['user'];

// Fetch unread notifications for the user
$notifications_sql = "SELECT * FROM notifications WHERE username = ? AND status = 'unread' ORDER BY timestamp DESC"; // Unread notifications only
$stmt = $conn_login->prepare($notifications_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$notifications_result = $stmt->get_result();

// Fetch total count of notifications to display on the badge
$total_notifications_sql = "SELECT * FROM notifications WHERE username = ? AND status = 'unread'";
$stmt = $conn_login->prepare($total_notifications_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$total_notifications_result = $stmt->get_result();
$total_notifications = $total_notifications_result->num_rows;

// Fetch saved schemes for the user
$saved_schemes_sql = "
    SELECT s.id, s.scheme_name 
    FROM schemes s
    JOIN scheme_likes sl ON s.id = sl.scheme_id
    WHERE sl.username = ?
";
$stmt = $conn_login->prepare($saved_schemes_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$saved_schemes_result = $stmt->get_result();

// Fetch first_login status and MFA toggle status
$user_details_sql = "SELECT first_login, mfa_toggle FROM users WHERE username = ?";
$stmt = $conn_login->prepare($user_details_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user_details_result = $stmt->get_result();
$user_details = $user_details_result->fetch_assoc();
$first_login = $user_details['first_login'];
$mfa_toggle = $user_details['mfa_toggle']; // Fetch MFA toggle status
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <?php include '../includes/header.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="user_dashboard.css" rel="stylesheet"> <!-- Link to the CSS file -->
    <style>
        /* Black Theme Styles */
        body {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
        }

        /* Dashboard Buttons */
        .dashboard-buttons .btn {
            margin: 5px;
        }

        /* Modals */
        .modal-content {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
            border: 1px solid #444 !important; /* Dark gray border */
        }

        .modal-header {
            border-bottom: 1px solid #000 !important; /* Dark gray border */
        }

        .modal-footer {
            border-top: 1px solid #000 !important; /* Dark gray border */
        }

        .modal-body {
            color: #fff !important; /* White text */
        }

        .modal-body .list-group-item {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
            border: 1px solid #444 !important; /* Dark gray border */
        }

        .modal-body .list-group-item strong {
            color: #fff !important; /* White text */
        }

        .modal-body .list-group-item em {
            color: #ccc !important; /* Light gray text */
        }

        /* Buttons and Icons */
        .btn-primary,
        .btn-danger,
        .btn-info,
        .btn-success,
        .btn-secondary,
        .btn-warning {
            color: #fff !important; /* White text */
        }

        .btn-primary {
            background-color: #007BFF !important; /* Blue background */
            border-color: #007BFF !important; /* Blue border */
        }

        .btn-danger {
            background-color: #DC3545 !important; /* Red background */
            border-color: #DC3545 !important; /* Red border */
        }

        .btn-info {
            background-color: #17A2B8 !important; /* Teal background */
            border-color: #17A2B8 !important; /* Teal border */
        }

        .btn-success {
            background-color: #28A745 !important; /* Green background */
            border-color: #28A745 !important; /* Green border */
        }

        .btn-secondary {
            background-color: #6C757D !important; /* Gray background */
            border-color: #6C757D !important; /* Gray border */
        }

        .btn-warning {
            background-color: #FFC107 !important; /* Yellow background */
            border-color: #FFC107 !important; /* Yellow border */
        }

        /* Badge */
        .badge-notification {
            background-color: #DC3545 !important; /* Red background */
            color: #fff !important; /* White text */
        }

        /* MFA Toggle */
        .custom-control-label {
            color: #fff !important; /* White text */
        }

        .custom-switch .custom-control-label::before {
            background-color: #444 !important; /* Dark gray background */
            border-color: #444 !important; /* Dark gray border */
        }

        .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #007BFF !important; /* Blue background */
            border-color: #007BFF !important; /* Blue border */
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Message -->
        <div class="welcome-message" style="background-color: #000; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <h2 style="color: #fff;">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p style="color: #fff;">This is your dashboard. Use the buttons below to manage your account and view your saved schemes and notifications.</p>
</div>

        <!-- Dashboard Buttons -->
        <div class="dashboard-buttons">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#notificationsModal">
                <i class="fas fa-bell"></i> Notifications
                <?php if ($total_notifications > 0): ?>
                    <span id="notification-badge-count" class="badge badge-notification"><?php echo $total_notifications; ?></span>
                <?php endif; ?>
            </button>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#savedSchemesModal">
                <i class="fas fa-heart"></i> Saved Schemes
            </button>
            <a href="https://schemesfinder.com/ai/ai_search-form.php" class="btn btn-info">
                <i class="fas fa-search"></i> AI Search
            </a>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#accountSettingsModal">
                <i class="fas fa-cog"></i> Account Settings
            </button>
            <a href="logout.php" class="btn btn-secondary">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>
    </div>

    <!-- Modal for Notifications -->
    <div class="modal fade" id="notificationsModal" tabindex="-1" role="dialog" aria-labelledby="notificationsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationsModalLabel">Notifications</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($notifications_result->num_rows > 0): ?>
                        <ul class="list-group">
                            <?php while ($notification = $notifications_result->fetch_assoc()): ?>
                                <li class="list-group-item notification-item">
                                    <strong>Subject:</strong> <?php echo htmlspecialchars($notification['message']); ?>
                                    <br>
                                    <span><em>Received on: <?php echo htmlspecialchars($notification['timestamp']); ?></em></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>No notifications available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Saved Schemes -->
    <div class="modal fade" id="savedSchemesModal" tabindex="-1" role="dialog" aria-labelledby="savedSchemesModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="savedSchemesModalLabel">Saved Schemes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($saved_schemes_result->num_rows > 0): ?>
                        <ul class="list-group">
                            <?php while ($scheme = $saved_schemes_result->fetch_assoc()): ?>
                                <li class="list-group-item">
                                    <strong>Scheme Name:</strong> <?php echo htmlspecialchars($scheme['scheme_name']); ?>
                                    <a href="../search/scheme_details.php?id=<?php echo $scheme['id']; ?>" class="btn btn-info btn-sm float-right">View Details</a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>No saved schemes found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Account Settings -->
    <div class="modal fade" id="accountSettingsModal" tabindex="-1" role="dialog" aria-labelledby="accountSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountSettingsModalLabel">Account Settings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Edit Profile Button -->
                    <a href="edit_user_details.php" class="btn btn-primary btn-block mb-3">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>

                    <!-- Change Password Button -->
                    <a href="change_user_pass.php" class="btn btn-warning btn-block mb-3">
                        <i class="fas fa-key"></i> Change Password
                    </a>

                    <!-- Multi-Factor Authentication (MFA) Button -->
                    <button type="button" class="btn btn-info btn-block mb-3" id="mfaButton">
                        <i class="fas fa-shield-alt"></i> Multi-Factor Authentication (MFA)
                    </button>

                    <!-- MFA Toggle Container -->
                    <div id="mfaToggleContainer">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="mfaToggle" <?php echo ($mfa_toggle == 1) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="mfaToggle">
                                    <span id="mfaStatusText"><?php echo ($mfa_toggle == 1) ? 'MFA Email OTP ON' : 'MFA Email OTP OFF'; ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demo Rundown (Only for First Login) -->
    <?php if ($first_login == 1): ?>
        <?php include 'demo_user_dash.php'; ?>
    <?php endif; ?>

    <!-- JavaScript for Modals -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle notifications modal
            $('#notificationsModal').on('show.bs.modal', function () {
                $("#notification-badge-count").hide();

                // Mark notifications as read using AJAX
                $.ajax({
                    url: 'mark_read_notifications.php',
                    method: 'POST',
                    success: function(response) {
                        console.log(response);
                        $("#notification-badge-count").text("0").hide();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error marking notifications as read: " + error);
                    }
                });
            });

            // Handle MFA button click
            $('#mfaButton').click(function() {
                $('#mfaToggleContainer').toggle(); // Show/hide the MFA toggle
            });

            // Handle MFA toggle
            $('#mfaToggle').change(function() {
                var mfaStatus = $(this).is(':checked') ? 1 : 0;
                var mfaStatusText = mfaStatus ? 'MFA Email OTP ON' : 'MFA Email OTP OFF';
                $('#mfaStatusText').text(mfaStatusText);

                $.ajax({
                    url: 'update_mfa_status.php',
                    method: 'POST',
                    data: { mfa_status: mfaStatus },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating MFA status: " + error);
                    }
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
$conn_login->close();
?>