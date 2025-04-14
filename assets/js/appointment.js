/**
 * Appointment Management JavaScript
 * Handles AJAX requests for real-time appointment updates
 */

/**
 * Doctor's appointment management functions
 */
const DoctorAppointments = {
  // Poll interval in milliseconds (10 seconds)
  pollInterval: 10000,
  // Variable to store polling timeout ID
  pollTimeoutId: null,

  /**
   * Initialize doctor appointment functionality
   */
  init: function () {
    this.setupEventListeners();
    this.startPolling();
  },

  /**
   * Setup event listeners for doctor actions
   */
  setupEventListeners: function () {
    // Use event delegation for dynamically added elements
    document.addEventListener("click", function (e) {
      // Handle accept appointment button
      if (
        e.target.matches(".accept-appointment-btn") ||
        e.target.closest(".accept-appointment-btn")
      ) {
        e.preventDefault();
        const btn = e.target.matches(".accept-appointment-btn")
          ? e.target
          : e.target.closest(".accept-appointment-btn");
        const appointmentId = btn.getAttribute("data-id");
        const messageEl = document.getElementById(
          "js-message-" + appointmentId
        );
        const message = messageEl ? messageEl.value : "";

        DoctorAppointments.updateStatus(appointmentId, "confirmed", message);
      }

      // Handle decline appointment button
      if (
        e.target.matches(".decline-appointment-btn") ||
        e.target.closest(".decline-appointment-btn")
      ) {
        e.preventDefault();
        const btn = e.target.matches(".decline-appointment-btn")
          ? e.target
          : e.target.closest(".decline-appointment-btn");
        const appointmentId = btn.getAttribute("data-id");
        const messageEl = document.getElementById(
          "js-message-" + appointmentId
        );
        const message = messageEl ? messageEl.value : "";

        DoctorAppointments.updateStatus(appointmentId, "cancelled", message);
      }
    });
  },

  /**
   * Update appointment status
   * @param {number} appointmentId - The appointment ID
   * @param {string} status - The new status (confirmed or cancelled)
   * @param {string} message - Optional message for the patient
   */
  updateStatus: function (appointmentId, status, message) {
    const formData = new FormData();
    formData.append("action", "update_status");
    formData.append("appointment_id", appointmentId);
    formData.append("status", status);
    formData.append("message", message);

    // Get the correct API path based on current page location
    const apiPath = this.getApiPath();

    fetch(apiPath + "api/appointment_actions.php", {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Update UI
          const appointmentItem = document.querySelector(
            `[data-appointment-id="${appointmentId}"]`
          );

          if (appointmentItem) {
            // Replace the appointment item with updated content
            this.updateAppointmentItem(
              appointmentItem,
              data.appointment,
              status
            );
          }

          // Show success message
          this.showAlert("success", data.message);
        } else {
          // Show error message
          this.showAlert(
            "danger",
            data.message || "Error updating appointment status"
          );
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        this.showAlert("danger", "Network error. Please try again.");
      });
  },

  /**
   * Get API path based on current page location
   * @returns {string} Base path for API calls
   */
  getApiPath: function () {
    // Check if we're in a subdirectory by looking at the URL path
    const path = window.location.pathname;
    if (path.includes("/pages/")) {
      return "../"; // We're in a subdirectory
    }
    return ""; // We're in the root directory
  },

  /**
   * Update appointment item in the UI
   * @param {Element} appointmentItem - The appointment item element
   * @param {Object} appointment - The updated appointment data
   * @param {string} status - The new status
   */
  updateAppointmentItem: function (appointmentItem, appointment, status) {
    // Update status badge
    const statusBadge = appointmentItem.querySelector(".status");
    if (statusBadge) {
      statusBadge.className = `status status-${status}`;
      statusBadge.textContent =
        status.charAt(0).toUpperCase() + status.slice(1);
    }

    // Replace form with confirmation message
    const actionsSection = appointmentItem.querySelector(
      ".appointment-actions"
    );
    if (actionsSection) {
      let messageHtml = "";

      if (appointment.message) {
        messageHtml = `
                    <div class="doctor-message">
                        <strong>Your message:</strong><br>
                        ${appointment.message}
                    </div>
                `;
      }

      actionsSection.innerHTML = messageHtml;
    }
  },

  /**
   * Start polling for appointment updates
   */
  startPolling: function () {
    this.pollTimeoutId = setTimeout(() => {
      this.fetchAppointments();
      this.startPolling();
    }, this.pollInterval);
  },

  /**
   * Stop polling for appointment updates
   */
  stopPolling: function () {
    if (this.pollTimeoutId) {
      clearTimeout(this.pollTimeoutId);
      this.pollTimeoutId = null;
    }
  },

  /**
   * Fetch doctor's appointments via AJAX
   */
  fetchAppointments: function () {
    const apiPath = this.getApiPath();

    fetch(apiPath + "api/appointment_actions.php?action=get_appointments", {
      method: "GET",
      credentials: "same-origin",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.refreshAppointmentsList(data.appointments);
        }
      })
      .catch((error) => {
        console.error("Error fetching appointments:", error);
      });
  },

  /**
   * Refresh appointments list with new data
   * @param {Array} appointments - List of appointments
   */
  refreshAppointmentsList: function (appointments) {
    const appointmentsContainer = document.querySelector(
      ".appointments-container"
    );
    if (!appointmentsContainer) return;

    const pendingAppointments = appointments.filter(
      (app) => app.status === "pending"
    );

    // Update pending count
    const pendingCountEl = document.querySelector(".pending-count");
    if (pendingCountEl) {
      pendingCountEl.textContent = pendingAppointments.length;
    }

    // Only update if the number of appointments has changed
    const currentAppointmentItems =
      appointmentsContainer.querySelectorAll(".appointment-item");

    if (currentAppointmentItems.length !== appointments.length) {
      // Build new appointment list
      let html = "";

      if (appointments.length === 0) {
        html = '<p class="no-data">No appointments scheduled.</p>';
      } else {
        appointments.forEach((appointment) => {
          html += this.createAppointmentHtml(appointment);
        });
      }

      appointmentsContainer.innerHTML = html;
    }
  },

  /**
   * Create HTML for a single appointment
   * @param {Object} appointment - The appointment data
   * @returns {string} HTML string
   */
  createAppointmentHtml: function (appointment) {
    // Format actions based on status
    let actionsHtml = "";

    if (appointment.status === "pending") {
      actionsHtml = `
                <div class="appointment-actions">
                    <div class="form-group">
                        <label for="message-${appointment.id}">Message to Patient:</label>
                        <textarea name="message" id="message-${appointment.id}" 
                                placeholder="Optional message for the patient"></textarea>
                    </div>
                    <div class="button-group">
                        <button type="button" data-id="${appointment.id}" class="btn btn-success accept-appointment-btn">
                            <i class="fas fa-check"></i> Accept
                        </button>
                        <button type="button" data-id="${appointment.id}" class="btn btn-danger decline-appointment-btn">
                            <i class="fas fa-times"></i> Decline
                        </button>
                    </div>
                </div>
            `;
    } else if (appointment.doctor_message) {
      actionsHtml = `
                <div class="appointment-actions">
                    <div class="doctor-message">
                        <strong>Your message:</strong><br>
                        ${appointment.doctor_message}
                    </div>
                </div>
            `;
    }

    return `
            <div class="appointment-item" data-appointment-id="${
              appointment.id
            }">
                <div class="appointment-info">
                    <h4>${appointment.patient_name}</h4>
                    <p>
                        <i class="fas fa-envelope"></i> 
                        ${appointment.patient_email}
                    </p>
                    <p>
                        <i class="fas fa-phone"></i> 
                        ${appointment.patient_phone}
                    </p>
                    <p>
                        <i class="fas fa-calendar"></i> 
                        ${appointment.appointment_date}
                    </p>
                    <p>
                        <i class="fas fa-clock"></i> 
                        ${appointment.appointment_time}
                    </p>
                    <p>
                        <i class="fas fa-comment"></i> 
                        Notes: ${appointment.notes || "No notes provided"}
                    </p>
                    <span class="status status-${appointment.status}">
                        ${
                          appointment.status.charAt(0).toUpperCase() +
                          appointment.status.slice(1)
                        }
                    </span>
                </div>
                ${actionsHtml}
            </div>
        `;
  },

  /**
   * Show alert message
   * @param {string} type - Alert type (success, danger, etc.)
   * @param {string} message - Alert message
   */
  showAlert: function (type, message) {
    const alertElement = document.createElement("div");
    alertElement.className = `alert alert-${type} fade-in`;
    alertElement.innerHTML = message;

    const container = document.querySelector(".welcome-section");
    if (container) {
      container.appendChild(alertElement);

      // Remove alert after 5 seconds
      setTimeout(() => {
        alertElement.classList.add("fade-out");
        setTimeout(() => {
          alertElement.remove();
        }, 500);
      }, 5000);
    }
  },
};

