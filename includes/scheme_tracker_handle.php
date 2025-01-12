<?php
// Start the session
session_start();

// Include database connection
include('db_login_connection.php');

// Check if the database connection was successful
if (!$conn_login) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get the scheme ID from the URL
if (isset($_GET['slug'])) {
    $slug = htmlspecialchars($_GET['slug']);

    // Fetch the scheme ID based on the slug
    $stmt = $conn_login->prepare("SELECT id FROM schemes WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $scheme = $result->fetch_assoc();
        $scheme_id = $scheme['id'];
    } else {
        die("Invalid scheme slug.");
    }
    $stmt->close();
} else {
    die("Scheme slug not provided.");
}

// Track user or viewer
$username = isset($_SESSION['user']) ? $_SESSION['user'] : 'viewer';

// Track the referring page (where the user came from)
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'direct';

// Track the browser and platform
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Track the city and state (using a free IP geolocation API)
$ip_address = $_SERVER['REMOTE_ADDR'];
$geo_url = "http://ip-api.com/json/{$ip_address}";
$geo_data = json_decode(file_get_contents($geo_url), true);
$city = $geo_data['city'] ?? 'Unknown';
$state = $geo_data['regionName'] ?? 'Unknown';

// Track device type and operating system
$device_type = preg_match("/Mobile|Android|iPhone/i", $user_agent) ? 'Mobile' : 'Desktop';
$os = 'Unknown';
if (preg_match("/Windows/i", $user_agent)) {
    $os = 'Windows';
} elseif (preg_match("/Mac/i", $user_agent)) {
    $os = 'macOS';
} elseif (preg_match("/Android/i", $user_agent)) {
    $os = 'Android';
} elseif (preg_match("/iPhone|iPad|iPod/i", $user_agent)) {
    $os = 'iOS';
}

// Track browser type
$browser = 'Unknown';
if (preg_match("/Chrome/i", $user_agent)) {
    $browser = 'Chrome';
} elseif (preg_match("/Firefox/i", $user_agent)) {
    $browser = 'Firefox';
} elseif (preg_match("/Safari/i", $user_agent)) {
    $browser = 'Safari';
} elseif (preg_match("/Edge/i", $user_agent)) {
    $browser = 'Edge';
}

// Insert the tracking data into the database
$stmt = $conn_login->prepare("
    INSERT INTO scheme_tracker (
        scheme_id, 
        username, 
        referrer, 
        user_agent, 
        city, 
        state, 
        ip_address, 
        device_type, 
        os, 
        browser, 
        viewed_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param(
    "isssssssss", 
    $scheme_id, 
    $username, 
    $referrer, 
    $user_agent, 
    $city, 
    $state, 
    $ip_address, 
    $device_type, 
    $os, 
    $browser
);
$stmt->execute();
$stmt->close();

// Close the database connection
$conn_login->close();
?>