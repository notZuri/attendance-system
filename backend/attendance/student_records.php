<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    send_json_response(403, ['success' => false, 'error' => 'Unauthorized - Students only']);
    exit();
}

$student_id = (int)$_SESSION['user_id'];

try {
    // Get student's attendance records with schedule information
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
    ");
    
    $stmt->execute(['student_id' => $student_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the records for display
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
        'records' => $formatted_records
    ]);
    
} catch (PDOException $e) {
    error_log('Student attendance records error: ' . $e->getMessage());
    send_json_response(500, ['success' => false, 'error' => 'Database error']);
}
?> 