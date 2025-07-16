<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  send_json_response(403, ['error' => 'Unauthorized']);
  exit();
}
$professor_id = $_SESSION['user_id'];

$content = file_get_contents('php://input');
$data = json_decode($content, true) ?? [];
$id = $data['id'] ?? '';

if (!$id) {
  send_json_response(400, ['error' => 'Missing ID.']);
  exit();
}

try {
  $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ? AND professor_id = ?");
  $stmt->execute([(int)$id, $professor_id]);
  if ($stmt->rowCount() > 0) {
    send_json_response(200, ['success' => true, 'message' => 'Deleted.']);
  } else {
    send_json_response(404, ['error' => 'Schedule not found or not deleted.']);
  }
} catch (PDOException $e) {
  send_json_response(500, ['error' => 'Error deleting: ' . $e->getMessage()]);
}
