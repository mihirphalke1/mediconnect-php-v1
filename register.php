<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $user_type = $_POST['user_type'];
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = "All required fields must be filled";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = "Email already registered";
            } else {
                // Handle profile image upload
                $profile_image = '';
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png'];
                    $filename = $_FILES['profile_image']['name'];
                    $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $filesize = $_FILES['profile_image']['size'];
                    
                    // Validate file type
                    if (!in_array($filetype, $allowed)) {
                        $error = "Only JPG, JPEG, and PNG files are allowed";
                    } 
                    // Validate file size (max 5MB)
                    elseif ($filesize > 5000000) {
                        $error = "File size must be less than 5MB";
                    } else {
                        $newname = uniqid() . '.' . $filetype;
                        // Store in different directories based on user type
                        $upload_path = ($user_type == 'doctor') 
                            ? 'uploads/doctor_profiles/' . $newname 
                            : 'uploads/profiles/' . $newname;
                        
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                            $profile_image = $upload_path;
                        } else {
                            $error = "Failed to upload image";
                        }
                    }
                }
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                if ($user_type == 'user') {
                    // Insert into users table
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address, $profile_image]);
                } else {
                    // Insert into doctors table
                    $specialization = filter_var($_POST['specialization'], FILTER_SANITIZE_STRING);
                    $experience = filter_var($_POST['experience'], FILTER_SANITIZE_NUMBER_INT);
                    
                    $stmt = $conn->prepare("INSERT INTO doctors (full_name, email, password, specialization, experience, phone, address, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$full_name, $email, $hashed_password, $specialization, $experience, $phone, $address, $profile_image]);
                }
                
                $success = "Registration successful! Please login.";
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
    <title>Register - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <h1><i class="fas fa-heartbeat"></i>MediConnect</h1>
                </a>
                <nav>
                    <a href="index.php">Home</a>
                    <a href="pages/doctors.php">Doctors</a>
                    <a href="register.php" class="active">Register</a>
                    <a href="index.php" class="login-trigger">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="register-container">
            <div class="register-box">
                <h2>Create your account</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br>
                        <a href="index.php" class="btn btn-primary btn-sm">Go to Login</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="register-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="user_type">Account Type</label>
                        <select id="user_type" name="user_type" required>
                            <option value="user">Patient</option>
                            <option value="doctor">Doctor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required
                               pattern="[a-zA-Z0-9_]{3,20}"
                               title="Username must be between 3 and 20 characters and can only contain letters, numbers, and underscores">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                               title="Please enter a valid email address">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required
                               minlength="8"
                               title="Password must be at least 8 characters long">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required
                               pattern="[0-9]{10}"
                               title="Please enter a valid 10-digit phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    
                    <div id="doctor_fields" style="display: none;">
                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <input type="text" id="specialization" name="specialization">
                        </div>
                        
                        <div class="form-group">
                            <label for="experience">Years of Experience</label>
                            <input type="number" id="experience" name="experience" min="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image">Profile Image</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                        <small>Supported formats: JPG, JPEG, PNG</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                
                <div class="login-link">
                    <p>Already have an account? <a href="index.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Show/hide doctor fields based on user type
    document.getElementById('user_type').addEventListener('change', function() {
        const doctorFields = document.getElementById('doctor_fields');
        doctorFields.style.display = this.value === 'doctor' ? 'block' : 'none';
    });
    
    // Client-side validation
    document.querySelector('.register-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const phone = document.getElementById('phone').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
            return;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return;
        }
        
        if (!/^[0-9]{10}$/.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid 10-digit phone number');
            return;
        }
        
        const userType = document.getElementById('user_type').value;
        if (userType === 'doctor') {
            const specialization = document.getElementById('specialization').value;
            const experience = document.getElementById('experience').value;
            
            if (!specialization || !experience) {
                e.preventDefault();
                alert('Please fill in all doctor-specific fields');
                return;
            }
        }
    });
    </script>
</body>
</html> 