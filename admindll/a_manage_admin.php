<?php
session_start();
include('../includes/db_login_connection.php');

// Initialize variables
$username = $email = $role = '';
$error = '';
$success = '';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['adminUsername']);
    $password = password_hash(trim($_POST['adminPassword']), PASSWORD_DEFAULT); // Hash the password
    $email = trim($_POST['adminEmail']);
    $role = trim($_POST['adminRole']);

    // Validate input
    if (empty($username) || empty($_POST['adminPassword']) || empty($email) || empty($role)) {
        $error = "All fields are required!";
    } else {
        // Insert admin into the database
        $sql = "INSERT INTO admin_users (username, password, email, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn_login->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $password, $email, $role);
            if ($stmt->execute()) {
                $success = "User added successfully!";
                // Clear form fields after successful submission
                $username = $email = $role = '';
            } else {
                $error = "Error: " . $stmt->error; // Database error
            }
            $stmt->close();
        } else {
            $error = "Error: " . $conn_login->error; // SQL preparation error
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin/Editor/Clerk</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-back {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Add Admin/Editor/Clerk</h2>

        <!-- Display Error Message -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Display Success Message -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add User Form -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="adminUsername">Username</label>
                <input type="text" class="form-control" id="adminUsername" name="adminUsername" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="adminEmail">Email</label>
                <input type="email" class="form-control" id="adminEmail" name="adminEmail" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="adminPassword">Password</label>
                <input type="password" class="form-control" id="adminPassword" name="adminPassword" required>
            </div>
            <div class="form-group">
                <label for="adminRole">Role</label>
                <select class="form-control" id="adminRole" name="adminRole" required>
                    <option value="">Select Role</option>
                    <option value="Admin" <?php echo ($role === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="Editor" <?php echo ($role === 'Editor') ? 'selected' : ''; ?>>Editor</option>
                    <option value="Clerk" <?php echo ($role === 'Clerk') ? 'selected' : ''; ?>>Clerk</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Add User</button>
        </form>

        <!-- Go Back to Dashboard -->
        <div class="text-center btn-back">
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection if it was opened
if ($conn_login) {
    $conn_login->close();
}
?>