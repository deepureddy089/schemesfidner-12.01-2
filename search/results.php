<?php
include('../includes/session_start.php'); // Include the session start file

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection file

include('../includes/db_login_connection.php');


// Check if the database connection was successful
if (!$conn_login) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch filters
$query = isset($_GET['query']) ? $_GET['query'] : '';
$state = isset($_GET['state']) ? $_GET['state'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$label = isset($_GET['label']) ? $_GET['label'] : '';

// Pagination settings
$results_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Build the SQL query dynamically
$sql = "SELECT * FROM schemes WHERE suspended = 0"; // Exclude suspended schemes

// Add filters to the query
if (!empty($state)) {
    $sql .= " AND state = '" . $conn_login->real_escape_string($state) . "'";
}

// Handle category filter (comma-separated values)
if (!empty($category)) {
    $escapedCategory = $conn_login->real_escape_string($category);
    $sql .= " AND (FIND_IN_SET('$escapedCategory', REPLACE(category, ', ', ',')) > 0)";
}

// Handle label filter (comma-separated values)
if (!empty($label)) {
    $escapedLabel = $conn_login->real_escape_string($label);
    $sql .= " AND (FIND_IN_SET('$escapedLabel', REPLACE(label, ', ', ',')) > 0)";
}

// If a search query is provided, add search logic
if (!empty($query)) {
    // Split the search query into individual keywords
    $keywords = explode(' ', $query);
    $keywordConditions = [];

    // Build conditions for each keyword
    foreach ($keywords as $keyword) {
        $escapedKeyword = $conn_login->real_escape_string($keyword);
        $keywordConditions[] = "(scheme_name LIKE '%$escapedKeyword%' OR 
                                description LIKE '%$escapedKeyword%' OR 
                                benefits LIKE '%$escapedKeyword%' OR 
                                eligibility LIKE '%$escapedKeyword%')";
    }

    // Combine keyword conditions with OR logic
    $keywordCondition = implode(' OR ', $keywordConditions);

    // Add keyword conditions to the SQL query
    $sql .= " AND ($keywordCondition)";

    // Order results by relevance (scheme_name matches first, then other columns)
    $sql .= " ORDER BY 
              CASE 
                WHEN scheme_name LIKE '%$query%' THEN 1 
                ELSE 2 
              END, 
              scheme_name ASC";
}

// Add pagination
$sql .= " LIMIT $offset, $results_per_page";

// Debugging: Print the final SQL query
// echo "SQL Query: " . $sql . "<br>";

// Execute the query
$result = $conn_login->query($sql);
if (!$result) {
    die("Error in query execution: " . $conn_login->error);
}

// Count total results for pagination
$total_sql = "SELECT COUNT(*) FROM schemes WHERE suspended = 0"; // Exclude suspended schemes
if (!empty($state)) $total_sql .= " AND state = '" . $conn_login->real_escape_string($state) . "'";
if (!empty($category)) $total_sql .= " AND (FIND_IN_SET('" . $conn_login->real_escape_string($category) . "', REPLACE(category, ', ', ',')) > 0)";
if (!empty($label)) $total_sql .= " AND (FIND_IN_SET('" . $conn_login->real_escape_string($label) . "', REPLACE(label, ', ', ',')) > 0)";
if (!empty($query)) {
    $total_sql .= " AND ($keywordCondition)";
}

$total_result = $conn_login->query($total_sql);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $results_per_page);

// Generate the dynamic heading based on search filters
$heading = "Showing $total_rows Results"; // Add total rows count here
if (!empty($query)) {
    $heading .= " for '$query'";
}
if (!empty($state)) {
    $heading .= " in '$state'";
}
if (!empty($category)) {
    $heading .= " in category '$category'";
}
if (!empty($label)) {
    $heading .= " with label '$label'";
}

// Handle recently viewed schemes using cookies
$recentlyViewed = [];
if (isset($_COOKIE['recently_viewed'])) {
    $recentlyViewed = json_decode($_COOKIE['recently_viewed'], true);
}

// Add the current scheme to the recently viewed list (if viewing a scheme)
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    if (!in_array($slug, $recentlyViewed)) {
        array_unshift($recentlyViewed, $slug);
        $recentlyViewed = array_slice($recentlyViewed, 0, 3); // Keep only the last 3 schemes
        setcookie('recently_viewed', json_encode($recentlyViewed), time() + (86400 * 30), "/"); // Cookie valid for 30 days
    }
}

