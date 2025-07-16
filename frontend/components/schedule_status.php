<?php
/**
 * Schedule Status Component
 * Displays real-time schedule status and active schedules
 */
?>

<div class="schedule-status-card card">
    <div class="card-header">
        <h3>📅 Schedule Status</h3>
        <div class="status-indicator" id="schedule-status-indicator">
            <span class="status-dot"></span>
            <span class="status-text">Checking...</span>
        </div>
    </div>
    
    <div class="card-body">
        <div id="schedule-status-content">
            <div class="loading-spinner">Loading schedule status...</div>
        </div>
    </div>
</div>
<!-- CSS moved to assets/css/style.css -->
<!-- JS moved to assets/js/script.js --> 