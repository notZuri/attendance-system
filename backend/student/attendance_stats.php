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
    
    // Calculate attendance percentage
    $total_attendance = (int)($stats['total_attendance'] ?? 0);
    $present_count = (int)($stats['present_count'] ?? 0);
    $late_count = (int)($stats['late_count'] ?? 0);
    $absent_count = (int)($stats['absent_count'] ?? 0);
    
    $attendance_percentage = $total_attendance > 0 ? round(($present_count / $total_attendance) * 100, 2) : 0;
    
    send_json_response(200, [
        'success' => true,
        'stats' => [
            'total' => $total_attendance,
            'present' => $present_count,
            'late' => $late_count,
            'absent' => $absent_count,
            'percentage' => $attendance_percentage
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Student attendance stats error: ' . $e->getMessage());
    send_json_response(500, ['success' => false, 'error' => 'Database error']);
}
?> 