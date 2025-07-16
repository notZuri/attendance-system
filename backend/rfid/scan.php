<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$session_id = $data['session_id'] ?? null;
$card_uid = $data['scanned_id'] ?? null;

if (!$session_id || !$card_uid) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

try {
    // Get session and user
    $stmt = $pdo->prepare('SELECT * FROM enrollment_sessions WHERE id = ? AND type = "rfid" AND status = "waiting"');
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$session) {
        http_response_code(404);
        echo json_encode(['error' => 'RFID enrollment session not found or already completed']);
        exit;
    }
    $user_id = $session['user_id'];
    // Prevent duplicate card_uid
    $check = $pdo->prepare('SELECT id FROM rfid_cards WHERE card_uid = ?');
    $check->execute([$card_uid]);
    if ($check->fetch()) {
        $pdo->prepare('UPDATE enrollment_sessions SET status = "error", scanned_id = ? WHERE id = ?')->execute([$card_uid, $session_id]);
        echo json_encode(['error' => 'RFID card already enrolled.']);
        exit;
    }
    // Insert into rfid_cards
    $insert = $pdo->prepare('INSERT INTO rfid_cards (user_id, card_uid) VALUES (?, ?)');
    $insert->execute([$user_id, $card_uid]);
    // Update session
    $pdo->prepare('UPDATE enrollment_sessions SET scanned_id = ?, status = "success", completed_at = NOW() WHERE id = ?')->execute([$card_uid, $session_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
