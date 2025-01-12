<?php
include('../includes/session_start.php'); // Include the session start file
require_once '../PHPMailer/Exception.php'; //Include PHP mailer to send emails
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['users']) && isset($_POST['subject']) && isset($_POST['body'])) {
    $users = explode(',', $_POST['users']);
    $subject = $_POST['subject'];
    $body = $_POST['body'];

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
        foreach ($users as $user) {
            $mail->addAddress(trim($user));
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        echo "Email sent successfully!";
    } catch (Exception $e) {
        echo "Failed to send email. Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}
?>