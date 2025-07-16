<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

class ScheduleActivator {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Check and activate schedules based on current time
     */
    public function checkAndActivateSchedules() {
        try {
            $currentTime = date('H:i:s');
            $currentDate = date('Y-m-d');
            
            // Get schedules that should be active now
            $stmt = $this->pdo->prepare("
                SELECT id, subject, room, date, start_time, end_time, late_threshold, current_status
                FROM schedules 
                WHERE date = ? 
                AND current_status = 'pending'
                AND start_time <= ?
                AND end_time >= ?
            ");
            $stmt->execute([$currentDate, $currentTime, $currentTime]);
            $schedulesToActivate = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $activatedCount = 0;
            foreach ($schedulesToActivate as $schedule) {
                if ($this->activateSchedule($schedule['id'])) {
                    $activatedCount++;
                    $this->logScheduleActivation($schedule);
                }
            }
            
            // Deactivate schedules that have ended
            $this->deactivateEndedSchedules();
            
            return [
                'success' => true,
                'activated_count' => $activatedCount,
                'message' => "Activated {$activatedCount} schedules"
            ];
            
        } catch (Exception $e) {
            error_log('Schedule activation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error activating schedules: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Activate a specific schedule
     */
    public function activateSchedule($schedule_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE schedules 
                SET is_active = 1, current_status = 'active'
                WHERE id = ? AND current_status = 'pending'
            ");
            $stmt->execute([$schedule_id]);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log('Schedule activation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deactivate schedules that have ended
     */
    private function deactivateEndedSchedules() {
        try {
            $currentTime = date('H:i:s');
            $currentDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                UPDATE schedules 
                SET is_active = 0, current_status = 'completed'
                WHERE date = ? 
                AND current_status = 'active'
                AND end_time < ?
            ");
            $stmt->execute([$currentDate, $currentTime]);
            
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            error_log('Schedule deactivation error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check if a schedule is currently active
     */
    public function isScheduleActive($schedule_id) {
        try {
            $currentTime = date('H:i:s');
            $currentDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT id, subject, room, start_time, end_time, late_threshold
                FROM schedules 
                WHERE id = ? 
                AND date = ? 
                AND current_status = 'active'
                AND start_time <= ?
                AND end_time >= ?
            ");
            $stmt->execute([$schedule_id, $currentDate, $currentTime, $currentTime]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Schedule status check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all active schedules
     */
    public function getActiveSchedules() {
        try {
            $currentTime = date('H:i:s');
            $currentDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT id, subject, room, date, start_time, end_time, late_threshold, current_status
                FROM schedules 
                WHERE date = ? 
                AND current_status = 'active'
                AND start_time <= ?
                AND end_time >= ?
                ORDER BY start_time ASC
            ");
            $stmt->execute([$currentDate, $currentTime, $currentTime]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Get active schedules error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming schedules for today
     */
    public function getUpcomingSchedules() {
        try {
            $currentTime = date('H:i:s');
            $currentDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT id, subject, room, date, start_time, end_time, late_threshold, current_status
                FROM schedules 
                WHERE date = ? 
                AND current_status = 'pending'
                AND start_time > ?
                ORDER BY start_time ASC
                LIMIT 5
            ");
            $stmt->execute([$currentDate, $currentTime]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Get upcoming schedules error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all upcoming schedules (today and future dates)
     */
    public function getAllUpcomingSchedules($limit = 10) {
        try {
            $currentTime = date('H:i:s');
            $currentDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT id, subject, room, date, start_time, end_time, late_threshold, current_status
                FROM schedules 
                WHERE (date > ?) OR (date = ? AND start_time > ?)
                AND current_status = 'pending'
                ORDER BY date ASC, start_time ASC
                LIMIT ?
            ");
            $stmt->execute([$currentDate, $currentDate, $currentTime, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Get all upcoming schedules error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log schedule activation for monitoring
     */
    private function logScheduleActivation($schedule) {
        try {
            $logMessage = sprintf(
                "Schedule activated: ID=%d, Subject=%s, Room=%s, Time=%s-%s",
                $schedule['id'],
                $schedule['subject'],
                $schedule['room'],
                $schedule['start_time'],
                $schedule['end_time']
            );
            
            error_log($logMessage);
            
            // You can also store this in a log table if needed
            $stmt = $this->pdo->prepare("
                INSERT INTO hardware_sessions (schedule_id, session_start, status, hardware_status)
                VALUES (?, NOW(), 'active', ?)
            ");
            
            $hardwareStatus = json_encode([
                'activation_time' => date('Y-m-d H:i:s'),
                'schedule_info' => $schedule
            ]);
            
            $stmt->execute([$schedule['id'], $hardwareStatus]);
            
        } catch (Exception $e) {
            error_log('Schedule activation logging error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get schedule statistics
     */
    public function getScheduleStats() {
        try {
            $currentDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_schedules,
                    COUNT(CASE WHEN current_status = 'active' THEN 1 END) as active_schedules,
                    COUNT(CASE WHEN current_status = 'completed' THEN 1 END) as completed_schedules,
                    COUNT(CASE WHEN current_status = 'pending' THEN 1 END) as pending_schedules
                FROM schedules 
                WHERE date = ?
            ");
            $stmt->execute([$currentDate]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Schedule stats error: ' . $e->getMessage());
            return null;
        }
    }
} 