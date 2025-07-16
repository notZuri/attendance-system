<?php
/**
 * Hardware Status Monitoring Component
 * Displays real-time hardware device status and connections
 */
?>

<div class="hardware-status-card card">
    <div class="card-header">
        <h3>🔌 Hardware Status</h3>
        <div class="status-controls">
            <button id="refresh-hardware" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <div class="auto-refresh-toggle">
                <input type="checkbox" id="auto-refresh-hardware" checked>
                <label for="auto-refresh-hardware">Auto-refresh</label>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div id="hardware-status-content">
            <div class="loading-spinner">Loading hardware status...</div>
        </div>
    </div>
</div>
<!-- CSS moved to assets/css/style.css -->
<!-- JS moved to assets/js/script.js --> 