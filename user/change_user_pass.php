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

// Initialize error and success messages
$error = '';
$success = '';
//
// Handle form submission to change password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    } else {
        // Password validation (same as user_signup.php)
        if (strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters long!";
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $error = "Password must contain at least one number!";
        } elseif (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $new_password)) {
            $error = "Password must contain at least one special character!";
        } else {
            // Fetch the current password from the database
            $query = "SELECT password, email FROM users WHERE username = ?";
            $stmt = $conn_login->prepare($query);
            $stmt->bind_param("s", $_SESSION['user']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verify current password
                if (password_verify($current_password, $user['password'])) {
                    // Hash the new password and update in the database
                    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = ? WHERE username = ?";
                    $update_stmt = $conn_login->prepare($update_query);
                    $update_stmt->bind_param("ss", $new_password_hashed, $_SESSION['user']);
                    $update_stmt->execute();

                    // After successful password change
                    if ($update_stmt->affected_rows > 0) {
                        $success = 'Password successfully updated.';

                        // Send email notification
                        $mail = new PHPMailer(true);

                        try {
                            // Server settings
                            $mail->isSMTP();
                    $mail->Host = 'mail.schemesfinder.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'info@schemesfinder.com';
                    $mail->Password = 'Succe$$@0809';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('info@schemesfinder.com', 'Schemes Finder');
                            $mail->addAddress($user['email']); // User's email

                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = 'Password Change Notification - Schemes Finder';
                            $mail->Body = "Hello {$_SESSION['user']},<br><br>Your password has been successfully changed on " . date('Y-m-d H:i:s') . ". If you did not request this change, please contact support immediately at info@schemesfinder.com <br><br>Best Regards,<br>Schemes Finder Team";

                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                        }
                    } else {
                        $error = 'Failed to update password. Please try again later.';
                    }

                    $update_stmt->close();
                } else {
                    $error = 'Current password is incorrect.';
                }
            } else {
                $error = 'User not found.';
            }

            $stmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <?php include '../includes/header.php'; ?>
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

        /* Validation message styles */
        .valid {
            color: green !important; /* Green for valid */
        }
        .invalid {
            color: red !important; /* Red for invalid */
        }
        .password-match {
            color: green !important; /* Green for matching passwords */
        }
        .password-mismatch {
            color: red !important; /* Red for mismatched passwords */
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
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Change Your Password</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <form id="changePasswordForm" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <small id="lengthRequirement" class="form-text invalid">Password must be at least 8 characters long.</small>
                    <small id="numberRequirement" class="form-text invalid">Password must contain at least one number.</small>
                    <small id="specialCharRequirement" class="form-text invalid">Password must contain at least one special character.</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <small id="passwordMatchMessage" class="form-text"></small>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Change Password</button>
            </form>
        <?php endif; ?>

        <!-- Back to Dashboard Button -->
        <div class="mt-3">
            <a href="user_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Function to initialize real-time validation
        function initializeValidation() {
            // Check if the form elements exist
            var newPasswordInput = document.getElementById('new_password');
            var confirmPasswordInput = document.getElementById('confirm_password');

            if (newPasswordInput && confirmPasswordInput) {
                // Real-time password validation
                newPasswordInput.addEventListener('input', function() {
                    var password = this.value;
                    var lengthRequirement = document.getElementById('lengthRequirement');
                    var numberRequirement = document.getElementById('numberRequirement');
                    var specialCharRequirement = document.getElementById('specialCharRequirement');

                    // Check length
                    if (password.length >= 8) {
                        lengthRequirement.classList.remove('invalid');
                        lengthRequirement.classList.add('valid');
                    } else {
                        lengthRequirement.classList.remove('valid');
                        lengthRequirement.classList.add('invalid');
                    }

                    // Check for at least one number
                    if (/\d/.test(password)) {
                        numberRequirement.classList.remove('invalid');
                        numberRequirement.classList.add('valid');
                    } else {
                        numberRequirement.classList.remove('valid');
                        numberRequirement.classList.add('invalid');
                    }

                    // Check for at least one special character
                    if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
                        specialCharRequirement.classList.remove('invalid');
                        specialCharRequirement.classList.add('valid');
                    } else {
                        specialCharRequirement.classList.remove('valid');
                        specialCharRequirement.classList.add('invalid');
                    }
                });

                // Password confirmation check
                confirmPasswordInput.addEventListener('input', function() {
                    var password = document.getElementById('new_password').value;
                    var confirmPassword = this.value;
                    var message = document.getElementById('passwordMatchMessage');

                    if (password === confirmPassword) {
                        message.textContent = 'Passwords match!';
                        message.classList.remove('password-mismatch');
                        message.classList.add('password-match');
                    } else {
                        message.textContent = 'Passwords do not match!';
                        message.classList.remove('password-match');
                        message.classList.add('password-mismatch');
                    }
                });
            }
        }

        // Initialize validation after the content is loaded
        initializeValidation();
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
$conn_login->close();
?>