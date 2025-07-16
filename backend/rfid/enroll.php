<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require_once __DIR__ . '/../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID.']);
    exit();
}
$student_id = intval($data['student_id']);

try {
    // Check if student exists in users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
        exit();
    }
    // Create enrollment session
    $insert = $pdo->prepare("INSERT INTO enrollment_sessions (user_id, type) VALUES (?, 'rfid')");
    $insert->execute([$student_id]);
    $session_id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'session_id' => $session_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
