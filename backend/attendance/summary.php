<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

session_start();

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    send_json_response(403, ['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$professor_id = $_SESSION['user_id'];

try {
    // Total classes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE professor_id = ?");
    $stmt->execute([$professor_id]);
    $totalClasses = $stmt->fetchColumn();
    
    // Total students
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $stmt->execute();
    $totalStudents = $stmt->fetchColumn();
    
    // Total attendance
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE schedule_id IN (SELECT id FROM schedules WHERE professor_id = ?)");
    $stmt->execute([$professor_id]);
    $totalAttendance = $stmt->fetchColumn();
    
    // Total present
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE schedule_id IN (SELECT id FROM schedules WHERE professor_id = ?) AND status = 'present'");
    $stmt->execute([$professor_id]);
    $totalPresent = $stmt->fetchColumn();
    
    // Total late
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE schedule_id IN (SELECT id FROM schedules WHERE professor_id = ?) AND status = 'late'");
    $stmt->execute([$professor_id]);
    $totalLate = $stmt->fetchColumn();
    
    // Total absent
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE schedule_id IN (SELECT id FROM schedules WHERE professor_id = ?) AND status = 'absent'");
    $stmt->execute([$professor_id]);
    $totalAbsent = $stmt->fetchColumn();
    
    // Attendances today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE schedule_id IN (SELECT id FROM schedules WHERE professor_id = ?) AND DATE(attendance_date) = ?");
    $stmt->execute([$professor_id, $today]);
    $attendancesToday = $stmt->fetchColumn();
    if ((int)$attendancesToday === 0) {
        error_log("[DEBUG] No attendance for today: professor_id=$professor_id, today=$today");
        // Optionally, log how many records exist for this professor in general
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE schedule_id IN (SELECT id FROM schedules WHERE professor_id = ?)");
        $stmt2->execute([$professor_id]);
        $totalForProf = $stmt2->fetchColumn();
        error_log("[DEBUG] Total attendance for professor: $totalForProf");
    }

    // Calculate attendance rate
    $attendanceRate = $totalAttendance > 0 ? round(($totalPresent / $totalAttendance) * 100, 2) : 0;

    // Get detailed summary
    $stmt = $pdo->prepare('
        SELECT u.id, u.name, COUNT(a.id) AS attendance_count
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.attendance_date) BETWEEN :start_date AND :end_date
        WHERE u.role = "student"
        GROUP BY u.id, u.name
        ORDER BY u.name ASC
    ');

    $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

    send_json_response(200, [
        'success' => true,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'summary' => $summary,
        'total_classes' => (int)$totalClasses,
        'total_students' => (int)$totalStudents,
        'total_attendance' => (int)$totalAttendance,
        'present_count' => (int)$totalPresent,
        'late_count' => (int)$totalLate,
        'absent_count' => (int)$totalAbsent,
        'today_attendance' => (int)$attendancesToday,
        'attendance_rate' => $attendanceRate
    ]);
} catch (PDOException $e) {
    error_log('Attendance summary error: ' . $e->getMessage());
    send_json_response(500, ['success' => false, 'error' => 'Internal server error']);
}
