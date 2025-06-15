<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $professorId = (int) $_SESSION['user_id'];
    $subject = trim($data['subject'] ?? '');
    $dayOfWeek = strtolower(trim($data['day_of_week'] ?? ''));
    $startTime = $data['start_time'] ?? '';
    $endTime = $data['end_time'] ?? '';
    $scheduleId = $data['id'] ?? null;

    if (!$subject || !$dayOfWeek || !$startTime || !$endTime) {
        throw new InvalidArgumentException('Missing required fields.');
    }

    $validDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    if (!in_array($dayOfWeek, $validDays, true)) {
        throw new InvalidArgumentException('Invalid day of week.');
    }

    $timeFormat = '/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/';
    if (!preg_match($timeFormat, $startTime) || !preg_match($timeFormat, $endTime)) {
        throw new InvalidArgumentException('Invalid time format.');
    }

    if (strtotime($startTime) >= strtotime($endTime)) {
        throw new InvalidArgumentException('Start time must be before end time.');
    }

    if ($scheduleId) {
        // Update
        $stmt = $pdo->prepare("UPDATE schedules SET subject = ?, day_of_week = ?, start_time = ?, end_time = ? WHERE id = ? AND professor_id = ?");
        $stmt->execute([$subject, $dayOfWeek, $startTime, $endTime, $scheduleId, $professorId]);
        $message = "Schedule updated successfully.";
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO schedules (professor_id, subject, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$professorId, $subject, $dayOfWeek, $startTime, $endTime]);
        $message = "Schedule created successfully.";
    }

    echo json_encode(['status' => 'success', 'message' => $message]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
