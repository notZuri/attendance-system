<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../attendance/log.php';  // attendance log function
require_once __DIR__ . '/../utils/response.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['fingerprint_hash'])) {
    send_json_response(400, ['error' => 'Fingerprint data missing']);
}

$fingerprintHash = trim($input['fingerprint_hash']);

try {
    $studentId = getStudentIdByFingerprint($pdo, $fingerprintHash);
    if ($studentId === null) {
        send_json_response(404, ['error' => 'Fingerprint not recognized']);
    }

    // Log attendance
    $result = logAttendance($pdo, $studentId, 'fingerprint');

    if ($result) {
        send_json_response(200, ['message' => 'Attendance logged successfully']);
    } else {
        send_json_response(500, ['error' => 'Failed to log attendance']);
    }
} catch (PDOException $e) {
    send_json_response(500, ['error' => 'Server error: ' . $e->getMessage()]);
}
