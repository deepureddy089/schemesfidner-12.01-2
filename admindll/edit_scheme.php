<?php
include('../includes/session_start.php'); // Include the session start file
include('../includes/db_login_connection.php');

// Check if user is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

if (isset($_GET['id'])) {
    $scheme_id = $_GET['id'];

    // Fetch scheme details from the database
    $sql = "SELECT * FROM schemes WHERE id = ?";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param("i", $scheme_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $scheme = $result->fetch_assoc();
    } else {
        echo "Scheme not found!";
        exit;
    }

    if (isset($_POST['submit'])) {
        $scheme_name = $_POST['scheme_name'];
        $state = $_POST['state'];
        $age_group = $_POST['age_group'];
        $caste = $_POST['caste'];
        $state_logo = $_POST['state_logo'];
        $scheme_link = $_POST['scheme_link'];

        // Update scheme in the database
        $update_sql = "UPDATE schemes SET scheme_name = ?, state = ?, age_group = ?, caste = ?, state_logo = ?, scheme_link = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssssi", $scheme_name, $state, $age_group, $caste, $state_logo, $scheme_link, $scheme_id);

        if ($update_stmt->execute()) {
            header('Location: admin_dashboard.php');
        } else {
            $error = "Error updating scheme!";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Scheme</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../includes/header.php'; ?>
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Scheme</h2>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <form method="POST">
            <div class="form-group">
                <label for="scheme_name">Scheme Name</label>
                <input type="text" class="form-control" id="scheme_name" name="scheme_name" value="<?php echo htmlspecialchars($scheme['scheme_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($scheme['state']); ?>" required>
            </div>
            <div class="form-group">
                <label for="age_group">Age Group</label>
                <input type="text" class="form-control" id="age_group" name="age_group" value="<?php echo htmlspecialchars($scheme['age_group']); ?>" required>
            </div>
            <div class="form-group">
                <label for="caste">Caste</label>
                <input type="text" class="form-control" id="caste" name="caste" value="<?php echo htmlspecialchars($scheme['caste']); ?>" required>
            </div>

            <!-- State Logo Edit Section -->
            <div class="form-group">
                <label for="state_logo">State Logo URL</label>
                <input type="text" class="form-control" id="state_logo" name="state_logo" value="<?php echo htmlspecialchars($scheme['state_logo']); ?>" required>
                <!-- Button to view logo -->
                <button type="button" class="btn btn-info mt-2" onclick="window.open('<?php echo htmlspecialchars($scheme['state_logo']); ?>', '_blank')">View Logo</button>
            </div>

            <!-- Scheme Link Edit Section -->
            <div class="form-group">
                <label for="scheme_link">Scheme Link</label>
                <input type="text" class="form-control" id="scheme_link" name="scheme_link" value="<?php echo htmlspecialchars($scheme['scheme_link']); ?>" required>
                <!-- Button to view scheme link -->
                <button type="button" class="btn btn-info mt-2" onclick="window.open('<?php echo htmlspecialchars($scheme['scheme_link']); ?>', '_blank')">View Link</button>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Update Scheme</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>