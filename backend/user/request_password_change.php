<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/validation.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
        throw new Exception('Unauthorized: not logged in as student.');
    }
    $studentId = (int)$_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    if (!$currentPassword || !$newPassword || !$confirmPassword) {
        throw new Exception('All password fields are required.');
    }
    // Validate current password
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$studentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
        throw new Exception('Current password is incorrect.');
    }
    if ($newPassword !== $confirmPassword) {
        throw new Exception('New password and confirm password do not match.');
    }
    if (!isStrongPassword($newPassword)) {
        throw new Exception('New password must be at least 6 characters.');
    }
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    // Find the student's professor(s)
    $stmt = $pdo->prepare('SELECT professor_id FROM schedules WHERE id = (SELECT schedule_id FROM attendance WHERE user_id = ? LIMIT 1)');
    $stmt->execute([$studentId]);
    $professorId = $stmt->fetchColumn();
    if (!$professorId) {
        // Fallback: assign to all professors
        $professors = $pdo->query("SELECT id FROM users WHERE role = 'professor'")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($professors)) {
            error_log('Password change request failed: No professors found for student ID ' . $studentId);
            throw new Exception('No professors found in the system. Please contact admin.');
        }
    } else {
        $professors = [$professorId];
    }
    // Insert password change request for each professor
    foreach ($professors as $profId) {
        $stmt = $pdo->prepare('INSERT INTO password_change_requests (student_id, professor_id, new_password_hash, status) VALUES (?, ?, ?, "pending")');
        $stmt->execute([$studentId, $profId, $newHash]);
        // Create notification for professor
        $student = $pdo->prepare('SELECT name, student_number FROM users WHERE id = ?')->execute([$studentId]);
        $studentData = $pdo->prepare('SELECT name, student_number FROM users WHERE id = ?');
        $studentData->execute([$studentId]);
        $s = $studentData->fetch();
        $title = 'Password Change Request';
        $message = 'Student ' . htmlspecialchars($s['name']) . ' (' . htmlspecialchars($s['student_number']) . ') has requested a password change.';
        $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
        $notif->execute([$profId, $title, $message]);
    }
    echo json_encode(['success' => true, 'message' => 'Password change request submitted. Waiting for professor approval.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 