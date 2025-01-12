<?php
include('../includes/scheme_tracker_handle.php'); // Track visitor/user's details 
// Start the session
include('../includes/session_start.php');

// Include database connection
include('../includes/db_login_connection.php');

// Check if the database connection was successful
if (!$conn_login) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Add the current scheme to the recently viewed list
if (isset($_GET['slug'])) {
    $slug = htmlspecialchars($_GET['slug']); // Sanitize input
    $recentlyViewed = [];
    if (isset($_COOKIE['recently_viewed'])) {
        $recentlyViewed = json_decode($_COOKIE['recently_viewed'], true);
    }
    if (!in_array($slug, $recentlyViewed)) {
        array_unshift($recentlyViewed, $slug);
        $recentlyViewed = array_slice($recentlyViewed, 0, 3); // Keep only the last 3 schemes
        setcookie('recently_viewed', json_encode($recentlyViewed), time() + (86400 * 30), "/"); // Cookie valid for 30 days
    }
}

// Fetch scheme details based on the slug from the URL
if (isset($_GET['slug'])) {
    $slug = htmlspecialchars($_GET['slug']); // Sanitize input
    $stmt = $conn_login->prepare("SELECT * FROM schemes WHERE slug = ? AND suspended = 0");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $scheme = $result->fetch_assoc();
        $scheme_id = $scheme['id']; // Get the ID for voting and likes
    } else {
        // Redirect to 404 page if scheme is suspended or not found
        header("Location: ../pages/error404.php");
        exit();
    }
    $stmt->close();
} else {
    die("Invalid request.");
}

// Fetch total upvotes and downvotes for the scheme
$stmt = $conn_login->prepare("SELECT COUNT(*) as count FROM scheme_votes WHERE scheme_id = ? AND upvote = TRUE");
$stmt->bind_param("i", $scheme_id);
$stmt->execute();
$upvote_result = $stmt->get_result();
$upvotes = $upvote_result ? $upvote_result->fetch_assoc()['count'] : 0;
$stmt->close();

$stmt = $conn_login->prepare("SELECT COUNT(*) as count FROM scheme_votes WHERE scheme_id = ? AND downvote = TRUE");
$stmt->bind_param("i", $scheme_id);
$stmt->execute();
$downvote_result = $stmt->get_result();
$downvotes = $downvote_result ? $downvote_result->fetch_assoc()['count'] : 0;
$stmt->close();

// Fetch the user's vote (if any) - only if logged in
$user_upvoted = FALSE;
$user_downvoted = FALSE;
if (isset($_SESSION['user'])) {
    $current_user = $_SESSION['user']; // Username from the session
    $stmt = $conn_login->prepare("SELECT upvote, downvote FROM scheme_votes WHERE scheme_id = ? AND username = ?");
    $stmt->bind_param("is", $scheme_id, $current_user);
    $stmt->execute();
    $user_vote_result = $stmt->get_result();
    $user_vote = $user_vote_result ? $user_vote_result->fetch_assoc() : [];

    $user_upvoted = $user_vote['upvote'] ?? FALSE;
    $user_downvoted = $user_vote['downvote'] ?? FALSE;
    $stmt->close();
}

// Fetch the user's like (if any) - only if logged in
$user_liked = FALSE;
if (isset($_SESSION['user'])) {
    $stmt = $conn_login->prepare("SELECT id FROM scheme_likes WHERE scheme_id = ? AND username = ?");
    $stmt->bind_param("is", $scheme_id, $current_user);
    $stmt->execute();
    $user_like_result = $stmt->get_result();
    $user_liked = $user_like_result ? $user_like_result->num_rows > 0 : FALSE;
    $stmt->close();
}

