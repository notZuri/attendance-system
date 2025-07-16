<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'User not found in session.']);
    exit();
}

try {
    // RFID status
    $stmt = $pdo->prepare('SELECT card_uid FROM rfid_cards WHERE user_id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $rfid = $stmt->fetch(PDO::FETCH_ASSOC);
    $rfid_status = $rfid ? 'enrolled' : 'not_enrolled';
    $rfid_id = $rfid['card_uid'] ?? null;

    // Fingerprint status
    $stmt = $pdo->prepare('SELECT template_id FROM fingerprint_templates WHERE user_id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $fp = $stmt->fetch(PDO::FETCH_ASSOC);
    $fp_status = $fp ? 'enrolled' : 'not_enrolled';
    $fp_id = $fp['template_id'] ?? null;

    echo json_encode([
        'success' => true,
        'rfid' => [
            'status' => $rfid_status,
            'id' => $rfid_id
        ],
        'fingerprint' => [
            'status' => $fp_status,
            'id' => $fp_id
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
} 