<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    send_json_response(403, ['success' => false, 'error' => 'Unauthorized - Professors only']);
    exit();
}

$professor_id = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$attendance_id = isset($input['attendance_id']) ? (int)$input['attendance_id'] : 0;
$status = isset($input['status']) ? $input['status'] : null;
$time_in = isset($input['time_in']) ? $input['time_in'] : null;

if (!$attendance_id || (!$status && !$time_in)) {
    send_json_response(400, ['success' => false, 'error' => 'Missing required fields.']);
    exit();
}

if ($status && !in_array($status, ['present', 'late', 'absent'])) {
    send_json_response(400, ['success' => false, 'error' => 'Invalid status value.']);
    exit();
}

try {
    // Ensure the attendance record belongs to a schedule owned by this professor
    $stmt = $pdo->prepare('
        SELECT a.id FROM attendance a
        JOIN schedules s ON a.schedule_id = s.id
        WHERE a.id = :attendance_id AND s.professor_id = :professor_id
    ');
    $stmt->execute([':attendance_id' => $attendance_id, ':professor_id' => $professor_id]);
    $record = $stmt->fetch();
    if (!$record) {
        send_json_response(403, ['success' => false, 'error' => 'Unauthorized or record not found.']);
        exit();
    }
    // Build update query
    $fields = [];
    $params = [':id' => $attendance_id];
    if ($status) {
        $fields[] = 'status = :status';
        $params[':status'] = $status;
    }
    if ($time_in) {
        $fields[] = 'time_in = :time_in';
        $params[':time_in'] = $time_in;
    }
    if (empty($fields)) {
        send_json_response(400, ['success' => false, 'error' => 'No fields to update.']);
        exit();
    }
    $sql = 'UPDATE attendance SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    send_json_response(200, ['success' => true]);
} catch (PDOException $e) {
    error_log('Update attendance error: ' . $e->getMessage());
    send_json_response(500, ['success' => false, 'error' => 'Database error']);
} 