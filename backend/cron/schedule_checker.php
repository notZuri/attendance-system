<?php
/**
 * Schedule Checker - Background Process
 * This script runs continuously to check and activate schedules based on time
 * Run this as a background process: php backend/cron/schedule_checker.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../schedule/activation.php';
require_once __DIR__ . '/../utils/response.php';

class ScheduleChecker {
    private $pdo;
    private $activator;
    private $isRunning = true;
    private $checkInterval = 30; // Check every 30 seconds
    private $logFile;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->activator = new ScheduleActivator($pdo);
        $this->logFile = __DIR__ . '/../../logs/schedule_checker.log';
        
        // Create logs directory if it doesn't exist
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }
    
    public function start() {
        $this->log("Schedule Checker started at " . date('Y-m-d H:i:s'));
        $this->log("Checking schedules every {$this->checkInterval} seconds");
        
        while ($this->isRunning) {
            try {
                $this->checkSchedules();
                $this->cleanupOldSessions();
                sleep($this->checkInterval);
            } catch (Exception $e) {
                $this->log("Error in schedule checker: " . $e->getMessage());
                sleep(60); // Wait 1 minute on error
            }
        }
    }
    
    private function checkSchedules() {
        $currentTime = date('H:i:s');
        $currentDate = date('Y-m-d');
        
        // Get schedules that should be activated
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
        
        foreach ($schedulesToActivate as $schedule) {
            $this->activateSchedule($schedule);
        }
        
        // Deactivate schedules that have ended
        $this->deactivateEndedSchedules();
        
        // Log current status
        $this->logScheduleStatus();
    }
    
    private function activateSchedule($schedule) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE schedules 
                SET is_active = 1, current_status = 'active'
                WHERE id = ? AND current_status = 'pending'
            ");
            $stmt->execute([$schedule['id']]);
            
            if ($stmt->rowCount() > 0) {
                $this->log("ACTIVATED: Schedule ID {$schedule['id']} - {$schedule['subject']} in {$schedule['room']}");
                $this->createHardwareSession($schedule);
                $this->notifyScheduleActivation($schedule);
            }
            
        } catch (Exception $e) {
            $this->log("ERROR activating schedule {$schedule['id']}: " . $e->getMessage());
        }
    }
    
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
            
            $deactivatedCount = $stmt->rowCount();
            if ($deactivatedCount > 0) {
                $this->log("DEACTIVATED: {$deactivatedCount} schedules completed");
            }
            
        } catch (Exception $e) {
            $this->log("ERROR deactivating schedules: " . $e->getMessage());
        }
    }
    
    private function createHardwareSession($schedule) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO hardware_sessions (schedule_id, session_start, status, hardware_status)
                VALUES (?, NOW(), 'active', ?)
            ");
            
            $hardwareStatus = json_encode([
                'activation_time' => date('Y-m-d H:i:s'),
                'schedule_info' => $schedule,
                'attendance_window_start' => $schedule['start_time'],
                'attendance_window_end' => date('H:i:s', strtotime($schedule['start_time'] . ' + ' . $schedule['late_threshold'] . ' minutes'))
            ]);
            
            $stmt->execute([$schedule['id'], $hardwareStatus]);
            
        } catch (Exception $e) {
            $this->log("ERROR creating hardware session: " . $e->getMessage());
        }
    }
    
    private function cleanupOldSessions() {
        try {
            // End sessions older than 24 hours
            $stmt = $this->pdo->prepare("
                UPDATE hardware_sessions 
                SET session_end = NOW(), status = 'ended'
                WHERE status = 'active' 
                AND session_start < DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute();
            
            $cleanedCount = $stmt->rowCount();
            if ($cleanedCount > 0) {
                $this->log("CLEANUP: Ended {$cleanedCount} old hardware sessions");
            }
            
        } catch (Exception $e) {
            $this->log("ERROR cleaning up sessions: " . $e->getMessage());
        }
    }
    
    private function logScheduleStatus() {
        try {
            $currentDate = date('Y-m-d');
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN current_status = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN current_status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN current_status = 'completed' THEN 1 END) as completed
                FROM schedules 
                WHERE date = ?
            ");
            $stmt->execute([$currentDate]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stats['active'] > 0 || $stats['pending'] > 0) {
                $this->log("STATUS: Total={$stats['total']}, Active={$stats['active']}, Pending={$stats['pending']}, Completed={$stats['completed']}");
            }
            
        } catch (Exception $e) {
            $this->log("ERROR getting schedule status: " . $e->getMessage());
        }
    }
    
    private function notifyScheduleActivation($schedule) {
        // This will be implemented in Part 3 for real-time notifications
        // For now, just log the activation
        $this->log("NOTIFICATION: Schedule {$schedule['subject']} activated in {$schedule['room']}");
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Write to log file
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Also output to console if running in terminal
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
    
    public function stop() {
        $this->isRunning = false;
        $this->log("Schedule Checker stopped");
    }
}

// Run the checker if this script is executed directly
if (php_sapi_name() === 'cli') {
    echo "Starting Schedule Checker...\n";
    echo "Press Ctrl+C to stop\n\n";
    
    $checker = new ScheduleChecker($pdo);
    
    // Handle graceful shutdown
    pcntl_signal(SIGINT, function() use ($checker) {
        echo "\nShutting down...\n";
        $checker->stop();
        exit(0);
    });
    
    $checker->start();
} else {
    // If accessed via web, just return status
    header('Content-Type: application/json');
    
    try {
        $activator = new ScheduleActivator($pdo);
        $stats = $activator->getScheduleStats();
        
        echo json_encode([
            'success' => true,
            'message' => 'Schedule Checker Status',
            'stats' => $stats,
            'current_time' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} 