<?php
// Start the session
include('../includes/session_start.php');

// Include database connection
include('../includes/db_login_connection.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

$logged_in_admin = $_SESSION['admin'];
$admin_role = $_SESSION['role']; // Get role from session

// Include header
include('../includes/header.php');
?>
<?php
// Fetch weekly views data
$weekly_query = "
    SELECT 
        DATE_FORMAT(viewed_at, '%Y-%u') AS week, 
        COUNT(*) AS total_views 
    FROM scheme_tracker 
    WHERE viewed_at >= NOW() - INTERVAL 6 MONTH
    GROUP BY week 
    ORDER BY week
";
$weekly_result = $conn_login->query($weekly_query);

$labels = []; // X-axis labels (weeks in mm/yy format)
$data = [];   // Y-axis data (total views)

// Start date is 6 months before the current date
$start_date = new DateTime();
$start_date->modify('-6 months');

// Generate labels and data for all weeks from start_date to now
$current_date = clone $start_date;
$now = new DateTime();

while ($current_date <= $now) {
    $labels[] = $current_date->format('m/y'); // Format as mm/yy
    $week = $current_date->format('Y-W');

    // Check if this week exists in the database results
    $weekly_result->data_seek(0); // Reset result pointer
    $found = false;
    while ($row = $weekly_result->fetch_assoc()) {
        if ($row['week'] === $week) {
            $data[] = $row['total_views'];
            $found = true;
            break;
        }
    }

    // If the week is not found, set views to 0
    if (!$found) {
        $data[] = 0;
    }

    // Move to the next week
    $current_date->modify('+1 week');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheme Statistics</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Date Picker CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            margin-top: 20px;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .table {
            margin-top: 20px;
        }
        .date-picker {
            margin-bottom: 20px;
        }
        .top-schemes {
            margin-top: 40px;
        }
        /* Graph Section */
.graph-section {
    margin-top: 40px;
    margin-bottom: 40px;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.graph-section h3 {
    margin-bottom: 20px;
    font-size: 1.5rem;
    color: #333;
}

#weeklyViewsChart {
    max-height: 400px;
    width: 100%;
}
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Analytics Dashboard</h1>
        <!-- Graph Section -->
<div class="graph-section">
    <h3>Weekly Views</h3>
    <canvas id="weeklyViewsChart"></canvas>
</div>

<script>
    // Get the weekly data from PHP
    const labels = <?php echo json_encode($labels); ?>;
    const data = <?php echo json_encode($data); ?>;

    // Render the chart
    const ctx = document.getElementById('weeklyViewsChart').getContext('2d');
    const weeklyViewsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Views',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4, // Smooth line
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Views'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Week (mm/yy)'
                    },
                    grid: {
                        display: false, // Hide vertical grid lines
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        title: (context) => `Week: ${context[0].label}`,
                        label: (context) => `Views: ${context.raw}`,
                    }
                }
            }
        }
    });
