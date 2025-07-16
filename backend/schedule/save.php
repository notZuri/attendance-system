<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  send_json_response(403, ['error' => 'Unauthorized']);
  exit();
}
$professor_id = $_SESSION['user_id'];

$id = $_POST['schedule_id'] ?? '';
$subject = trim($_POST['subject'] ?? '');
$room = trim($_POST['room'] ?? '');
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$late_threshold = (int)($_POST['late_threshold'] ?? 10); // Default 10 minutes

if (!$subject || !$room || !$date || !$start_time || !$end_time) {
  send_json_response(400, ['error' => 'Missing required fields.']);
  exit();
}
if ($late_threshold < 0 || $late_threshold > 120) {
  send_json_response(400, ['error' => 'Late threshold must be between 0 and 120 minutes.']);
  exit();
}

try {
  if ($id === '') {
    // Duplicate check
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE professor_id = ? AND subject = ? AND room = ? AND date = ? AND start_time = ? AND end_time = ?");
    $stmt->execute([$professor_id, $subject, $room, $date, $start_time, $end_time]);
    if ($stmt->fetchColumn() > 0) {
      send_json_response(409, ['error' => 'A schedule with the same details already exists.']);
      exit();
    }
    
    // Insert new schedule with initial status
    $stmt = $pdo->prepare("
      INSERT INTO schedules (professor_id, subject, room, date, start_time, end_time, late_threshold, is_active, current_status) 
      VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'pending')
    ");
    $stmt->execute([$professor_id, $subject, $room, $date, $start_time, $end_time, $late_threshold]);
    
    $schedule_id = $pdo->lastInsertId();
    
    // Notify all students
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'student'");
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $title = 'New Schedule Added';
    $message = 'A new schedule for ' . htmlspecialchars($subject) . ' on ' . htmlspecialchars($date) . ' has been added.';
    foreach ($students as $student_id) {
      $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
      $notif->execute([$student_id, $title, $message]);
    }
    send_json_response(200, [
      'success' => true, 
      'message' => 'Schedule created successfully',
      'schedule_id' => $schedule_id
    ]);
    
  } else {
    // Update existing schedule
    // Fetch current status and start_time
    $stmt = $pdo->prepare("SELECT current_status, start_time, date FROM schedules WHERE id = ? AND professor_id = ?");
    $stmt->execute([(int)$id, $professor_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current) {
      send_json_response(404, ['error' => 'Schedule not found.']);
      exit();
    }
    $current_status = $current['current_status'];
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $new_start = new DateTime($date . ' ' . $start_time, new DateTimeZone('Asia/Manila'));
    // If new start time is in the future and current status is 'active', revert to pending
    if ($current_status === 'active' && $new_start > $now) {
      $stmt = $pdo->prepare("
        UPDATE schedules 
        SET subject = ?, room = ?, date = ?, start_time = ?, end_time = ?, late_threshold = ?, current_status = 'pending', is_active = 0
        WHERE id = ? AND professor_id = ?
      ");
      $stmt->execute([$subject, $room, $date, $start_time, $end_time, $late_threshold, (int)$id, $professor_id]);
    } else {
    $stmt = $pdo->prepare("
      UPDATE schedules 
      SET subject = ?, room = ?, date = ?, start_time = ?, end_time = ?, late_threshold = ? 
      WHERE id = ? AND professor_id = ?
    ");
    $stmt->execute([$subject, $room, $date, $start_time, $end_time, $late_threshold, (int)$id, $professor_id]);
    }
    // Notify all students
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'student'");
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $title = 'Schedule Updated';
    $message = 'Schedule for ' . htmlspecialchars($subject) . ' on ' . htmlspecialchars($date) . ' has been updated.';
    foreach ($students as $student_id) {
      $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
      $notif->execute([$student_id, $title, $message]);
    }
    send_json_response(200, [
      'success' => true, 
      'message' => 'Schedule updated successfully'
    ]);
  }
  
} catch (PDOException $e) {
  send_json_response(500, ['error' => 'Database error: ' . $e->getMessage()]);
}
