<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

// Only professors can access
if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    send_json_response(403, ['success' => false, 'error' => 'Unauthorized - Professors only']);
    exit();
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
if ($student_id <= 0) {
    send_json_response(400, ['success' => false, 'error' => 'Missing or invalid student_id']);
    exit();
}

try {
    // Get attendance statistics for this student
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_attendance,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
        FROM attendance 
        WHERE user_id = :student_id
    ");
    $stmt->execute(['student_id' => $student_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_attendance = (int)($stats['total_attendance'] ?? 0);
    $present_count = (int)($stats['present_count'] ?? 0);
    $late_count = (int)($stats['late_count'] ?? 0);
    $absent_count = (int)($stats['absent_count'] ?? 0);
    $attendance_percentage = $total_attendance > 0 ? round(($present_count / $total_attendance) * 100, 2) : 0;

    // Get recent attendance records (last 10)
    $stmt = $pdo->prepare("
        SELECT 
            a.attendance_date,
            a.status,
            a.time_in,
            s.subject,
            s.room
        FROM attendance a
        LEFT JOIN schedules s ON a.schedule_id = s.id
        WHERE a.user_id = :student_id
        ORDER BY a.attendance_date DESC, a.time_in DESC
        LIMIT 10
    ");
    $stmt->execute(['student_id' => $student_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $formatted_records = [];
    foreach ($records as $record) {
        $formatted_records[] = [
            'attendance_date' => $record['attendance_date'],
            'attendance_date_formatted' => date('M j, Y', strtotime($record['attendance_date'])),
            'status' => $record['status'],
            'time_in' => $record['time_in'],
            'time_in_12h' => $record['time_in'] ? date('g:i A', strtotime($record['time_in'])) : null,
            'subject' => $record['subject'] ?? 'N/A',
            'room' => $record['room'] ?? 'N/A'
        ];
    }

    send_json_response(200, [
        'success' => true,
        'stats' => [
            'total' => $total_attendance,
            'present' => $present_count,
            'late' => $late_count,
            'absent' => $absent_count,
            'percentage' => $attendance_percentage
        ],
        'recent_records' => $formatted_records
    ]);
} catch (PDOException $e) {
    error_log('Professor student summary error: ' . $e->getMessage());
    send_json_response(500, ['success' => false, 'error' => 'Database error']);
} 