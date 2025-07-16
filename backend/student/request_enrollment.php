<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User not found in session.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$message = $data['message'] ?? '';

if (!in_array($type, ['rfid', 'fingerprint'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid enrollment type.']);
    exit();
}

try {
    // Prevent duplicate pending requests
    $stmt = $pdo->prepare('SELECT id FROM enrollment_requests WHERE user_id = ? AND type = ? AND status = "pending"');
    $stmt->execute([$user_id, $type]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'You already have a pending request for this enrollment type.']);
        exit();
    }
    // Insert request
    $insert = $pdo->prepare('INSERT INTO enrollment_requests (user_id, type, message, status, requested_at) VALUES (?, ?, ?, "pending", NOW())');
    $insert->execute([$user_id, $type, $message]);

    // Notify all professors
    $student_stmt = $pdo->prepare('SELECT name FROM users WHERE id = ?');
    $student_stmt->execute([$user_id]);
    $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
    $student_name = $student ? $student['name'] : 'A student';
    $notif_title = 'New Enrollment Request';
    $notif_message = $student_name . ' requested ' . strtoupper($type) . ' enrollment.';
    $notif_link = '/attendance-system/frontend/professor/students.php';
    $prof_stmt = $pdo->query("SELECT id FROM users WHERE role = 'professor'");
    $professors = $prof_stmt->fetchAll(PDO::FETCH_ASSOC);
    $notif_insert = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())');
    foreach ($professors as $prof) {
        $notif_insert->execute([$prof['id'], 'enrollment_request', $notif_title, $notif_message, $notif_link]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
} 