<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$session_id = $data['session_id'] ?? null;
$template_id = $data['scanned_id'] ?? null;
$template_data = $data['template_data'] ?? null; // Optional, for future use

if (!$session_id || !$template_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

try {
    // Get session and user
    $stmt = $pdo->prepare('SELECT * FROM enrollment_sessions WHERE id = ? AND type = "fingerprint" AND status = "waiting"');
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$session) {
        http_response_code(404);
        echo json_encode(['error' => 'Fingerprint enrollment session not found or already completed']);
        exit;
    }
    $user_id = $session['user_id'];
    // Prevent duplicate template_id
    $check = $pdo->prepare('SELECT id FROM fingerprint_templates WHERE template_id = ?');
    $check->execute([$template_id]);
    if ($check->fetch()) {
        $pdo->prepare('UPDATE enrollment_sessions SET status = "error", scanned_id = ? WHERE id = ?')->execute([$template_id, $session_id]);
        echo json_encode(['error' => 'Fingerprint already enrolled.']);
        exit;
    }
    // Insert into fingerprint_templates
    $insert = $pdo->prepare('INSERT INTO fingerprint_templates (user_id, template_id, template_data) VALUES (?, ?, ?)');
    $insert->execute([$user_id, $template_id, $template_data ?? '']);
    // Update session
    $pdo->prepare('UPDATE enrollment_sessions SET scanned_id = ?, status = "success", completed_at = NOW() WHERE id = ?')->execute([$template_id, $session_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
