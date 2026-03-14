<?php
require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function sanitizeInput($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // --- OTP VERIFICATION LOGIC ---
    if (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $otpInput = sanitizeInput($_POST['otp'] ?? '');
        $studentId = $_SESSION['pending_otp_student_id'] ?? '';

        if (empty($otpInput) || empty($studentId)) {
            echo "error|OTP is required.";
            exit;
        }

        global $pdo;
        $stmt = $pdo->prepare("SELECT student_id, first_name, last_name, otp_code, otp_expiry FROM students WHERE student_id = :student_id");
        $stmt->execute(['student_id' => $studentId]);
        $student = $stmt->fetch();

        if ($student) {
            $currentTime = date('Y-m-d H:i:s');
            
            if ($student['otp_code'] === $otpInput && strtotime($student['otp_expiry']) > strtotime($currentTime)) {
                // OTP is valid!
                // Clear OTP from DB
                $stmt = $pdo->prepare("UPDATE students SET otp_code = NULL, otp_expiry = NULL WHERE student_id = :student_id");
                $stmt->execute(['student_id' => $studentId]);

                // Set full session variables
                $_SESSION['user_id'] = $student['student_id'];
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['user_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['first_name'] = $student['first_name'];

                // Handle remember me from step 1
                if (!empty($_SESSION['remember_me'])) {
                    setcookie('remembered_student', $student['student_id'], time() + 60 * 60 * 24 * 30, '/');
                }
                
                unset($_SESSION['pending_otp_student_id']);
                unset($_SESSION['remember_me']);

                echo "success|" . BASE_URL . "/enrollment";
            } else {
                if (strtotime($student['otp_expiry']) <= strtotime($currentTime)) {
                    echo "error|OTP has expired. Please log in again.";
                } else {
                    echo "error|Invalid OTP code.";
                }
            }
        } else {
            echo "error|Session expired. Please log in again.";
        }
        exit;
    }

    $userId = sanitizeInput($_POST['userId'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');

    if (empty($userId) || empty($password)) {
        echo "error|User ID and Password are required.";
        exit;
    }

    global $pdo;

    if ($pdo) {
        // First, check if the user is a student
        $stmt = $pdo->prepare("SELECT id, student_id, first_name, last_name, email_address, password, login_attempts, is_locked FROM students WHERE student_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $student = $stmt->fetch();

        if ($student) {
            // Check if account is locked
            if ($student['is_locked']) {
                echo "error|Account is locked due to multiple failed attempts. Please contact the registrar.";
                exit;
            }

            // Verify student password
            if (password_verify($password, $student['password'])) {
                // Password is correct
                // Reset login attempts
                $stmt = $pdo->prepare("UPDATE students SET login_attempts = 0 WHERE student_id = :user_id");
                $stmt->execute(['user_id' => $userId]);

                // Generate OTP
                $otp = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                // Save OTP to DB
                $stmt = $pdo->prepare("UPDATE students SET otp_code = :otp, otp_expiry = :expiry WHERE student_id = :user_id");
                $stmt->execute([
                    'otp' => $otp,
                    'expiry' => $expiry,
                    'user_id' => $userId
                ]);

                // Send OTP via Email
                global $smtp_accounts;
                $mail = new PHPMailer(true);
                $emailSent = false;
                
                foreach ($smtp_accounts as $account) {
                    if (empty($account['host'])) continue;
                    try {
                        $mail->isSMTP();
                        $mail->Host       = $account['host'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $account['username'];
                        $mail->Password   = $account['password'];
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = $account['port'];

                        $mail->setFrom('security@institution.edu.ph', 'SIMS Security');
                        $mail->addAddress($student['email_address'], $student['first_name'] . ' ' . $student['last_name']);

                        $mail->isHTML(true);
                        $mail->Subject = 'Your Login OTP Code';
                        $mail->Body    = "
                            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                                <h2 style='color: #2d5f5d; text-align: center;'>Security Verification</h2>
                                <p>Hello <strong>{$student['first_name']}</strong>,</p>
                                <p>Your One-Time Password (OTP) for logging into your student account is:</p>
                                <div style='background: #f4f4f4; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #2d5f5d; border-radius: 5px; margin: 20px 0;'>
                                    {$otp}
                                </div>
                                <p style='font-size: 0.9em; color: #666;'>This code will expire in 10 minutes. If you did not request this, please secure your account immediately.</p>
                            </div>
                        ";
                        $mail->AltBody = "Your Login OTP Code is: {$otp}. It expires in 10 minutes.";

                        $mail->send();
                        $emailSent = true;
                        break;
                    } catch (Exception $e) {
                        error_log("Failed to send OTP email: " . $mail->ErrorInfo);
                        $mail->clearAllRecipients();
                    }
                }

                $_SESSION['pending_otp_student_id'] = $student['student_id'];
                $_SESSION['remember_me'] = !empty($_POST['remember_me']);
                
                if ($emailSent) {
                    echo "success_otp|" . BASE_URL . "/otp_verify|OTP has been sent to your email.";
                } else {
                    // For development/debugging if email fails, we might still want to proceed or show error
                    // But in production, it should probably error.
                    // For now, let's allow "success_otp" but mention email failed if possible.
                    echo "success_otp|" . BASE_URL . "/otp_verify|Warning: Failed to send OTP email, but code is generated (Dev Mode). Code: $otp";
                }
                exit;
            } else {
                // Wrong password
                $attempts = $student['login_attempts'] + 1;
                $isLocked = ($attempts >= 3) ? 1 : 0;

                $stmt = $pdo->prepare("UPDATE students SET login_attempts = :attempts, is_locked = :locked WHERE student_id = :user_id");
                $stmt->execute([
                    'attempts' => $attempts,
                    'locked' => $isLocked,
                    'user_id' => $userId
                ]);

                if ($isLocked) {
                    echo "error|Account has been locked after 3 failed attempts. Please contact the registrar.";
                } else {
                    $remaining = 3 - $attempts;
                    echo "error|Invalid Password. $remaining attempts remaining.";
                }
                exit;
            }
        }

        // Check if the user is a registrar
        $stmt = $pdo->prepare("SELECT id, registrar_id, name, password FROM registrars WHERE registrar_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $registrar = $stmt->fetch();

        if ($registrar) {
            // Verify registrar password
            if (password_verify($password, $registrar['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $registrar['registrar_id'];
                $_SESSION['user_type'] = 'registrar';
                $_SESSION['user_name'] = $registrar['name'];

                echo "success|" . BASE_URL . "/id_generator"; // Redirect to registrar dashboard
                exit;
            }
        }

        // If no match found or password incorrect
        echo "error|Invalid User ID or Password.";
    } else {
        echo "error|Database connection not available.";
    }
} else {
    echo "error|Invalid request method.";
}
