<?php
/**
 * Real-time Attendance Recording API
 * Handles automatic attendance recording via RFID and fingerprint
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/auto_record.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $method = $input['method'] ?? '';
    $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
    $deviceInfo = $input['device_info'] ?? [];
    
    $autoRecorder = new AutoAttendanceRecorder($pdo);
    $result = null;
    
    switch ($method) {
        case 'rfid':
            $cardUid = $input['card_uid'] ?? '';
            if (!$cardUid) {
                throw new Exception('Missing RFID card UID');
            }
            $result = $autoRecorder->recordRFIDAttendance($cardUid, $timestamp);
            break;
            
        case 'fingerprint':
            $templateId = $input['template_id'] ?? '';
            if (!$templateId) {
                throw new Exception('Missing fingerprint template ID');
            }
            $result = $autoRecorder->recordFingerprintAttendance($templateId, $timestamp);
            break;
            
        default:
            throw new Exception('Invalid method. Use "rfid" or "fingerprint"');
    }
    
    // Log the attendance attempt
    $logData = [
        'method' => $method,
        'timestamp' => $timestamp,
        'device_info' => $deviceInfo,
        'result' => $result,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    error_log('Attendance recording attempt: ' . json_encode($logData));
    
    // Return the result
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['data'],
            'timestamp' => $timestamp
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $result['message'],
            'timestamp' => $timestamp
        ]);
    }
    
} catch (Exception $e) {
    error_log('Attendance recording error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
} 