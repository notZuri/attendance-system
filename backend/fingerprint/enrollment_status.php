<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/config.php';

$session_id = $_GET['session_id'] ?? null;
if (!$session_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing session_id']);
    exit;
}
$stmt = $pdo->prepare('SELECT status, scanned_id FROM enrollment_sessions WHERE id = ? AND type = "fingerprint"');
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$session) {
    echo json_encode(['status' => 'error', 'message' => 'Session not found']);
    exit;
}
if ($session['status'] === 'success') {
    echo json_encode(['status' => 'success', 'scanned_id' => $session['scanned_id']]);
} elseif ($session['status'] === 'error') {
    echo json_encode(['status' => 'error', 'message' => $session['scanned_id']]);
} else {
    echo json_encode(['status' => 'waiting']);
} 