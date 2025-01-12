<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php'); // Include database connection
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

$logged_in_admin = $_SESSION['admin'];
$admin_role = $_SESSION['role']; // Get role from session

include('../includes/header.php'); // Include header only once
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="admin_dashboard.css" rel="stylesheet"> <!-- Link to external CSS -->
</head>
<body>
    <!-- Main Content -->
    <div class="container mt-5">
        <!-- Welcome Message and Logout Button -->
        <div class="welcome-section">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($logged_in_admin); ?>!</h2>
                <div class="role-display">Role: <?php echo htmlspecialchars($admin_role); ?></div>
            </div>
            <a href="admin_logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>

        <!-- Buttons for Add Scheme, Marketing, Manage Users, and Manage Admins -->
        <div class="mt-4 mb-4">
            <?php if ($admin_role === 'admin' || $admin_role === 'editor'): ?>
                <a href="add_scheme.php" class="btn btn-primary" target="_blank">
                    <i class="fas fa-plus"></i> Add Scheme
                </a>
            <?php endif; ?>
            <?php if ($admin_role === 'admin' || $admin_role === 'editor'): ?>
                <a href="send_market_email.php" class="btn btn-success">
                    <i class="fas fa-envelope"></i> Marketing
                </a>
            <?php endif; ?>
            <?php if ($admin_role === 'admin'): ?>
                <a href="a_manage_users.php" class="btn btn-info">
                    <i class="fas fa-users"></i> Manage Users
                </a>
            <?php endif; ?>
            <?php if ($admin_role === 'admin'): ?>
                <a href="a_manage_admin.php" class="btn btn-warning">
                    <i class="fas fa-user-shield"></i> Manage Admins
                </a>
                <?php endif; ?>
           
                <a href="a_scheme_stats.php" class="btn btn-secondary">
    <i class="fas fa-chart-bar"></i> View Scheme Stats
