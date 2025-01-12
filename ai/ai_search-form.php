<?php
// ai_search-form.php

// Start the session
include('ai_session_start.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Government Schemes</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
<style>
    /* Override Bootstrap styles */
    body {
        background-color: #000 !important; /* Black background */
        color: #fff !important; /* White text */
    }
    .search-container {
        max-width: 1200px;
        margin: 50px auto;
        padding: 20px;
        background: #000 !important; /* Black background */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.1) !important; /* White shadow */
    }
    .form-control {
        background-color: #000 !important; /* Black background for search field */
        color: #fff !important; /* White text */
        border: 1px solid #444 !important; /* Dark gray border */
    }
    .form-control::placeholder {
        color: #fff !important; /* White placeholder text */
        opacity: 0.7 !important; /* Slightly transparent for better contrast */
    }
    .form-control:focus {
        background-color: #000 !important; /* Black background on focus */
        color: #fff !important; /* White text */
        border-color: #007BFF !important; /* Blue border on focus */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important; /* Blue shadow on focus */
    }
    /* Webkit autofill styling */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px #000 inset !important; /* Black background */
        -webkit-text-fill-color: #fff !important; /* White text */
    }
    .card {
        margin-bottom: 20px !important;
        padding: 20px !important;
        border-radius: 10px !important;
        box-shadow: 0 4px 8px rgba(255, 255, 255, 0.1) !important; /* White shadow */
        background-color: #000 !important; /* Black background */
        color: #fff !important; /* White text */
        border: 1px solid #444 !important; /* Dark gray border */
    }
    .card .card-body {
        background-color: #000 !important; /* Black background */
        color: #fff !important; /* White text */
    }
    .card .card-title {
        font-size: 1.5rem !important;
        font-weight: bold !important;
        color: #fff !important; /* White text */
    }
    .card .card-text {
        font-size: 1rem !important;
        color: #ddd !important; /* Light gray text */
    }
    .btn-primary, .btn-secondary {
        background-color: #007BFF !important; /* Blue background */
        border-color: #007BFF !important; /* Blue border */
        color: #fff !important; /* White text */
    }
    .btn-secondary:disabled {
        opacity: 0.7 !important; /* Slightly transparent when disabled */
    }
    .results-info {
        font-size: 0.9rem !important;
        color: #ccc !important; /* Light gray text */
        margin-bottom: 20px !important;
    }
    .ai-badge {
        background-color: #ffc107 !important;
        color: #000 !important;
        font-size: 14px !important;
        font-weight: bold !important;
        padding: 5px 10px !important;
        border-radius: 20px !important;
        display: inline-block !important;
        margin-bottom: 20px !important;
    }
    .error-message {
        color: red !important;
        font-weight: bold !important;
        text-align: center !important;
        margin-top: 20px !important;
    }
    .descriptive-answer {
        background-color: #000 !important; /* Black background */
        padding: 15px !important;
        border-radius: 8px !important;
        margin-bottom: 20px !important;
        margin-top: 20px !important; /* Added gap between search bar and summary */
        color: #fff !important; /* White text */
        border: 1px solid #444 !important; /* Dark gray border */
        max-width: 1200px !important; /* Match search container width */
        margin-left: auto !important;
        margin-right: auto !important;
    }
    .disclaimer {
        font-size: 0.8rem !important;
        color: #ccc !important; /* Light gray text */
        text-align: center !important;
        margin-top: 10px !important;
    }
    .title-container {
        position: relative !important;
        text-align: center !important;
    }
    .ai-powered {
        font-size: 0.8rem !important; /* Tiny font size */
        color: #007BFF !important; /* Electric blue */
        margin-left: -1px !important; /* Reduced space between title and "AI Powered" */
        vertical-align: top !important; /* Align with the middle of the title */
        font-weight: bold !important; /* Make the text bold */
        position: relative !important; /* Allow for fine-tuning position */
        top: 2px !important; /* Move the text slightly down */
    }
    .web-logo {
        font-size: 1.2rem !important; /* Slightly larger size */
        color: #007BFF !important; /* Electric blue */
        margin-left: 5px !important; /* Space between title and logo */
        vertical-align: middle !important; /* Align with the middle of the title */
    }
    .spinner-border.text-primary {
        color: #007BFF !important; /* Electric blue color */
        border-width: 0.25em !important; /* Thicker border for a sleek look */
    }
    .loading-text {
        color: #007BFF !important; /* Electric blue */
        font-weight: bold !important;
        margin-top: 10px !important;
    }
    .results-container {
        margin-top: 20px !important;
        max-width: 1200px !important; /* Match search container width */
        margin-left: auto !important;
        margin-right: auto !important;
    }
    .alert {
        background-color: #444 !important; /* Darker gray background */
        color: #fff !important; /* White text */
        border-color: #555 !important; /* Dark gray border */
    }
    .alert-warning {
        background-color: #333 !important; /* Dark gray background */
        color: #ffc107 !important; /* Yellow text */
        border-color: #444 !important; /* Darker gray border */
    }
