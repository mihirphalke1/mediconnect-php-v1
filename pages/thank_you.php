<?php
/**
 * Thank You Page
 * Confirmation page displayed after various successful actions
 */

// Include configuration
require_once '../includes/config.php';

// Get type from query parameter
$type = isset($_GET['type']) ? $_GET['type'] : '';
$title = '';
$message = '';
$redirect_url = '';
$redirect_text = '';

switch ($type) {
    case 'appointment':
        $title = 'Appointment Booked Successfully';
        $message = 'Your appointment request has been submitted and is pending confirmation from the doctor. You will receive a notification once the doctor confirms or reschedules.';
        $redirect_url = isset($_SESSION['user_id']) ? ($_SESSION['user_type'] === 'user' ? '../dashboard.php' : '../doctor_dashboard.php') : '../index.php';
        $redirect_text = isset($_SESSION['user_id']) ? 'View My Dashboard' : 'Back to Home';
        break;
    case 'feedback':
        $title = 'Feedback Submitted Successfully';
        $message = 'Thank you for your valuable feedback. We appreciate your input and will use it to improve our services.';
        $redirect_url = isset($_SESSION['user_id']) ? ($_SESSION['user_type'] === 'user' ? '../dashboard.php' : '../doctor_dashboard.php') : '../index.php';
        $redirect_text = isset($_SESSION['user_id']) ? 'Back to Dashboard' : 'Back to Home';
        break;
    default:
        $title = 'Operation Completed Successfully';
        $message = 'Thank you for using MediConnect. Your request has been processed successfully.';
        $redirect_url = '../index.php';
        $redirect_text = 'Back to Home';
}

// Set navigation active page
$activePage = 'thank_you.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Thank You - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .thank-you-container {
            text-align: center;
            padding: 60px 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .thank-you-title {
            font-size: 30px;
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .thank-you-message {
            font-size: 18px;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        
        .redirect-container {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="thank-you-container fade-in">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="thank-you-title"><?php echo $title; ?></h1>
            <p class="thank-you-message"><?php echo $message; ?></p>
            <div class="redirect-container">
                <a href="<?php echo $redirect_url; ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> <?php echo $redirect_text; ?>
                </a>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
