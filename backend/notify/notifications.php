<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id'])) {
    send_json_response(403, ['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch notifications for the logged-in user
    $stmt = $pdo->prepare('SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    send_json_response(200, ['success' => true, 'notifications' => $notifications]);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notif_id = (int)($input['id'] ?? 0);
    if (!$notif_id) {
        send_json_response(400, ['success' => false, 'error' => 'Missing notification id.']);
        exit();
    }
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$notif_id, $user_id]);
    send_json_response(200, ['success' => true]);
    exit();
}
send_json_response(405, ['success' => false, 'error' => 'Method not allowed']); 