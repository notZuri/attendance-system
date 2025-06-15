<?php
header('Content-Type: application/json');
include '../../attendance_system.php';

$data = json_decode(file_get_contents('php://input'), true);

$student_id = $data['student_id'] ?? null;
$type = $data['type'] ?? null;

if (!$student_id || $type !== 'fingerprint') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input for Fingerprint enrollment']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO enrollment_sessions (student_id, type) VALUES (?, ?)");
$stmt->bind_param("is", $student_id, $type);
if ($stmt->execute()) {
    echo json_encode(['session_id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to start Fingerprint enrollment session']);
}
$stmt->close();
$conn->close();
