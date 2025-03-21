<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Feedback - MediConnect Clone</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../assets/styles/styles.css" />
    <link rel="stylesheet" href="../assets/styles/practo-enhanced.css" />
    <link rel="stylesheet" href="../assets/styles/profile.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
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
    <header>
      <div class="container">
        <div class="header-content">
          <a href="/" class="logo">
            <h1><i class="fas fa-heartbeat"></i> MediConnect</h1>
          </a>
          <nav>
            <a href="/">Home</a>
            <a href="doctors.html">Doctors</a>
            <a href="dashboard.html">Dashboard</a>
            <a href="submit_appointment.html">Appointments</a>
            <a href="feedback.html" class="active">Feedback</a>
            <a href="profile.html">Profile</a>
          </nav>
        </div>
      </div>
    </header>

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
            <form id="feedbackForm" onsubmit="return validateFeedbackForm()">
              <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Name:</label>
                <input type="text" id="name" name="name" required />
              </div>
              <div class="form-group">
                <label for="email"
                  ><i class="fas fa-envelope"></i> Email:</label
                >
                <input type="email" id="email" name="email" required />
              </div>
              <div class="form-group">
                <label for="appointment"
                  ><i class="fas fa-calendar-alt"></i> Select Past
                  Appointment:</label
                >
                <select id="appointment" name="appointment" required>
                  <option value="">Select an appointment</option>
                  <option value="2025-02-25">
                    Appointment with Dr. Rajat Singh - February 25, 2025
                  </option>
                  <option value="2025-01-15">
                    Appointment with Dr. Meera Kumar - January 15, 2025
                  </option>
                  <!-- Add more past appointments here -->
                </select>
              </div>
              <div class="form-group">
                <label for="message"
                  ><i class="fas fa-comment"></i> Message:</label
                >
                <textarea
                  id="message"
                  name="message"
                  rows="5"
                  required
                ></textarea>
              </div>
              <input type="submit" value="Submit Feedback" class="btn" />
            </form>
          </div>
        </div>
      </section>
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
      function validateFeedbackForm() {
        const form = document.getElementById("feedbackForm");
        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const appointment = form.appointment.value;
        const message = form.message.value.trim();

        if (!name || !email || !appointment || !message) {
          alert("Please fill in all fields.");
          return false;
        }

        alert("Feedback submitted successfully!");
        return true;
      }
    </script>
  </body>
</html>
