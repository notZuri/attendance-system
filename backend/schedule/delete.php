<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  http_response_code(403);
  echo "Unauthorized";
  exit();
}
$professor_id = $_SESSION['user_id'];

$content = file_get_contents('php://input');
$data = json_decode($content, true) ?? [];
$id = $data['id'] ?? '';

if (!$id) {
  http_response_code(400);
  echo "Missing ID.";
  exit();
}

$stmt = $conn->prepare("DELETE FROM schedules WHERE id = ? AND professor_id = ?");
$stmt->bind_param("ii", (int)$id, $professor_id);

if ($stmt->execute()) {
  echo "Deleted";
} else {
  http_response_code(500);
  echo "Error deleting.";
}

$stmt->close();
$conn->close();
?>
