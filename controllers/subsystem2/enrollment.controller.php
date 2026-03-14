<?php
// controller: C:\xampp\htdocs\subsystem1\controllers\subsystem2\enrollment.controller.php

require_once __DIR__ . '/../../models/config.php';
require_once __DIR__ . '/../../models/subsystem2/enrollment.model.php';

$enrollmentModel = new EnrollmentModel($pdo);

$error = '';
$message = '';

// Handle POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $student_id = $_POST['student_id'] ?? '';
        $password = $_POST['password'] ?? '';

        $student = $enrollmentModel->authenticateStudent($student_id, $password);

        if ($student) {
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['first_name'] = $student['first_name'];

            // persistent login cookie if requested
            if (!empty($_POST['remember_me'])) {
                setcookie('remembered_student', $student['student_id'], time() + 60 * 60 * 24 * 30, '/');
            } else {
                setcookie('remembered_student', '', time() - 3600, '/');
            }

            header('Location: ' . BASE_URL . '/enrollment');
            exit;
        } else {
            // Determine if the ID exists but the password was wrong, or student inactive
            $checkStmt = $pdo->prepare("SELECT status FROM students WHERE student_id = ?");
            $checkStmt->execute([$student_id]);
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['status'] !== 'Active') {
                $error = "Your account is not active. Please contact the registrar.";
            } else {
                $error = "Invalid Student ID or Password.";
            }
        }
    } elseif ($action === 'enroll') {
        if (!isset($_SESSION['student_id'])) {
            header('Location: ' . BASE_URL . '/enrollment');
            exit;
        }

        $student_id = $_SESSION['student_id'];

        // Profile Updates (Personal and Emergency Contact)
        $profileData = [
            'contact_number' => $_POST['contact_number'] ?? '',
            'address' => $_POST['address'] ?? '',
            'guardian_name' => $_POST['guardian_name'] ?? '',
            'guardian_contact' => $_POST['guardian_contact'] ?? '',
            'guardian_relation' => $_POST['guardian_relation'] ?? '',
            'guardian_address' => $_POST['guardian_address'] ?? ''
        ];

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
                $newFileName = $student_id . '_' . time() . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $profileData['profile_image'] = BASE_URL . '/resources/uploads/profiles/' . $newFileName;
                }
            }
        }

        // Remove empty values to avoid overwriting with blanks if not provided
        $profileData = array_filter($profileData);

        $enrollmentModel->updateStudentProfile($student_id, $profileData);

        $academic_year = $_POST['academic_year'] ?? '2024-2025';
        $semester = $_POST['semester'] ?? '1st Semester';
        $year_level = $_POST['year_level'] ?? 1;
        $program = $_POST['program'] ?? '';
        $subjects = $_POST['subjects'] ?? '';

        $success = $enrollmentModel->createEnrollment($student_id, $academic_year, $semester, $year_level, $program, $subjects);

        if ($success) {
            $message = "Enrollment submitted successfully!";
        } else {
            $error = "Failed to submit enrollment.";
        }
    }
}

$isLoggedIn = isset($_SESSION['student_id']) && $_SESSION['user_type'] === 'student';

// Provide student detail to the view if logged in
$studentDetails = null;
if ($isLoggedIn) {
    $studentDetails = $enrollmentModel->getStudentDetails($_SESSION['student_id']);
}

require_once __DIR__ . '/../../views/subsystem2/enrollment.php';
