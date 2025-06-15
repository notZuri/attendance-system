<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require_once __DIR__ . '/../config/config.php';

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID.']);
    exit();
}

$student_id = intval($data['student_id']);

try {
    // Check if student exists
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
        exit();
    }

    // Simulate RFID enrollment process
    // For example, you might receive the RFID tag from the hardware or request it here.
    // Since this is a simulation, let's assume we get a unique RFID tag.

    // You can replace this with actual logic to communicate with hardware or read RFID tag.
    $rfid_tag = 'RFID' . str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);

    // Save RFID tag to student record
    $update = $pdo->prepare("UPDATE students SET rfid_tag = ? WHERE id = ?");
    $update->execute([$rfid_tag, $student_id]);

    echo json_encode(['success' => true, 'message' => 'RFID enrolled successfully with tag: ' . $rfid_tag]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