</script>
        

        <!-- Search Bar Section -->
        <div class="search-bar">
            <h3>Search Scheme</h3>
            <h10>Seach with scheme name or id to display results</h10>
            <form method="GET" action="">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by Scheme Name or ID">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Display Search Results -->
        <?php
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            $query = "
                SELECT s.id, s.scheme_name, COUNT(st.id) AS total_views
                FROM schemes s
                LEFT JOIN scheme_tracker st ON s.id = st.scheme_id
                WHERE s.scheme_name LIKE ? OR s.id = ?
                GROUP BY s.id
            ";
            $stmt = $conn_login->prepare($query);
            $search_param = "%$search%";
            $stmt->bind_param("si", $search_param, $search);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Scheme ID</th>
                                <th>Scheme Name</th>
                                <th>Total Views</th>
                            </tr>
                        </thead>
                        <tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['scheme_name']}</td>
                            <td>{$row['total_views']}</td>
                          </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No results found.</p>';
            }
            $stmt->close();
        }
        ?>

        <!-- Date Range Filter Section -->
        <div class="date-picker">
            <h3>Total Views by Date Range</h3>
            <h10>Select Start Date and End Date Period To Display Results</h10>
            <form method="GET" action="">
                <div class="form-row">
                    <div class="col">
                        <input type="text" name="from_date" class="form-control datepicker" placeholder="From Date">
                    </div>
                    <div class="col">
                        <input type="text" name="to_date" class="form-control datepicker" placeholder="To Date">
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Display Date Range Results -->
        <?php
        if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
            $from_date = $_GET['from_date'];
            $to_date = $_GET['to_date'];

            $query = "
                SELECT s.id, s.scheme_name, COUNT(st.id) AS total_views
                FROM schemes s
                LEFT JOIN scheme_tracker st ON s.id = st.scheme_id
                WHERE st.viewed_at BETWEEN ? AND ?
                GROUP BY s.id
                ORDER BY total_views DESC
            ";
            $stmt = $conn_login->prepare($query);
            $stmt->bind_param("ss", $from_date, $to_date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Scheme ID</th>
                                <th>Scheme Name</th>
                                <th>Total Views</th>
                            </tr>
                        </thead>
                        <tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['scheme_name']}</td>
                            <td>{$row['total_views']}</td>
                          </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No results found for the selected date range.</p>';
            }
            $stmt->close();
        }
        ?>

        <!-- Top Schemes Section -->
        <div class="top-schemes">
            <h3>Top Schemes</h3>

            <!-- All-Time Top 5 Schemes -->
            <h8>All-Time Top 5 Schemes</h8>
            <?php
            $query = "
                SELECT s.id, s.scheme_name, COUNT(st.id) AS total_views
                FROM schemes s
                LEFT JOIN scheme_tracker st ON s.id = st.scheme_id
                GROUP BY s.id
                ORDER BY total_views DESC
                LIMIT 5
            ";
            $result = $conn_login->query($query);
            if ($result->num_rows > 0) {
                echo '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Scheme ID</th>
                                <th>Scheme Name</th>
                                <th>Total Views</th>
                            </tr>
                        </thead>
                        <tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['scheme_name']}</td>
                            <td>{$row['total_views']}</td>
                          </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No data available.</p>';
            }
            ?>

            <!-- Yesterday's Top 5 Schemes -->
            <h8>Yesterday's Top 5 Schemes</h8>
            <?php
            $query = "
                SELECT s.id, s.scheme_name, COUNT(st.id) AS total_views
                FROM schemes s
                LEFT JOIN scheme_tracker st ON s.id = st.scheme_id
                WHERE DATE(st.viewed_at) = DATE(NOW() - INTERVAL 1 DAY)
                GROUP BY s.id
                ORDER BY total_views DESC
                LIMIT 5
            ";
            $result = $conn_login->query($query);
            if ($result->num_rows > 0) {
                echo '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Scheme ID</th>
                                <th>Scheme Name</th>
                                <th>Total Views</th>
                            </tr>
                        </thead>
                        <tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['scheme_name']}</td>
                            <td>{$row['total_views']}</td>
                          </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No data available.</p>';
            }
            ?>

            <!-- Last Week's Top 5 Schemes -->
            <h8>Last Week's Top 5 Schemes</h8>
            <?php
            $query = "
                SELECT s.id, s.scheme_name, COUNT(st.id) AS total_views
                FROM schemes s
                LEFT JOIN scheme_tracker st ON s.id = st.scheme_id
                WHERE st.viewed_at >= NOW() - INTERVAL 7 DAY
                GROUP BY s.id
                ORDER BY total_views DESC
                LIMIT 5
            ";
            $result = $conn_login->query($query);
            if ($result->num_rows > 0) {
                echo '<table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Scheme ID</th>
                                <th>Scheme Name</th>
                                <th>Total Views</th>
                            </tr>
                        </thead>
                        <tbody>';
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['scheme_name']}</td>
                            <td>{$row['total_views']}</td>
                          </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No data available.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date picker
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
        });
    </script>
</body>
</html>