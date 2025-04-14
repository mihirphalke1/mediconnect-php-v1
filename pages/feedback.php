<?php
/**
 * Feedback Page
 * Allows users to submit feedback about the application or specific appointments
 */

// Include configuration
require_once '../includes/config.php';

// Set navigation active page
$activePage = 'feedback.php';
$isSubdirectory = true;

// Get user's previous appointments if logged in
$pastAppointments = [];
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("
            SELECT a.id, a.appointment_date, a.status, d.full_name as doctor_name
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.user_id = ? AND a.status = 'completed'
            ORDER BY a.appointment_date DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $pastAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silently fail, will just show empty appointments
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Feedback - <?php echo APP_NAME; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css">
    <link rel="stylesheet" href="../assets/styles/profile.css">
    <link rel="stylesheet" href="../assets/styles/minimalist-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      .feedback-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
      }

      .feedback-card {
        display: flex;
        flex-direction: column;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .feedback-card h3 {
        margin: 0 0 10px;
        font-size: 1.5rem;
      }

      .feedback-card p {
        margin: 5px 0;
        color: #555;
      }

      .profile-header i {
        font-size: 2rem;
        margin-right: 10px;
        color: #5b86e5;
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

    <div class="container dashboard-container">
      <section class="profile-header-section fade-in">
        <div class="profile-header">
          <div class="profile-info">
            <h1><i class="fas fa-comments"></i> Feedback</h1>
            <p class="lead">
              We value your feedback. Please share your thoughts with us.
            </p>
          </div>
        </div>
      </section>

      <section class="feedback-section fade-in">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Submit Your Feedback</h2>
          </div>
          <div class="feedback-card">
            <form action="../submit_feedback.php" method="POST">
              <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="form-group">
                  <label for="name"><i class="fas fa-user"></i> Name:</label>
                  <input type="text" id="name" name="name" required />
                </div>
                <div class="form-group">
                  <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                  <input type="email" id="email" name="email" required />
                </div>
              <?php endif; ?>
              
              <?php if (!empty($pastAppointments)): ?>
                <div class="form-group">
                  <label for="appointment"><i class="fas fa-calendar-alt"></i> Select Past Appointment:</label>
                  <select id="appointment" name="appointment_id">
                    <option value="">Select an appointment (optional)</option>
                    <?php foreach ($pastAppointments as $appointment): ?>
                      <option value="<?php echo $appointment['id']; ?>">
                        Appointment with Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?> - 
                        <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
              
              <div class="form-group">
                <label for="subject"><i class="fas fa-heading"></i> Subject:</label>
                <input type="text" id="subject" name="subject" required />
              </div>
              
              <div class="form-group">
                <label for="rating"><i class="fas fa-star"></i> Rating:</label>
                <select id="rating" name="rating" required>
                  <option value="">Select a rating</option>
                  <option value="5">5 - Excellent</option>
                  <option value="4">4 - Very Good</option>
                  <option value="3">3 - Good</option>
                  <option value="2">2 - Fair</option>
                  <option value="1">1 - Poor</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="message"><i class="fas fa-comment"></i> Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea>
              </div>
              
              <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
          </div>
        </div>
      </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
