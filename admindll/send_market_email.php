<?php
include('../includes/session_start.php'); // Include the session start file
require_once '../PHPMailer/Exception.php'; //Include PHP mailer to send emails
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

include('../includes/db_login_connection.php'); // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in as an admin (assuming session 'admin' is set)
if (!isset($_SESSION['admin'])) {
    header('Location: admin_dashboard.php'); // Redirect to login if not logged in
    exit;
}

$success_message = '';
$error_message = '';

// Function to fetch unique emails from the 'users' table
function fetchUniqueEmails($conn, $table, $column) {
    $emails = [];
    $query = "SELECT DISTINCT $column FROM $table";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $emails[] = $row[$column];
        }
    }
    return $emails;
}

if (isset($_POST['send_email'])) {
    // Get form data
    $subject = $_POST['subject'];
    $body = $_POST['body'];

    // Fetch emails for all registered users
    $emails = fetchUniqueEmails($conn_login, 'users', 'email');

    if (empty($emails)) {
        $error_message = "No registered users found.";
    } else {
        // Prepare the email content
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

            // Add recipients as BCC
            foreach ($emails as $email) {
                $mail->addBCC($email); // Use BCC instead of TO
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Send the email
            if ($mail->send()) {
                $success_message = "Email has been sent successfully!";
            } else {
                $error_message = "There was an error sending the email. Please try again later.";
            }
        } catch (Exception $e) {
            $error_message = "Mailer Error: {$mail->ErrorInfo}"; // Detailed error
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}"); // Log detailed error
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Marketing Email</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #f1f1f1;
            text-align: center;
            padding: 10px;
        }
        .alert {
            margin-top: 20px;
        }
        .btn-group .btn {
            transition: background-color 0.3s ease;
        }
        .btn-group .btn.active {
            background-color: #28a745; /* Green color for selected state */
            color: white;
        }
    </style>
</head>
<body>

<?php include('../includes/header.php'); ?>

<div class="container">
    <h1 class="mt-5 text-center">Send Marketing Email</h1>

    <!-- Display success or error message -->
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php elseif ($error_message): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="recipient_group">Send To:</label>
            <div class="btn-group d-flex" role="group" id="recipientGroup">
                <button type="button" class="btn btn-secondary w-100 active" onclick="setRecipientGroup('registered')">All Registered Users</button>
            </div>
            <input type="hidden" name="recipient_group" id="recipient_group" value="registered" required>
        </div>

        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" name="subject" class="form-control" required />
        </div>

        <div class="form-group">
            <label for="body">Body:</label>
            <textarea name="body" class="form-control" rows="5" required></textarea>
        </div>

        <button type="submit" name="send_email" class="btn btn-primary btn-block">Send Email</button>
    </form>

    <br>
    <a href="admin_dashboard.php" class="btn btn-secondary btn-block">Back to Dashboard</a>
</div>

<?php include('../includes/footer.php'); ?>

<script>
    // JavaScript to highlight the selected button (only one button now)
    function setRecipientGroup(group) {
        document.getElementById('recipient_group').value = group;

        // Highlight the selected button
        const buttons = document.querySelectorAll('#recipientGroup .btn');
        buttons.forEach(button => {
            if (button.textContent.toLowerCase().includes(group)) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }

    // Set the "All Registered Users" button as selected by default
    window.onload = function() {
        setRecipientGroup('registered');
    };
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>