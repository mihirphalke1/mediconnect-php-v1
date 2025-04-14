<?php
/**
 * Privacy Policy Page
 * Displays the privacy policy for the application
 */

// Include configuration
require_once '../includes/config.php';

// Set active page for navigation
$activePage = 'privacy.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Privacy Policy - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <section class="privacy-section">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Privacy Policy</h1>
                </div>
                <div class="card-content">
                    <p>Last updated: April 1, 2025</p>
                    
                    <h2>Introduction</h2>
                    <p>MediConnect ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.</p>
                    
                    <h2>Information We Collect</h2>
                    <p>We collect information that you provide directly to us, including:</p>
                    <ul>
                        <li>Personal identifiers (name, email address, phone number)</li>
                        <li>Health information (medical history, doctor visits, prescriptions)</li>
                        <li>Appointment details and preferences</li>
                    </ul>
                    
                    <h2>How We Use Your Information</h2>
                    <p>We use your information to:</p>
                    <ul>
                        <li>Provide and maintain our service</li>
                        <li>Process and manage appointments</li>
                        <li>Communicate with healthcare providers</li>
                        <li>Improve and personalize your experience</li>
                    </ul>
                    
                    <h2>Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy, please contact us at support@mediconnect.com</p>
                </div>
            </div>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
