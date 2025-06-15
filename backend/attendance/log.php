<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../auth/auth_helpers.php';

session_start();

if (!isUserLoggedIn() || !userHasRole(['professor', 'student'])) {
    sendJsonResponse(401, ['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(405, ['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['user_id']) || empty($input['method']) || empty($input['scan_value'])) {
    sendJsonResponse(400, ['error' => 'Missing required fields']);
    exit;
}

$userId = (int)$input['user_id'];
$method = trim($input['method']);          // 'rfid' or 'fingerprint'
$scanValue = trim($input['scan_value']);

if (!in_array($method, ['rfid', 'fingerprint'], true)) {
    sendJsonResponse(400, ['error' => 'Invalid attendance method']);
    exit;
}

try {
    // Verify scanned value belongs to the user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :user_id AND {$method}_id = :scan_value LIMIT 1");
    $stmt->execute([':user_id' => $userId, ':scan_value' => $scanValue]);
    if (!$stmt->fetch()) {
        sendJsonResponse(403, ['error' => 'Scan value does not match user']);
        exit;
    }

    // Insert attendance log
    $stmt = $pdo->prepare('INSERT INTO attendance (user_id, method, scan_value, timestamp) VALUES (:user_id, :method, :scan_value, NOW())');
    $stmt->execute([
        ':user_id' => $userId,
        ':method' => $method,
        ':scan_value' => $scanValue,
    ]);

    // TODO: Trigger asynchronous Gmail notification here

    sendJsonResponse(201, ['message' => 'Attendance logged successfully']);
} catch (PDOException $e) {
    error_log('Attendance log error: ' . $e->getMessage());
    sendJsonResponse(500, ['error' => 'Internal server error']);
}
