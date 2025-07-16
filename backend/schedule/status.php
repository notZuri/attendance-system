<?php
/**
 * Schedule Status API
 * Provides real-time schedule status information
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/activation.php';

header('Content-Type: application/json');

try {
    $activator = new ScheduleActivator($pdo);
    
    // Ensure schedule statuses are up-to-date
    $activator->checkAndActivateSchedules();
    
    // Get current active schedules
    $activeSchedules = $activator->getActiveSchedules();
    
    // Get upcoming schedules
    $upcomingSchedules = $activator->getAllUpcomingSchedules(10);
    
    // Get schedule statistics
    $stats = $activator->getScheduleStats();
    
    // Get current time and date
    $currentTime = date('H:i:s');
    $currentDate = date('Y-m-d');
    
    echo json_encode([
        'success' => true,
        'data' => [
            'current_time' => $currentTime,
            'current_date' => $currentDate,
            'active_schedules' => $activeSchedules,
            'upcoming_schedules' => $upcomingSchedules,
            'stats' => $stats,
            'has_active_schedule' => count($activeSchedules) > 0
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get schedule status: ' . $e->getMessage()
    ]);
} 