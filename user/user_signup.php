<?php
include('../includes/session_start.php'); // Include the session start file
require_once '../PHPMailer/Exception.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

include('../includes/db_login_connection.php'); // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is already logged in
if (isset($_SESSION['user'])) {
    // Redirect to user dashboard if already logged in
    header('Location: user_dashboard.php');
    exit;
}

$success_message = ''; // Initialize success message

if (isset($_POST['submit'])) {
    $username = $_POST['username']; // Get username from POST data
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];

    // Validate inputs
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $error = "All fields are required!";
    } else {
        // Check if the username already exists
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn_login->query($query);

        if ($result === false) {
            die('Error executing the query: ' . $conn_login->error);
        }

        if ($result->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Check if the email already exists
            $email_query = "SELECT * FROM users WHERE email = '$email'";
            $email_result = $conn_login->query($email_query);

            if ($email_result === false) {
                die('Error executing the email query: ' . $conn_login->error);
            }

            if ($email_result->num_rows > 0) {
                $error = "Email already exists!";
            } else {
                // Password validation
                if (strlen($password) < 8) {
                    $error = "Password must be at least 8 characters long!";
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $error = "Password must contain at least one number!";
                } elseif (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
                    $error = "Password must contain at least one special character!";
                } elseif ($password !== $confirm_password) {
                    $error = "Passwords do not match!";
                } else {
                    // Hash the password for security
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user into the database
                    $insert_query = "INSERT INTO users (username, password, email) VALUES ('$username', '$hashed_password', '$email')";
                    if ($conn_login->query($insert_query) === TRUE) {
                        // Send signup notification email
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
                            $mail->Subject = 'Signup Successful - Schemes Finder';
                            $mail->Body = "Hello $username,<br><br>Thank you for signing up with Schemes Finder! Your account has been successfully created.<br><br>Best Regards,<br>Schemes Finder Team";

                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                        }

                        // Set success message
                        $success_message = "User account created successfully! You will be redirected to the login page in 3 seconds.";

                        // Redirect to login page after 3 seconds
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = 'user_login.php';
                            }, 3000);
                        </script>";
                    } else {
                        $error = "Error inserting user: " . $conn_login->error;
                    }
                }
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
    <title>User Signup</title>
    <?php include '../includes/header.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Dark theme for the body */
    body {
        background-color: #000; /* Black background */
        color: #fff; /* White text */
        font-family: Arial, sans-serif;
    }

    /* Form container */
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

    /* Specific style for the username field */
    #username {
        background-color: #000 !important; /* Force black background */
        color: #fff !important; /* Force white text */
        border: 1px solid #444 !important; /* Dark border */
    }

    #username:focus {
        background-color: #000 !important; /* Keep black background on focus */
        color: #fff !important; /* Keep white text on focus */
        border-color: #555 !important; /* Light border on focus */
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.1) !important; /* Light shadow on focus */
    }

    /* Override autofill styles for all input fields */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
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

    /* Password requirements */
    .password-requirements {
        font-size: 0.875em;
        color: #6c757d; /* Gray text */
    }

    .password-match {
        font-size: 0.875em;
        color: #28a745; /* Green text */
    }

    .password-mismatch {
        font-size: 0.875em;
        color: #dc3545; /* Red text */
    }

    .requirement {
        font-size: 0.875em;
        color: #6c757d; /* Gray text */
    }

    .requirement.valid {
        color: #28a745; /* Green text */
    }

    .requirement.invalid {
        color: #dc3545; /* Red text */
    }

    /* Terms checkbox */
    .terms-checkbox {
        margin-top: 20px;
    }

    .form-check-label {
        color: #fff; /* White text */
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
        <h2>User Signup</h2>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <?php if (!empty($success_message)) { echo "<div class='alert alert-success'>$success_message</div>"; } ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" readonly required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="form-text password-requirements">
                    Password must meet the following requirements:
                    <ul>
                        <li id="lengthRequirement" class="requirement">At least 8 characters</li>
                        <li id="numberRequirement" class="requirement">At least one number</li>
                        <li id="specialCharRequirement" class="requirement">At least one special character (!@#$%^&*()\-_=+{};:,<.>)</li>
                    </ul>
                </small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <small id="passwordMatchMessage" class="form-text"></small>
            </div>
            <div class="form-group terms-checkbox">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="termsCheckbox" required>
                    <label class="form-check-label" for="termsCheckbox">
                        I agree to GovSearch's 
                        <a href="../pages/terms_and_conditions.php" target="_blank">Terms and Conditions</a> 
                        and 
                        <a href="../pages/privacy_policy.php" target="_blank">Privacy Policy</a>.
                    </label>
                </div>
            </div>
            <button type="submit" name="submit" class="btn btn-primary" id="signupButton" disabled>Signup</button>
        </form>
        <p class="mt-3">Already have an account? <a href="user_login.php">Login here</a></p>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
        // Autofill username based on email
        document.getElementById('email').addEventListener('input', function() {
            var email = this.value;
            var usernameField = document.getElementById('username');
            var atIndex = email.indexOf('@');
            if (atIndex > 0) {
                usernameField.value = email.substring(0, atIndex);
            } else {
                usernameField.value = '';
            }
        });

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
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
        document.getElementById('confirm_password').addEventListener('input', function() {
            var password = document.getElementById('password').value;
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

        // Enable/disable signup button based on checkbox
        document.getElementById('termsCheckbox').addEventListener('change', function() {
            var signupButton = document.getElementById('signupButton');
            signupButton.disabled = !this.checked;
        });
    </script>
</body>
</html>