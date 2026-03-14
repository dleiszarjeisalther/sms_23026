<?php
require_once __DIR__ . '/../../models/config.php';
require_once __DIR__ . '/../../models/subsystem1/admission.model.php';

// Import Composer Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic sanitization
    function sanitizeInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $firstName = sanitizeInput($_POST['firstName'] ?? '');
    $lastName = sanitizeInput($_POST['lastName'] ?? '');
    $dobMonth = sanitizeInput($_POST['dobMonth'] ?? '');
    $dobDay = sanitizeInput($_POST['dobDay'] ?? '');
    $dobYear = sanitizeInput($_POST['dobYear'] ?? '');
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $program = sanitizeInput($_POST['program'] ?? '');
    $contactNumber = sanitizeInput($_POST['contactNumber'] ?? '');
    $emailAddress = sanitizeInput($_POST['emailAddress'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $guardianName = sanitizeInput($_POST['guardianName'] ?? '');
    $guardianRelation = sanitizeInput($_POST['guardianRelation'] ?? '');
    $guardianContact = sanitizeInput($_POST['guardianContact'] ?? '');
    $guardianAddress = sanitizeInput($_POST['guardianAddress'] ?? '');

    // Basic Validation
    $errors = [];
    if (empty($firstName)) $errors[] = "First Name is required.";
    if (empty($lastName)) $errors[] = "Last Name is required.";
    if (empty($dobMonth) || empty($dobDay) || empty($dobYear)) $errors[] = "Full Date of Birth is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($program)) $errors[] = "Program is required.";
    if (empty($contactNumber)) $errors[] = "Contact Number is required.";
    if (empty($emailAddress) || !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email Address is required.";
    if (empty($address)) $errors[] = "Address is required.";
    // Guardian fields are optional in the simplified form

    if (empty($errors)) {
        // Format Date
        $dob = "$dobYear-$dobMonth-" . str_pad($dobDay, 2, '0', STR_PAD_LEFT);

        $studentData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_of_birth' => $dob,
            'gender' => $gender,
            'program' => $program,
            'contact_number' => $contactNumber,
            'email_address' => $emailAddress,
            'address' => $address,
            'guardian_name' => $guardianName,
            'guardian_relation' => $guardianRelation,
            'guardian_contact' => $guardianContact,
            'guardian_address' => $guardianAddress
        ];

        // Ensure global $pdo from config.php is available
        global $pdo;

        if ($pdo) {
            $admissionModel = new AdmissionModel($pdo);
            $result = $admissionModel->registerStudent($studentData);

            if ($result) {
                // Return success, auto-generated student ID, and raw password
                $newStudentId = $result['student_id'];
                $rawPassword = $result['raw_password'];

                // --- EMAIL DISPATCH ---
                global $smtp_accounts;
                $emailSent = false;
                $mail = new PHPMailer(true);

                foreach ($smtp_accounts as $account) {
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = $account['host'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $account['username'];
                        $mail->Password   = $account['password'];
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = $account['port'];

                        // Recipients
                        $mail->setFrom('admin@institution.edu.ph', 'Student Admissions');
                        $mail->addAddress($emailAddress, $firstName . ' ' . $lastName);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Welcome! Your Student Login Credentials';
                        $mail->Body    = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-top: 4px solid #2d5f5d;'>
                                <h2 style='color: #2d5f5d;'>Registration Successful</h2>
                                <p>Dear {$firstName},</p>
                                <p>Welcome to our institution! Your admission registration was successful. Please keep the following login credentials secure:</p>
                                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                                    <p style='margin: 0 0 10px 0;'><strong>Student ID / User ID:</strong> {$newStudentId}</p>
                                    <p style='margin: 0;'><strong>Password:</strong> {$rawPassword}</p>
                                </div>
                                <p>You can use these credentials to access the <a href='http://localhost" . BASE_URL . "/login'>Student Portal</a>.</p>
                                <p style='color: #777; font-size: 0.9em; margin-top: 30px;'>Do not share this email with anyone.</p>
                            </div>
                        ";
                        $mail->AltBody = "Dear {$firstName},\n\nYour registration was successful.\n\nStudent ID: {$newStudentId}\nPassword: {$rawPassword}\n\nPlease login at the Student Portal.";

                        $mail->send();
                        $emailSent = true;
                        break; // Exit loop if email sent successfully
                    } catch (Exception $e) {
                        // Email failed for this account, log error and try the next account (if any)
                        error_log("Failed to send with account {$account['username']}. Error: {$mail->ErrorInfo}");
                        $mail->clearAllRecipients(); // Clear recipients before trying again
                    }
                }

                echo "success|$newStudentId|$rawPassword|$emailSent";
            } else {
                echo "error|Failed to register student.";
            }
        } else {
            echo "error|Database connection not available.";
        }
    } else {
        echo "error|" . implode(" ", $errors);
    }
} else {
    echo "error|Invalid request method.";
}
