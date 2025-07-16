<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: /attendance-system/");
    exit();
}
include "../../includes/header.php";
include "../../includes/sidebar.php";
?>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">
  <div class="dashboard-header">
    <h2>Welcome, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></h2>
    <p>Here's an overview of your attendance system</p>
  </div>
  
  <div class="dashboard-stats">
    <div class="stat-card">
      <div class="stat-icon">📊</div>
      <div class="stat-content">
        <h3 id="total-students" class="stat-number">Loading...</h3>
        <p>Total Students</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📅</div>
      <div class="stat-content">
        <h3 id="total-schedules" class="stat-number">Loading...</h3>
        <p>Total Schedules</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-content">
        <h3 id="today-attendance" class="stat-number">Loading...</h3>
        <p>Today's Attendance</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📈</div>
      <div class="stat-content">
        <h3 id="attendance-rate" class="stat-number">Loading...</h3>
        <p>Attendance Rate</p>
      </div>
    </div>
  </div>
  
  <div class="dashboard-sections">
    <div class="dashboard-charts">
      <div class="chart-container">
        <h3>Recent Attendance</h3>
        <div id="recent-attendance-chart" class="dashboard-table">Loading...</div>
      </div>
      <div class="chart-container">
        <h3>Attendance by Status</h3>
        <div id="attendance-status-chart" class="dashboard-table">Loading...</div>
      </div>
    </div>
    <div class="dashboard-side">
      <div class="card" style="margin-bottom: 2rem;">
        <h3>Schedules</h3>
        <div id="professor-schedules">
          <p>Loading schedules...</p>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Real-time Attendance Feed -->
  <div class="dashboard-full-width">
    <?php include "../components/attendance_feed.php"; ?>
  </div>
  
  <!-- Hardware Status Monitoring -->
  <div class="dashboard-full-width">
    <?php include "../components/hardware_status.php"; ?>
  </div>
</main>

<div id="attendance-modal" class="modal" style="display:none;">
  <div class="modal-content" style="max-width:800px;">
    <span class="close" id="close-attendance-modal">&times;</span>
    <h3>Attendance Records</h3>
    <div id="attendance-modal-body">
      <p>Loading...</p>
    </div>
  </div>
</div>

<script>
// Utility: format date
function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

// Load dashboard statistics
function loadDashboardStats() {
  console.log('Loading dashboard stats...');
  
  // Load total students
  fetch('/attendance-system/backend/student/list.php?limit=1')
    .then(res => res.json())
    .then(data => {
      console.log('Student list response:', data);
      const element = document.getElementById('total-students');
      if (data.success && data.pagination) {
        element.textContent = data.pagination.total_count || 0;
      } else {
        element.textContent = '0';
      }
    })
    .catch(error => {
      console.error('Error loading students:', error);
      document.getElementById('total-students').textContent = '0';
    });

  // Load schedules
  fetch('/attendance-system/backend/schedule/read.php')
    .then(res => res.json())
    .then(data => {
      console.log('Schedule response:', data);
      const element = document.getElementById('total-schedules');
      if (data.success && Array.isArray(data.schedules)) {
        element.textContent = data.schedules.length;
      } else {
        element.textContent = '0';
      }
    })
    .catch(error => {
      console.error('Error loading schedules:', error);
      document.getElementById('total-schedules').textContent = '0';
    });

  // Load attendance summary
  fetch('/attendance-system/backend/attendance/summary.php')
    .then(res => res.json())
    .then(data => {
      console.log('Attendance summary response:', data);
      if (data.success) {
        document.getElementById('today-attendance').textContent = data.today_attendance || 0;
        document.getElementById('attendance-rate').textContent = (data.attendance_rate || 0) + '%';
      } else {
        document.getElementById('today-attendance').textContent = '0';
        document.getElementById('attendance-rate').textContent = '0%';
      }
    })
    .catch(error => {
      console.error('Error loading attendance summary:', error);
      document.getElementById('today-attendance').textContent = '0';
      document.getElementById('attendance-rate').textContent = '0%';
    });
}

