<?php
// Start the session at the top of the PHP file
session_start();

// Include necessary files
include('../includes/db_login_connection.php'); // Include database connection
require_once '../PHPMailer/Exception.php'; // Include PHP mailer to send emails
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

// Ensure database connection is valid
if (!$conn_login) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Redirect already logged in users to the admin dashboard
if (isset($_SESSION['admin'])) {
    header('Location: admin_dashboard.php');
    exit; // Stop further execution
}

// Handle form submission
if (isset($_POST['submit'])) {
    // Collect input data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // SQL query to fetch admin details
    $query = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = $conn_login->prepare($query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn_login->error);
        die("Database error.");
    }

    // Bind and execute the statement
    $stmt->bind_param('s', $username);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        die("Database error.");
    }

    $result = $stmt->get_result();

    // Check if the admin exists and verify the password
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $admin['password'])) {
            // Store admin details in session
            $_SESSION['admin'] = $admin['username']; // Store username in session
            $_SESSION['role'] = $admin['role'];      // Store role in session

            // Send login notification email
            $mail = new PHPMailer\PHPMailer\PHPMailer(true); // Correct instantiation
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'mail.schemesfinder.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'info@schemesfinder.com';
                $mail->Password = 'Succe$$@0809';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Correct namespace
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('info@schemesfinder.com', 'Schemes Finder');
                $mail->addAddress($admin['email']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = "Admin Login Notification - $username";
                $mail->Body    = "Hello $username,<br><br>You have successfully logged in as an admin on " . date('Y-m-d H:i:s') . ". If this is not you, please change your password or contact the superadmin immediately.<br><br>Best Regards,<br>Schemes Finder Team";

                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error: {$mail->ErrorInfo}");
            }

            // Redirect to dashboard after successful login
            header('Location: admin_dashboard.php');
            exit;  // Ensure no further code is executed
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No admin found with that username!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <?php include '../includes/header.php'; ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Admin Login</h2>
        <!-- Show error message if there was an issue during login -->
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        
        <!-- Login form -->
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <!-- Footer (if needed) -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>