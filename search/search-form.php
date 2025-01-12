<?php
// Include database connection file

include('includes/db_login_connection.php');


// Check if the database connection was successful
if (!$conn_login) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Function to fetch unique values from comma-separated fields
function fetchUniqueValues($conn_login, $column) {
    $uniqueValues = []; // Array to store unique values

    // Fetch all rows for the specified column
    $query = "SELECT $column FROM schemes";
    $result = $conn_login->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Split comma-separated values
            $values = explode(",", $row[$column]);

            // Trim whitespace and add to uniqueValues array
            foreach ($values as $value) {
                $trimmedValue = trim($value);
                if (!empty($trimmedValue) && !in_array($trimmedValue, $uniqueValues)) {
                    $uniqueValues[] = $trimmedValue;
                }
            }
        }
    }

    return $uniqueValues;
}

// Fetch unique categories
$categories = fetchUniqueValues($conn_login, "category");

// Fetch unique labels
$labels = fetchUniqueValues($conn_login, "label");

// Fetch unique states
$stateQuery = "SELECT DISTINCT state FROM schemes";
$statesResult = $conn_login->query($stateQuery);

// Fetch unique scheme names for autocomplete
$schemeQuery = "SELECT DISTINCT scheme_name FROM schemes";
$schemesResult = $conn_login->query($schemeQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Schemes</title>
    <!-- Add Font Awesome for the search icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Dark theme styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            margin: 0;
            padding: 0;
        }

        .search-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #000; /* Black background for the card */
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1); /* Light shadow for contrast */
            border: 1px solid #333; /* Subtle border for the card */
        }

        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff; /* White text */
            margin-bottom: 20px;
        }

        /* Input field styles */
        .form-control {
            border-radius: 5px;
            border: 1px solid #555; /* Darker border */
            padding: 10px;
            height: 40px; /* Fixed height for input */
            background-color: #000; /* Black input background */
            color: #fff; /* White text */
        }

        /* Ensure input field stays black with white text when focused */
        .form-control:focus {
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            border-color: #80bdff; /* Light blue border */
            box-shadow: 0 0 5px rgba(128, 189, 255, 0.25); /* Light blue focus shadow */
        }

        /* Dropdown styles */
        .filter-dropdown {
            width: 100%;
            margin-bottom: 15px; /* Space between dropdowns */
            background-color: #000; /* Black dropdown background */
            color: #fff; /* White text */
            border: 1px solid #555; /* Darker border */
            padding: 10px;
            height: 40px; /* Match height with input */
            border-radius: 5px;
        }

        /* Ensure dropdown stays black with white text when focused */
        .filter-dropdown:focus {
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            border-color: #80bdff; /* Light blue border */
            box-shadow: 0 0 5px rgba(128, 189, 255, 0.25); /* Light blue focus shadow */
        }

        /* Dropdown options */
        .filter-dropdown option {
            background-color: #000; /* Black background for options */
            color: #fff; /* White text for options */
        }

        /* Button styles */
        .btn-primary {
            background-color: #007bff; /* Blue button */
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            height: 40px; /* Match height with input */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff; /* White text */
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Label styles */
        .filter-label {
            font-size: 0.8rem; /* Tiny text */
            color: #ccc; /* Grey color */
            margin-bottom: 5px; /* Space between label and dropdown */
            display: block; /* Ensure label is on its own line */
        }

        @media (max-width: 768px) {
            .search-container {
                padding: 20px;
            }

            h1 {
                font-size: 2rem;
            }
        }
        
    </style>
</head>
<body>
    <div class="search-container">
        <h1 class="text-center">Search Schemes</h1>
        <form action="search/results.php" method="GET">
            <!-- Search Bar -->
            <div class="input-group mb-4">
                <input type="text" class="form-control" name="query" placeholder="Type scheme name..." list="schemeNames">
                <datalist id="schemeNames">
                    <?php
                    while ($row = $schemesResult->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['scheme_name']) . "'>";
                    }
                    ?>
                </datalist>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="form-row">
                <!-- State Filter -->
                <div class="col-md-4 mb-3">
                    <label class="filter-label" for="state">State</label>
                    <select class="form-control filter-dropdown" name="state" id="state">
                        <option value="">Select State</option>
                        <?php
                        while ($row = $statesResult->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['state']) . "'>" . htmlspecialchars($row['state']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-md-4 mb-3">
                    <label class="filter-label" for="category">Category</label>
                    <select class="form-control filter-dropdown" name="category" id="category">
                        <option value="">Select Category</option>
                        <?php
                        foreach ($categories as $category) {
                            echo "<option value='" . htmlspecialchars($category) . "'>" . htmlspecialchars($category) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Label Filter -->
                <div class="col-md-4 mb-3">
                    <label class="filter-label" for="label">Label</label>
                    <select class="form-control filter-dropdown" name="label" id="label">
                        <option value="">Select Label</option>
                        <?php
                        foreach ($labels as $label) {
                            echo "<option value='" . htmlspecialchars($label) . "'>" . htmlspecialchars($label) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</body>
</html>

<?php $conn_login->close(); ?>