/**
 * Patient's appointment functions
 */
const PatientAppointments = {
  // Poll interval in milliseconds (10 seconds)
  pollInterval: 10000,
  // Variable to store polling timeout ID
  pollTimeoutId: null,

  /**
   * Initialize patient appointment functionality
   */
  init: function () {
    this.startPolling();
  },

  /**
   * Get API path based on current page location
   * @returns {string} Base path for API calls
   */
  getApiPath: function () {
    // Check if we're in a subdirectory by looking at the URL path
    const path = window.location.pathname;
    if (path.includes("/pages/")) {
      return "../"; // We're in a subdirectory
    }
    return ""; // We're in the root directory
  },

  /**
   * Start polling for appointment updates
   */
  startPolling: function () {
    this.pollTimeoutId = setTimeout(() => {
      this.fetchAppointments();
      this.startPolling();
    }, this.pollInterval);
  },

  /**
   * Stop polling for appointment updates
   */
  stopPolling: function () {
    if (this.pollTimeoutId) {
      clearTimeout(this.pollTimeoutId);
      this.pollTimeoutId = null;
    }
  },

  /**
   * Fetch patient's appointments via AJAX
   */
  fetchAppointments: function () {
    const apiPath = this.getApiPath();

    fetch(
      apiPath + "api/appointment_actions.php?action=get_patient_appointments",
      {
        method: "GET",
        credentials: "same-origin",
      }
    )
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.refreshAppointmentsList(data.appointments);
        }
      })
      .catch((error) => {
        console.error("Error fetching appointments:", error);
      });
  },

  /**
   * Refresh appointments list with new data
   * @param {Array} appointments - List of appointments
   */
  refreshAppointmentsList: function (appointments) {
    const appointmentsContainer = document.querySelector(
      ".appointments-container"
    );
    if (!appointmentsContainer) return;

    // Update counts
    const confirmedAppointments = appointments.filter(
      (app) =>
        app.status === "confirmed" &&
        new Date(app.appointment_date) >= new Date()
    );
    const pendingAppointments = appointments.filter(
      (app) => app.status === "pending"
    );

    const upcomingCountEl = document.querySelector(".upcoming-count");
    if (upcomingCountEl) {
      upcomingCountEl.textContent = confirmedAppointments.length;
    }

    const pendingCountEl = document.querySelector(".pending-count");
    if (pendingCountEl) {
      pendingCountEl.textContent = pendingAppointments.length;
    }

    // Build new appointment list
    let html = "";

    if (appointments.length === 0) {
      html =
        '<p class="no-data">No appointments scheduled. <a href="' +
        this.getApiPath() +
        'pages/book_appointment.php">Book your first appointment</a></p>';
    } else {
      appointments.forEach((appointment) => {
        html += this.createAppointmentHtml(appointment);
      });
    }

    appointmentsContainer.innerHTML = html;
  },

  /**
   * Create HTML for a single appointment
   * @param {Object} appointment - The appointment data
   * @returns {string} HTML string
   */
  createAppointmentHtml: function (appointment) {
    let doctorMessageHtml = "";
    if (appointment.doctor_message) {
      doctorMessageHtml = `
                <div class="doctor-message">
                    <strong>Doctor's Message:</strong><br>
                    ${appointment.doctor_message}
                </div>
            `;
    }

    return `
            <div class="appointment-item" data-appointment-id="${
              appointment.id
            }">
                <div class="appointment-info">
                    <h4>Dr. ${appointment.doctor_name}</h4>
                    <p>${appointment.specialization}</p>
                    <p>
                        <i class="fas fa-calendar"></i> 
                        ${appointment.appointment_date}
                    </p>
                    <p>
                        <i class="fas fa-clock"></i> 
                        ${appointment.appointment_time}
                    </p>
                    <p>
                        <i class="fas fa-phone"></i> 
                        ${appointment.doctor_phone}
                    </p>
                    <p>
                        <i class="fas fa-envelope"></i> 
                        ${appointment.doctor_email}
                    </p>
                    <span class="status status-${appointment.status}">
                        ${
                          appointment.status.charAt(0).toUpperCase() +
                          appointment.status.slice(1)
                        }
                    </span>
                    ${doctorMessageHtml}
                </div>
            </div>
        `;
  },
};

/**
 * Initialize the appropriate functionality based on user type
 */
document.addEventListener("DOMContentLoaded", function () {
  // Show JavaScript interface elements
  const jsOnlyElements = document.querySelectorAll(".js-only");
  jsOnlyElements.forEach((element) => {
    element.style.display = "block";
  });

  // Hide regular forms when JavaScript is available
  const regularForms = document.querySelectorAll(".status-form");
  regularForms.forEach((form) => {
    form.style.display = "none";
  });

  // Check if this is the doctor dashboard
  if (document.body.classList.contains("doctor-dashboard")) {
    DoctorAppointments.init();
  }

  // Check if this is the patient dashboard
  if (document.body.classList.contains("patient-dashboard")) {
    PatientAppointments.init();
  }
});
