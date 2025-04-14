<?php
/**
 * Homepage - MediConnect
 * Main landing page for the website
 */

// Include configuration
require_once 'includes/config.php';

$error = '';

// Process login form if submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
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
$activePage = 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>MediConnect - Healthcare Made Simple</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/minimalist-theme.css">
    <link rel="stylesheet" href="assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="assets/styles/profile.css">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            width: 100%;
        }
        
        .btn {
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            border-radius: 6px;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
        .btn-primary {
            background: var(--primary-color, #5b86e5);
            color: white;
            border: none;
        }
        
        .btn-secondary {
            background: white;
            color: var(--primary-color, #5b86e5);
            border: 2px solid var(--primary-color, #5b86e5);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .profile-info {
            margin-top: 30px;
        }
        
        .profile-info h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--dark-color, #2d3748);
        }
        
        .profile-info .lead {
            font-size: 1.2rem;
            color: var(--text-color, #4a5568);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container dashboard-container">
        <section class="profile-header-section fade-in">
            <div class="profile-header">
                <div class="profile-image-container">
                    <img src="assets/images/person.png" alt="Healthcare" class="profile-image">
                </div>
                <div class="profile-info">
                    <h1>Your Health, Our Priority</h1>
                    <p class="lead">Welcome to our centralized portal for managing all your healthcare needs. Experience seamless healthcare management with our modern platform.</p>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-user-md"></i> Expert Doctors
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-check"></i> Easy Booking
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-file-medical"></i> Health Records
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i> 24/7 Support
                        </div>
                    </div>
                    <div class="cta-buttons">
                        <a href="pages/doctors.php" class="btn btn-primary">Find a Doctor</a>
                        <?php if(!isset($_SESSION['user_id'])): ?>
                            <a href="#" class="btn btn-secondary login-trigger">Login to Dashboard</a>
                        <?php else: ?>
                            <?php if($_SESSION['user_type'] == 'user'): ?>
                                <a href="dashboard.php" class="btn btn-secondary">View Dashboard</a>
                            <?php else: ?>
                                <a href="doctor_dashboard.php" class="btn btn-secondary">View Dashboard</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="features fade-in">
            <div class="card-header">
                <h2 class="card-title">Why Choose Us</h2>
            </div>
            <div class="feature-grid">
                <div class="enhanced-card pulse-hover">
                    <i class="fas fa-user-md"></i>
                    <h3>Expert Doctors</h3>
                    <p>Access to qualified healthcare professionals with years of experience in their respective fields</p>
                </div>
                <div class="enhanced-card pulse-hover">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Easy Booking</h3>
                    <p>Simple and quick appointment scheduling with just a few clicks</p>
                </div>
                <div class="enhanced-card pulse-hover">
                    <i class="fas fa-file-medical"></i>
                    <h3>Health Records</h3>
                    <p>Secure access to your medical history and test results in one place</p>
                </div>
                <div class="enhanced-card pulse-hover">
                    <i class="fas fa-clock"></i>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock assistance available for all your healthcare needs</p>
                </div>
            </div>
        </section>

        <section class="how-it-works fade-in">
            <div class="card-header">
                <h2 class="card-title">How It Works</h2>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Search Doctors</h4>
                    <p>Find healthcare providers in your area based on specialty and location</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>Book Appointment</h4>
                    <p>Choose your preferred time slot and book with a single click</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Visit Doctor</h4>
                    <p>Get treated by healthcare professionals at your scheduled time</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h4>Follow Up</h4>
                    <p>Receive reminders and care suggestions after your appointment</p>
                </div>
            </div>
        </section>

        <section class="card fade-in cta-section">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Take Control of Your Health?</h2>
                <p class="cta-description">Join thousands of satisfied patients who have transformed their healthcare experience with MediConnect. Book your appointment today and experience healthcare the way it should be - simple, efficient, and patient-centered.</p>
                <div class="cta-buttons">
                    <a href="pages/doctors.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check"></i>   Book an Appointment
                    </a>
                    <a href="register.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-user-plus"></i> Create Free Account
                    </a>
                </div>
                <div class="cta-stats">
                    <div class="stat-item">
                        <span class="stat-number">10,000+</span>
                        <span class="stat-label">Happy Patients</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Expert Doctors</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Support Available</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">Patient Testimonials</h2>
            </div>
            <div class="testimonials">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-meta">
                            <h4>Raj Sharma</h4>
                            <div class="meta-info">Mumbai • Cardiology Patient</div>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                    </div>
                    <p class="testimonial-text">"MediConnect made it incredibly easy to find a specialist for my heart condition. The entire process from booking to follow-up was seamless and professional."</p>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-meta">
                            <h4>Priya Patel</h4>
                            <div class="meta-info">Delhi • Regular Checkups</div>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="fas fa-star star"></i>
                        <i class="far fa-star star"></i>
                    </div>
                    <p class="testimonial-text">"I've been using MediConnect for all my family's healthcare needs. The digital records feature is amazing as I can access our medical history anytime, anywhere."</p>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Login to MediConnect</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="" class="enhanced-form">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="user_type">Account Type:</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="user">Patient</option>
                        <option value="doctor">Doctor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
                <p class="form-footer">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the modal
        var modal = document.getElementById('loginModal');

        // Get all elements that should open the modal
        var triggers = document.getElementsByClassName('login-trigger');

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName('close')[0];

        // Add click event to all trigger elements
        Array.from(triggers).forEach(function(trigger) {
            trigger.onclick = function(e) {
                e.preventDefault();
                modal.style.display = 'block';
            }
        });

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = 'none';
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    });
    </script>

    <style>
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 8px;
        position: relative;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .form-footer {
        margin-top: 15px;
        text-align: center;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    </style>
</body>
</html>
