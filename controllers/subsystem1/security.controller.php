<?php
require_once __DIR__ . '/../../models/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Ensure only registrars can access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'registrar') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

global $pdo;

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $stmt = $pdo->query("SELECT student_id, first_name, last_name, login_attempts, is_locked FROM students ORDER BY last_name ASC");
            $students = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $students]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'toggle_lock':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit;
        }

        $studentId = $_POST['student_id'] ?? '';
        $isLocked = $_POST['is_locked'] ?? '';

        if (empty($studentId)) {
            echo json_encode(['status' => 'error', 'message' => 'Student ID is required.']);
            exit;
        }

        try {
            // If unlocking, also reset login attempts
            if ($isLocked == 0) {
                $stmt = $pdo->prepare("UPDATE students SET is_locked = 0, login_attempts = 0 WHERE student_id = ?");
                $stmt->execute([$studentId]);
            } else {
                $stmt = $pdo->prepare("UPDATE students SET is_locked = 1 WHERE student_id = ?");
                $stmt->execute([$studentId]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
