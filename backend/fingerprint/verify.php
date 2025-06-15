<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../attendance/log.php';  // attendance log function
require_once __DIR__ . '/../utils/response.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['fingerprint_hash'])) {
    sendJsonResponse(400, ['error' => 'Fingerprint data missing']);
}

$fingerprintHash = trim($input['fingerprint_hash']);

try {
    $studentId = getStudentIdByFingerprint($pdo, $fingerprintHash);
    if ($studentId === null) {
        sendJsonResponse(404, ['error' => 'Fingerprint not recognized']);
    }

    // Log attendance
    $result = logAttendance($pdo, $studentId, 'fingerprint');

    if ($result) {
        sendJsonResponse(200, ['message' => 'Attendance logged successfully']);
    } else {
        sendJsonResponse(500, ['error' => 'Failed to log attendance']);
    }
} catch (PDOException $e) {
    sendJsonResponse(500, ['error' => 'Server error: ' . $e->getMessage()]);
}
