<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

class AutoAttendanceRecorder {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Record attendance via RFID card
     */
    public function recordRFIDAttendance($card_uid, $timestamp = null) {
        if (!$timestamp) {
            $timestamp = date('Y-m-d H:i:s');
        }
        
        try {
            // Find student by RFID card
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.name, u.student_number, rc.card_uid
                FROM users u
                JOIN rfid_cards rc ON u.id = rc.user_id
                WHERE rc.card_uid = ?
            ");
            $stmt->execute([$card_uid]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                return [
                    'success' => false,
                    'message' => 'RFID card not registered or inactive',
                    'data' => null
                ];
            }
            
            // Check for active schedule
            $activeSchedule = $this->getActiveSchedule();
            if (!$activeSchedule) {
                return [
                    'success' => false,
                    'message' => 'No active schedule found',
                    'data' => null
                ];
            }
            
            // Check for duplicate attendance
            if ($this->isDuplicateAttendance($student['id'], $activeSchedule['id'], $timestamp)) {
                return [
                    'success' => false,
                    'message' => 'Attendance already recorded for this schedule',
                    'data' => null
                ];
            }
            
            // Determine attendance status (present/late)
            $status = $this->determineAttendanceStatus($activeSchedule, $timestamp);
            
            // Record attendance
            $stmt = $this->pdo->prepare("
                INSERT INTO attendance (user_id, schedule_id, attendance_date, status, time_in, method, device_response)
                VALUES (?, ?, ?, ?, ?, 'rfid', ?)
            ");
            
            $deviceResponse = json_encode([
                'card_uid' => $card_uid,
                'scan_timestamp' => $timestamp,
                'hardware_type' => 'rfid_rc522'
            ]);
            
            $stmt->execute([
                $student['id'],
                $activeSchedule['id'],
                date('Y-m-d', strtotime($timestamp)),
                $status,
                date('H:i:s', strtotime($timestamp)),
                $deviceResponse
            ]);
            
            // Update last used timestamp for RFID card
            $stmt = $this->pdo->prepare("
                UPDATE rfid_cards SET last_used = ? WHERE card_uid = ?
            ");
            $stmt->execute([$timestamp, $card_uid]);
            
            return [
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'data' => [
                    'student_name' => $student['name'],
                    'student_number' => $student['student_number'],
                    'status' => $status,
                    'schedule_subject' => $activeSchedule['subject'],
                    'schedule_room' => $activeSchedule['room'],
                    'timestamp' => $timestamp
                ]
            ];
            
        } catch (Exception $e) {
            error_log('RFID attendance recording error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Record attendance via fingerprint
     */
    public function recordFingerprintAttendance($template_id, $timestamp = null) {
        if (!$timestamp) {
            $timestamp = date('Y-m-d H:i:s');
        }
        
        try {
            // Find student by fingerprint template
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.name, u.student_number, ft.template_id
                FROM users u
                JOIN fingerprint_templates ft ON u.id = ft.user_id
                WHERE ft.template_id = ?
            ");
            $stmt->execute([$template_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                return [
                    'success' => false,
                    'message' => 'Fingerprint template not registered or inactive',
                    'data' => null
                ];
            }
            
            // Check for active schedule
            $activeSchedule = $this->getActiveSchedule();
            if (!$activeSchedule) {
                return [
                    'success' => false,
                    'message' => 'No active schedule found',
                    'data' => null
                ];
            }
            
            // Check for duplicate attendance
            if ($this->isDuplicateAttendance($student['id'], $activeSchedule['id'], $timestamp)) {
                return [
                    'success' => false,
                    'message' => 'Attendance already recorded for this schedule',
                    'data' => null
                ];
            }
            
            // Determine attendance status (present/late)
            $status = $this->determineAttendanceStatus($activeSchedule, $timestamp);
            
            // Record attendance
            $stmt = $this->pdo->prepare("
                INSERT INTO attendance (user_id, schedule_id, attendance_date, status, time_in, method, device_response)
                VALUES (?, ?, ?, ?, ?, 'fingerprint', ?)
            ");
            
            $deviceResponse = json_encode([
                'template_id' => $template_id,
                'scan_timestamp' => $timestamp,
                'hardware_type' => 'r305_fingerprint'
            ]);
            
            $stmt->execute([
                $student['id'],
                $activeSchedule['id'],
                date('Y-m-d', strtotime($timestamp)),
                $status,
                date('H:i:s', strtotime($timestamp)),
                $deviceResponse
            ]);
            
            // Update last used timestamp for fingerprint template
            $stmt = $this->pdo->prepare("
                UPDATE fingerprint_templates SET last_used = ? WHERE template_id = ?
            ");
            $stmt->execute([$timestamp, $template_id]);
            
            return [
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'data' => [
                    'student_name' => $student['name'],
                    'student_number' => $student['student_number'],
                    'status' => $status,
                    'schedule_subject' => $activeSchedule['subject'],
                    'schedule_room' => $activeSchedule['room'],
                    'timestamp' => $timestamp
                ]
            ];
            
        } catch (Exception $e) {
            error_log('Fingerprint attendance recording error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Get currently active schedule
     */
    private function getActiveSchedule() {
        $currentTime = date('H:i:s');
        $currentDate = date('Y-m-d');
        
        $stmt = $this->pdo->prepare("
            SELECT id, subject, room, date, start_time, end_time, late_threshold
            FROM schedules 
            WHERE date = ? 
            AND current_status = 'active'
            AND start_time <= ?
            AND end_time >= ?
            ORDER BY start_time ASC
            LIMIT 1
        ");
        $stmt->execute([$currentDate, $currentTime, $currentTime]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check for duplicate attendance
     */
    private function isDuplicateAttendance($user_id, $schedule_id, $timestamp) {
        $attendanceDate = date('Y-m-d', strtotime($timestamp));
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count
            FROM attendance 
            WHERE user_id = ? AND schedule_id = ? AND attendance_date = ?
        ");
        $stmt->execute([$user_id, $schedule_id, $attendanceDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    /**
     * Determine if attendance is on time or late
     */
    private function determineAttendanceStatus($schedule, $timestamp) {
        $scheduleStart = $schedule['start_time'];
        $lateThreshold = $schedule['late_threshold'] ?? 10; // Default 10 minutes
        $scanTime = date('H:i:s', strtotime($timestamp));
        
        // Convert times to minutes for comparison
        $startMinutes = $this->timeToMinutes($scheduleStart);
        $scanMinutes = $this->timeToMinutes($scanTime);
        $thresholdMinutes = $startMinutes + $lateThreshold;
        
        if ($scanMinutes <= $thresholdMinutes) {
            return 'present';
        } else {
            return 'late';
        }
    }
    
    /**
     * Convert time string to minutes
     */
    private function timeToMinutes($time) {
        $parts = explode(':', $time);
        return ($parts[0] * 60) + $parts[1];
    }
    
    /**
     * Get hardware status
     */
    public function getHardwareStatus() {
        try {
            // Check RFID cards
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total_cards, 
                       COUNT(CASE WHEN last_used >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 END) as active_today
                FROM rfid_cards 
                WHERE is_active = 1
            ");
            $stmt->execute();
            $rfidStatus = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check fingerprint templates
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total_templates, 
                       COUNT(CASE WHEN last_used >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 END) as active_today
                FROM fingerprint_templates 
                WHERE is_active = 1
            ");
            $stmt->execute();
            $fingerprintStatus = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'rfid' => $rfidStatus,
                'fingerprint' => $fingerprintStatus,
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log('Hardware status error: ' . $e->getMessage());
            return null;
        }
    }
} 