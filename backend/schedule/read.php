<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  http_response_code(403);
  echo json_encode([]);
  exit();
}
$professor_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
  SELECT id, subject, day_of_week, start_time, end_time 
  FROM schedules 
  WHERE professor_id = ? 
  ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time
");
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
  $schedules[] = $row;
}

echo json_encode($schedules);
$stmt->close();
$conn->close();
?>
