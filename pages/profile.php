<?php
/**
 * Profile Page
 * Allows users to view and edit their profile information
 */

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Set active page for navigation
$activePage = 'profile.php';
$isSubdirectory = true;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Profile - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../assets/styles/styles.css" />
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css" />
    <link rel="stylesheet" href="../assets/styles/profile.css" />
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <style>
      /* Global Styles */
      :root {
        --primary-gradient: linear-gradient(135deg, #4a6fa5, #5b86e5);
        --secondary-gradient: linear-gradient(135deg, #6e89a9, #829fd9);
        --accent-gradient: linear-gradient(135deg, #e94f37, #ff6b58);
        --light-gradient: linear-gradient(135deg, #f4f7f9, #e6eef3);
        --dark-color: #2d3748;
        --success-gradient: linear-gradient(135deg, #48bb78, #38a169);
        --warning-gradient: linear-gradient(135deg, #f6ad55, #ed8936);
        --danger-gradient: linear-gradient(135deg, #e53e3e, #c53030);
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        --shadow-hover: 0 15px 30px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s ease;
      }

      /* Profile-specific styles */
      .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
      }

      /* Header Section */
      .profile-header-section {
        background: white;
        background-image: linear-gradient(to right, #ffffff, #f9fbfd);
        box-shadow: var(--shadow-md);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
      }

      .profile-header-section::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 300px;
        background: linear-gradient(
          135deg,
          rgba(91, 134, 229, 0.05),
          rgba(54, 209, 220, 0.1)
        );
        border-radius: 50%;
        transform: translate(50%, -50%);
        z-index: 0;
      }

      .profile-header {
        position: relative;
        z-index: 1;
      }

      .profile-header i {
        font-size: 2rem;
        margin-right: 10px;
        color: #5b86e5;
      }

      .profile-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
      }

      .profile-card {
        display: flex;
        flex-direction: column;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .profile-card h3 {
        margin: 0 0 10px;
        font-size: 1.5rem;
      }

      .profile-card p {
        margin: 5px 0;
        color: #555;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
      }

      .form-group input,
      .form-group textarea,
      .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
      }

      .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #5b86e5;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-align: center;
      }

      .btn:hover {
        background-color: #4a74c5;
      }
    </style>
  </head>
  <body>
    <?php include '../includes/header.php'; ?>

    <div class="container profile-container">
      <div class="profile-header">
        <h1>My Profile</h1>
        <div class="btn-container">
          <a href="#" class="btn btn-primary" id="editProfileBtn">
            <i class="fas fa-edit"></i> Edit Profile
          </a>
          <a href="#" class="btn btn-secondary" id="changePasswordBtn">
            <i class="fas fa-key"></i> Change Password
          </a>
        </div>
      </div>

      <div class="container dashboard-container">
        <section class="profile-header-section fade-in">
          <div class="profile-header">
            <div class="profile-info">
              <h1><i class="fas fa-user"></i> Profile</h1>
              <p class="lead">Manage your profile information and settings.</p>
            </div>
          </div>
        </section>

        <section class="profile-section fade-in">
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">Profile Information</h2>
            </div>
            <div class="profile-card">
              <form id="profileForm" onsubmit="return validateProfileForm()">
                <div class="form-group">
                  <label for="name"><i class="fas fa-user"></i> Name:</label>
                  <input
                    type="text"
                    id="name"
                    name="name"
                    value="Mihir Phalke"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="email"
                    ><i class="fas fa-envelope"></i> Email:</label
                  >
                  <input
                    type="email"
                    id="email"
                    name="email"
                    value="mihir@example.com"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="phone"><i class="fas fa-phone"></i> Phone:</label>
                  <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="+1 (555) 123-4567"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="address"
                    ><i class="fas fa-map-marker-alt"></i> Address:</label
                  >
                  <textarea id="address" name="address" rows="3" required>
123 Main St, Mumbai, India</textarea
                  >
                </div>
                <input type="submit" value="Update Profile" class="btn" />
              </form>
            </div>
          </div>
        </section>
      </div>
    </div>

    <footer>
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h4>Quick Links</h4>
            <a href="doctors.html">Find Doctors</a>
            <a href="dashboard.html">Dashboard</a>
            <a href="feedback.html">Feedback</a>
          </div>
          <div class="footer-section">
            <h4>Legal</h4>
            <a href="terms.html">Terms of Service</a>
            <a href="privacy.html">Privacy Policy</a>
          </div>
          <div class="footer-section">
            <h4>Contact</h4>
            <p><i class="fas fa-envelope"></i> support@mediconnect.com</p>
            <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2024 MediConnect. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <script>
      function validateProfileForm() {
        const form = document.getElementById("profileForm");
        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const phone = form.phone.value.trim();
        const address = form.address.value.trim();

        if (!name || !email || !phone || !address) {
          alert("Please fill in all fields.");
          return false;
        }

        alert("Profile updated successfully!");
        return true;
      }
    </script>
    
    <?php include '../includes/footer.php'; ?>
  </body>
</html>