// Fetch details of recently viewed schemes
$recentSchemes = [];
if (!empty($recentlyViewed)) {
    $recentSlugs = "'" . implode("','", $recentlyViewed) . "'";
    $recentQuery = "SELECT id, scheme_name, scheme_by, category, slug, label FROM schemes WHERE slug IN ($recentSlugs) AND suspended = 0 ORDER BY FIELD(slug, $recentSlugs)"; // Exclude suspended schemes
    $recentResult = $conn_login->query($recentQuery);
    if ($recentResult) {
        while ($row = $recentResult->fetch_assoc()) {
            $recentSchemes[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include('../includes/header.php'); ?>
    <title>Scheme Results</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS (loaded after Bootstrap) -->
    <style>
        /* Dark theme for the body */
        body {
            background-color: #000 !important; /* Black background */
            color: #fff !important; /* White text */
            font-family: Arial, sans-serif;
        }

        /* Card styling with higher specificity */
        .container .card {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.1); /* Light shadow for contrast */
            background-color: #000 !important; /* Black card background */
            color: #fff !important; /* White text */
            border: 1px solid #333 !important; /* Add a subtle border for better visibility */
            height: 100%; /* Ensure all cards have the same height */
        }

        .container .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important; /* White text */
        }

        .container .scheme-by {
            font-size: 0.875rem;
            color: #888 !important; /* Grey text */
            margin-bottom: 10px;
        }

        .container .description-heading {
            font-size: 1rem;
            font-weight: bold;
            color: #fff !important; /* White text */
            margin-bottom: 5px;
        }

        .container .description {
            font-size: 0.875rem;
            color: #ccc !important; /* Light gray text */
            margin-bottom: 10px;
        }

        .container .more-link {
            color: #007bff !important; /* Blue link color */
            text-decoration: none;
            font-size: 0.875rem;
        }

        .container .more-link:hover {
            text-decoration: underline;
        }

        .container .category-label {
            font-size: 0.875rem;
            color: #ccc !important; /* Light gray text */
            margin-bottom: 10px;
        }

        .container .voting-icons a {
            text-decoration: none;
            margin-right: 10px;
            color: #fff !important; /* White icons */
        }

        .container .voting-icons a:hover {
            opacity: 0.7;
        }

        /* Recently Viewed Cards */
        .container .recent-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1); /* Light shadow for contrast */
            transition: transform 0.3s ease;
            background-color: #000 !important; /* Black card background */
            border: 1px solid #333 !important; /* Add a subtle border for better visibility */
            height: 100%; /* Ensure all cards have the same height */
        }

        .container .recent-card:hover {
            transform: translateY(-5px);
        }

        .container .recent-card-body {
            padding: 20px;
        }

        .container .recent-card-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #fff !important; /* White text */
        }

        .container .recent-scheme-by {
            font-size: 0.875rem;
            color: #888 !important; /* Grey text */
            margin-bottom: 10px;
        }

        .container .recent-category-label {
            font-size: 0.875rem;
            color: #ccc !important; /* Light gray text */
            margin-bottom: 10px;
        }

        .container .recent-card-text {
            font-size: 1rem;
            color: #ccc !important; /* Light gray text */
        }

        /* Pagination */
        .container .pagination {
            margin-top: 30px; /* Add margin to the pagination for better spacing */
        }

        .container .page-item.active .page-link {
            background-color: #007bff !important; /* Blue active pagination button */
            border-color: #007bff !important;
        }

        .container .page-link {
            color: #fff !important; /* White text for pagination */
            background-color: #000 !important; /* Black background for pagination */
            border: 1px solid #444 !important; /* Dark border */
        }

        .container .page-link:hover {
            background-color: #333 !important; /* Darker background on hover */
            border-color: #444 !important;
        }
    </style>
</head>
<body>
    
    <div class="container mt-5">
        <h2><?php echo $heading; ?></h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
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
                                <!-- Updated "View More Details" Button -->
                                <a href="/schemes/<?php echo $row['slug']; ?>" class="btn btn-primary">View More Details</a>
                                
                                <!-- Voting Icons -->
                                <div class="voting-icons">
                                    <?php
                                    // Fetch upvotes and downvotes for the scheme
                                    $scheme_id = $row['id'];
                                    $upvote_sql = "SELECT COUNT(*) as upvotes FROM scheme_votes WHERE scheme_id = $scheme_id AND upvote = TRUE";
                                    $downvote_sql = "SELECT COUNT(*) as downvotes FROM scheme_votes WHERE scheme_id = $scheme_id AND downvote = TRUE";

                                    $upvote_result = $conn_login->query($upvote_sql);
                                    $downvote_result = $conn_login->query($downvote_sql);

                                    $upvotes = $upvote_result->fetch_assoc()['upvotes'] ?? 0;
                                    $downvotes = $downvote_result->fetch_assoc()['downvotes'] ?? 0;
                                    ?>
                                    <a href="<?php echo isset($_SESSION['user']) ? 'scheme_details.php?id=' . $row['id'] : '../user/user_login.php?redirect_to=scheme_details.php?id=' . $row['id']; ?>" title="Thumbs Up">
                                        👍 <span><?php echo $upvotes; ?></span>
                                    </a>
                                    <a href="<?php echo isset($_SESSION['user']) ? 'scheme_details.php?id=' . $row['id'] : '../user/user_login.php?redirect_to=scheme_details.php?id=' . $row['id']; ?>" title="Thumbs Down">
                                        👎 <span><?php echo $downvotes; ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No schemes found.</p>
        <?php endif; ?>

        <!-- Recently Viewed Schemes -->
        <?php if (!empty($recentSchemes)): ?>
            <div class="recent-schemes">
                <h2>Recently Viewed Schemes</h2>
                <div class="row">
                    <?php foreach ($recentSchemes as $scheme): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card recent-card">
                                <div class="card-body recent-card-body">
                                    <h5 class="recent-card-title"><?php echo htmlspecialchars($scheme['scheme_name']); ?></h5>
                                    <p class="recent-scheme-by">Scheme by: <?php echo htmlspecialchars($scheme['scheme_by']); ?></p>
                                    <p class="recent-category-label">Category: <?php echo htmlspecialchars($scheme['category']); ?></p>
                                    <p class="recent-category-label">Label: <?php echo htmlspecialchars($scheme['label']); ?></p>
                                    <a href="/schemes/<?php echo $scheme['slug']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?query=<?php echo $query; ?>&state=<?php echo $state; ?>&category=<?php echo $category; ?>&label=<?php echo $label; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>
</html>

<?php $conn_login->close(); ?>