// Dynamic Schema Markup
$schema = [];
if (strtolower($scheme['category']) === 'government') {
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "GovernmentService",
        "name" => $scheme['scheme_name'],
        "serviceType" => "Government Benefits",
        "description" => $scheme['description'],
        "jurisdiction" => [
            "@type" => "AdministrativeArea",
            "name" => $scheme['state'] ?? "India"
        ],
        "serviceOperator" => [
            "@type" => "Organization",
            "name" => $scheme['scheme_by'] ?? "Government of India"
        ],
        "provider" => [
            "@type" => "Organization",
            "name" => $scheme['scheme_by'] ?? "Government of India"
        ],
        "availableChannel" => [
            "@type" => "ServiceChannel",
            "serviceUrl" => $scheme['scheme_link']
        ],
        "areaServed" => [
            "@type" => "AdministrativeArea",
            "name" => $scheme['state'] ?? "India"
        ],
        "url" => $scheme['scheme_link'],
        "offers" => [
            "@type" => "Offer",
            "eligibleRegion" => [
                "@type" => "Place",
                "name" => $scheme['state'] ?? "India"
            ]
        ]
    ];
} elseif (strtolower($scheme['category']) === 'financial') {
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "FinancialProduct",
        "name" => $scheme['scheme_name'],
        "provider" => [
            "@type" => "Organization",
            "name" => $scheme['scheme_by'] ?? "Finance"
        ],
        "url" => $scheme['scheme_link']
    ];
}

