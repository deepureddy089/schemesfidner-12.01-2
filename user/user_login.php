<?php
include('../includes/session_start.php'); // Include the session start file
require_once '../PHPMailer/Exception.php'; //Include PHP mailer to send emails
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

include('../includes/db_login_connection.php'); // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is already logged in
if (isset($_SESSION['user'])) {
    // Redirect to the dashboard or the redirect_to URL
    if (isset($_GET['redirect_to'])) {
        header('Location: ' . urldecode($_GET['redirect_to']));
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

if (isset($_POST['submit'])) {
    $email = $_POST['email']; // Changed from username to email
    $password = $_POST['password'];

    // SQL query to fetch user details from the users table
    $query = "SELECT * FROM users WHERE email = ?"; // Changed to email
    $stmt = $conn_login->prepare($query);
    $stmt->bind_param('s', $email); // Changed to email
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if the password matches
        if (password_verify($password, $user['password'])) {
            // Check if MFA is enabled for the user
            if ($user['mfa_toggle'] == 1) {
                // MFA is enabled, generate and send OTP
                $otp = rand(100000, 999999);

                // Store the OTP in the session for verification
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_email'] = $email; // Store the email for OTP verification
                $_SESSION['otp_user'] = $user['username']; // Store the username for later use

                // Send OTP to the user's email
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
                    $mail->Subject = 'Your OTP for Login';
                    $mail->Body = "Hello " . $user['username'] . ",<br><br>Your OTP for login is: <strong>$otp</strong>.<br>
                    <br>This OTP is valid for 5 minutes. Do not share it with anyone.
                    <br>Best Regards,<br>Gov Search Team";

                    $mail->send();
                    $emailSent = true; // Flag to indicate email was sent successfully
                } catch (Exception $e) {
                    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                    $error = "Failed to send OTP. Please try again.";
                }

                // Display the OTP modal
                $showOtpModal = true;
            } else {
                // MFA is disabled, log the user in directly
                $_SESSION['user'] = $user['username']; // Store username in session

                // Insert notification into database
                $message = "You have successfully logged in on " . date('Y-m-d H:i:s');
                $status = 'unread'; // or 'read' based on your system's behavior
                $insert_query = "INSERT INTO notifications (username, message, status, timestamp) VALUES (?, ?, ?, NOW())";
                $stmt = $conn_login->prepare($insert_query);
                $stmt->bind_param("sss", $user['username'], $message, $status); // Use username for notifications
                $stmt->execute();

                // Display success message and redirect after 3 seconds
                $successMessage = "Login successful! Redirecting to dashboard...";
                $redirectUrl = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : 'user_dashboard.php';
                $redirectScript = "<script>
                    setTimeout(function() {
                        window.location.href = '$redirectUrl';
                    }, 3000);
                </script>";
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No user found with that email address!";
    }
}

// Verify OTP
if (isset($_POST['verify_otp'])) {
    $enteredOtp = $_POST['otp'];
    $storedOtp = $_SESSION['otp'];
    $email = $_SESSION['otp_email'];
    $username = $_SESSION['otp_user'];

    if ($enteredOtp == $storedOtp) {
        // OTP is correct
        $_SESSION['user'] = $username; // Store username in session

        // Insert notification into database
        $message = "You have successfully logged in on " . date('Y-m-d H:i:s');
        $status = 'unread'; // or 'read' based on your system's behavior
        $insert_query = "INSERT INTO notifications (username, message, status, timestamp) VALUES (?, ?, ?, NOW())";
        $stmt = $conn_login->prepare($insert_query);
        $stmt->bind_param("sss", $username, $message, $status); // Use username for notifications
        $stmt->execute();

        // Clear OTP session data
        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);
        unset($_SESSION['otp_user']);

        // Display success message and redirect after 3 seconds
        $successMessage = "Login successful! Redirecting to dashboard...";
        $redirectUrl = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : 'user_dashboard.php';
        $redirectScript = "<script>
            setTimeout(function() {
                window.location.href = '$redirectUrl';
            }, 3000);
        </script>";
    } else {
        $otpError = "Invalid OTP! Please try again.";
        $showOtpModal = true; // Show the OTP modal again
    }
}

// Resend OTP
if (isset($_POST['resend_otp'])) {
    $email = $_SESSION['otp_email'];
    $username = $_SESSION['otp_user'];

    // Generate a new 6-digit OTP
    $otp = rand(100000, 999999);

    // Store the new OTP in the session
    $_SESSION['otp'] = $otp;

    // Send the new OTP to the user's email
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
        $mail->addAddress($email); // User's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your New OTP for Login';
        $mail->Body = "Hello " . $username . ",<br><br>Your new OTP for login is: <strong>$otp</strong>.<br>
        <br>This OTP is valid for 5 minutes. Do not share it with anyone.
        <br>Best Regards,<br>Gov Search Team";

        $mail->send();
        $resendSuccess = "A new OTP has been sent to your email.";
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        $resendError = "Failed to resend OTP. Please try again.";
    }

    $showOtpModal = true; // Show the OTP modal again
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <?php include '../includes/header.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>

    /* Dark theme for the body */
    body {
        background-color: #000; /* Black background */
        color: #fff; /* White text */
        font-family: Arial, sans-serif;
    }

    /* Login form container */
    .container {
        background-color: #000; /* Black background */
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(255, 255, 255, 0.1); /* Light shadow for contrast */
    }

    /* Form labels */
    .form-group label {
        color: #fff; /* White text for labels */
    }

    /* Form inputs */
    .form-control {
        background-color: #000; /* Black background */
        color: #fff; /* White text */
        border: 1px solid #444; /* Dark border */
    }

    .form-control:focus {
        background-color: #000; /* Keep black background on focus */
        color: #fff; /* White text */
        border-color: #555; /* Light border on focus */
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.1); /* Light shadow on focus */
    }

    /* Specific styles for email and password fields */
    #email,
    #password {
        background-color: #000 !important; /* Force black background */
        color: #fff !important; /* Force white text */
        border: 1px solid #444 !important; /* Dark border */
    }

    #email:focus,
    #password:focus {
        background-color: #000 !important; /* Keep black background on focus */
        color: #fff !important; /* Keep white text on focus */
        border-color: #555 !important; /* Light border on focus */
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.1) !important; /* Light shadow on focus */
    }

    /* Override autofill styles for email and password fields */
    #email:-webkit-autofill,
    #email:-webkit-autofill:hover,
    #email:-webkit-autofill:focus,
    #email:-webkit-autofill:active,
    #password:-webkit-autofill,
    #password:-webkit-autofill:hover,
    #password:-webkit-autofill:focus,
    #password:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 1000px #000 inset !important; /* Black background */
        -webkit-text-fill-color: #fff !important; /* White text */
        transition: background-color 5000s ease-in-out 0s; /* Prevent background color change */
    }

    /* Buttons */
    .btn-primary {
        background-color: #007bff; /* Blue button */
        border: none;
    }

    .btn-primary:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    .btn-link {
        color: #007bff; /* Blue link color */
    }

    .btn-link:hover {
        color: #0056b3; /* Darker blue on hover */
    }

    /* OTP Modal */
    .modal-content {
        background-color: #000; /* Black background */
        color: #fff; /* White text */
        border: 1px solid #333; /* Dark border */
    }

    .modal-header {
        border-bottom: 1px solid #333; /* Dark border */
    }

    .modal-title {
        color: #fff; /* White text */
    }

    .close {
        color: #fff; /* White close button */
    }

    .close:hover {
        color: #ccc; /* Light gray on hover */
    }

    /* Alerts */
    .alert {
        background-color: #222; /* Dark alert background */
        color: #fff; /* White text */
        border: 1px solid #444; /* Dark border */
    }

    .alert-success {
        background-color: #155724; /* Dark green for success */
        border-color: #155724;
    }

    .alert-danger {
        background-color: #721c24; /* Dark red for danger */
        border-color: #721c24;
    }
