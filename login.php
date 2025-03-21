<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        try {
            if ($user_type == 'user') {
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            } else {
                $stmt = $conn->prepare("SELECT * FROM doctors WHERE email = ?");
            }
            
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user_type;
                $_SESSION['full_name'] = $user['full_name'];
                
                // Redirect based on user type
                if ($user_type == 'user') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: doctor-dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password";
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
    <title>Login - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/unified-practo.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-box">
                <h2><i class="fas fa-heartbeat"></i> MediConnect</h2>
                <p class="lead">Welcome back! Please login to your account</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="login-form">
                    <div class="form-group">
                        <label for="user_type">Account Type</label>
                        <select id="user_type" name="user_type" required>
                            <option value="user">Patient</option>
                            <option value="doctor">Doctor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 