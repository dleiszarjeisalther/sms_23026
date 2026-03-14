<?php
require_once __DIR__ . '/../../models/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only students can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $action = $_GET['action'] ?? 'details';
    $studentId = $_SESSION['user_id'];

    global $pdo;

    if ($action === 'details') {
        try {
            $query = "SELECT s.*, e.academic_year 
                      FROM students s 
                      LEFT JOIN enrollments e ON s.student_id = e.student_id 
                      WHERE s.student_id = :student_id 
                      ORDER BY e.id DESC LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['student_id' => $studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                if (!empty($student['profile_image'])) {
                    if (!str_starts_with($student['profile_image'], '/')) {
                        $student['profile_image'] = BASE_URL . '/resources/uploads/profiles/' . $student['profile_image'];
                    }
                }
                echo json_encode(['status' => 'success', 'data' => $student]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Student record not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
