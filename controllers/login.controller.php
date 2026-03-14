<?php
require_once __DIR__ . '/../models/config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function sanitizeInput($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
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
        $stmt = $pdo->prepare("SELECT id, student_id, first_name, last_name, password FROM students WHERE student_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $student = $stmt->fetch();

        if ($student) {
            // Verify student password
            if (password_verify($password, $student['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $student['student_id'];
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['user_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['first_name'] = $student['first_name'];

                // Handle remember me
                if (!empty($_POST['remember_me'])) {
                    setcookie('remembered_student', $student['student_id'], time() + 60 * 60 * 24 * 30, '/');
                } else {
                    setcookie('remembered_student', '', time() - 3600, '/');
                }

                echo "success|" . BASE_URL . "/enrollment"; // Redirect to student enrollment
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
