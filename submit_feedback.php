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

// Get doctor ID from URL
$doctor_id = isset($_GET['doctor_id']) ? filter_var($_GET['doctor_id'], FILTER_SANITIZE_NUMBER_INT) : 0;

// Get doctor details
if ($doctor_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch();
        
        if (!$doctor) {
            header("Location: doctors.php");
            exit();
        }
        
        // Check if user has already submitted feedback for this doctor
        $stmt = $conn->prepare("SELECT COUNT(*) FROM feedback WHERE user_id = ? AND doctor_id = ?");
        $stmt->execute([$_SESSION['user_id'], $doctor_id]);
        $has_feedback = $stmt->fetchColumn() > 0;
        
        if ($has_feedback) {
            $error = "You have already submitted feedback for this doctor.";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$has_feedback) {
    $rating = filter_var($_POST['rating'], FILTER_SANITIZE_NUMBER_INT);
    $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
    
    // Validate input
    if ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating";
    } elseif (empty($comment)) {
        $error = "Please provide your feedback";
    } else {
        try {
            // Insert feedback
            $stmt = $conn->prepare("INSERT INTO feedback (user_id, doctor_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $doctor_id, $rating, $comment]);
            
            $success = "Thank you for your feedback!";
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Submit Feedback - MediConnect</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles/unified-practo.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="feedback-container">
            <div class="feedback-box">
                <h2><i class="fas fa-comment-medical"></i> Submit Feedback</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($doctor && !$has_feedback): ?>
                    <div class="doctor-info">
                        <img src="<?php echo $doctor['profile_image'] ?: 'assets/images/default-doctor.png'; ?>" alt="Doctor" class="doctor-image">
                        <h3>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                        <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    </div>
                    
                    <form method="POST" action="" class="feedback-form">
                        <div class="form-group">
                            <label>Rating</label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1"><i class="fas fa-star"></i></label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Your Feedback</label>
                            <textarea id="comment" name="comment" rows="5" required
                                      minlength="10"
                                      title="Please provide at least 10 characters of feedback"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Submit Feedback</button>
                    </form>
                <?php elseif ($has_feedback): ?>
                    <div class="alert alert-info">
                        You have already submitted feedback for this doctor.
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">Doctor not found</div>
                <?php endif; ?>
                
                <div class="back-link">
                    <a href="doctors.php" class="btn btn-secondary">Back to Doctors</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Client-side validation
    document.querySelector('.feedback-form').addEventListener('submit', function(e) {
        const rating = document.querySelector('input[name="rating"]:checked');
        const comment = document.getElementById('comment').value;
        
        if (!rating) {
            e.preventDefault();
            alert('Please select a rating');
            return;
        }
        
        if (comment.length < 10) {
            e.preventDefault();
            alert('Please provide at least 10 characters of feedback');
            return;
        }
    });
    
    // Star rating hover effect
    const stars = document.querySelectorAll('.rating-input label');
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.getAttribute('for').replace('star', '');
            highlightStars(rating);
        });
    });
    
    document.querySelector('.rating-input').addEventListener('mouseout', function() {
        const selected = document.querySelector('input[name="rating"]:checked');
        if (selected) {
            highlightStars(selected.value);
        } else {
            stars.forEach(star => star.querySelector('i').classList.remove('fas'));
            stars.forEach(star => star.querySelector('i').classList.add('far'));
        }
    });
    
    function highlightStars(rating) {
        stars.forEach(star => {
            const starRating = star.getAttribute('for').replace('star', '');
            const icon = star.querySelector('i');
            
            if (starRating <= rating) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        });
    }
    </script>
</body>
</html> 