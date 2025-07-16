<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: /attendance-system/");
    exit();
}

// ✅ Correct path and use of PDO
require_once __DIR__ . "/../../backend/config/config.php";

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    die("Student ID not found in session.");
}

// ✅ Fix column name and use PDO
$sql = "SELECT attendance_date, status FROM attendance WHERE user_id = ? ORDER BY attendance_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$records = $stmt->fetchAll();

include "../../includes/header.php";
include "../../includes/sidebar.php";
?>
<link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">
  <h2>Your Attendance Records</h2>
  <div class="table-responsive" style="overflow-x:auto; margin-top: 1rem;">
    <table style="width:100%; border-collapse: collapse;">
      <thead style="background-color: #17a2b8; color: white;">
        <tr>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Date</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Subject</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Room</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Time In</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Status</th>
        </tr>
      </thead>
      <tbody id="attendance-tbody">
        <tr><td colspan="5" id="attendance-loading">Loading...</td></tr>
      </tbody>
    </table>
  </div>
</main>
<script>
const userId = <?php echo json_encode($userId); ?>;
const tbody = document.getElementById('attendance-tbody');

// Function to format date
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}

// Function to format time
function formatTime(timeString) {
  if (!timeString) return 'N/A';
  return formatTime12h(timeString);
}

// Function to get status color and text
function getStatusDisplay(status) {
  switch(status) {
    case 'present':
      return "<span style='color:green; font-weight:bold;'>Present</span>";
    case 'late':
      return "<span style='color:orange; font-weight:bold;'>Late</span>";
    case 'absent':
      return "<span style='color:red; font-weight:bold;'>Absent</span>";
    default:
      return "<span style='color:gray; font-weight:bold;'>Unknown</span>";
  }
}

// Fetch attendance records
fetch(`/attendance-system/backend/attendance/student_records.php`)
  .then(res => res.json())
  .then(data => {
    tbody.innerHTML = '';
    if (data.success && Array.isArray(data.records) && data.records.length > 0) {
      data.records.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td style='padding:0.5rem; border:1px solid #ddd;'>${row.attendance_date_formatted}</td>
          <td style='padding:0.5rem; border:1px solid #ddd;'>${row.subject || 'N/A'}</td>
          <td style='padding:0.5rem; border:1px solid #ddd;'>${row.room || 'N/A'}</td>
          <td style='padding:0.5rem; border:1px solid #ddd;'>${row.time_in_12h || 'N/A'}</td>
          <td style='padding:0.5rem; border:1px solid #ddd;'>${getStatusDisplay(row.status)}</td>
        `;
        tbody.appendChild(tr);
      });
    } else {
      tbody.innerHTML = `<tr><td colspan='5' style='text-align:center; padding:1rem;'>No attendance records found.</td></tr>`;
    }
  })
  .catch((error) => {
    console.error('Error fetching attendance records:', error);
    tbody.innerHTML = `<tr><td colspan='5' style='text-align:center; padding:1rem; color:red;'>Failed to load attendance records.</td></tr>`;
  });
</script>
<?php include "../../includes/footer.php"; ?>
