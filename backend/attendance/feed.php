<?php
/**
 * Real-time Attendance Feed API
 * Provides live attendance updates for the professor dashboard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

header('Content-Type: application/json');

try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : null;
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Build the query
    $query = "
        SELECT 
            a.id,
            a.user_id,
            a.schedule_id,
            a.attendance_date,
            a.status,
            a.time_in,
            a.method,
            a.device_response,
            a.created_at,
            u.name as student_name,
            u.student_number,
            s.subject,
            s.room,
            s.start_time,
            s.end_time
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        JOIN schedules s ON a.schedule_id = s.id
        WHERE a.attendance_date = ?
    ";
    
    $params = [$date];
    
    if ($schedule_id) {
        $query .= " AND a.schedule_id = ?";
        $params[] = $schedule_id;
    }
    
    $query .= " ORDER BY a.created_at DESC LIMIT $limit";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get summary statistics
    $summaryQuery = "
        SELECT 
            COUNT(*) as total_attendance,
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
            COUNT(CASE WHEN status = 'late' THEN 1 END) as late_count,
            COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
            COUNT(CASE WHEN method = 'rfid' THEN 1 END) as rfid_count,
            COUNT(CASE WHEN method = 'fingerprint' THEN 1 END) as fingerprint_count,
            COUNT(CASE WHEN method = 'manual' THEN 1 END) as manual_count
        FROM attendance 
        WHERE attendance_date = ?
    ";
    
    $summaryParams = [$date];
    
    if ($schedule_id) {
        $summaryQuery .= " AND schedule_id = ?";
        $summaryParams[] = $schedule_id;
    }
    
    $stmt = $pdo->prepare($summaryQuery);
    $stmt->execute($summaryParams);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get active schedule info
    $activeScheduleQuery = "
        SELECT id, subject, room, start_time, end_time, current_status, is_active
        FROM schedules 
        WHERE date = ? AND current_status = 'active'
        ORDER BY start_time ASC
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($activeScheduleQuery);
    $stmt->execute([$date]);
    $activeSchedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format attendance data
    $formattedAttendance = array_map(function($record) {
        $deviceResponse = json_decode($record['device_response'], true);
        // Add 12-hour formatted times and formatted date
        $time_in_12h = $record['time_in'] ? date('g:i A', strtotime($record['time_in'])) : null;
        $created_at_12h = $record['created_at'] ? date('g:i A', strtotime($record['created_at'])) : null;
        $attendance_date_formatted = $record['attendance_date'] ? date('M j, Y', strtotime($record['attendance_date'])) : null;
        return [
            'id' => $record['id'],
            'student_name' => $record['student_name'],
            'student_number' => $record['student_number'],
            'status' => $record['status'],
            'method' => $record['method'],
            'time_in' => $record['time_in'],
            'time_in_12h' => $time_in_12h,
            'created_at' => $record['created_at'],
            'created_at_12h' => $created_at_12h,
            'attendance_date' => $record['attendance_date'],
            'attendance_date_formatted' => $attendance_date_formatted,
            'schedule_info' => [
                'subject' => $record['subject'],
                'room' => $record['room'],
                'start_time' => $record['start_time'],
                'end_time' => $record['end_time']
            ],
            'device_info' => $deviceResponse ?: null
        ];
    }, $attendance);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'attendance' => $formattedAttendance,
            'summary' => $summary,
            'active_schedule' => $activeSchedule,
            'current_time' => date('H:i:s'),
            'current_date' => $date,
            'total_records' => count($formattedAttendance)
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Attendance feed error: ' . $e->getMessage());
    error_log('SQL: ' . (isset($query) ? $query : 'N/A'));
    error_log('Params: ' . json_encode(isset($params) ? $params : []));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load attendance feed: ' . $e->getMessage()
    ]);
} 