</a>

            
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <?php if ($admin_role === 'admin' || $admin_role === 'editor'): ?>
                <li class="nav-item">
                    <a class="nav-link active" id="schemes-tab" data-toggle="tab" href="#schemes" role="tab" aria-controls="schemes" aria-selected="true">
                        <i class="fas fa-list"></i> Manage Schemes
                    </a>
                </li>
            <?php endif; ?>

            <!-- Enquiries Tab (Accessible to all admin levels) -->
            <li class="nav-item">
                <a class="nav-link" id="enquiries-tab" data-toggle="tab" href="#enquiries" role="tab" aria-controls="enquiries" aria-selected="false">
                    <i class="fas fa-question-circle"></i> Enquiries
                    <span class="badge" id="unreadEnquiryCount">0</span>
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="adminTabsContent">
            <!-- Manage Schemes Tab -->
            <?php if ($admin_role === 'admin' || $admin_role === 'editor'): ?>
                <div class="tab-pane fade show active" id="schemes" role="tabpanel" aria-labelledby="schemes-tab">
                    <div class="form-group mt-3">
                        <input type="text" class="form-control" id="searchScheme" placeholder="Search by Scheme Name or ID">
                        <button type="button" class="btn btn-primary mt-2" id="searchSchemeButton">Search</button>
                    </div>
                    <div class="search-results" id="schemeSearchResults"></div>
                </div>
            <?php endif; ?>

            <!-- Enquiries Tab -->
            <div class="tab-pane fade" id="enquiries" role="tabpanel" aria-labelledby="enquiries-tab">
                <!-- Subtab Navigation -->
                <ul class="nav nav-tabs" id="enquirySubtabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="unread-enquiries-tab" data-toggle="tab" href="#unreadEnquiries" role="tab" aria-controls="unreadEnquiries" aria-selected="true">
                            Unread Enquiries
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="read-enquiries-tab" data-toggle="tab" href="#readEnquiries" role="tab" aria-controls="readEnquiries" aria-selected="false">
                            Read Enquiries
                        </a>
                    </li>
                </ul>

                <!-- Subtab Content -->
                <div class="tab-content" id="enquirySubtabsContent">
                    <!-- Unread Enquiries Subtab -->
                    <div class="tab-pane fade show active" id="unreadEnquiries" role="tabpanel" aria-labelledby="unread-enquiries-tab">
                        <div class="subtab-content">
                            <div class="search-results" id="unreadEnquiryResults"></div>
                        </div>
                    </div>

                    <!-- Read Enquiries Subtab -->
                    <div class="tab-pane fade" id="readEnquiries" role="tabpanel" aria-labelledby="read-enquiries-tab">
                        <div class="subtab-content">
                            <div class="search-results" id="readEnquiryResults"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Tabs, Search, and Email Sending -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Fetch and display unread enquiries
            function fetchUnreadEnquiries() {
                $.ajax({
                    url: 'a_manage_enquiries.php',
                    method: 'POST',
                    data: { action: 'fetch', status: 'unread' },
                    success: function(response) {
                        $('#unreadEnquiryResults').html(response);
                        updateUnreadEnquiryCount();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching unread enquiries: " + error);
                    }
                });
            }

            // Fetch and display read enquiries
            function fetchReadEnquiries() {
                $.ajax({
                    url: 'a_manage_enquiries.php',
                    method: 'POST',
                    data: { action: 'fetch', status: 'read' },
                    success: function(response) {
                        $('#readEnquiryResults').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching read enquiries: " + error);
                    }
                });
            }

            // Update the unread enquiry count
            function updateUnreadEnquiryCount() {
                $.ajax({
                    url: 'a_manage_enquiries.php',
                    method: 'POST',
                    data: { action: 'count_unread' },
                    success: function(response) {
                        $('#unreadEnquiryCount').text(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error updating unread enquiry count: " + error);
                    }
                });
            }

            // Search for schemes
            $('#searchSchemeButton').on('click', function() {
                var query = $('#searchScheme').val();
                if (query) {
                    $.ajax({
                        url: 'a_manage_scheme.php',
                        method: 'POST',
                        data: { query: query },
                        success: function(response) {
                            $('#schemeSearchResults').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error searching for schemes: " + error);
                        }
                    });
                } else {
                    alert("Please enter a search term.");
                }
            });

            // Delete scheme confirmation and action
            $(document).on('click', '.delete-scheme', function(e) {
                e.preventDefault();
                var schemeId = $(this).data('id');
                if (confirm("Are you sure you want to delete this scheme?")) {
                    $.ajax({
                        url: 'delete_scheme.php',
                        method: 'POST',
                        data: { id: schemeId },
                        success: function(response) {
                            if (response === "success") {
                                alert("Scheme deleted successfully!");
                                $('#searchSchemeButton').click(); // Refresh the search results
                            } else {
                                alert("Error: " + response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error deleting scheme: " + error);
                        }
                    });
                }
            });

            // Suspend/Unsuspend scheme confirmation and action
            $(document).on('click', '.suspend-scheme', function(e) {
                e.preventDefault();
                var schemeId = $(this).data('id');
                var action = $(this).data('action'); // 'suspend' or 'unsuspend'
                var confirmMessage = action === 'suspend' ? "Are you sure you want to suspend this scheme?" : "Are you sure you want to unsuspend this scheme?";
                if (confirm(confirmMessage)) {
                    $.ajax({
                        url: 'suspend_scheme.php',
                        method: 'POST',
                        data: { id: schemeId, action: action },
                        success: function(response) {
                            if (response === "success") {
                                alert("Scheme " + action + "ed successfully!");
                                $('#searchSchemeButton').click(); // Refresh the search results
                            } else {
                                alert("Error: " + response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error " + action + "ing scheme: " + error);
                        }
                    });
                }
            });

            // Approve/Disapprove scheme confirmation and action
            $(document).on('click', '.approve-scheme', function(e) {
                e.preventDefault();
                var schemeId = $(this).data('id');
                var action = $(this).data('action'); // 'approve' or 'disapprove'
                var confirmMessage = action === 'approve' ? "Are you sure you want to approve this scheme?" : "Are you sure you want to disapprove this scheme?";
                if (confirm(confirmMessage)) {
                    $.ajax({
                        url: 'a_approve_scheme.php',
                        method: 'POST',
                        data: { id: schemeId, action: action },
                        success: function(response) {
                            if (response === "success") {
                                alert("Scheme " + action + "d successfully!");
                                $('#searchSchemeButton').click(); // Refresh the search results
                            } else {
                                alert("Error: " + response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error " + action + "ing scheme: " + error);
                        }
                    });
                }
            });

            // Initial fetch of enquiries
            fetchUnreadEnquiries();
            fetchReadEnquiries();
        });
    </script>
</body>
    <?php include '../includes/footer.php'; ?>
</html>

<?php
$conn_login->close();
?>