// Output Schema Markup
if (!empty($schema)) {
   echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($scheme['scheme_name']); ?> - Details</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="https://www.schemesfinder.com/assets/site_images/favicon.ico">
    <style>
    .breadcrumb {
        background-color: transparent !important; /* Override Bootstrap's background */
        padding: 0 !important; /* Remove padding */
        margin-bottom: 5px; /* Reduce space below breadcrumbs */
        font-size: 0.8rem; /* Tiny text */
    }
    .breadcrumb-item a {
        color: #ccc !important; /* Grey color for links */
        text-decoration: none;
    }
    .breadcrumb-item a:hover {
        text-decoration: underline;
    }
    .breadcrumb-item.active {
        color: #999 !important; /* Grey color for active item */
    }
    .breadcrumb-item + .breadcrumb-item::before {
        color: #ccc !important; /* Grey color for the separator */
    }
</style>
    <style>
        body {
            color: white;
            background-color: #333;
        }
        .voting-buttons button {
            background: none;
            border: none;
            color: white;
        }
        .voting-buttons button.voted i {
            color: #00bfff;
        }
        .like-button i {
            color: <?php echo ($user_liked) ? 'red' : 'white'; ?>;
        }
        .like-button {
            color: white;
        }
        .state-text {
            font-size: 0.8rem;
            color: #ccc;
        }
        .scheme-link-icon {
            font-size: 1.2rem;
            margin-left: 10px;
            color: #00bfff;
        }
        .scheme-link-icon:hover {
            opacity: 0.7;
        }
        .scheme-title {
            font-size: 1.8rem; /* Reduced title size */
            margin-bottom: 0.5rem;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            margin-bottom: 20px;
            justify-content: flex-start;
            align-items: center;
        }
        .nav-tabs {
            overflow-x: auto; /* Enable horizontal scrolling */
            overflow-y: hidden; /* Hide vertical scrollbar */
            white-space: nowrap; /* Prevent wrapping of tabs */
            flex-wrap: nowrap; /* Prevent wrapping in flex container */
            display: flex; /* Ensure tabs are in a single row */
            height: 50px; /* Set a fixed height to prevent vertical overflow */
        }
        .nav-tabs .nav-item {
            display: inline-block; /* Ensure tabs are displayed inline */
            float: none; /* Prevent floating behavior */
        }
        .nav-tabs .nav-link {
            padding: 0.5rem 1rem; /* Add padding for better touch targets */
        }
/* Category and Label Section */
.category-label-section, .label-section {
    margin-top: 20px;
    margin-bottom: 20px;
    font-size: 0.8rem; /* Tiny text */
}

.category-label-section strong, .label-section strong {
    color: #fff; /* White text for headings */
    margin-right: 5px; /* Space between heading and links */
    display: inline; /* Ensure headings are inline */
}

.category-label-section a, .label-section a {
    color: #ccc !important; /* Grey text for links */
    text-decoration: none;
    margin-right: 10px; /* Space between links */
    display: inline; /* Ensure links are inline */
}

.category-label-section a:hover, .label-section a:hover {
    text-decoration: underline; /* Underline on hover */
}
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container mt-3"> <!-- Reduced margin-top -->
        <!-- Breadcrumbs Section -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="https://schemesfinder.com/search/results.php">Schemes</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($scheme['scheme_name']); ?></li>
            </ol>
        </nav>

        <!-- Title Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="scheme-title">
                    <?php echo htmlspecialchars($scheme['scheme_name']); ?>
                    <a href="<?php echo htmlspecialchars($scheme['scheme_link']); ?>" target="_blank" class="scheme-link-icon">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </h1>
                <p class="state-text"><?php echo htmlspecialchars($scheme['scheme_by']); ?></p>
                <!-- Voting and Save Buttons -->
                <div class="action-buttons">
                    <div class="voting-buttons">
                        <button id="thumbs-up" class="btn btn-link p-0 <?php echo ($user_upvoted) ? 'voted' : ''; ?>">
                            <i class="far fa-thumbs-up"></i> <span id="upvote-count"><?php echo $upvotes; ?></span>
                        </button>
                        <button id="thumbs-down" class="btn btn-link p-0 <?php echo ($user_downvoted) ? 'voted' : ''; ?>">
                            <i class="far fa-thumbs-down"></i> <span id="downvote-count"><?php echo $downvotes; ?></span>
                        </button>
                    </div>
                    <button id="like-button" class="btn btn-link p-0 like-button">
                        <i class="<?php echo ($user_liked) ? 'fas' : 'far'; ?> fa-heart"></i>
                        <span><?php echo ($user_liked) ? 'Scheme Saved' : 'Save Scheme'; ?></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs flex-nowrap overflow-auto" id="schemeTabs" role="tablist" style="white-space: nowrap;">
                    <li class="nav-item" style="display: inline-block; float: none;">
                        <a class="nav-link active" id="description-tab" data-toggle="tab" href="#description" role="tab">Description</a>
                    </li>
                    <li class="nav-item" style="display: inline-block; float: none;">
                        <a class="nav-link" id="benefits-tab" data-toggle="tab" href="#benefits" role="tab">Benefits</a>
                    </li>
                    <li class="nav-item" style="display: inline-block; float: none;">
                        <a class="nav-link" id="eligibility-tab" data-toggle="tab" href="#eligibility" role="tab">Eligibility</a>
                    </li>
                    <li class="nav-item" style="display: inline-block; float: none;">
                        <a class="nav-link" id="exclusions-tab" data-toggle="tab" href="#exclusions" role="tab">Exclusions</a>
                    </li>
                    <li class="nav-item" style="display: inline-block; float: none;">
                        <a class="nav-link" id="application-tab" data-toggle="tab" href="#application" role="tab">Application Process</a>
                    </li>
                </ul>
                <div class="tab-content" id="schemeTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="p-3">
                           <p><?php echo nl2br(htmlspecialchars($scheme['description'])); ?></p>
                        </div>
                    </div>
                    <!-- Benefits Tab -->
                    <div class="tab-pane fade" id="benefits" role="tabpanel">
                        <div class="p-3">
                            <p><?php echo nl2br(htmlspecialchars($scheme['benefits'])); ?></p>
                        </div>
                    </div>
                    <!-- Eligibility Tab -->
                    <div class="tab-pane fade" id="eligibility" role="tabpanel">
                        <div class="p-3">
                            <p><?php echo nl2br(htmlspecialchars($scheme['eligibility'])); ?></p>
                        </div>
                    </div>
                    <!-- Exclusions Tab -->
                    <div class="tab-pane fade" id="exclusions" role="tabpanel">
                        <div class="p-3">
                            <p><?php echo nl2br(htmlspecialchars($scheme['exclusions'])); ?></p>
                        </div>
                    </div>
                    <!-- Application Process Tab -->
                    <div class="tab-pane fade" id="application" role="tabpanel">
                        <div class="p-3">
                            <p><?php echo nl2br(htmlspecialchars($scheme['application_Process'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category and Label Section -->
<div class="row mt-4">
    <div class="col-12">
        <!-- Category Section -->
        <?php if (!empty($scheme['category'])): ?>
            <div class="category-label-section">
                <strong>Category:</strong>
                <?php
                $categories = explode(',', $scheme['category']);
                foreach ($categories as $category):
                    $trimmedCategory = trim($category);
                    if (!empty($trimmedCategory)):
                ?>
                    <a href="https://schemesfinder.com/search/results.php?category=<?php echo urlencode($trimmedCategory); ?>"><?php echo htmlspecialchars($trimmedCategory); ?></a>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        <?php endif; ?>

        <!-- Label Section -->
        <?php if (!empty($scheme['label'])): ?>
            <div class="label-section">
                <strong>Label:</strong>
                <?php
                $labels = explode(',', $scheme['label']);
                foreach ($labels as $label):
                    $trimmedLabel = trim($label);
                    if (!empty($trimmedLabel)):
                ?>
                    <a href="https://schemesfinder.com/search/results.php?label=<?php echo urlencode($trimmedLabel); ?>"><?php echo htmlspecialchars($trimmedLabel); ?></a>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

        <!-- Go Back Button -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="javascript:history.back()" class="btn btn-secondary">Go Back to Results</a>
            </div>
        </div>
    </div>
    
    <?php include('relevant_schemes.php'); ?>

    <!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" role="dialog" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: #000; color: #fff;">
            <!-- Modal Header -->
            <div class="modal-header" style="border-bottom: 1px solid #333;">
                <h5 class="modal-title" id="loginRequiredModalLabel">Login Required</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                You need to login to vote or save the scheme.
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer" style="border-top: 1px solid #333; justify-content: flex-start;">
                <a href="../user/user_login.php" class="btn btn-primary">Login</a>
                <a href="../user/user_signup.php" class="btn btn-link" style="color: #007bff; margin-left: 10px;">Don't have an account? Sign up</a>
            </div>
        </div>
    </div>
</div>

    <?php include('../includes/footer.php'); ?>

    <!-- JavaScript for Voting and Like Button -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle thumbs up vote
            $('#thumbs-up').click(function() {
                <?php if (isset($_SESSION['user'])): ?>
                    vote('upvote');
                <?php else: ?>
                    $('#loginRequiredModal').modal('show');
                <?php endif; ?>
            });

            // Handle thumbs down vote
            $('#thumbs-down').click(function() {
                <?php if (isset($_SESSION['user'])): ?>
                    vote('downvote');
                <?php else: ?>
                    $('#loginRequiredModal').modal('show');
                <?php endif; ?>
            });

            // Handle like button click
            $('#like-button').click(function() {
                <?php if (isset($_SESSION['user'])): ?>
                    $.ajax({
                        url: '../user/handle_like.php',
                        method: 'POST',
                        data: {
                            scheme_id: <?php echo $scheme_id; ?>
                        },
                        success: function(response) {
                            try {
                                let data = JSON.parse(response);
                                if (data.success) {
                                    // Toggle like button color and text
                                    if (data.liked) {
                                        $('#like-button i').removeClass('far').addClass('fas').css('color', 'red');
                                        $('#like-button span').text('Scheme Saved');
                                    } else {
                                        $('#like-button i').removeClass('fas').addClass('far').css('color', 'white');
                                        $('#like-button span').text('Save Scheme');
                                    }
                                } else {
                                    alert(data.message); // Show error message
                                }
                            } catch (e) {
                                console.error("Invalid JSON response:", response);
                                alert("An error occurred. Please try again.");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX error:", error);
                            alert("An error occurred. Please try again.");
                        }
                    });
                <?php else: ?>
                    $('#loginRequiredModal').modal('show');
                <?php endif; ?>
            });

            function vote(vote_type) {
                $.ajax({
                    url: '../user/handle_vote.php',
                    method: 'POST',
                    data: {
                        scheme_id: <?php echo $scheme_id; ?>,
                        vote_type: vote_type
                    },
                    success: function(response) {
                        try {
                            let data = JSON.parse(response);
                            if (data.success) {
                                // Update vote counts
                                $('#upvote-count').text(data.upvotes);
                                $('#downvote-count').text(data.downvotes);

                                // Toggle voted class
                                if (vote_type === 'upvote') {
                                    $('#thumbs-up i').removeClass('far').addClass('fas').css('color', '#00bfff');
                                    $('#thumbs-down i').removeClass('fas').addClass('far').css('color', 'white');
                                } else {
                                    $('#thumbs-down i').removeClass('far').addClass('fas').css('color', '#00bfff');
                                    $('#thumbs-up i').removeClass('fas').addClass('far').css('color', 'white');
                                }
                            } else {
                                alert(data.message); // Show error message
                            }
                        } catch (e) {
                            console.error("Invalid JSON response:", response);
                            alert("An error occurred. Please try again.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", error);
                        alert("An error occurred. Please try again.");
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php
// Close the database connection if it was opened
if ($conn_login) {
    $conn_login->close();
}
?>