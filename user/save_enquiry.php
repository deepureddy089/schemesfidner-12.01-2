<?php
include('../includes/db_login_connection.php');// Include Databse Connection File

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        echo "All fields are required!";
        exit;
    }

    // Insert enquiry into the database
    $sql = "INSERT INTO enquiries (name, email, phone, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn_login->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $phone, $message);
        if ($stmt->execute()) {
            echo "success"; // Success response
        } else {
            echo "Error: " . $stmt->error; // Database error
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn_login->error; // SQL preparation error
    }
} else {
    echo "Invalid request method!";
}

$conn_login->close();
?>