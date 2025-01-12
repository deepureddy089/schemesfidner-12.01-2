<?php
include('../includes/session_start.php'); // Include the session start file
require_once '../PHPMailer/Exception.php';// Include PHPMailer classes for email functionality
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';
include('../includes/db_login_connection.php'); // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false];

if (isset($_POST['username'])) {
    $username = $_POST['username'];

    // Fetch user email
    $query = "SELECT email FROM users WHERE username = ?";
    $stmt = $conn_login->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && !empty($user['email'])) {
        // Generate a new OTP
        $otp = rand(100000, 999999);
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Update OTP and expiry in the database
        $query = "UPDATE users SET otp = ?, otp_expiry = ? WHERE username = ?";
        $stmt = $conn_login->prepare($query);
        $stmt->bind_param('sss', $otp, $otpExpiry, $username);
        if ($stmt->execute()) {
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
                $mail->addAddress($user['email']); // User's email

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your New OTP for Login - Schemes Finder';
                $mail->Body = "Your new OTP for password reset for Schemes Finder accouint is: <strong>$otp</strong>. It will expire in 5 minutes.<br><br><strong>Do not share this OTP with anyone.</strong><br> From<br>Schemes Finder Admin";

                $mail->send();
                $response['success'] = true;
            } catch (Exception $e) {
                $response['error'] = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $response['error'] = "Failed to update OTP in the database.";
        }
    } else {
        $response['error'] = "User not found or email is missing.";
    }
} else {
    $response['error'] = "Username not provided.";
}

echo json_encode($response);