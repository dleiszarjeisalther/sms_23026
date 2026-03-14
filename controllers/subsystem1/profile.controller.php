<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login");
    exit();
}

require_once __DIR__ . '/../../models/config.php';
require_once __DIR__ . '/../../models/subsystem1/profile.model.php';

$profileModel = new ProfileModel($pdo);

$studentId = $_SESSION['user_id']; // This could be student or admin, if admin, we might need to get $studentId from GET params
// Assuming for now it's a student updating their own profile
if ($_SESSION['user_type'] !== 'student' && isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedBy = $_SESSION['user_id'];

    // Sanitize inputs
    $data = [
        'contact_number' => htmlspecialchars(trim($_POST['contact_number'] ?? '')),
        'email_address' => filter_var(trim($_POST['email_address'] ?? ''), FILTER_SANITIZE_EMAIL),
        'address' => htmlspecialchars(trim($_POST['address'] ?? '')),
        'guardian_name' => htmlspecialchars(trim($_POST['guardian_name'] ?? '')),
        'guardian_relation' => htmlspecialchars(trim($_POST['guardian_relation'] ?? '')),
        'guardian_contact' => htmlspecialchars(trim($_POST['guardian_contact'] ?? '')),
        'guardian_address' => htmlspecialchars(trim($_POST['guardian_address'] ?? ''))
    ];

    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!empty($newPassword)) {
        if ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 8 || strlen($newPassword) > 64) {
            $error = 'Password must be between 8 and 64 characters.';
        } elseif (
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/[a-z]/', $newPassword) ||
            !preg_match('/[0-9]/', $newPassword) ||
            !preg_match('/[!@#$%^&*]/', $newPassword)
        ) {
            $error = 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character (!@#$%^&*).';
        } else {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }

    $imagePath = null;

    // Handle Profile Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/../../resources/uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            // Create a unique filename based on student ID to prevent collisions
            $newFileName = $studentId . '_' . time() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $imagePath = BASE_URL . '/resources/uploads/profiles/' . $newFileName;
            } else {
                $error = 'There was an error moving the uploaded file.';
            }
        } else {
            $error = 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.';
        }
    }

    if (empty($error)) {
        $success = $profileModel->updateStudentInfo($studentId, $updatedBy, $data, $imagePath);
        if ($success) {
            $message = 'Profile updated successfully.';
        } else {
            $error = 'Failed to update profile. It is possible no changes were made.';
        }
    }
}

// Fetch current details and history
$studentDetails = $profileModel->getStudentDetails($studentId);
$updateHistory = $profileModel->getStudentUpdateHistory($studentId);

if (!$studentDetails) {
    echo "Student not found.";
    exit();
}

// Load view
require __DIR__ . '/../../views/subsystem1/profile_update.php';
