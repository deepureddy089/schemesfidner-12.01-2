<?php
// Include database connection
include('../includes/db_login_connection.php');

// Check if the database connection was successful
if (!$conn_login) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch labels from the currently viewed scheme
if (isset($_GET['slug'])) {
    $slug = htmlspecialchars($_GET['slug']); // Sanitize input

    // Fetch the scheme details to get its labels
    $stmt = $conn_login->prepare("SELECT label FROM schemes WHERE slug = ? AND suspended = 0");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $scheme = $result->fetch_assoc();
        $labels = explode(',', $scheme['label']); // Split labels into an array
        $labels = array_map('trim', $labels); // Remove any extra spaces
    } else {
        die("Scheme not found.");
    }
    $stmt->close();
} else {
    die("Invalid request.");
}

// Fetch relevant schemes based on the labels
$relevantSchemes = [];
if (!empty($labels)) {
    // Build the SQL query to fetch schemes with matching labels
    $labelConditions = [];
    foreach ($labels as $label) {
        $escapedLabel = $conn_login->real_escape_string($label);
        $labelConditions[] = "FIND_IN_SET('$escapedLabel', REPLACE(label, ', ', ',')) > 0";
    }
    $labelCondition = implode(' OR ', $labelConditions);

    // Fetch up to 3 relevant schemes (excluding the current scheme)
    $sql = "SELECT * FROM schemes WHERE suspended = 0 AND ($labelCondition) AND slug != '$slug' LIMIT 3";
    $result = $conn_login->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $relevantSchemes[] = $row;
        }
    }
}

// If fewer than 3 schemes are found, fetch additional schemes based on random labels
if (count($relevantSchemes) < 3) {
    $additionalLimit = 3 - count($relevantSchemes);

    // Exclude already fetched schemes and the current scheme
    $excludedSlugs = array_column($relevantSchemes, 'slug'); // Get slugs of already fetched schemes
    $excludedSlugs[] = $slug; // Exclude the current scheme

    // Build the SQL query to fetch additional schemes
    $excludedSlugsCondition = "'" . implode("','", $excludedSlugs) . "'";
    $sql = "SELECT * FROM schemes WHERE suspended = 0 AND slug NOT IN ($excludedSlugsCondition) ORDER BY RAND() LIMIT $additionalLimit";
    $result = $conn_login->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $relevantSchemes[] = $row;
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevant Schemes</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Dark theme for the body */
        body {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
            font-family: Arial, sans-serif;
        }

        /* Card styling */
        .card {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.1); /* Light shadow for contrast */
            background-color: #000 !important; /* Black card background */
            color: #fff !important; /* White text */
            border: 1px solid #333 !important; /* Subtle border */
            height: 100%; /* Ensure all cards have the same height */
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important; /* White text */
        }

        .scheme-by {
            font-size: 0.875rem;
            color: #888 !important; /* Grey text */
            margin-bottom: 10px;
        }

        .description-heading {
            font-size: 1rem;
            font-weight: bold;
            color: #fff !important; /* White text */
            margin-bottom: 5px;
        }

        .description {
            font-size: 0.875rem;
            color: #ccc !important; /* Light gray text */
            margin-bottom: 10px;
        }

        .more-link {
            color: #007bff !important; /* Blue link color */
            text-decoration: none;
            font-size: 0.875rem;
        }

        .more-link:hover {
            text-decoration: underline;
        }

        .category-label {
            font-size: 0.875rem;
            color: #ccc !important; /* Light gray text */
            margin-bottom: 10px;
        }

        .btn-primary {
            background-color: #007bff !important; /* Blue button */
            border-color: #007bff !important;
        }

        .btn-primary:hover {
            background-color: #0056b3 !important; /* Darker blue on hover */
            border-color: #0056b3 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Relevant Schemes</h2>
        <div class="row">
            <?php if (!empty($relevantSchemes)): ?>
                <?php foreach ($relevantSchemes as $row): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['scheme_name']); ?></h5>
                                <p class="scheme-by">Scheme by: <?php echo htmlspecialchars($row['scheme_by']); ?></p>
                                <p class="description-heading">Description:</p>
                                <?php
                                // Truncate description to a suitable length
                                $description = $row['description'];
                                if (strlen($description) > 150) {
                                    $description = substr($description, 0, 150) . '... <a href="/schemes/' . $row['slug'] . '" class="more-link">More</a>';
                                }
                                ?>
                                <p class="description"><?php echo $description; ?></p>
                                <p class="category-label">Category: <?php echo htmlspecialchars($row['category']); ?></p>
                                <p class="category-label">Label: <?php echo htmlspecialchars($row['label']); ?></p>
                                <!-- "View More Details" Button -->
                                <a href="/schemes/<?php echo $row['slug']; ?>" class="btn btn-primary">View More Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No relevant schemes found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>