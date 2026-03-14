<?php

class EnrollmentModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        // Ensure the enrollments table exists and has the required columns.
        $this->ensureEnrollmentTable();
    }

    /**
     * Authenticate student using existing students table
     */
    public function authenticateStudent($student_id, $password)
    {
        // Only allow students who are still active in the system.
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE student_id = ? AND status = 'Active'");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return false; // either non‑existent or not active
        }

        // Verify password (hashed or plain as fallback)
        if (password_verify($password, $student['password'])) {
            return $student;
        } elseif ($password === $student['password']) { // plain text fallback
            return $student;
        }

        return false;
    }

    /**
     * Get student details from the existing students table
     */
    public function getStudentDetails($student_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create an enrollment record
     */
    public function createEnrollment($student_id, $academic_year, $semester, $year_level, $program, $subjects = '')
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO enrollments (student_id, academic_year, semester, year_level, program, subjects, enrollment_status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            return $stmt->execute([$student_id, $academic_year, $semester, $year_level, $program, $subjects]);
        } catch (PDOException $e) {
            // in case the table is missing a column we try to fix it
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $this->ensureEnrollmentTable();
                // retry once
                $stmt = $this->pdo->prepare("INSERT INTO enrollments (student_id, academic_year, semester, year_level, program, subjects, enrollment_status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                return $stmt->execute([$student_id, $academic_year, $semester, $year_level, $program, $subjects]);
            }
            throw $e;
        }
    }

    /**
     * Ensure the enrollments table is created and has the necessary columns.
     * If the table is missing a column (academic_year, semester, etc.) we add it.
     */
    private function ensureEnrollmentTable()
    {
        // create table if it doesn't exist (mirrors migrate_enrollments.php)
        $sql = "CREATE TABLE IF NOT EXISTS `enrollments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `student_id` varchar(50) NOT NULL,
          `academic_year` varchar(20) NOT NULL,
          `semester` varchar(30) NOT NULL,
          `year_level` tinyint(1) NOT NULL DEFAULT 1,
          `program` varchar(50) NOT NULL,
          `subjects` TEXT DEFAULT NULL,
          `enrollment_status` enum('Pending','Validated','Approved','Enrolled','Rejected') NOT NULL DEFAULT 'Pending',
          `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->pdo->exec($sql);

        // make sure essential columns are present, add if missing
        $required = [
            // provide sensible defaults to make ALTER TABLE safe on existing rows
            'academic_year' => "varchar(20) NOT NULL DEFAULT ''",
            'semester'      => "varchar(30) NOT NULL DEFAULT ''",
            'year_level'    => "tinyint(1) NOT NULL DEFAULT 1",
            'program'       => "varchar(50) NOT NULL DEFAULT ''",
            'subjects'      => "TEXT DEFAULT NULL",
            'enrollment_status' => "enum('Pending','Validated','Approved','Enrolled','Rejected') NOT NULL DEFAULT 'Pending'",
        ];
        foreach ($required as $col => $definition) {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM enrollments LIKE ?");
            $stmt->execute([$col]);
            if (!$stmt->fetch()) {
                $this->pdo->exec("ALTER TABLE enrollments ADD COLUMN `$col` $definition");
            }
        }
    }

    /**
     * Get enrollment history for a student
     */
    public function getEnrollmentHistory($student_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? ORDER BY enrollment_date DESC");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update student profile information
     */
    public function updateStudentProfile($student_id, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "`$key` = ?";
            $params[] = $value;
        }

        if (empty($fields)) return true;

        $params[] = $student_id;
        $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE student_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
