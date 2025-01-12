<?php
// Check if the user or admin is logged in
$is_logged_in = isset($_SESSION['user']) || isset($_SESSION['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <!-- Organization Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "url": "https://www.schemesfinder.com/",
      "name": "Schemes Finder",
      "logo": "https://www.schemesfinder.com/assets/site_images/logo.svg",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+91-7386252089",
        "contactType": "customer service",
        "email": "info@schemesfinder.com",
        "areaServed": "IN"
      },
      "sameAs": [
        "https://www.facebook.com/schemesfinder",
        "https://twitter.com/schemesfinder",
        "https://www.linkedin.com/company/schemesfinder"
      ]
    }
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="https://www.schemesfinder.com/assets/site_images/favicon.ico">

    <!-- Meta Tags -->
    <meta name="description" content="Schemes Finder helps you find a list of government schemes, private schemes, financial schemes from Indian state and central governments. Easily search and explore available schemes." />
    <meta name="keywords" content="Government Schemes, India, financial schemes, State Government, Central Government, Government Support Programs, Welfare Programs, Find Government Schemes, Search Schemes, Post Office, Bank Schemes, LIC Schemes" />
    <meta name="author" content="Schemes Finder Team" />

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Schemes Finder - Find Schemes in India" />
    <meta property="og:description" content="Easily search for welfare and financial schemes from state and central governments of India. Find relevant programs based on your eligibility." />
    <meta property="og:image" content="https://www.schemesfinder.com/assets/site_images/logo.png" />
    <meta property="og:url" content="https://www.schemesfinder.com/" />
    <meta property="og:type" content="website" />

    <!-- Twitter Meta Tags -->
    <meta name="twitter:title" content="Schemes Finder - Find Schemes in India" />
    <meta name="twitter:description" content="Easily search for welfare, financial and insurance schemes from state and central governments of India. Find relevant programs based on your profile." />
    <meta name="twitter:image" content="https://www.schemesfinder.com/assets/site_images/logo.png" />

    <!-- Theme Color Meta Tag -->
    <meta name="theme-color" content="#000000" id="themeColorMeta"> <!-- Default: Black -->

    <!-- Page Title -->
    <title>Schemes Finder - Welfare and Government</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        /* Dark theme for the body */
        body {
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            font-family: Arial, sans-serif;
        }

        /* Navbar styling */
        .navbar {
            background-color: #000 !important; /* Black background */
            border-bottom: 1px solid #333; /* Subtle border for separation */
        }

        /* Logo and brand text */
        .navbar-brand {
            color: #fff !important; /* White text */
            font-weight: bold;
        }

        /* Nav links */
        .navbar-nav .nav-link {
            color: #fff !important; /* White text */
        }

        /* Hamburger icon for mobile */
        .navbar-toggler {
            border-color: #fff !important; /* White border */
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") !important; /* White hamburger icon */
        }

        /* Hover effect for nav links */
        .navbar-nav .nav-link:hover {
            color: #ccc !important; /* Light gray on hover */
        }

        /* Active link styling */
        .navbar-nav .nav-item.active .nav-link {
            color: #fff !important; /* White text */
            font-weight: bold;
        }

        /* Modal styling (if needed) */
        .modal-content {
            background-color: #222; /* Dark background for modals */
            color: #fff; /* White text */
        }

        /* Custom button styling */
        .btn-custom {
            background-color: #4CAF50;
            color: white;
        }

        .btn-custom:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" id="mainHeader">
        <a class="navbar-brand" href="https://schemesfinder.com/">Schemes Finder</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="https://schemesfinder.com/">Home</a>
                </li>

                <?php if ($is_logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../user/user_dashboard.php">Dashboard</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../user/user_login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>