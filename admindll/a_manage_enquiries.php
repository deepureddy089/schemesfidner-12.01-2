<?php
include('../includes/session_start.php'); // Include the session start file
require_once '../PHPMailer/Exception.php'; //Include PHP mailer to send emails
require_once '../PHPMailer/PHPMailer.php';
require_once '../PHPMailer/SMTP.php';

include('../includes/db_login_connection.php'); // Include the database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'fetch') {
        $status = $_POST['status'];
        $sql = "SELECT * FROM enquiries WHERE status = ? ORDER BY created_at DESC";
        $stmt = $conn_login->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $statusClass = ($row['status'] === 'unread') ? 'unread' : 'read';
                    echo "<div class='enquiry-item $statusClass'>";
                    echo "<p><strong>Name:</strong> " . htmlspecialchars($row['name']) . "</p>";
                    echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>";
                    echo "<p><strong>Phone:</strong> " . htmlspecialchars($row['phone']) . "</p>";
                    echo "<p><strong>Message:</strong> " . htmlspecialchars($row['message']) . "</p>";
                    echo "<p><strong>Date:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
                    if ($row['status'] === 'unread') {
                        echo "<button class='btn btn-primary mark-as-read' data-id='" . $row['id'] . "'>Mark as Read</button>";
                        echo "<button class='btn btn-success reply-to-enquiry' data-id='" . $row['id'] . "' data-subject='" . htmlspecialchars($row['message']) . "'>Reply</button>";
                    } else {
                        echo "<button class='btn btn-warning mark-as-unread' data-id='" . $row['id'] . "'>Mark as Unread</button>";
                    }
                    echo "</div><hr>";
                }
            } else {
                echo "<p>No enquiries found.</p>";
            }
            $stmt->close();
        } else {
            echo "Error: " . $conn_login->error;
        }
    } elseif ($action === 'count_unread') {
        // Count unread enquiries
        $sql = "SELECT COUNT(*) AS count FROM enquiries WHERE status = 'unread'";
        $result = $conn_login->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            echo $row['count'];
        } else {
            echo "0";
        }
    } elseif ($action === 'mark_as_read') {
        // Mark enquiry as read
        $id = $_POST['id'];
        $sql = "UPDATE enquiries SET status = 'read' WHERE id = ?";
        $stmt = $conn_login->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "success";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error: " . $conn_login->error;
        }
    } elseif ($action === 'mark_as_unread') {
        // Mark enquiry as unread
        $id = $_POST['id'];
        $sql = "UPDATE enquiries SET status = 'unread' WHERE id = ?";
        $stmt = $conn_login->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "success";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error: " . $conn_login->error;
        }
    } elseif ($action === 'reply_to_enquiry') {
        // Reply to enquiry using SMTP
        $id = $_POST['id'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];

        // Fetch the enquiry details
        $sql = "SELECT email FROM enquiries WHERE id = ?";
        $stmt = $conn_login->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $to = $row['email'];

                // Create a new PHPMailer instance
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
                    $mail->addAddress($to); // Recipient email

                    // Content
                    $mail->isHTML(false); // Set email format to plain text
                    $mail->Subject = $subject; // Email subject
                    $mail->Body = $message; // Email body

                    // Send the email
                    $mail->send();
                    echo "success";
                } catch (Exception $e) {
                    echo "Error: " . $mail->ErrorInfo; // Display error message if sending fails
                }
            } else {
                echo "Error: Enquiry not found.";
            }
            $stmt->close();
        } else {
            echo "Error: " . $conn_login->error;
        }
    }
} else {
    echo "Invalid request method!";
}

$conn_login->close();
?>