# MediConnect

A healthcare platform connecting patients with healthcare providers.

## Features

### Patient Features

- Doctor search by specialty, name, or location
- View doctor profiles with qualifications, experience, and fees
- Expandable doctor cards with detailed information
- Google Maps integration for clinic locations
- Book, view, and manage appointments
- Personal profile and medical history management

### Doctor Features

- Professional profile management
- Consultation fee and schedule settings
- Clinic information and working hours configuration
- Appointment calendar and patient management

### Admin Features

- User management system with role-based access
- Content management for platform resources
- System configuration and maintenance

## User Flow

### Patient Journey

1. Register/Login to the platform
2. Search for doctors using filters
3. View doctor details by expanding doctor cards
4. Book an appointment with selected doctor
5. Manage appointments through the dashboard
6. Update personal profile as needed

### Doctor Journey

1. Login with doctor credentials
2. Configure professional profile and availability
3. View and manage upcoming appointments
4. Review patient information
5. Upload Prescriptions and Medical Records

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, jQuery
- **Framework**: Bootstrap 5
- **Additional Libraries**: Font Awesome
- **APIs**: Google Maps
- **Security**: PDO, input sanitization

## Installation

1. Clone to web server directory
2. Import database from database/mediconnect.sql
3. Configure database connection in includes/config.php
4. Access via localhost/mediconnect-php
