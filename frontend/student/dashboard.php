<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: /attendance-system/");
    exit();
}
include "../../includes/header.php";
include "../../includes/sidebar.php";
$userId = $_SESSION['user_id'];
?>
<link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">
  <h2>Student Dashboard</h2>

  <!-- Student Information Card -->
  <div class="card" style="margin-bottom: 2rem;">
    <h3>📋 My Information</h3>
    <div id="student-info">
      <p>Loading student information...</p>
    </div>
  </div>
  
  <!-- Attendance Statistics -->
  <div class="card">
    <h3>📊 Attendance Statistics</h3>
    <div id="attendance-stats">
      <p>Loading attendance statistics...</p>
    </div>
  </div>

  <!-- Schedules Section -->
  <div class="card" style="margin-top: 2rem;">
    <h3>📅 Schedules</h3>
    <div id="student-schedules">
      <p>Loading schedules...</p>
    </div>
  </div>
</main>

<script>
const userId = <?php echo json_encode($userId); ?>;
const studentInfo = document.getElementById('student-info');
const attendanceStats = document.getElementById('attendance-stats');

// Helper function to get ordinal suffix
function getOrdinalSuffix(num) {
  const j = num % 10;
  const k = num % 100;
  if (j == 1 && k != 11) {
    return "st";
  }
  if (j == 2 && k != 12) {
    return "nd";
  }
  if (j == 3 && k != 13) {
    return "rd";
  }
  return "th";
}

// Helper to format time as 12-hour with AM/PM
function formatTime12h(timeStr) {
  if (!timeStr) return '';
  const [h, m] = timeStr.split(":");
  const date = new Date();
  date.setHours(h, m);
  let hours = date.getHours();
  const minutes = date.getMinutes().toString().padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12;
  hours = hours ? hours : 12; // 0 should be 12
  return `${hours}:${minutes} ${ampm}`;
}

// Helper to format date
function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

// Load student information
fetch(`/attendance-system/backend/student/get_student.php?id=${userId}`)
  .then(res => res.json())
  .then(data => {
    if (data.success && data.student) {
      const s = data.student;
      
      // Display student information in enhanced table format
      studentInfo.innerHTML = `
        <div class="student-info-table">
          <div class="info-section">
            <h4>👤 Personal Information</h4>
            <div class="info-grid">
              <div class="info-item">
                <div class="info-icon">👤</div>
                <div class="info-content">
                  <div class="info-label">Full Name</div>
                  <div class="info-value">${s.name || 'Not provided'}</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon">🆔</div>
                <div class="info-content">
                  <div class="info-label">Student Number</div>
                  <div class="info-value">${s.student_number || 'Not provided'}</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon">📧</div>
                <div class="info-content">
                  <div class="info-label">Email Address</div>
                  <div class="info-value">${s.email || 'Not provided'}</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon">📱</div>
                <div class="info-content">
                  <div class="info-label">Phone Number</div>
                  <div class="info-value">${s.phone || 'Not provided'}</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="info-section">
            <h4>🎓 Academic Information</h4>
            <div class="info-grid">
              <div class="info-item">
                <div class="info-icon">📚</div>
                <div class="info-content">
                  <div class="info-label">Course/Program</div>
                  <div class="info-value">${s.course || 'Not provided'}</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon">📅</div>
                <div class="info-content">
                  <div class="info-label">Year Level</div>
                  <div class="info-value">${s.year_level ? s.year_level + getOrdinalSuffix(s.year_level) + ' Year' : 'Not provided'}</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon">🏫</div>
                <div class="info-content">
                  <div class="info-label">Section</div>
                  <div class="info-value">${s.section || 'Not provided'}</div>
                </div>
              </div>
              <div class="info-item">
                <div class="info-icon">📅</div>
                <div class="info-content">
                  <div class="info-label">Account Created</div>
                  <div class="info-value">${new Date(s.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                  })}</div>
                </div>
              </div>
            </div>
          </div>
          

        </div>
      `;
    } else {
      studentInfo.innerHTML = `<p style="color: #e74c3c;">${data.error || 'No student data found.'}</p>`;
    }
  })
  .catch((error) => {
    console.error('Error loading student info:', error);
    studentInfo.innerHTML = '<p style="color: #e74c3c;">Failed to load student data.</p>';
  });

// Load attendance statistics
fetch(`/attendance-system/backend/student/attendance_stats.php`)
  .then(res => res.json())
  .then(data => {
    if (data.success && data.stats) {
      const stats = data.stats;
      attendanceStats.innerHTML = `
        <div class="attendance-stats-grid">
          <div class="attendance-stat-card total">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
              <div class="stat-number">${stats.total}</div>
              <div class="stat-label">Total Classes</div>
            </div>
          </div>
          <div class="attendance-stat-card present">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
              <div class="stat-number">${stats.present}</div>
              <div class="stat-label">Present</div>
            </div>
          </div>
          <div class="attendance-stat-card late">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
              <div class="stat-number">${stats.late}</div>
              <div class="stat-label">Late</div>
            </div>
          </div>
          <div class="attendance-stat-card absent">
            <div class="stat-icon">❌</div>
            <div class="stat-content">
              <div class="stat-number">${stats.absent}</div>
              <div class="stat-label">Absent</div>
            </div>
          </div>
          <div class="attendance-stat-card percentage">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
              <div class="stat-number">${stats.percentage}%</div>
              <div class="stat-label">Attendance Rate</div>
            </div>
          </div>
        </div>
      `;
    } else {
      attendanceStats.innerHTML = '<p style="color: #e74c3c;">No attendance records found.</p>';
    }
  })
  .catch((error) => {
    console.error('Error loading attendance stats:', error);
    attendanceStats.innerHTML = '<p style="color: #e74c3c;">Failed to load attendance statistics.</p>';
  });

// Load today's schedules (active and upcoming)
const schedulesContainer = document.getElementById('student-schedules');
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
      schedulesContainer.innerHTML = html;
    } else {
      schedulesContainer.innerHTML = '<p style="color: #e74c3c;">Failed to load schedules.</p>';
    }
  })
  .catch(error => {
    console.error('Error loading schedules:', error);
    schedulesContainer.innerHTML = '<p style="color: #e74c3c;">Failed to load schedules.</p>';
  });
</script>
<?php include "../../includes/footer.php"; ?>
