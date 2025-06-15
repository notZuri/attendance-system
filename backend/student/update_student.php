<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$id = $_POST['id'] ?? null;
$full_name = trim($_POST['full_name'] ?? '');
$student_number = trim($_POST['student_number'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Validate required fields
if (!$id || !$full_name || !$student_number || !$email || !$phone) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Optional: Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Prepare and execute update
$stmt = $pdo->prepare("UPDATE students SET full_name = ?, student_number = ?, email = ?, phone = ? WHERE id = ?");
$success = $stmt->execute([$full_name, $student_number, $email, $phone, $id]);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update student']);
}
