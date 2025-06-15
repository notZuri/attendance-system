<?php
header('Content-Type: application/json');
include '../../attendance_system.php';

$data = json_decode(file_get_contents('php://input'), true);

$session_id = $data['session_id'] ?? null;
$scanned_id = $data['scanned_id'] ?? null;

if (!$session_id || !$scanned_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("UPDATE enrollment_sessions SET scanned_id = ?, status = 'success' WHERE session_id = ? AND status = 'waiting'");
$stmt->bind_param("si", $scanned_id, $session_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $stmt2 = $conn->prepare("SELECT student_id FROM enrollment_sessions WHERE session_id = ?");
    $stmt2->bind_param("i", $session_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    if ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];
        $stmt3 = $conn->prepare("UPDATE students SET fingerprint_id = ? WHERE student_id = ?");
        $stmt3->bind_param("si", $scanned_id, $student_id);
        $stmt3->execute();
        $stmt3->close();
    }
    $stmt2->close();

    echo json_encode(['success' => true]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Fingerprint enrollment session not found or already completed']);
}

$stmt->close();
$conn->close();
