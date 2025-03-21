<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Get user details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($full_name) || empty($phone) || empty($address)) {
        $error = "Please fill in all required fields";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Please enter a valid 10-digit phone number";
    } else {
        try {
            // Handle profile image upload
            $profile_image = $user['profile_image'];
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_image']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    $newname = uniqid() . '.' . $filetype;
                    $upload_path = 'uploads/profiles/' . $newname;
                    
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                        // Delete old profile image if exists
                        if ($profile_image && file_exists($profile_image)) {
                            unlink($profile_image);
                        }
                        $profile_image = $upload_path;
                    }
                } else {
                    $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
                }
            }
            
            // Update user information
            if (empty($current_password)) {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $address, $profile_image, $_SESSION['user_id']]);
            } else {
                // Verify current password
                if (!password_verify($current_password, $user['password'])) {
                    $error = "Current password is incorrect";
                } elseif ($new_password !== $confirm_password) {
                    $error = "New passwords do not match";
                } elseif (strlen($new_password) < 8) {
                    $error = "New password must be at least 8 characters long";
                } else {
                    // Update with new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, profile_image = ?, password = ? WHERE id = ?");
                    $stmt->execute([$full_name, $phone, $address, $profile_image, $hashed_password, $_SESSION['user_id']]);
                }
            }
            
            if (empty($error)) {
                $success = "Profile updated successfully";
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-box">
                <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="profile-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_image">Profile Image</label>
                        <div class="profile-image-upload">
                            <img src="<?php echo $user['profile_image'] ?: 'assets/images/default-user.png'; ?>" alt="Profile" class="profile-preview">
                            <input type="file" id="profile_image" name="profile_image" accept="image/*">
                            <label for="profile_image" class="btn btn-secondary">Change Image</label>
                        </div>
                        <small>Supported formats: JPG, JPEG, PNG, GIF</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required
                               pattern="[0-9]{10}"
                               title="Please enter a valid 10-digit phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Change Password</label>
                        <div class="password-fields">
                            <input type="password" name="current_password" placeholder="Current Password">
                            <input type="password" name="new_password" placeholder="New Password">
                            <input type="password" name="confirm_password" placeholder="Confirm New Password">
                        </div>
                        <small>Leave password fields empty to keep current password</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Preview profile image before upload
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.profile-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Client-side validation
    document.querySelector('.profile-form').addEventListener('submit', function(e) {
        const phone = document.getElementById('phone').value;
        const currentPassword = document.querySelector('input[name="current_password"]').value;
        const newPassword = document.querySelector('input[name="new_password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
        
        if (!/^[0-9]{10}$/.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid 10-digit phone number');
            return;
        }
        
        if (currentPassword || newPassword || confirmPassword) {
            if (!currentPassword) {
                e.preventDefault();
                alert('Please enter your current password');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match');
                return;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('New password must be at least 8 characters long');
                return;
            }
        }
    });
    </script>
</body>
</html> 