</style>
</head>
<body>
    <div class="container mt-5">
        <h2>User Login</h2>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <?php if (isset($successMessage)) { echo "<div class='alert alert-success'>$successMessage</div>"; } ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email-1</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Login</button>
        </form>
        <div class="mt-3">
            <p>Don't have an account? <a href="user_signup.php" class="btn btn-link">Sign Up</a></p>
            <p>Forgot your password? <a href="forgot_password.php" class="btn btn-link">Reset Password</a></p>
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">Enter OTP</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if (isset($otpError)) { echo "<div class='alert alert-danger'>$otpError</div>"; } ?>
                    <?php if (isset($resendSuccess)) { echo "<div class='alert alert-success'>$resendSuccess</div>"; } ?>
                    <?php if (isset($resendError)) { echo "<div class='alert alert-danger'>$resendError</div>"; } ?>
                    <p>An OTP has been sent to your email. Please enter it below:</p>
                    <form method="POST" id="otpForm">
                        <div class="form-group">
                            <label for="otp">OTP</label>
                            <input type="text" class="form-control" id="otp" name="otp" required>
                        </div>
                        <button type="submit" name="verify_otp" class="btn btn-primary">Verify OTP</button>
                        <button type="button" id="resendOtpBtn" class="btn btn-secondary" disabled>Resend OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- Show OTP Modal if required -->
    <?php if (isset($showOtpModal) && $showOtpModal): ?>
        <script>
            $(document).ready(function() {
                // Show the OTP modal
                $('#otpModal').modal('show');

                // Disable Resend OTP button for 30 seconds
                let resendBtn = $('#resendOtpBtn');
                let countdown = 30;

                resendBtn.prop('disabled', true);
                resendBtn.text(`Resend OTP (${countdown}s)`);

                let timer = setInterval(function() {
                    countdown--;
                    resendBtn.text(`Resend OTP (${countdown}s)`);

                    if (countdown <= 0) {
                        clearInterval(timer);
                        resendBtn.prop('disabled', false);
                        resendBtn.text('Resend OTP');
                    }
                }, 1000);

                // Handle Resend OTP button click
                resendBtn.click(function() {
                    $.ajax({
                        url: 'user_login.php', // The same file or a separate endpoint for resending OTP
                        type: 'POST',
                        data: { resend_otp: true },
                        success: function(response) {
                            // Show a success message in the modal
                            $('#otpModal .modal-body').prepend(
                                '<div class="alert alert-success">A new OTP has been sent to your email.</div>'
                            );

                            // Reset the countdown for the resend button
                            countdown = 30;
                            resendBtn.prop('disabled', true);
                            resendBtn.text(`Resend OTP (${countdown}s)`);

                            timer = setInterval(function() {
                                countdown--;
                                resendBtn.text(`Resend OTP (${countdown}s)`);

                                if (countdown <= 0) {
                                    clearInterval(timer);
                                    resendBtn.prop('disabled', false);
                                    resendBtn.text('Resend OTP');
                                }
                            }, 1000);
                        },
                        error: function(xhr, status, error) {
                            // Show an error message in the modal
                            $('#otpModal .modal-body').prepend(
                                '<div class="alert alert-danger">Failed to resend OTP. Please try again.</div>'
                            );
                        }
                    });
                });
            });
        </script>
    <?php endif; ?>

    <!-- Redirect script after successful login -->
    <?php if (isset($redirectScript)) { echo $redirectScript; } ?>
    <?php include '../includes/footer.php'; ?>
</body>
</html>