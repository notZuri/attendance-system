<?php
/**
 * Hardware Status Monitoring API
 * Manages device connections, status updates, and monitoring
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetStatus();
            break;
            
        case 'POST':
            handleUpdateStatus();
            break;
            
        case 'PUT':
            handleHeartbeat();
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Hardware status error: ' . $e->getMessage());
    error_log('Params: ' . json_encode($_REQUEST));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleGetStatus() {
    global $pdo;
    
    $deviceId = $_GET['device_id'] ?? null;
    
    if ($deviceId) {
        // Get specific device status
        $stmt = $pdo->prepare("
            SELECT * FROM hardware_sessions 
            WHERE device_id = ?
            ORDER BY updated_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$deviceId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($device) {
            echo json_encode([
                'success' => true,
                'data' => $device
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Device not found'
            ]);
        }
    } else {
        // Get all devices
        $stmt = $pdo->query("
            SELECT * FROM hardware_sessions 
            ORDER BY updated_at DESC
        ");
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $devices
        ]);
    }
}

function handleUpdateStatus() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $deviceId = $input['device_id'] ?? null;
    $status = $input['status'] ?? null;
    $message = $input['message'] ?? '';
    $deviceInfo = $input['device_info'] ?? [];
    
    if (!$deviceId || !$status) {
        throw new Exception('Missing required fields: device_id, status');
    }
    
    // Update or insert device status
    $stmt = $pdo->prepare("
        INSERT INTO hardware_sessions 
        (device_id, device_type, location, status, ip_address, mac_address, firmware_version, last_heartbeat, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
        status = VALUES(status),
        location = VALUES(location),
        ip_address = VALUES(ip_address),
        mac_address = VALUES(mac_address),
        firmware_version = VALUES(firmware_version),
        last_heartbeat = NOW(),
        updated_at = NOW()
    ");
    
    $stmt->execute([
        $deviceId,
        $deviceInfo['device_type'] ?? 'combined',
        $deviceInfo['location'] ?? null,
        $status,
        $deviceInfo['ip_address'] ?? null,
        $deviceInfo['mac_address'] ?? null,
        $deviceInfo['firmware_version'] ?? null
    ]);
    
    // Log the status update
    $stmt = $pdo->prepare("
        INSERT INTO attendance_logs 
        (action, method, device_info, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        'hardware_status_update',
        'hardware',
        json_encode([
            'device_id' => $deviceId,
            'status' => $status,
            'message' => $message,
            'device_info' => $deviceInfo
        ])
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'data' => [
            'device_id' => $deviceId,
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
}

function handleHeartbeat() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $deviceId = $input['device_id'] ?? null;
    
    if (!$deviceId) {
        throw new Exception('Missing device_id');
    }
    
    // Update heartbeat timestamp
    $stmt = $pdo->prepare("
        UPDATE hardware_sessions 
        SET last_heartbeat = NOW(), updated_at = NOW()
        WHERE device_id = ?
    ");
    
    $result = $stmt->execute([$deviceId]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Heartbeat received',
            'data' => [
                'device_id' => $deviceId,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Device not found'
        ]);
    }
} 