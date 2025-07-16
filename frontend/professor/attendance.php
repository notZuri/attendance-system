<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: /attendance-system/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Take Attendance - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
</head>
<body class="dashboard-bg">
  <?php include "../../includes/header.php"; ?>
  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>
    <main class="main-content">
      <h2>Take Attendance</h2>
      <div class="card card-accent-blue" style="max-width: 600px; margin-bottom: 2rem;">
        <label for="class-select" class="section-label">Select Class/Session</label>
        <select id="class-select" class="custom-select w-100">
          <option value="">-- Select a class --</option>
        </select>
      </div>
      <div id="session-info-panel" class="card card-accent-cyan" style="max-width: 700px; margin-bottom: 1.5rem; display: none;">
        <div><strong>Subject:</strong> <span id="session-info-subject"></span></div>
        <div><strong>Room:</strong> <span id="session-info-room"></span></div>
        <div><strong>Date:</strong> <span id="session-info-date"></span></div>
        <div><strong>Time:</strong> <span id="session-info-time"></span></div>
      </div>
      <div class="section-header" style="margin-bottom:1rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
        <div class="search-bar" style="position: relative; display: flex; align-items: center;">
          <span style="position: absolute; left: 10px; color: #b0bec5; font-size: 1.2em; pointer-events: none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </span>
          <input type="text" id="search-bar" class="custom-input" placeholder="Search by name..." style="max-width: 220px; padding-left: 2.2em; border-radius: 24px;" aria-label="Search students by name">
          <button type="button" id="clear-search" aria-label="Clear search" style="position: absolute; right: 6px; background: none; border: none; color: #b0bec5; font-size: 1.1em; cursor: pointer; display: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
      </div>
        <select id="status-filter" class="custom-select" style="max-width: 160px;">
          <option value="all">All Statuses</option>
          <option value="unmarked">Unmarked</option>
          <option value="present">Present</option>
          <option value="late">Late</option>
          <option value="absent">Absent</option>
        </select>
        <button type="button" class="btn btn-outline-primary" id="bulk-edit-toggle">Bulk Edit</button>
        <div id="bulk-controls" style="display:none; align-items:center; gap:0.5rem;">
          <label for="set-all-status" style="margin:0 0.5em 0 0;">Set All To:</label>
          <select id="set-all-status" class="custom-select" style="max-width: 140px;">
            <option value="">Unmarked</option>
            <option value="present">Present</option>
            <option value="late">Late</option>
            <option value="absent">Absent</option>
            </select>
          <button type="button" class="btn btn-success" id="save-all-btn">Save All</button>
          <button type="button" class="btn btn-secondary" id="cancel-bulk-btn">Cancel</button>
        </div>
          </div>
      <form id="attendance-form" class="card card-accent-lightblue" style="display:none; animation: fadeInUp 0.5s; max-width: 1000px;">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="attendance-table">
              <thead>
                <tr>
                  <th>Student Name</th>
                  <th>Student Number</th>
                  <th>Status</th>
                <th>Action</th>
                </tr>
              </thead>
              <tbody id="students-tbody"></tbody>
            </table>
          </div>
          <div style="text-align:right; margin-top:1.5rem;">
          <button type="button" class="btn btn-primary" id="submit-attendance" disabled>Submit Attendance</button>
        </div>
      </form>
    </main>
  </div>
  <?php include "../../includes/footer.php"; ?>
  <script>
    const classSelect = document.getElementById('class-select');
    const attendanceForm = document.getElementById('attendance-form');
    const studentsTbody = document.getElementById('students-tbody');
    const submitBtn = document.getElementById('submit-attendance');
    const searchBar = document.getElementById('search-bar');
    const statusFilter = document.getElementById('status-filter');
    let students = [];
    let filteredStudents = [];
    let selectedScheduleId = null;
    let scheduleMap = {};
    let editMode = {};
    let changes = {};
    let bulkEditMode = false;
    // Utility
    function formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
    function getStatusBadge(status) {
      if (!status) return '<span class="badge badge-unmarked">Unmarked</span>';
      const color = status === 'present' ? 'badge-present' : status === 'late' ? 'badge-late' : status === 'absent' ? 'badge-absent' : 'badge-unmarked';
      return `<span class="badge ${color}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
    }
    // Fetch classes
    fetch('/attendance-system/backend/schedule/read.php')
      .then(res => res.json())
      .then(data => {
        if (data.success && data.schedules) {
          data.schedules.forEach(cls => {
            scheduleMap[cls.id] = cls;
            const opt = document.createElement('option');
            opt.value = cls.id;
            opt.textContent = `${cls.subject} (${formatDate(cls.date)} ${cls.start_time} - ${cls.end_time})`;
            classSelect.appendChild(opt);
          });
        } else {
          showToast('Failed to load schedules.', 'error');
        }
      })
      .catch(() => showToast('Network error loading schedules.', 'error'));
    // On class select, fetch students
    classSelect.addEventListener('change', () => {
      selectedScheduleId = classSelect.value;
      if (!selectedScheduleId) {
        attendanceForm.style.display = 'none';
        studentsTbody.innerHTML = '';
        return;
      }
      showLoading();
      fetch(`/attendance-system/backend/student/list.php?limit=100&schedule_id=${selectedScheduleId}`)
        .then(res => res.json())
        .then(data => {
          hideLoading();
          if (data.success && data.students && Array.isArray(data.students)) {
            students = data.students;
            filteredStudents = students;
            editMode = {};
            changes = {};
            renderTable();
            attendanceForm.style.display = 'block';
            updateSessionPanel();
          } else {
            attendanceForm.style.display = 'none';
            showToast('Failed to load students.', 'error');
          }
        })
        .catch(() => {
          hideLoading();
          attendanceForm.style.display = 'none';
          showToast('Failed to load students.', 'error');
        });
    });
    function updateSessionPanel() {
            const sessionPanel = document.getElementById('session-info-panel');
            const infoSubject = document.getElementById('session-info-subject');
            const infoRoom = document.getElementById('session-info-room');
            const infoDate = document.getElementById('session-info-date');
            const infoTime = document.getElementById('session-info-time');
            if (selectedScheduleId && scheduleMap[selectedScheduleId]) {
              const cls = scheduleMap[selectedScheduleId];
              infoSubject.textContent = cls.subject;
              infoRoom.textContent = cls.room;
              infoDate.textContent = formatDate(cls.date);
              infoTime.textContent = `${cls.start_time} - ${cls.end_time}`;
              sessionPanel.style.display = '';
            } else {
              sessionPanel.style.display = 'none';
            }
    }
    // Search and filter (add clear button logic)
    searchBar.addEventListener('input', () => {
      document.getElementById('clear-search').style.display = searchBar.value ? 'block' : 'none';
      filterAndRender();
    });
    document.getElementById('clear-search').addEventListener('click', () => {
      searchBar.value = '';
      document.getElementById('clear-search').style.display = 'none';
      filterAndRender();
      searchBar.focus();
    });
    statusFilter.addEventListener('change', () => {
      filterAndRender();
    });
    function filterAndRender() {
      const search = searchBar.value.trim().toLowerCase();
      const filter = statusFilter.value;
      filteredStudents = students.filter(student => {
        const matchesName = student.fullname.toLowerCase().includes(search);
        let matchesStatus = true;
        if (filter === 'unmarked') matchesStatus = !student.attendance_status;
        else if (filter !== 'all') matchesStatus = student.attendance_status === filter;
        return matchesName && matchesStatus;
      });
      renderTable();
    }
    // Bulk Edit toggle
    document.getElementById('bulk-edit-toggle').addEventListener('click', function() {
      bulkEditMode = !bulkEditMode;
      document.getElementById('bulk-controls').style.display = bulkEditMode ? 'flex' : 'none';
      if (bulkEditMode) {
        // Enable edit mode for all filtered students
        filteredStudents.forEach(s => { editMode[s.id] = true; });
      } else {
        // Cancel all edits
        editMode = {};
        changes = {};
      }
      renderTable();
    });
    // Set All To dropdown
    document.getElementById('set-all-status').addEventListener('change', function() {
      const value = this.value;
      // Apply to all filtered students (checkboxes removed)
      filteredStudents.forEach(s => {
        changes[s.id] = { status: value };
      });
      renderTable();
    });
    // Save All
    document.getElementById('save-all-btn').addEventListener('click', function() {
      // Prepare summary and show modal (reuse submitBtn logic)
      submitBtn.click();
      // Exit bulk edit mode after submission
      bulkEditMode = false;
      document.getElementById('bulk-controls').style.display = 'none';
      editMode = {};
    });
    // Cancel Bulk
    document.getElementById('cancel-bulk-btn').addEventListener('click', function() {
      bulkEditMode = false;
      document.getElementById('bulk-controls').style.display = 'none';
      editMode = {};
      changes = {};
      renderTable();
    });
    // Render table
    function renderTable() {
      studentsTbody.innerHTML = '';
      if (filteredStudents.length === 0) {
        const tr = document.createElement('tr');
        tr.className = 'no-students-row';
        tr.innerHTML = `<td colspan="4">No students found.</td>`;
        studentsTbody.appendChild(tr);
        updateSubmitButtonState();
        return;
      }
      filteredStudents.forEach((student, idx) => {
        const tr = document.createElement('tr');
        // Highlight row if edited
        if (changes[student.id]) tr.classList.add('row-edited');
        // Status cell
        let statusCell = '';
        // If in edit mode, bulk edit mode, or student is unmarked, show dropdown
        if (editMode[student.id] || bulkEditMode || !student.attendance_status) {
          const current = (changes[student.id] && changes[student.id].status !== undefined)
            ? changes[student.id].status
            : (student.attendance_status || '');
          statusCell = `<select class="status-dropdown" data-student-id="${student.id}" aria-label="Attendance status">
            <option value="" ${current === '' ? 'selected' : ''}>Unmarked</option>
            <option value="present" ${current === 'present' ? 'selected' : ''}>Present</option>
            <option value="late" ${current === 'late' ? 'selected' : ''}>Late</option>
            <option value="absent" ${current === 'absent' ? 'selected' : ''}>Absent</option>
          </select>`;
        } else {
          // Show badge for the value in changes if present, else backend value
          const statusToShow = changes[student.id]?.status !== undefined ? changes[student.id].status : student.attendance_status;
          statusCell = getStatusBadge(statusToShow);
          if (changes[student.id]) statusCell += '<span class="unsaved-dot" title="Unsaved change"></span>';
        }
        // Action cell
        let actionCell = '';
        // Only show Edit button if student has a status and not in edit/bulk mode
        if (student.attendance_status && !editMode[student.id] && !bulkEditMode) {
          actionCell = `<button type="button" class="btn btn-sm btn-secondary action-btn edit-btn" data-student-id="${student.id}" title="Edit attendance">Edit</button>`;
        } else if (editMode[student.id] && !bulkEditMode) {
          actionCell = `<button type="button" class="btn btn-sm btn-success action-btn save-btn" data-student-id="${student.id}" title="Save change">Save</button>
                        <button type="button" class="btn btn-sm btn-secondary action-btn cancel-btn" data-student-id="${student.id}" title="Cancel edit">Cancel</button>`;
        } else {
          actionCell = '';
        }
        tr.innerHTML = `
          <td>${student.fullname}</td>
          <td>${student.student_number}</td>
          <td>${statusCell}</td>
          <td>${actionCell}</td>
        `;
        studentsTbody.appendChild(tr);
      });
      updateSubmitButtonState();
      addTableEventListeners();
    }
    // Table event listeners
    function addTableEventListeners() {
      // Dropdown change
      document.querySelectorAll('.status-dropdown').forEach(dropdown => {
        dropdown.addEventListener('change', function() {
          const studentId = this.getAttribute('data-student-id');
          const value = this.value;
          changes[studentId] = { status: value };
          updateSubmitButtonState();
        });
          });
      // Edit button
      document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const studentId = this.getAttribute('data-student-id');
          editMode[studentId] = true;
          renderTable();
        });
      });
      // Unmark button
      document.querySelectorAll('.unmark-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const studentId = this.getAttribute('data-student-id');
          changes[studentId] = { status: '' };
          editMode[studentId] = true;
          renderTable();
        });
      });
      // Save button
      document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const studentId = this.getAttribute('data-student-id');
          const dropdown = document.querySelector(`.status-dropdown[data-student-id='${studentId}']`);
          changes[studentId] = { status: dropdown.value };
          editMode[studentId] = false;
          renderTable();
        });
      });
      // Cancel button
      document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const studentId = this.getAttribute('data-student-id');
          editMode[studentId] = false;
          delete changes[studentId];
          renderTable();
        });
      });
    }
    // Enable submit only if there are changes
    function updateSubmitButtonState() {
      submitBtn.disabled = Object.keys(changes).length === 0;
    }
    // Submit logic
    submitBtn.addEventListener('click', function() {
      // Prepare summary
      const changedStudents = students.filter(s => changes[s.id]);
      if (changedStudents.length === 0) return;
      const summaryList = changedStudents.map(s => {
        const newStatus = changes[s.id].status;
        const statusText = newStatus ? newStatus.charAt(0).toUpperCase() + newStatus.slice(1) : 'Unmarked';
        return `<li>${s.fullname} (${s.student_number}): <strong>${statusText}</strong></li>`;
      }).join('');
      const modal = document.getElementById('confirm-modal');
      const summary = document.getElementById('confirm-summary');
      summary.innerHTML = `<p>Are you sure you want to submit the following changes?</p><ul>${summaryList}</ul>`;
      modal.style.display = 'block';
      // Cancel
      document.getElementById('cancel-confirm').onclick = function() {
        modal.style.display = 'none';
      };
      // Proceed
      document.getElementById('proceed-confirm').onclick = function() {
        modal.style.display = 'none';
        // Prepare data
        const now = new Date();
        const timeIn = now.toTimeString().slice(0,8);
        const attendanceData = changedStudents.map(s => ({
          student_id: s.id,
          status: changes[s.id].status,
          time_in: timeIn
        }));
        showLoading();
        fetch('/attendance-system/backend/attendance/manual.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            schedule_id: selectedScheduleId,
            date: new Date().toISOString().slice(0,10),
            attendance: attendanceData
          })
        })
        .then(res => res.json())
        .then(data => {
          hideLoading();
          if (data.success) {
            showToast('Attendance updated!', 'success');
            // Refresh students
            classSelect.dispatchEvent(new Event('change'));
          } else {
            showToast(data.message || 'Failed to update attendance.', 'error');
          }
        })
        .catch(() => {
          hideLoading();
          showToast('Network error.', 'error');
        });
      };
    });
  </script>
  <div id="confirm-modal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:400px;">
      <h3>Confirm Attendance Submission</h3>
      <div id="confirm-summary"></div>
      <div style="margin-top:1.5rem; text-align:right;">
        <button type="button" class="btn btn-secondary" id="cancel-confirm">Cancel</button>
        <button type="button" class="btn btn-primary" id="proceed-confirm">Submit</button>
      </div>
    </div>
  </div>
</body>
</html>
