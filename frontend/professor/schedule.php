<?php
session_start();
if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  header("Location: /attendance-system/index.php");
  exit();
}
$professorId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Class Schedule - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
</head>
<body class="dashboard-bg">
  <?php include "../../includes/header.php"; ?>
  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>
    <main class="main-content">
      <div class="schedule-header">
        <h2 style="margin: 0;">Class Schedule</h2>
        <div style="margin-left: auto;">
          <button class="btn btn-primary" id="btn-add-schedule">
            Add New Schedule
          </button>
        </div>
      </div>
      <!-- Enhanced Search Bar for Class Schedule -->
      <form id="schedule-search-form" class="search-bar page-section" aria-label="Search class schedules" autocomplete="off" role="search">
        <div class="search-input-wrapper" style="position:relative; flex:1; display:flex; align-items:center;">
          <span class="search-input-icon" aria-hidden="true" style="position:absolute; left:14px; color:#b3d1f7; font-size:1.1em; pointer-events:none;">
            <i class="fa fa-search"></i>
          </span>
          <input type="text" id="schedule-search" class="custom-search-input" placeholder="Search by subject or room..." aria-label="Search by subject or room" style="padding-left:2.2em;" />
          <button type="button" id="clear-schedule-search" class="clear-search-btn" aria-label="Clear search" style="display:none; position:absolute; right:10px; background:none; border:none; color:#aaa; font-size:1.2em; cursor:pointer;">
            &times;
          </button>
        </div>
        <button type="submit" class="btn btn-primary search-btn" aria-label="Search" style="margin-left:0.5em;">
          <span class="fa fa-search" aria-hidden="true"></span> Search
        </button>
        <button type="button" id="schedule-show-all" class="btn btn-secondary" aria-label="Show all schedules" style="margin-left:0.5em;">Show All</button>
      </form>
      <!-- Schedule Form and Table Section -->
      <section class="page-section">
        <div class="card card-accent-lightblue table-responsive" style="margin-top: 1.5rem;">
          <table class="attendance-table">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Room</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Late Threshold</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="schedule-table-body">
              <!-- Data loaded dynamically by script.js -->
            </tbody>
          </table>
        </div>
      </section>
      <!-- Attendance Records Modal -->
      <div id="attendance-records-modal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:700px;">
          <span class="close" id="close-attendance-records-modal">&times;</span>
          <h3 id="attendance-records-title">Attendance Records</h3>
          <div id="attendance-records-body">
            <p>Loading...</p>
          </div>
        </div>
      </div>
      <!-- Schedule Modal (Add/Edit) -->
      <div id="schedule-modal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:500px;">
          <span class="close" id="close-schedule-modal">&times;</span>
          <h3 id="schedule-modal-title">Add Schedule</h3>
          <form id="form-schedule-modal" autocomplete="off">
            <input type="hidden" id="modal-schedule-id" name="schedule_id" />
            <div class="form-grid">
              <div class="form-group">
                <label for="modal-subject"><span class="icon-summary">📚</span> Subject</label>
                <input type="text" id="modal-subject" name="subject" required />
                <div class="input-feedback" id="feedback-modal-subject"></div>
              </div>
              <div class="form-group">
                <label for="modal-room"><span class="icon-summary">🏫</span> Room</label>
                <input type="text" id="modal-room" name="room" required />
                <div class="input-feedback" id="feedback-modal-room"></div>
              </div>
              <div class="form-group">
                <label for="modal-date"><span class="icon-summary">📅</span> Date</label>
                <input type="date" id="modal-date" name="date" required />
                <div class="input-feedback" id="feedback-modal-date"></div>
              </div>
              <div class="form-group">
                <label for="modal-start_time"><span class="icon-summary">⏰</span> Start Time</label>
                <input type="time" id="modal-start_time" name="start_time" required />
                <div class="input-feedback" id="feedback-modal-start"></div>
              </div>
              <div class="form-group">
                <label for="modal-end_time"><span class="icon-summary">⏰</span> End Time</label>
                <input type="time" id="modal-end_time" name="end_time" required />
                <div class="input-feedback" id="feedback-modal-end"></div>
              </div>
              <div class="form-group">
                <label for="modal-late_threshold"><span class="icon-summary">⏳</span> Late Threshold (minutes)</label>
                <input type="number" id="modal-late_threshold" name="late_threshold" min="0" max="120" required />
                <div class="input-feedback" id="feedback-modal-late-threshold"></div>
              </div>
            </div>
            <input type="hidden" id="modal-professor_id" name="professor_id" value="<?= htmlspecialchars($professorId) ?>" />
            <div class="form-actions" style="display:flex;gap:1rem;margin-top:1.5rem;">
              <button type="submit" class="btn btn-primary w-100" id="modal-save-btn">Save</button>
              <button type="button" class="btn w-100" id="modal-cancel-btn" style="background:#e74c3c;">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
  <?php include "../../includes/footer.php"; ?>
  <script>
  // Attendance Records Modal Logic
  document.addEventListener('DOMContentLoaded', function() {
    function showAttendanceRecordsModal(scheduleId, label) {
      const modal = document.getElementById('attendance-records-modal');
      const title = document.getElementById('attendance-records-title');
      const body = document.getElementById('attendance-records-body');
      title.textContent = `Attendance Records for ${label}`;
      body.innerHTML = '<p>Loading...</p>';
      modal.style.display = 'block';
      fetch(`/attendance-system/backend/attendance/schedule_records.php?schedule_id=${scheduleId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success && data.records && data.records.length > 0) {
            let table = `<table class="attendance-table"><thead><tr><th>Student Name</th><th>Student Number</th><th>Date</th><th>Status</th><th>Time In</th><th>Method</th></tr></thead><tbody>`;
            data.records.forEach(r => {
              table += `<tr><td>${r.student_name}</td><td>${r.student_number}</td><td>${r.attendance_date_formatted || ''}</td><td>${r.status.charAt(0).toUpperCase() + r.status.slice(1)}</td><td>${r.time_in_12h || ''}</td><td>${r.method || ''}</td></tr>`;
            });
            table += '</tbody></table>';
            body.innerHTML = table;
          } else {
            body.innerHTML = '<p>No attendance records found for this schedule.</p>';
          }
        })
        .catch(() => {
          body.innerHTML = '<p style="color:#e74c3c;">Failed to load attendance records.</p>';
        });
    }
    // Event delegation for dynamic buttons
    document.body.addEventListener('click', function(e) {
      if (e.target.classList.contains('btn-view-record')) {
        const scheduleId = e.target.getAttribute('data-schedule-id');
        const label = e.target.getAttribute('data-schedule-label') || '';
        showAttendanceRecordsModal(scheduleId, label);
      }
      if (e.target.id === 'close-attendance-records-modal' || e.target.closest('#close-attendance-records-modal')) {
        document.getElementById('attendance-records-modal').style.display = 'none';
      }
    });
    // Close modal on outside click
    window.addEventListener('click', function(e) {
      const modal = document.getElementById('attendance-records-modal');
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });
    // Enhanced search bar clear button logic
    const searchInput = document.getElementById('schedule-search');
    const clearBtn = document.getElementById('clear-schedule-search');
    if (searchInput && clearBtn) {
      searchInput.addEventListener('input', function() {
        clearBtn.style.display = this.value.length > 0 ? 'block' : 'none';
      });
      clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        searchInput.focus();
        // Optionally trigger search reset here
      });
    }
  });
  </script>
</body>
</html>
