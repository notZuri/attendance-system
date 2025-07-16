<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  send_json_response(403, ['error' => 'Unauthorized']);
  exit();
}
$professor_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('
  SELECT s.subject AS class_name, u.name AS student_name, u.student_number, a.attendance_date AS date, a.status, a.time_in, a.created_at
  FROM attendance a
  JOIN users u ON a.user_id = u.id
  JOIN schedules s ON a.schedule_id = s.id
  WHERE s.professor_id = ? AND u.role = "student"
  ORDER BY a.attendance_date DESC, a.id DESC
  LIMIT 10
');
$stmt->execute([$professor_id]);
$attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add 12-hour formatted time and formatted date for display
foreach ($attendance as &$row) {
    $row['time_in_12h'] = $row['time_in'] ? date('g:i A', strtotime($row['time_in'])) : null;
    $row['created_at_12h'] = $row['created_at'] ? date('g:i A', strtotime($row['created_at'])) : null;
    $row['attendance_date_formatted'] = $row['date'] ? date('M j, Y', strtotime($row['date'])) : null;
}
unset($row);

send_json_response(200, ['success' => true, 'attendance' => $attendance]); 