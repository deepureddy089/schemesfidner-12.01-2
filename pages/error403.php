<?php
// Start the session (if not already started)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #333; /* Dark background */
            color: white; /* White text */
        }
        .error-container {
            text-align: center;
            margin-top: 100px; /* Adjust as needed */
        }
        .error-container h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .error-container p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 10px; /* Space between buttons */
        }
        .btn-primary {
            background-color: #00bfff; /* Blue button color */
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #009acd; /* Darker blue on hover */
        }
    </style>
    <?php include('../includes/header.php'); ?>
</head>
<body>
    <!-- Main Content -->
    <div class="container">
        <div class="error-container">
            <h1>403 - Access Denied</h1>
            <p>You don't have permission to access this page.</p>
            <p>If you believe this is an error, please contact support or check your login credentials.</p>
            <div class="btn-container">
                <a href="/" class="btn btn-primary"><i class="fas fa-home"></i> Go Home</a>
                <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Log In</a>
                <a href="contact_us.php" class="btn btn-primary"><i class="fas fa-envelope"></i> Contact Support</a>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>