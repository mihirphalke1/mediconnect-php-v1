<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Search Results - MediConnect Clone</title>
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
      .doctor-cards {
        display: flex;
        flex-direction: column;
        gap: 20px;
      }

      .enhanced-card {
        display: flex;
        align-items: center;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .doctor-icon {
        font-size: 40px;
        color: #5b86e5;
        margin-right: 20px;
      }

      .doctor-info {
        flex: 1;
      }

      .doctor-info h3 {
        margin: 0 0 10px;
        font-size: 1.5rem;
      }

      .doctor-info p {
        margin: 5px 0;
        color: #555;
      }

      .status-tag {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.875rem;
        margin-top: 10px;
      }

      .status-success {
        background-color: #d4edda;
        color: #155724;
      }

      .status-warning {
        background-color: #fff3cd;
        color: #856404;
      }

      .status-danger {
        background-color: #f8d7da;
        color: #721c24;
      }

      .btn {
        margin-top: 10px;
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
            <a href="feedback.html">Feedback</a>
            <a href="profile.html">Profile</a>
          </nav>
        </div>
      </div>
    </header>

    <div class="container dashboard-container">
      <section class="profile-header-section fade-in">
        <div class="profile-header">
          <div class="profile-info">
            <h1>Search Results</h1>
            <p class="lead">
              Here are the doctors matching your search criteria.
            </p>
          </div>
        </div>
      </section>

      <section class="card fade-in">
        <div class="card-header">
          <h2 class="card-title">Doctors Found</h2>
        </div>
        <div class="doctor-cards">
          <!-- Doctor Card 1 -->
          <div class="enhanced-card pulse-hover">
            <i class="fas fa-user-md doctor-icon"></i>
            <div class="doctor-info">
              <h3>Dr. Aditya Sharma</h3>
              <p><strong>Specialty:</strong> Cardiologist</p>
              <p><strong>Experience:</strong> 12 years</p>
              <p><strong>Location:</strong> Vashi, Navi Mumbai</p>
              <span class="status-tag status-success">Available Today</span>
              <a href="book_appointment.html" class="btn btn-primary"
                >Book Appointment</a
              >
            </div>
          </div>

          <!-- Doctor Card 2 -->
          <div class="enhanced-card pulse-hover">
            <i class="fas fa-user-md doctor-icon"></i>
            <div class="doctor-info">
              <h3>Dr. Priya Patel</h3>
              <p><strong>Specialty:</strong> Pediatrician</p>
              <p><strong>Experience:</strong> 8 years</p>
              <p><strong>Location:</strong> Andheri, Mumbai</p>
              <span class="status-tag status-warning">Few Slots Left</span>
              <a href="book_appointment.html" class="btn btn-primary"
                >Book Appointment</a
              >
            </div>
          </div>

          <!-- Doctor Card 3 -->
          <div class="enhanced-card pulse-hover">
            <i class="fas fa-user-md doctor-icon"></i>
            <div class="doctor-info">
              <h3>Dr. Rajat Singh</h3>
              <p><strong>Specialty:</strong> Dermatologist</p>
              <p><strong>Experience:</strong> 10 years</p>
              <p><strong>Location:</strong> Dadar, Mumbai</p>
              <span class="status-tag status-success">Available Today</span>
              <a href="book_appointment.html" class="btn btn-primary"
                >Book Appointment</a
              >
            </div>
          </div>

          <!-- Doctor Card 4 -->
          <div class="enhanced-card pulse-hover">
            <i class="fas fa-user-md doctor-icon"></i>
            <div class="doctor-info">
              <h3>Dr. Meera Kumar</h3>
              <p><strong>Specialty:</strong> Gynecologist</p>
              <p><strong>Experience:</strong> 15 years</p>
              <p><strong>Location:</strong> Powai, Mumbai</p>
              <span class="status-tag status-danger">Fully Booked Today</span>
              <a href="book_appointment.html" class="btn btn-primary"
                >Book Appointment</a
              >
            </div>
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
  </body>
</html>
