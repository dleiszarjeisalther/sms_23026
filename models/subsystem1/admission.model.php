<?php

require_once __DIR__ . '/../config.php';

class AdmissionModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function generateStudentId()
    {
        // New ID generation: YYYY-XXX (where XXX is a progressive number)
        $year = date('Y');

        $stmt = $this->db->prepare("SELECT student_id FROM students WHERE student_id LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => "$year-%"]);
        $lastStudent = $stmt->fetch();

        if ($lastStudent && isset($lastStudent['student_id'])) {
            $parts = explode('-', $lastStudent['student_id']);
            $lastNumber = intval($parts[1]);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            return "$year-$newNumber";
        } else {
            return "$year-001";
        }
    }

    private function generateStrongPassword($length = 8)
    {
        // Ensure at least one lowercase, one uppercase, one number, and one special character
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*';

        $password = $lower[rand(0, strlen($lower) - 1)] .
            $upper[rand(0, strlen($upper) - 1)] .
            $numbers[rand(0, strlen($numbers) - 1)] .
            $special[rand(0, strlen($special) - 1)];

        $all = $lower . $upper . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    public function registerStudent($data)
    {
        // Generate an ID first
        $studentId = $this->generateStudentId();

        // Generate a strong raw password
        $rawPassword = $this->generateStrongPassword();
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        $query = "INSERT INTO students (
                    student_id, 
                    first_name, 
                    last_name, 
                    date_of_birth, 
                    gender, 
                    program, 
                    contact_number, 
                    email_address, 
                    address, 
                    guardian_name, 
                    guardian_relation, 
                    guardian_contact,
                    guardian_address,
                    password
                  ) VALUES (
                    :student_id, 
                    :first_name, 
                    :last_name, 
                    :date_of_birth, 
                    :gender, 
                    :program, 
                    :contact_number, 
                    :email_address, 
                    :address, 
                    :guardian_name, 
                    :guardian_relation,
                    :guardian_contact,
                    :guardian_address,
                    :password
                  )";

        $stmt = $this->db->prepare($query);

        // Bind parameters
        $stmt->bindParam(':student_id', $studentId);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':program', $data['program']);
        $stmt->bindParam(':contact_number', $data['contact_number']);
        $stmt->bindParam(':email_address', $data['email_address']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':guardian_name', $data['guardian_name']);
        $stmt->bindParam(':guardian_relation', $data['guardian_relation']);
        $stmt->bindParam(':guardian_contact', $data['guardian_contact']);
        $stmt->bindParam(':guardian_address', $data['guardian_address']);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            return [
                'student_id' => $studentId,
                'raw_password' => $rawPassword
            ]; // Return credentials structure on success
        }

        return false;
    }
}
