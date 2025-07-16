<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    send_json_response(403, ['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $professorId = (int) $_SESSION['user_id'];
    $subject = trim($data['subject'] ?? '');
    $room = trim($data['room'] ?? '');
    $date = $data['date'] ?? '';
    $startTime = $data['start_time'] ?? '';
    $endTime = $data['end_time'] ?? '';
    $lateThreshold = (int)($data['late_threshold'] ?? 0);
    $scheduleId = $data['id'] ?? null;

    if (!$subject || !$room || !$date || !$startTime || !$endTime) {
        throw new InvalidArgumentException('Missing required fields.');
    }

    $timeFormat = '/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/';
    if (!preg_match($timeFormat, $startTime) || !preg_match($timeFormat, $endTime)) {
        throw new InvalidArgumentException('Invalid time format.');
    }
    if (strtotime($startTime) >= strtotime($endTime)) {
        throw new InvalidArgumentException('Start time must be before end time.');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new InvalidArgumentException('Invalid date format.');
    }
    if ($lateThreshold < 0 || $lateThreshold > 120) {
        throw new InvalidArgumentException('Late threshold must be between 0 and 120 minutes.');
    }

    if ($scheduleId) {
        // Update
        $stmt = $pdo->prepare("UPDATE schedules SET subject = ?, room = ?, date = ?, start_time = ?, end_time = ?, late_threshold = ? WHERE id = ? AND professor_id = ?");
        $stmt->execute([$subject, $room, $date, $startTime, $endTime, $lateThreshold, $scheduleId, $professorId]);
        $message = "Schedule updated successfully.";
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO schedules (professor_id, subject, room, date, start_time, end_time, late_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$professorId, $subject, $room, $date, $startTime, $endTime, $lateThreshold]);
        $message = "Schedule created successfully.";
    }

    echo json_encode(['status' => 'success', 'message' => $message]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
