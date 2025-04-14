<?php
/**
 * Terms of Service Page
 * Displays the terms of service for the application
 */

// Include configuration
require_once '../includes/config.php';

// Set active page for navigation
$activePage = 'terms.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Terms of Service - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../assets/styles/styles.css" />
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <style>
        .terms-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .terms-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .terms-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .terms-section {
            margin-bottom: 30px;
        }
        
        .terms-section h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .terms-section p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
    </style>
  </head>
  <body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
      <div class="terms-container fade-in">
        <div class="terms-header">
          <h1><i class="fas fa-gavel"></i> Terms of Service</h1>
          <p>Last updated: <?php echo date('F j, Y'); ?></p>
        </div>
        
        <div class="terms-section">
          <h2>1. Acceptance of Terms</h2>
          <p>By accessing or using the MediConnect services, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.</p>
          <p>MediConnect provides a platform for connecting patients with healthcare providers. These terms govern your use of our website, mobile applications, and services.</p>
        </div>
        
        <div class="terms-section">
          <h2>2. User Accounts</h2>
          <p>To use certain features of our service, you may be required to register for an account. You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account.</p>
          <p>You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>
        </div>
        
        <div class="terms-section">
          <h2>3. Privacy Policy</h2>
          <p>Your use of MediConnect services is also governed by our Privacy Policy, which can be found at <a href="privacy.php">Privacy Policy</a>.</p>
        </div>
        
        <div class="terms-section">
          <h2>4. Medical Disclaimer</h2>
          <p>MediConnect is not a medical service provider. We facilitate connections between patients and healthcare providers but do not provide medical advice, diagnosis, or treatment.</p>
          <p>Always consult with a qualified healthcare provider for medical advice. In case of emergency, contact emergency services immediately.</p>
        </div>
        
        <div class="terms-section">
          <h2>5. Limitation of Liability</h2>
          <p>MediConnect shall not be liable for any direct, indirect, incidental, special, consequential, or exemplary damages resulting from your use or inability to use the service.</p>
        </div>
      </div>
    </div>

    <?php include '../includes/footer.php'; ?>
  </body>
</html>
