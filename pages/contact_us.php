<?php
include('../includes/header.php'); // Include header
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ContactPage",
      "name": "Contact Us",
      "description": "Get in touch with Schemes Finder for any queries related to schemes or account support.",
      "url": "https://www.schemesfinder.com/contact_us.php",
       "mainEntity": {
        "@type": "ContactPoint",
        "telephone": "+91-7386252089",
        "contactType": "customer service",
        "email": "info@schemesfinder.com",
        "areaServed": "IN"
      }
    }
    </script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- For icons -->
    <style>
        /* Dark theme for the body */
        body {
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            font-family: Arial, sans-serif;
        }

        /* Contact form container */
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }

        /* Left content */
        .contact-content {
            flex: 1;
            padding: 20px;
            margin-right: 20px;
        }

        .contact-content h2 {
            margin-bottom: 20px;
            color: #fff; /* White text */
        }

        .contact-content p {
            font-size: 1.1em;
            line-height: 1.6;
            color: #ccc; /* Light grey text */
        }

        .contact-content .contact-info {
            margin-top: 20px;
        }

        .contact-content .contact-info a {
            color: #007bff; /* Blue link color */
            text-decoration: none;
        }

        .contact-content .contact-info a:hover {
            color: #0056b3; /* Darker blue on hover */
        }

        .contact-content .contact-info i {
            margin-right: 10px;
            color: #007bff; /* Blue icon color */
        }

        /* Contact form */
        .contact-form {
            flex: 1;
            padding: 20px;
            border: 1px solid #444; /* Dark border */
            border-radius: 10px;
            background-color: #111; /* Dark grey background */
        }

        .contact-form h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #fff; /* White text */
        }

        /* Form labels */
        .contact-form .form-group label {
            color: #fff; /* White text for labels */
        }

        /* Form inputs */
        .contact-form .form-control {
            background-color: #000; /* Black background */
            color: #fff; /* White text */
            border: 1px solid #444; /* Dark border */
        }

        .contact-form .form-control:focus {
            background-color: #000; /* Keep black background on focus */
            color: #fff; /* White text */
            border-color: #555; /* Light border on focus */
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.1); /* Light shadow on focus */
        }

        /* Override autofill styles */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active,
        textarea:-webkit-autofill,
        textarea:-webkit-autofill:hover,
        textarea:-webkit-autofill:focus,
        textarea:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #000 inset !important; /* Black background */
            -webkit-text-fill-color: #fff !important; /* White text */
            transition: background-color 5000s ease-in-out 0s; /* Prevent background color change */
        }

        /* Buttons */
        .contact-form .btn-primary {
            background-color: #007bff; /* Blue button */
            border: none;
        }

        .contact-form .btn-primary:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Alerts */
        .alert {
            background-color: #222; /* Dark alert background */
            color: #fff; /* White text */
            border: 1px solid #444; /* Dark border */
        }

        .alert-success {
            background-color: #155724; /* Dark green for success */
            border-color: #155724;
        }

        .alert-danger {
            background-color: #721c24; /* Dark red for danger */
            border-color: #721c24;
        }

        /* Mobile layout */
        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }

            .contact-content {
                margin-right: 0;
                margin-bottom: 20px;
                text-align: center;
            }

            .contact-form {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <!-- Left Content -->
        <div class="contact-content">
            <h2>Contact Us</h2>
            <p>
                If you have any questions about any schemes, issues with your account, or want to become part of our team, you can use the form to contact us.
            </p>
            <div class="contact-info">
    <p>
        <i class="fas fa-envelope"></i>
        <a href="mailto:info@schemesfinder.com">info@schemesfinder.com</a>
    </p>
    <p>
        <i class="fas fa-phone"></i>
        <a href="tel:+917386252089">+91 73862 52089</a>
    </p>
    <p>
        <i class="fab fa-whatsapp"></i>
        <a href="https://wa.me/917386252089" target="_blank">Message on WhatsApp</a>
    </p>
    <p>
        <i class="fab fa-instagram"></i>
        <a href="https://www.instagram.com/your_instagram_id" target="_blank">Follow on Instagram</a>
    </p>
    <p>
        <i class="fab fa-facebook"></i>
        <a href="https://www.facebook.com/your_facebook_page" target="_blank">Like on Facebook</a>
    </p>
    <p>
        <i class="fas fa-globe"></i>
        <a href="https://www.schemesfinder.com" target="_blank">www.schemesfinder.com</a>
    </p>
</div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form">
            <h2>Contact Form</h2>
            <form id="contactForm" action="../user/save_enquiry.php" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <select class="form-control" id="subject" name="subject" required>
                        <option value="">Select a subject</option>
                        <option value="Scheme Related Enquiry">Scheme Related Enquiry</option>
                        <option value="Account Issues">Account Issues</option>
                        <option value="Join Schemes Finder Team">Join Schemes Finder Team</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            <div id="successMessage" class="mt-3 text-center"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#contactForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: '../user/save_enquiry.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#successMessage').html('<div class="alert alert-success">Thank you! Your enquiry has been submitted.</div>');
                        $('#contactForm')[0].reset();
                    },
                    error: function(xhr, status, error) {
                        $('#successMessage').html('<div class="alert alert-danger">Error submitting enquiry. Please try again.</div>');
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
include('../includes/footer.php'); // Include footer
?>