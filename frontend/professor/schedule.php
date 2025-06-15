<?php
session_start();
if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  header("Location: /frontend/login.php");
  exit();
}
$professorId = $_SESSION['user_id'];

include "../../includes/header.php";
include "../../includes/sidebar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Class Schedule - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
</head>
<body>
  <div class="container">
    <main class="main-content">
      <h2>Class Schedule</h2>

      <button class="btn" id="btn-add-schedule" style="margin-bottom: 1rem;">Add New Schedule</button>

      <form id="form-add-schedule" class="form-card" style="display:none;">
        <h3>Add Schedule</h3>

        <!-- hidden professor id field -->
        <input type="hidden" id="professor_id" name="professor_id" value="<?= htmlspecialchars($professorId) ?>" />

        <label for="subject">Class Name</label>
        <input type="text" id="subject" name="subject" placeholder="Enter class name" required />

        <label for="day_of_week">Day of Week</label>
        <select id="day_of_week" name="day_of_week" required>
          <option value="">Select day</option>
          <option value="Monday">Monday</option>
          <option value="Tuesday">Tuesday</option>
          <option value="Wednesday">Wednesday</option>
          <option value="Thursday">Thursday</option>
          <option value="Friday">Friday</option>
          <option value="Saturday">Saturday</option>
          <option value="Sunday">Sunday</option>
        </select>

        <label for="start_time">Start Time</label>
        <input type="time" id="start_time" name="start_time" required />

        <label for="end_time">End Time</label>
        <input type="time" id="end_time" name="end_time" required />

        <div class="form-actions">
          <button type="submit" class="btn">Save Schedule</button>
          <button type="button" class="btn" id="btn-cancel-schedule" style="background:#e74c3c;">Cancel</button>
        </div>
      </form>

      <div class="table-container">
        <table class="styled-table">
          <thead>
            <tr>
              <th>Class</th>
              <th>Day</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="schedule-table-body">
            <!-- Data loaded dynamically -->
          </tbody>
        </table>
      </div>
    </main>
  </div>

<script>
  const btnAdd = document.getElementById('btn-add-schedule');
  const formAdd = document.getElementById('form-add-schedule');
  const btnCancel = document.getElementById('btn-cancel-schedule');
  const tbody = document.getElementById('schedule-table-body');

  btnAdd.addEventListener('click', () => {
    formAdd.style.display = 'block';
    btnAdd.style.display = 'none';
  });

  btnCancel.addEventListener('click', () => {
    formAdd.style.display = 'none';
    btnAdd.style.display = 'inline-block';
    formAdd.reset();
  });

  // Fetch schedules
  function fetchSchedules() {
    fetch('/attendance-system/backend/schedule/read.php')
      .then(res => res.json())
      .then(data => {
        tbody.innerHTML = '';
        if (data.length > 0) {
          data.forEach(schedule => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${schedule.subject}</td>
              <td>${schedule.day_of_week}</td>
              <td>${schedule.start_time}</td>
              <td>${schedule.end_time}</td>
              <td>
                <button class="btn btn-sm" onclick="editSchedule(${schedule.id}, '${schedule.subject}', '${schedule.day_of_week}', '${schedule.start_time}', '${schedule.end_time}')">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(${schedule.id})">Delete</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        } else {
          tbody.innerHTML = '<tr><td colspan="5">No schedules found.</td></tr>';
        }
      })
      .catch(() => {
        tbody.innerHTML = '<tr><td colspan="5">Error loading schedules.</td></tr>';
      });
  }

  // Save schedule (create/update)
  formAdd.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(formAdd);

    fetch('/attendance-system/backend/schedule/save.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(text => {
      if (text.includes('success')) {
        alert('Schedule saved.');
        formAdd.reset();
        formAdd.style.display = 'none';
        btnAdd.style.display = 'inline-block';
        fetchSchedules();
      } else {
        alert('Error: ' + text);
      }
    })
    .catch(() => alert('Failed to save schedule.'));
  });

  // Delete schedule
  function deleteSchedule(id) {
    if (!confirm('Are you sure?')) return;
    fetch('/attendance-system/backend/schedule/delete.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ id })
    })
    .then(res => res.text())
    .then(text => {
      if (text.includes('Deleted')) {
        alert('Deleted.');
        fetchSchedules();
      } else {
        alert('Error: ' + text);
      }
    })
    .catch(() => alert('Failed to delete schedule.'));
  }

  // Edit schedule - pre-fill form for update
  function editSchedule(id, subject, day, start, end) {
    formAdd.style.display = 'block';
    btnAdd.style.display = 'none';
    document.getElementById('schedule_id')?.remove();
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.id = 'schedule_id';
    hidden.name = 'schedule_id';
    hidden.value = id;
    formAdd.prepend(hidden);

    document.getElementById('subject').value = subject;
    document.getElementById('day_of_week').value = day;
    document.getElementById('start_time').value = start;
    document.getElementById('end_time').value = end;
  }

  fetchSchedules();
</script>

<?php include "../../includes/footer.php"; ?>
</body>
</html>