</style>
</head>
<body>
    <!-- Include Header -->
    <?php include('../includes/header.php'); ?>

    <div class="search-container">
        <h1 class="text-center mb-4">
            Search Government Schemes <span class="ai-powered">AI Powered</span> <!-- Tiny "AI Powered" text -->
        </h1>

        <!-- Search Form -->
        <form action="ai_openapi_handle.php" method="POST" id="search-form">
            <div class="form-group">
                <input type="text" name="query" class="form-control" placeholder="Ask about government schemes..." required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Search</button>
        </form>

        <!-- Disclaimer -->
        <p class="disclaimer">
            Results are powered by GPT-3.5 Turbo and may contain errors. Please verify with official sources.
        </p>
    </div>

    <!-- Loading Spinner and Text -->
    <div id="loading-spinner" class="text-center mt-4" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="loading-text mt-2">Loading schemes, please wait...</p>
    </div>

    <!-- Results Container -->
    <div class="results-container">
        <!-- Display the user's query -->
        <?php if (isset($_POST['query'])): ?>
            <p class="text-center mt-3">Showing results for: <strong><?php echo htmlspecialchars($_POST['query']); ?></strong></p>
        <?php endif; ?>

        <!-- Error Handling -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Descriptive Answer -->
        <?php if (isset($_SESSION['descriptive_answer'])): ?>
            <div class="descriptive-answer">
                <h3>Summary</h3>
                <p><?php echo $_SESSION['descriptive_answer']; ?></p>
            </div>
            <?php unset($_SESSION['descriptive_answer']); ?>
        <?php endif; ?>

        <!-- Display AI Results from Database -->
        <?php if (isset($_SESSION['ai_results_database']) && !empty($_SESSION['ai_results_database'])): ?>
            <h2 class="text-center mb-4 mt-5">Results from Database</h2>
            <p class="results-info text-center">
                The following results are generated by our AI based on the data available in our database.
            </p>
            <div class="row">
                <?php foreach ($_SESSION['ai_results_database'] as $scheme): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($scheme['scheme_name']); ?></h5>
                                <p class="card-text"><strong>State:</strong> <?php echo htmlspecialchars($scheme['state']); ?></p>
                                <p class="card-text"><strong>Age Group:</strong> <?php echo htmlspecialchars($scheme['age_group']); ?></p>
                                <?php if (isset($scheme['scheme_link']) && !empty($scheme['scheme_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($scheme['scheme_link']); ?>" class="btn btn-primary" target="_blank">View More Details</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>No Link Available</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['ai_results_database']); ?>
        <?php elseif (isset($_SESSION['ai_results_database']) && empty($_SESSION['ai_results_database'])): ?>
            <div class="alert alert-warning text-center mt-4">
                No database results found for your query.
            </div>
            <?php unset($_SESSION['ai_results_database']); ?>
        <?php endif; ?>

        <!-- Display AI Results from Web -->
        <?php if (isset($_SESSION['ai_results_web']) && !empty($_SESSION['ai_results_web'])): ?>
            <h2 class="text-center mb-4 mt-5">
                AI Results from Web <span class="web-logo">🌐</span> <!-- World logo -->
            </h2>
            <p class="results-info text-center">
                The following results are generated by our AI based on general knowledge and publicly available information.
            </p>
            <div class="row">
                <?php foreach ($_SESSION['ai_results_web'] as $scheme): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($scheme['scheme_name']); ?></h5>
                                <p class="card-text"><strong>State:</strong> <?php echo htmlspecialchars($scheme['state']); ?></p>
                                <p class="card-text"><strong>Age Group:</strong> <?php echo htmlspecialchars($scheme['age_group']); ?></p>
                                <?php if (isset($scheme['link'])): ?>
                                    <a href="<?php echo htmlspecialchars($scheme['link']); ?>" class="btn btn-primary" target="_blank">View More Details</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['ai_results_web']); ?>
        <?php elseif (isset($_SESSION['ai_results_web']) && empty($_SESSION['ai_results_web'])): ?>
            <div class="alert alert-warning text-center mt-4">
                No web-based results found for your query.
            </div>
            <?php unset($_SESSION['ai_results_web']); ?>
        <?php endif; ?>
    </div>

    <!-- Include Footer -->
    <?php include('../includes/footer.php'); ?>

    <!-- JavaScript to Handle Spinner and Content Visibility -->
    <script>
        document.getElementById('search-form').addEventListener('submit', function () {
            // Show the spinner
            document.getElementById('loading-spinner').style.display = 'block';

            // Hide the results container
            document.querySelector('.results-container').style.display = 'none';
        });

        // If results are already displayed, ensure the spinner is hidden
        <?php if (isset($_POST['query'])): ?>
            document.getElementById('loading-spinner').style.display = 'none';
            document.querySelector('.results-container').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>