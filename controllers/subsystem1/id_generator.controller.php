<?php
require_once __DIR__ . '/../../models/config.php';

session_start();

// Ensure only registrars can access this API
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Determine the action
    $action = $_GET['action'] ?? 'list';

    global $pdo;

    if ($action === 'list') {
        // Fetch all students to populate the dropdown
        try {
            $section = $_GET['section'] ?? '';
            $search = $_GET['search'] ?? '';

            $query = "SELECT student_id, first_name, last_name, program, section FROM students WHERE 1=1";
            $params = [];

            if (!empty($section)) {
                $query .= " AND section = :section";
                $params['section'] = $section;
            }

            if (!empty($search)) {
                $query .= " AND (student_id LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
                $params['search'] = "%$search%";
            }

            $query .= " ORDER BY last_name ASC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $students]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } elseif ($action === 'sections') {
        // Fetch unique sections
        try {
            $stmt = $pdo->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL ORDER BY section ASC");
            $sections = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['status' => 'success', 'data' => $sections]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } elseif ($action === 'details') {
        // Fetch specific student details
        $studentId = $_GET['id'] ?? '';
        if (empty($studentId)) {
            echo json_encode(['status' => 'error', 'message' => 'Student ID is required.']);
            exit;
        }

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
                // Ensure profile_image path is correct if set
                if (!empty($student['profile_image'])) {
                    // Check if it's already a full path or just a filename
                    if (!str_starts_with($student['profile_image'], '/')) {
                        $student['profile_image'] = BASE_URL . '/resources/uploads/profiles/' . $student['profile_image'];
                    }
                }
                echo json_encode(['status' => 'success', 'data' => $student]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
