<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_GET['id'])) {
    send_json_response(400, ['error' => 'Missing student ID']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    send_json_response(400, ['error' => 'Invalid student ID']);
    exit;
}

try {
    // Get student information from users table
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, student_number, role, profile_photo, created_at
        FROM users
        WHERE id = :id AND role = 'student'
    ");
    $stmt->execute(['id' => $id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // Get additional student information from students table
        $stmt = $pdo->prepare("
            SELECT course, year_level, section
            FROM students
            WHERE student_number = :student_number
        ");
        $stmt->execute(['student_number' => $userData['student_number']]);
        $studentData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Merge user data with student data
        $student = array_merge($userData, $studentData ?: []);
        
        // Get attendance statistics for this student
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_attendance,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
            FROM attendance 
            WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $id]);
        $attendanceStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate attendance percentage
        $totalAttendance = $attendanceStats['total_attendance'] ?? 0;
        $presentCount = $attendanceStats['present_count'] ?? 0;
        $attendancePercentage = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 0;
        
        $student['attendance_stats'] = [
            'total' => $totalAttendance,
            'present' => $presentCount,
            'late' => $attendanceStats['late_count'] ?? 0,
            'absent' => $attendanceStats['absent_count'] ?? 0,
            'percentage' => $attendancePercentage
        ];
        
        send_json_response(200, [
            'success' => true,
            'student' => $student
        ]);
    } else {
        send_json_response(404, ['error' => 'Student not found']);
    }
} catch (PDOException $e) {
    error_log("Get student error: " . $e->getMessage());
    send_json_response(500, ['error' => 'Failed to fetch student information']);
}
?>
