<?php
/**
 * Login Page
 * Allows users to login to their accounts
 */

// Include configuration
require_once 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'user') {
        header("Location: dashboard.php");
    } else {
        header("Location: doctor_dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $userType = sanitizeInput($_POST['user_type']);
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        try {
            if ($userType == 'user') {
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            } else {
                $stmt = $conn->prepare("SELECT * FROM doctors WHERE email = ?");
            }
            
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $userType;
                $_SESSION['full_name'] = $user['full_name'];
                
                // Set success message
                setFlashMessage('success', 'Login successful. Welcome back, ' . $user['full_name'] . '!');
                
                // Redirect based on user type
                if ($userType == 'user') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: doctor_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Set active page for navigation
$activePage = 'login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        
        .login-box {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .login-box h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .login-box .lead {
            margin-bottom: 25px;
            color: #718096;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4a5568;
        }
        
        .login-form input,
        .login-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .btn-block {
            width: 100%;
            padding: 12px;
            font-size: 16px;
        }
        
        .register-link {
            margin-top: 25px;
            font-size: 14px;
            color: #718096;
        }
        
        .register-link a {
            color: var(--primary-color);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="login-container fade-in">
            <div class="login-box">
                <h2><i class="fas fa-heartbeat"></i> <?php echo APP_NAME; ?></h2>
                <p class="lead">Welcome back! Please login to your account</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="login-form">
                    <div class="form-group">
                        <label for="user_type">Account Type</label>
                        <select id="user_type" name="user_type" class="form-control" required>
                            <option value="user">Patient</option>
                            <option value="doctor">Doctor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 