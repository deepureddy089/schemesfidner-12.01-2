<?php
include('../includes/session_start.php'); // Include the session start file
require_once '../PHPMailer/Exception.php';
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

include('../includes/db_login_connection.php'); // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';
$step = 1; // Step 1: Enter email, Step 2: Enter OTP, Step 3: Reset password

if (isset($_POST['get_otp'])) {
    // Step 1: Get email and send OTP
    $email = $_POST['email'];

    // Check if the email exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn_login->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); // OTP expires in 5 minutes

        // Insert OTP into the database
        $insert_query = "INSERT INTO otps (email, otp, expires_at) VALUES (?, ?, ?)";
        $stmt = $conn_login->prepare($insert_query);
        $stmt->bind_param('sss', $email, $otp, $expires_at);
        $stmt->execute();

        // Send OTP via email
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
            $mail->Subject = 'Password Reset OTP - Schemes Finder';
            $mail->Body = "Your OTP for password reset for Schemes Finder accouint is: <strong>$otp</strong>. It will expire in 5 minutes.<br><br><strong>Do not share this OTP with anyone.</strong><br> From<br>Schemes Finder Admin";

            $mail->send();
            $success = "OTP has been sent to your email.";
            $step = 2; // Move to Step 2: Enter OTP
        } catch (Exception $e) {
            $error = "Failed to send OTP. Please try again.";
        }
    } else {
        $error = "No user found with that email address.";
    }
} elseif (isset($_POST['verify_otp'])) {
    // Step 2: Verify OTP
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    // Check if the OTP is valid and not expired
    $query = "SELECT * FROM otps WHERE email = ? AND otp = ? AND expires_at > NOW()";
    $stmt = $conn_login->prepare($query);
    $stmt->bind_param('ss', $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $success = "OTP verified successfully.";
        $step = 3; // Move to Step 3: Reset password
    } else {
        $error = "Invalid or expired OTP.";
    }
} elseif (isset($_POST['reset_password'])) {
    // Step 3: Reset password
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn_login->prepare($update_query);
        $stmt->bind_param('ss', $hashed_password, $email);
        $stmt->execute();

        // Display success message and redirect
        $success = "Password reset successfully. Redirecting to the login page in 3 seconds.";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'user_login.php';
            }, 3000);
        </script>";
        $step = 4; // Move to Step 4: Hide fields and show success message
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <?php include '../includes/header.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .password-requirements {
            font-size: 0.875em;
            color: #6c757d;
        }
        .password-match {
            font-size: 0.875em;
            color: #28a745;
        }
        .password-mismatch {
            font-size: 0.875em;
            color: #dc3545;
        }
        .requirement {
            font-size: 0.875em;
            color: #6c757d;
        }
        .requirement.valid {
            color: #28a745;
        }
        .requirement.invalid {
            color: #dc3545;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Forgot Password</h2>
        <?php if (!empty($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <?php if (!empty($success)) { echo "<div class='alert alert-success'>$success</div>"; } ?>

        <?php if ($step == 1) { ?>
            <!-- Step 1: Enter Email -->
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" name="get_otp" class="btn btn-primary">Get OTP</button>
            </form>
        <?php } elseif ($step == 2) { ?>
            <!-- Step 2: Enter OTP -->
            <form method="POST">
                <div class="form-group">
                    <label for="otp">OTP</label>
                    <input type="text" class="form-control" id="otp" name="otp" required>
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>">
                </div>
                <button type="submit" name="verify_otp" class="btn btn-primary">Verify OTP</button>
            </form>
        <?php } elseif ($step == 3) { ?>
            <!-- Step 3: Reset Password -->
            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password</label>
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
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>">
                <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
            </form>
        <?php } elseif ($step == 4) { ?>
            <!-- Step 4: Success Message -->
            <p>Redirecting to the login page in 3 seconds...</p>
        <?php } ?>
        <p class="mt-3">Remember your password? <a href="user_login.php">Login here</a></p>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
        // Real-time password validation
        document.getElementById('password')?.addEventListener('input', function() {
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
        document.getElementById('confirm_password')?.addEventListener('input', function() {
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
    </script>
</body>
</html>