// Load charts and tables
function loadCharts() {
  console.log('Loading charts...');
  
  // Recent attendance chart
  fetch('/attendance-system/backend/attendance/recent.php')
    .then(res => res.json())
    .then(data => {
      console.log('Recent attendance response:', data);
      const chartContainer = document.getElementById('recent-attendance-chart');
      chartContainer.innerHTML = '';
      
      if (data.success && data.attendance && data.attendance.length > 0) {
        const table = document.createElement('table');
        table.className = 'data-table';
        table.innerHTML = `
          <thead>
            <tr>
              <th>Student</th>
              <th>Class</th>
              <th>Date & Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            ${data.attendance.slice(0, 5).map(record => `
              <tr>
                <td>${record.student_name || 'N/A'}</td>
                <td>${record.class_name || 'N/A'}</td>
                <td>${record.attendance_date_formatted ? record.attendance_date_formatted : (record.date ? formatDate(record.date) : 'N/A')}${record.time_in_12h ? ' ' + record.time_in_12h : ''}</td>
                <td><span class="status-${record.status || 'unknown'}">${(record.status || 'unknown').charAt(0).toUpperCase() + (record.status || 'unknown').slice(1)}</span></td>
              </tr>
            `).join('')}
          </tbody>
        `;
        
        const card = document.createElement('div');
        card.className = 'dashboard-table';
        card.appendChild(table);
        chartContainer.appendChild(card);
      } else {
        chartContainer.innerHTML = '<div class="dashboard-table"><p>No recent attendance records</p></div>';
      }
    })
    .catch(error => {
      console.error('Error loading recent attendance:', error);
      document.getElementById('recent-attendance-chart').innerHTML = '<div class="dashboard-table"><p class="error">Failed to load recent attendance</p></div>';
    });

  // Attendance status chart
  fetch('/attendance-system/backend/attendance/summary.php')
    .then(res => res.json())
    .then(data => {
      console.log('Attendance status response:', data);
      const chartContainer = document.getElementById('attendance-status-chart');
      chartContainer.innerHTML = '';
      
      if (data.success) {
        const statusBreakdown = document.createElement('div');
        statusBreakdown.className = 'status-breakdown';
        statusBreakdown.innerHTML = `
          <div class="status-item">
            <span class="status-present">Present</span>
            <span>${data.present_count || 0}</span>
          </div>
          <div class="status-item">
            <span class="status-late">Late</span>
            <span>${data.late_count || 0}</span>
          </div>
          <div class="status-item">
            <span class="status-absent">Absent</span>
            <span>${data.absent_count || 0}</span>
          </div>
        `;
        
        const card = document.createElement('div');
        card.className = 'dashboard-table';
        card.appendChild(statusBreakdown);
        chartContainer.appendChild(card);
      } else {
        chartContainer.innerHTML = '<div class="dashboard-table"><p class="error">Failed to load attendance status</p></div>';
      }
    })
    .catch(error => {
      console.error('Error loading attendance status:', error);
      document.getElementById('attendance-status-chart').innerHTML = '<div class="dashboard-table"><p class="error">Failed to load attendance status</p></div>';
    });
}

// Load today's schedules (active and upcoming) for professor
const professorSchedulesContainer = document.getElementById('professor-schedules');
fetch('/attendance-system/backend/schedule/status.php')
  .then(res => res.json())
  .then(data => {
    if (data.success && data.data) {
      const { active_schedules, upcoming_schedules } = data.data;
      let html = '';
      // Active Schedules
      if (active_schedules && active_schedules.length > 0) {
        html += '<h4 style="color: #27ae60;">\u25CF Ongoing Class</h4>';
        active_schedules.forEach(sch => {
          html += `
            <div class="schedule-item" style="border-left: 4px solid #27ae60; margin-bottom: 1rem; padding: 0.5rem 1rem; background: #e8f5e8; border-radius: 6px;">
              <strong>Subject:</strong> ${sch.subject} <br/>
              <strong>Room:</strong> ${sch.room} <br/>
              <span><strong>Time:</strong> ${formatTime12h(sch.start_time)} - ${formatTime12h(sch.end_time)}</span>
            </div>
          `;
        });
      }
      // Upcoming Schedules
      if (upcoming_schedules && upcoming_schedules.length > 0) {
        html += '<h4 style="color: #2980b9;">\u23F0 Upcoming Classes</h4>';
        upcoming_schedules.forEach(sch => {
          const scheduleDate = new Date(sch.date);
          const today = new Date();
          const isToday = scheduleDate.toDateString() === today.toDateString();
          const dateDisplay = isToday ? 'Today' : formatDate(sch.date);
          
          html += `
            <div class="schedule-item" style="border-left: 4px solid #2980b9; margin-bottom: 1rem; padding: 0.5rem 1rem; background: #d1ecf1; border-radius: 6px;">
              <strong>Subject:</strong> ${sch.subject} <br/>
              <strong>Room:</strong> ${sch.room} <br/>
              <strong>Date:</strong> ${dateDisplay} <br/>
              <span><strong>Time:</strong> ${formatTime12h(sch.start_time)} - ${formatTime12h(sch.end_time)}</span>
            </div>
          `;
        });
      }
      if (!html) {
        html = '<p>No upcoming schedules.</p>';
      }
      professorSchedulesContainer.innerHTML = html;
      addViewRecordsButtons();
    } else {
      professorSchedulesContainer.innerHTML = '<p style="color: #e74c3c;">Failed to load schedules.</p>';
    }
  })
  .catch(error => {
    console.error('Error loading schedules:', error);
    professorSchedulesContainer.innerHTML = '<p style="color: #e74c3c;">Failed to load schedules.</p>';
  });

// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
  loadDashboardStats();
  loadCharts();
});

function openAttendanceModal(scheduleId, scheduleLabel) {
  const modal = document.getElementById('attendance-modal');
  const body = document.getElementById('attendance-modal-body');
  modal.style.display = 'block';
  body.innerHTML = `<p>Loading records for <strong>${scheduleLabel}</strong>...</p>`;
  fetch(`/attendance-system/backend/attendance/schedule_records.php?schedule_id=${scheduleId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.records && data.records.length > 0) {
        let html = `<table class='data-table'><thead><tr><th>Student</th><th>Number</th><th>Date</th><th>Time In</th><th>Status</th><th>Method</th><th>Action</th></tr></thead><tbody>`;
        data.records.forEach(rec => {
          html += `<tr data-id='${rec.attendance_id}'>
            <td>${rec.student_name}</td>
            <td>${rec.student_number}</td>
            <td>${rec.attendance_date_formatted}</td>
            <td><input type='time' value='${rec.time_in ? rec.time_in.slice(0,5) : ''}' class='edit-time-in' /></td>
            <td>
              <select class='edit-status'>
                <option value='present' ${rec.status==='present'?'selected':''}>Present</option>
                <option value='late' ${rec.status==='late'?'selected':''}>Late</option>
                <option value='absent' ${rec.status==='absent'?'selected':''}>Absent</option>
              </select>
            </td>
            <td>${rec.method}</td>
            <td><button class='btn btn-sm btn-primary save-attendance-row'>Save</button></td>
          </tr>`;
        });
        html += `</tbody></table>`;
        body.innerHTML = html;
      } else {
        body.innerHTML = '<p>No attendance records found for this schedule.</p>';
      }
    })
    .catch(() => {
      body.innerHTML = '<p style="color:red;">Failed to load attendance records.</p>';
    });
}

document.getElementById('close-attendance-modal').onclick = function() {
  document.getElementById('attendance-modal').style.display = 'none';
};

window.onclick = function(event) {
  const modal = document.getElementById('attendance-modal');
  if (event.target == modal) modal.style.display = 'none';
};

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('save-attendance-row')) {
    const tr = e.target.closest('tr');
    const attendanceId = tr.getAttribute('data-id');
    const status = tr.querySelector('.edit-status').value;
    const timeIn = tr.querySelector('.edit-time-in').value;
    fetch('/attendance-system/backend/attendance/update_record.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ attendance_id: attendanceId, status: status, time_in: timeIn })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        tr.style.background = '#d4edda';
        setTimeout(() => { tr.style.background = ''; }, 1000);
      } else {
        alert(data.error || 'Failed to update attendance.');
      }
    })
    .catch(() => alert('Network error.'));
  }
});

// Add View Records button to each schedule card after rendering
function addViewRecordsButtons() {
  setTimeout(() => {
    document.querySelectorAll('.schedule-item').forEach(item => {
      if (!item.querySelector('.view-records-btn')) {
        const subject = item.querySelector('strong:nth-child(1)')?.textContent.replace('Subject:','').trim() || '';
        const room = item.querySelector('strong:nth-child(2)')?.textContent.replace('Room:','').trim() || '';
        const time = item.querySelector('span')?.textContent.replace('Time:','').trim() || '';
        const label = `${subject} (${room}) ${time}`;
        // Try to extract schedule_id from a data attribute or fallback (requires backend to add data-schedule-id)
        const scheduleId = item.getAttribute('data-schedule-id') || item.dataset.scheduleId || null;
        if (scheduleId) {
          const btn = document.createElement('button');
          btn.className = 'btn btn-sm btn-info view-records-btn';
          btn.textContent = 'View Records';
          btn.onclick = () => openAttendanceModal(scheduleId, label);
          item.appendChild(btn);
        }
      }
    });
  }, 500);
}
</script>

<?php include "../../includes/footer.php"; ?>
