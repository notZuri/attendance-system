<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id'])) {
  send_json_response(403, ['error' => 'Unauthorized']);
  exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch profile for the logged-in user (professor or student)
$stmt = $pdo->prepare('SELECT id, name, email, phone, student_number, role FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile) {
  send_json_response(200, ['success' => true, 'user' => $profile]);
} else {
  send_json_response(404, ['success' => false, 'error' => 'Profile not found']);
} 