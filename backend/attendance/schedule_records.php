<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    send_json_response(403, ['success' => false, 'error' => 'Unauthorized - Professors only']);
    exit();
}

$professor_id = (int)$_SESSION['user_id'];
$schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : null;

if (!$schedule_id) {
    send_json_response(400, ['success' => false, 'error' => 'Missing schedule_id']);
    exit();
}

try {
    $sql = "
        SELECT a.id, a.user_id, u.name AS student_name, u.student_number, a.attendance_date, a.status, a.time_in, a.method
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        JOIN schedules s ON a.schedule_id = s.id
        WHERE a.schedule_id = :schedule_id AND s.professor_id = :professor_id";
    $params = [':schedule_id' => $schedule_id, ':professor_id' => $professor_id];
    if ($date) {
        $sql .= ' AND a.attendance_date = :date';
        $params[':date'] = $date;
    }
    $sql .= ' ORDER BY a.attendance_date DESC, a.time_in DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $formatted = [];
    foreach ($records as $row) {
        $formatted[] = [
            'attendance_id' => $row['id'],
            'user_id' => $row['user_id'],
            'student_name' => $row['student_name'],
            'student_number' => $row['student_number'],
            'attendance_date' => $row['attendance_date'],
            'attendance_date_formatted' => $row['attendance_date'] ? date('M j, Y', strtotime($row['attendance_date'])) : null,
            'status' => $row['status'],
            'time_in' => $row['time_in'],
            'time_in_12h' => $row['time_in'] ? date('g:i A', strtotime($row['time_in'])) : null,
            'method' => $row['method']
        ];
    }
    send_json_response(200, ['success' => true, 'records' => $formatted]);
} catch (PDOException $e) {
    error_log('Schedule records error: ' . $e->getMessage());
    error_log('SQL: ' . $sql);
    error_log('Params: ' . json_encode($params ?? []));
    send_json_response(500, ['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} 