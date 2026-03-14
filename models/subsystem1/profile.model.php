<?php

class ProfileModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getStudentDetails($studentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }

    public function updateStudentInfo($studentId, $userId, $data, $imagePath = null)
    {
        try {
            // Start transaction
            $this->pdo->beginTransaction();

            // 1. Fetch current data to detect changes
            $currentData = $this->getStudentDetails($studentId);
            if (!$currentData) {
                throw new Exception("Student not found.");
            }

            $fieldsToUpdate = [];
            $params = [];
            $changes = [];

            // Fields that are updatable: contact_number, email_address, address, guardian_name, guardian_relation, guardian_contact, guardian_address, password
            $updatableFields = ['contact_number', 'email_address', 'address', 'guardian_name', 'guardian_relation', 'guardian_contact', 'guardian_address', 'password'];

            foreach ($updatableFields as $field) {
                if (isset($data[$field]) && !empty($data[$field]) && $data[$field] !== $currentData[$field]) {
                    $fieldsToUpdate[] = "$field = ?";
                    $params[] = $data[$field];
                    $changes[] = [
                        'field_changed' => $field,
                        'old_value' => ($field === 'password') ? '[PROTECTED]' : $currentData[$field],
                        'new_value' => ($field === 'password') ? '[PASSWORD UPDATED]' : $data[$field]
                    ];
                }
            }

            // Handle Profile Image Update
            if ($imagePath !== null) {
                $fieldsToUpdate[] = "profile_image = ?";
                $params[] = $imagePath;
                $changes[] = [
                    'field_changed' => 'profile_image',
                    'old_value' => $currentData['profile_image'],
                    'new_value' => $imagePath
                ];
            }

            // Only update if there are changes
            if (!empty($fieldsToUpdate)) {
                $sql = "UPDATE students SET " . implode(", ", $fieldsToUpdate) . " WHERE student_id = ?";
                $params[] = $studentId;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);

                // Insert into tracking history
                $historySql = "INSERT INTO student_updates_history (student_id, updated_by, field_changed, old_value, new_value) VALUES (?, ?, ?, ?, ?)";
                $historyStmt = $this->pdo->prepare($historySql);

                foreach ($changes as $change) {
                    $historyStmt->execute([
                        $studentId,
                        $userId,
                        $change['field_changed'],
                        $change['old_value'],
                        $change['new_value']
                    ]);
                }
            }

            // Commit transaction
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Profile Update Error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentUpdateHistory($studentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM student_updates_history WHERE student_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
