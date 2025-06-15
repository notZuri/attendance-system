<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  http_response_code(403);
  echo "Unauthorized";
  exit();
}
$professor_id = $_SESSION['user_id'];

$id = $_POST['schedule_id'] ?? '';
$subject = trim($_POST['subject'] ?? '');
$day_of_week = $_POST['day_of_week'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

if (!$subject || !$day_of_week || !$start_time || !$end_time) {
  http_response_code(400);
  echo "Missing required fields.";
  exit();
}

if ($id === '') {
  $stmt = $conn->prepare("INSERT INTO schedules (professor_id, subject, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("issss", $professor_id, $subject, $day_of_week, $start_time, $end_time);
} else {
  $stmt = $conn->prepare("UPDATE schedules SET subject = ?, day_of_week = ?, start_time = ?, end_time = ? WHERE id = ? AND professor_id = ?");
  $stmt->bind_param("ssssii", $subject, $day_of_week, $start_time, $end_time, (int)$id, $professor_id);
}

if ($stmt->execute()) {
  echo "success";
} else {
  http_response_code(500);
  echo "Database error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
