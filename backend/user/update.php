<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/validation.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized: not logged in.');
    }
    $userId = (int)$_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);

    $fieldsToUpdate = [];

    // Allow updating name, email, and phone
    if (isset($data['name'])) {
        $name = trim($data['name']);
        if ($name === '') {
            throw new InvalidArgumentException('Name cannot be empty.');
        }
        $fieldsToUpdate['name'] = $name;
    }

    if (isset($data['email'])) {
        $email = strtolower(trim($data['email']));
        if (!isValidEmail($email)) {
            throw new InvalidArgumentException('Invalid email format.');
        }
        // Check if email belongs to another user
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
        $stmt->execute([':email' => $email, ':id' => $userId]);
        if ($stmt->fetch()) {
            throw new RuntimeException('Email already in use by another account.');
        }
        $fieldsToUpdate['email'] = $email;
    }

    if (isset($data['phone'])) {
        $phone = trim($data['phone']);
        // Remove non-digit characters for validation
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneDigits) < 10) {
            throw new InvalidArgumentException('Phone must be at least 10 digits.');
        }
        $fieldsToUpdate['phone'] = $phone; // Store the original formatted phone number
    }

    // Password change logic
    if (isset($data['current_password']) && isset($data['new_password']) && isset($data['confirm_password']) &&
        $data['current_password'] !== '' && $data['new_password'] !== '' && $data['confirm_password'] !== '') {
        $currentPassword = $data['current_password'];
        $newPassword = $data['new_password'];
        $confirmPassword = $data['confirm_password'];
        // Fetch current password hash
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            throw new InvalidArgumentException('Current password is incorrect.');
        }
        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException('New password and confirm password do not match.');
        }
        if (!isStrongPassword($newPassword)) {
            throw new InvalidArgumentException('New password must be at least 6 characters.');
        }
        $fieldsToUpdate['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    if (empty($fieldsToUpdate)) {
        throw new InvalidArgumentException('No valid fields provided for update.');
    }

    $setParts = [];
    $params = [];
    foreach ($fieldsToUpdate as $field => $value) {
        $setParts[] = "$field = :$field";
        $params[":$field"] = $value;
    }
    $params[':id'] = $userId;

    $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Notify professor(s) if a student updates their profile
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && (isset($fieldsToUpdate['name']) || isset($fieldsToUpdate['email']) || isset($fieldsToUpdate['phone']))) {
        // Find all professors associated with the student's schedules
        $stmt = $pdo->prepare('SELECT DISTINCT s.professor_id FROM schedules s INNER JOIN attendance a ON a.schedule_id = s.id WHERE a.user_id = ?');
        $stmt->execute([$userId]);
        $professors = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$professors || count($professors) === 0) {
            // Fallback: assign to all professors
            $professors = $pdo->query("SELECT id FROM users WHERE role = 'professor'")->fetchAll(PDO::FETCH_COLUMN);
        }
        $studentData = $pdo->prepare('SELECT name, student_number FROM users WHERE id = ?');
        $studentData->execute([$userId]);
        $s = $studentData->fetch();
        $title = 'Student Profile Updated';
        $message = 'Student ' . htmlspecialchars($s['name']) . ' (' . htmlspecialchars($s['student_number']) . ') updated their profile.';
        foreach ($professors as $profId) {
            $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
            $notif->execute([$profId, $title, $message]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
