<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/models/config.php';

try {
    // Add profile_image column if it doesn't exist
    $pdo->exec("ALTER TABLE students ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL;");
    echo "Added profile_image column.\n";
} catch (PDOException $e) {
    echo "Column profile_image may already exist: " . $e->getMessage() . "\n";
}

try {
    // Create student_updates_history table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS student_updates_history (
          id INT(11) NOT NULL AUTO_INCREMENT,
          student_id VARCHAR(50) NOT NULL,
          updated_by VARCHAR(50) NOT NULL,
          field_changed VARCHAR(100) NOT NULL,
          old_value TEXT,
          new_value TEXT,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY(id),
          FOREIGN KEY(student_id) REFERENCES students(student_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Created student_updates_history table.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}

try {
    // Add guardian_contact column if it doesn't exist
    $pdo->exec("ALTER TABLE students ADD COLUMN guardian_contact VARCHAR(20) DEFAULT NULL AFTER guardian_address;");
    echo "Added guardian_contact column.\n";
} catch (PDOException $e) {
    echo "Column guardian_contact may already exist: " . $e->getMessage() . "\n";
}

try {
    // Add status and section columns
    $pdo->exec("ALTER TABLE students ADD COLUMN status ENUM('Active', 'Dropped', 'Graduated', 'On Leave') DEFAULT 'Active'");
    echo "Added status column.\n";
} catch (PDOException $e) {
    echo "Status column may exist: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE students ADD COLUMN section VARCHAR(50) DEFAULT NULL");
    echo "Added section column.\n";
} catch (PDOException $e) {
    echo "Section column may exist: " . $e->getMessage() . "\n";
}

// Bulk Seeder for Students
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $count = $stmt->fetchColumn();

    if ($count < 50) {
        echo "Seeding 50+ students...\n";

        $firstNames = ['James', 'Mary', 'Robert', 'Patricia', 'John', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
        $programs = ['BS Computer Science', 'BS Information Technology'];
        $sections = ['1A', '1B', '2A', '2B', '3A', '4A'];
        $statuses = ['Active', 'Dropped', 'Graduated', 'Active', 'Active']; // Weighted towards Active

        $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, gender, program, contact_number, address, guardian_name, guardian_contact, guardian_address, status, section, password) VALUES (:student_id, :first_name, :last_name, :gender, :program, :contact_number, :address, :guardian_name, :guardian_contact, :guardian_address, :status, :section, :password)");

        for ($i = 1; $i <= 55; $i++) {
            $year = 2026;
            $id = $year . '-' . str_pad($i + 10, 3, '0', STR_PAD_LEFT);
            $fname = $firstNames[array_rand($firstNames)];
            $lname = $lastNames[array_rand($lastNames)];
            $gender = (rand(0, 1) == 0) ? 'Male' : 'Female';
            $prog = $programs[array_rand($programs)];
            $sec = $sections[array_rand($sections)];
            $status = $statuses[array_rand($statuses)];
            $pass = password_hash('password123', PASSWORD_DEFAULT);

            try {
                $stmt->execute([
                    'student_id' => $id,
                    'first_name' => $fname,
                    'last_name' => $lname,
                    'gender' => $gender,
                    'program' => $prog,
                    'contact_number' => '09' . rand(100000000, 999999999),
                    'address' => rand(1, 999) . ' Example St, City',
                    'guardian_name' => 'Guardian of ' . $fname,
                    'guardian_contact' => '09' . rand(100000000, 999999999),
                    'guardian_address' => rand(1, 999) . ' Example St, City',
                    'status' => $status,
                    'section' => $sec,
                    'password' => $pass
                ]);
            } catch (PDOException $e) {
                // Skip duplicates
            }
        }
        echo "Seeding complete.\n";
    }
} catch (PDOException $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
}

try {
    // Create registrars table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `registrars` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `registrar_id` varchar(50) NOT NULL UNIQUE,
          `name` varchar(150) NOT NULL,
          `password` varchar(255) NOT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Created registrars table.\n";

    // Seed default admin account
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrars WHERE registrar_id = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO registrars (registrar_id, name, password) VALUES ('admin', 'System Registrar', :password)");
        $insert->execute(['password' => $hashedPassword]);
        echo "Seeded default registrar account (admin / admin123).\n";
    }
} catch (PDOException $e) {
    echo "Error with registrars table/seed: " . $e->getMessage() . "\n";
}
