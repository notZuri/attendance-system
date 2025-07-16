<?php
/**
 * Real-time Attendance Feed Component
 * Displays live attendance updates from RFID and fingerprint scans
 */
?>

<div class="attendance-feed-card card">
    <div class="card-header">
        <h3>📊 Live Attendance Feed</h3>
        <div class="feed-controls">
            <button id="refresh-feed" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <div class="auto-refresh-toggle">
                <input type="checkbox" id="auto-refresh" checked>
                <label for="auto-refresh">Auto-refresh</label>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div id="attendance-feed-content">
            <div class="loading-spinner">Loading attendance feed...</div>
        </div>
    </div>
</div>
<!-- Decline Reason Modal -->
<div id="declineReasonModal" class="modal fade-modal" style="display:none;z-index:1200;" role="dialog" aria-modal="true" aria-labelledby="declineReasonTitle">
  <div class="modal-content" style="max-width:400px;margin:auto;padding:2rem 1.5rem 1.2rem 1.5rem;position:relative;">
    <span id="closeDeclineReasonModal" class="close" style="float:right;font-size:1.5rem;cursor:pointer;" tabindex="0" aria-label="Close">&times;</span>
    <div style="display:flex;align-items:center;gap:0.7rem;margin-bottom:0.7rem;">
      <span style="color:#e67e22;font-size:2rem;" aria-hidden="true">&#9888;</span>
      <h3 id="declineReasonTitle" style="margin:0;color:#c0392b;font-size:1.25rem;">Decline Password Change Request</h3>
    </div>
    <div id="declineStudentInfo" style="margin-bottom:0.7rem;font-weight:500;color:#34495e;"></div>
    <label for="declineReasonTextarea" style="font-weight:500;">Reason <span style="color:#888;font-weight:400;">(optional)</span>:</label>
    <textarea id="declineReasonTextarea" rows="4" style="width:100%;margin:0.5rem 0 1rem 0;resize:vertical;" aria-label="Reason for declining"></textarea>
    <div id="declineReasonError" style="color:#e74c3c;font-size:0.95rem;min-height:18px;"></div>
    <div style="display:flex;justify-content:flex-end;gap:0.5rem;align-items:center;">
      <button id="submitDeclineReasonBtn" class="btn btn-danger">
        <span class="btn-text">Submit</span>
        <span class="btn-spinner" style="display:none;margin-left:0.5em;"></span>
      </button>
      <button id="cancelDeclineReasonBtn" class="btn btn-secondary">Cancel</button>
    </div>
    <div id="declineConfirmationMsg" style="display:none;margin-top:1.2rem;color:#27ae60;font-weight:600;text-align:center;"></div>
  </div>
</div>
<!-- CSS moved to assets/css/style.css -->
<!-- JS moved to assets/js/script.js --> 