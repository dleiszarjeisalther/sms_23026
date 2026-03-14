# Student Information Management System (SIMS)

A comprehensive and secure system designed for maintaining student records, handling admissions, and managing academic data. This system is built for both Students and Registrars.

## 🚀 Key Features

### 🛡️ Security & Authentication
- **Two-Factor Authentication (OTP)**: Enhanced security for student logins with mandatory 6-digit OTP codes sent via email.
- **Account Locking**: Automated protection that locks accounts after 3 consecutive failed login attempts.
- **Registrar Security Portal**: Dedicated tools for registrars to monitor and manage student account security (Lock/Unlock features).

### 🎓 Student Features
- **Admission System**: Streamlined registration for new students with automated credential generation.
- **Enrollment Management**: Full tracking of enrollment status from Pending to Approved.
- **Profile Management**: Secure student profiles with support for profile image uploads.
- **Academic Records**: View grades, schedules, accomplishments, and organization involvement.

### 📋 Registrar Features
- **Student Status Tracking**: Monitor Active, Dropped, Graduated, and On Leave statuses.
- **ID Generation**: Automated generation of student IDs in PDF format.
- **Global Security Controls**: Centralized management of student account locks.

## 🛠️ Technical Stack
- **Backend**: PHP 8.0+
- **Database**: MariaDB / MySQL
- **Frontend**: Custom HTML5, Vanilla CSS3 (Glassmorphism & Sleek Dark Modes)
- **Mailing**: PHPMailer Integration for secure communications.
- **Security**: Password hashing and dynamic session management.

## ⚙️ Installation & Setup

1. **Clone the repository**:
   ```cmd
   git clone https://github.com/dleiszarjeisalther/sms_23026.git
   ```
2. **Environment Configuration**:
   The system automatically detects its base path. Ensure `models/config.php` has your database credentials.
3. **Database Migration**:
   Import `sms_db.sql` or run the migration script:
   ```cmd
   php migrate.php
   ```
4. **Collaboration**:
   Refer to `collab.txt` for detailed guidelines on adding new subsystems.

## 📁 Project Architecture
- `controllers/`: Handles business logic and request processing.
- `models/`: Database interactions and core configuration.
- `views/`: User interface and subsystem-specific pages.
- `resources/`: Global assets (CSS, JS, Images).

---
Developed with focus on **Security**, **Scalability**, and **User Experience**.
