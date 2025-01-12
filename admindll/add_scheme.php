<?php
session_start();
include('../includes/header.php');
include('../includes/db_login_connection.php'); // Include your database connection

// Check if user is logged in and has admin or editor role
if (!isset($_SESSION['admin']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header('Location: admin_login.php');
    exit;
}

// Fetch unique states from the database, sorted alphabetically
$query = "SELECT DISTINCT state FROM schemes ORDER BY state ASC";
$result = $conn_login->query($query);

$success = null;
$error = null;

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Sanitize and validate input data
    $scheme_name = trim($_POST['scheme_name']);
    $slug = trim($_POST['slug']); // Get the slug from the form
    $state = trim($_POST['state']);
    $age_group = trim($_POST['age_group']);
    $state_logo = trim($_POST['state_logo']);
    $scheme_link = trim($_POST['scheme_link']);
    $description = trim($_POST['description']);
    $benefits = trim($_POST['benefits']);
    $eligibility = trim($_POST['eligibility']);
    $exclusions = trim($_POST['exclusions']);
    $application_Process = trim($_POST['application_Process']);
    $category = trim($_POST['category']);
    $label = trim($_POST['label']);
    $scheme_by = trim($_POST['scheme_by']);

    // Set suspended value based on role
    $suspended = ($_SESSION['admin'] === 'admin') ? 0 : 2;

    // Insert new scheme into the database
    $sql = "INSERT INTO schemes (
                scheme_name, slug, state, age_group, state_logo, scheme_link, description, 
                benefits, eligibility, exclusions, application_Process, category, 
                label, scheme_by, suspended
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn_login->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssi",
        $scheme_name, $slug, $state, $age_group, $state_logo, $scheme_link, $description,
        $benefits, $eligibility, $exclusions, $application_Process, $category,
        $label, $scheme_by, $suspended
    );

    if ($stmt->execute()) {
        $success = "Scheme successfully added!";
    } else {
        $error = "Error adding scheme: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Scheme</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-group textarea {
            min-height: 150px; /* Ensure enough space for 500 words */
        }
    </style>
    <script>
        // Function to generate a slug from the scheme name
        function generateSlug(text) {
            return text
                .toLowerCase() // Convert to lowercase
                .replace(/ /g, '-') // Replace spaces with hyphens
                .replace(/[^a-z0-9-]/g, ''); // Remove special characters
        }

        // Automatically fill the slug field when the scheme name changes
        document.addEventListener('DOMContentLoaded', function () {
            const schemeNameField = document.getElementById('scheme_name');
            const slugField = document.getElementById('slug');

            schemeNameField.addEventListener('input', function () {
                if (!slugField.dataset.manualEdit) {
                    slugField.value = generateSlug(schemeNameField.value);
                }
            });

            // Allow manual editing of the slug field
            slugField.addEventListener('input', function () {
                slugField.dataset.manualEdit = true;
            });
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Scheme</h2>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <?php if ($success) { ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <div class="mt-3">
                <a href="add_scheme.php" class="btn btn-secondary">Add Another Scheme</a>
                <a href="admin_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        <?php } else { ?>
            <form method="POST">
                <!-- Scheme Name -->
                <div class="form-group">
                    <label for="scheme_name">Scheme Name</label>
                    <input type="text" class="form-control" id="scheme_name" name="scheme_name" required>
                </div>

                <!-- Slug -->
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug" placeholder="Auto-generated slug" required>
                    <small class="form-text text-muted">The slug will be auto-generated based on the scheme name, but you can edit it if needed.</small>
                </div>

                <!-- State -->
                <div class="form-group">
                    <label for="state">State</label>
                    <select class="form-control" id="state" name="state" required>
                        <option value="" disabled selected>Select a state</option>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value=\"" . htmlspecialchars($row['state']) . "\">" . htmlspecialchars($row['state']) . "</option>";
                            }
                        } else {
                            echo "<option value=\"\" disabled>No states available</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Age Group -->
                <div class="form-group">
                    <label for="age_group">Age Group</label>
                    <input type="text" class="form-control" id="age_group" name="age_group">
                </div>

                <!-- State Logo URL -->
                <div class="form-group">
                    <label for="state_logo">State Logo URL</label>
                    <input type="url" class="form-control" id="state_logo" name="state_logo" placeholder="Enter the URL of the state logo">
                </div>

                <!-- Scheme Link -->
                <div class="form-group">
                    <label for="scheme_link">Scheme Link</label>
                    <input type="url" class="form-control" id="scheme_link" name="scheme_link" placeholder="Enter the scheme link">
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>

                <!-- Benefits -->
                <div class="form-group">
                    <label for="benefits">Benefits</label>
                    <textarea class="form-control" id="benefits" name="benefits" required></textarea>
                </div>

                <!-- Eligibility -->
                <div class="form-group">
                    <label for="eligibility">Eligibility</label>
                    <textarea class="form-control" id="eligibility" name="eligibility" required></textarea>
                </div>

                <!-- Exclusions -->
                <div class="form-group">
                    <label for="exclusions">Exclusions</label>
                    <textarea class="form-control" id="exclusions" name="exclusions" required></textarea>
                </div>

                <!-- Application Process -->
                <div class="form-group">
                    <label for="application_Process">Application Process</label>
                    <textarea class="form-control" id="application_Process" name="application_Process" required></textarea>
                </div>

                <!-- Category (Comma-Separated) -->
                <div class="form-group">
                    <label for="category">Category (Comma-Separated)</label>
                    <input type="text" class="form-control" id="category" name="category" placeholder="e.g., Education, Health, Finance" required>
                </div>

                <!-- Label (Comma-Separated) -->
                <div class="form-group">
                    <label for="label">Label (Comma-Separated)</label>
                    <input type="text" class="form-control" id="label" name="label" placeholder="e.g., Women, Kids, Senior Citizens" required>
                </div>

                <!-- Scheme By -->
                <div class="form-group">
                    <label for="scheme_by">Scheme By</label>
                    <input type="text" class="form-control" id="scheme_by" name="scheme_by" placeholder="Enter the organization or authority">
                </div>

                <!-- Suspended Field (Greyed Out) -->
                <div class="form-group">
                    <label for="suspended">Status</label>
                    <input type="text" class="form-control" id="suspended" name="suspended" value="<?php echo ($_SESSION['role'] === 'admin') ? 'Approved' : 'Draft'; ?>" readonly style="background-color: #e9ecef;">
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" class="btn btn-primary">Add Scheme</button>
            </form>
        <?php } ?>
    </div>
</body>
<?php include('../includes/footer.php'); ?>
</html>