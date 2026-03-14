<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Ensure only registrars can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit;
    }
    header('Location: ' . BASE_URL . '/login');
    exit;
}

require_once __DIR__ . '/../../models/config.php';

// Handle AJAX POST requests to update status or program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $studentId = $_POST['student_id'] ?? '';

    if ($_POST['action'] === 'update_status') {
        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['Active', 'Dropped', 'Graduated', 'On Leave'];

        if (empty($studentId) || !in_array($newStatus, $validStatuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status data.']);
            exit;
        }

        try {
            global $pdo;
            $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE student_id = ?");
            if ($stmt->execute([$newStatus, $studentId])) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'update_program') {
        $newProgram = $_POST['program'] ?? '';
        if (empty($studentId) || empty($newProgram)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid program data.']);
            exit;
        }

        try {
            global $pdo;
            $stmt = $pdo->prepare("UPDATE students SET program = ? WHERE student_id = ?");
            if ($stmt->execute([$newProgram, $studentId])) {
                echo json_encode(['status' => 'success', 'message' => 'Program updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $e->getMessage()]);
        }
        exit;
    }
}

// For GET requests, fetch all students
$students = [];
$error = '';
try {
    global $pdo;
    $stmt = $pdo->query("SELECT student_id, first_name, last_name, program, section, status FROM students ORDER BY last_name ASC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to fetch students: " . $e->getMessage();
}

// Load the view
require_once __DIR__ . '/../../views/subsystem1/student_status.php';
