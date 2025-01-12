<?php
include('../includes/session_start.php'); // Include the session start file
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../PHPMailer/Exception.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

include('../includes/db_login_connection.php'); // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure database connection is valid
if (!$conn_login) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: user_login.php');
    exit;
}

$current_user = $_SESSION['user'];

// Fetch current user details
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn_login->prepare($query);
$stmt->bind_param('s', $current_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = 'email'; // Only allow email updates
    $new_value = trim($_POST['value']); // New value for the email field

    // Validate input
    if (empty($new_value)) {
        $error = "Email is required.";
    } elseif (!filter_var($new_value, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check for uniqueness if email is changed
        $check_query = "SELECT * FROM users WHERE email = ? AND username != ?";
        $check_stmt = $conn_login->prepare($check_query);
        $check_stmt->bind_param('ss', $new_value, $current_user);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            // Update user email
            $update_query = "UPDATE users SET email = ? WHERE username = ?";
            $update_stmt = $conn_login->prepare($update_query);
            $update_stmt->bind_param('ss', $new_value, $current_user);

            if ($update_stmt->execute()) {
                // Send email notification
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'mail.schemesfinder.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'info@schemesfinder.com';
                    $mail->Password = 'Succe$$@0809';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('info@schemesfinder.com', 'Schemes Finder');
                    $mail->addAddress($new_value); // Send to the new email

                    $mail->isHTML(true);
                    $mail->Subject = 'Account Details Updated - Schemes Finder';
                    $mail->Body = "Hello $current_user,<br><br>Your email has been successfully updated.<br>If you were not aware of this change, please contact our support team immediately at info@schemesfinder.com.<br><br>Best Regards,<br>Schemes Finder Team";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mailer Error: {$mail->ErrorInfo}");
                }

                $success = "Email updated successfully.";
            } else {
                $error = "Failed to update email. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Dark theme styles */
        body {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
        }

        /* Override Bootstrap form controls */
        .form-control {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
            border: 1px solid #444 !important; /* Dark gray border */
        }

        .form-control:focus {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
            border-color: #007BFF !important; /* Blue border on focus */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important; /* Blue shadow on focus */
        }

        /* Webkit autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #000 inset !important; /* Black background */
            -webkit-text-fill-color: #fff !important; /* White text */
        }

        /* Button styles */
        .btn-primary {
            background-color: #007BFF !important; /* Blue background */
            border-color: #007BFF !important; /* Blue border */
        }

        .btn-secondary {
            background-color: #6C757D !important; /* Gray background */
            border-color: #6C757D !important; /* Gray border */
        }

        /* Alert styles */
        .alert-danger {
            background-color: #dc3545 !important; /* Red background */
            border-color: #dc3545 !important; /* Red border */
            color: #fff !important; /* White text */
        }

        .alert-success {
            background-color: #28a745 !important; /* Green background */
            border-color: #28a745 !important; /* Green border */
            color: #fff !important; /* White text */
        }

        /* Editable field styles */
        .editable-field {
            margin-bottom: 20px;
        }

        .editable-field label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .editable-field .input-group {
            display: flex;
            align-items: center;
        }

        .editable-field .input-group input {
            flex: 1;
            margin-right: 10px;
        }

        .editable-field .input-group button {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Edit Profile</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Go Back to Dashboard Button -->
        <div class="mb-4">
            <a href="user_dashboard.php" class="btn btn-secondary">Go Back to Dashboard</a>
        </div>

        <!-- Username Field (Read-only) -->
        <div class="editable-field">
            <label for="username">Username</label>
            <div class="input-group">
                <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            </div>
        </div>

        <!-- Email Field -->
        <div class="editable-field">
            <label for="email">Email</label>
            <div class="input-group">
                <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                <button type="button" class="btn btn-secondary" onclick="enableEdit('email')">Edit</button>
                <button type="button" class="btn btn-primary" onclick="saveField('email')" style="display: none;">Save</button>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script>
        function enableEdit(field) {
            const input = document.getElementById(field);
            const editButton = input.nextElementSibling;
            const saveButton = editButton.nextElementSibling;

            input.removeAttribute('readonly');
            editButton.style.display = 'none';
            saveButton.style.display = 'inline-block';
        }

        function saveField(field) {
            const input = document.getElementById(field);
            const newValue = input.value.trim();

            if (newValue === "") {
                alert(field.charAt(0).toUpperCase() + field.slice(1) + " is required.");
                return;
            }

            const formData = new FormData();
            formData.append('field', field);
            formData.append('value', newValue);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload(); // Reload the page to reflect changes
                } else {
                    alert("Failed to update " + field + ".");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
            });
        }
    </script>
</body>
</html>