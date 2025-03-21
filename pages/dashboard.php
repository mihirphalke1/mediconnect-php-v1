<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Dashboard - MediConnect Clone</title>
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
            <a href="dashboard.html" class="active">Dashboard</a>
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
            <h1><i class="fas fa-user"></i> Mihir Phalke</h1>
            <p class="lead">
              Welcome to your dashboard. Manage your appointments, view your
              health records, and track your health progress all in one place.
            </p>
          </div>
        </div>
      </section>

      <section class="dashboard fade-in">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Upcoming Appointments</h2>
          </div>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-date">March 15, 2025</div>
              <div class="timeline-title">
                Appointment with Dr. Aditya Sharma
              </div>
              <div class="timeline-desc">Cardiologist - Vashi, Navi Mumbai</div>
            </div>
            <div class="timeline-item">
              <div class="timeline-date">March 20, 2025</div>
              <div class="timeline-title">Appointment with Dr. Priya Patel</div>
              <div class="timeline-desc">Pediatrician - Andheri, Mumbai</div>
            </div>
            <div class="timeline-item">
              <div class="timeline-date">March 25, 2025</div>
              <div class="timeline-title">Appointment with Dr. Rajat Singh</div>
              <div class="timeline-desc">Dermatologist - Dadar, Mumbai</div>
            </div>
            <!-- New Appointment -->
            <div class="timeline-item">
              <div class="timeline-date">April 1, 2025</div>
              <div class="timeline-title">Appointment with Dr. Meera Kumar</div>
              <div class="timeline-desc">Gynecologist - Powai, Mumbai</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Health Records</h2>
          </div>
          <div class="info-grid">
            <div class="info-item">
              <div class="info-label">Blood Pressure</div>
              <div class="info-value">120/80 mmHg</div>
            </div>
            <div class="info-item">
              <div class="info-label">Blood Sugar</div>
              <div class="info-value">90 mg/dL</div>
            </div>
            <div class="info-item">
              <div class="info-label">Cholesterol</div>
              <div class="info-value">180 mg/dL</div>
            </div>
            <div class="info-item">
              <div class="info-label">Weight</div>
              <div class="info-value">70 kg</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Recent Activities</h2>
          </div>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-date">March 10, 2025</div>
              <div class="timeline-title">Blood Test</div>
              <div class="timeline-desc">Results: Normal</div>
            </div>
            <div class="timeline-item">
              <div class="timeline-date">March 5, 2025</div>
              <div class="timeline-title">
                Consultation with Dr. Meera Kumar
              </div>
              <div class="timeline-desc">Gynecologist - Powai, Mumbai</div>
            </div>
            <div class="timeline-item">
              <div class="timeline-date">March 1, 2025</div>
              <div class="timeline-title">X-Ray</div>
              <div class="timeline-desc">Results: No issues detected</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Hospitalization History</h2>
          </div>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-date">February 20, 2025</div>
              <div class="timeline-title">Hospitalized for Surgery</div>
              <div class="timeline-desc">
                Appendectomy at Fortis Hospital, Mumbai
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-date">January 15, 2025</div>
              <div class="timeline-title">Hospitalized for Observation</div>
              <div class="timeline-desc">
                Observation for chest pain at Apollo Hospital, Mumbai
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-date">December 5, 2024</div>
              <div class="timeline-title">Hospitalized for Treatment</div>
              <div class="timeline-desc">
                Treatment for pneumonia at Lilavati Hospital, Mumbai
              </div>
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
