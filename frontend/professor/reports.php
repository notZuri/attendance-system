<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: /attendance-system/index.php");
    exit();
}

// Correct require path to config.php using __DIR__ for robustness
require_once __DIR__ . "/../../backend/config/config.php";

// Set default filter values
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // 1st day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-t');     // last day of current month

// Sanitize inputs (simple example)
$dateFrom = htmlspecialchars($dateFrom);
$dateTo = htmlspecialchars($dateTo);

// Prepare SQL with correct column names from your attendance table
$sql = "SELECT u.student_number, u.name, a.attendance_date, a.status, s.subject 
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        JOIN schedules s ON a.schedule_id = s.id
        WHERE a.attendance_date BETWEEN ? AND ?
        ORDER BY a.attendance_date DESC, u.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$dateFrom, $dateTo]);
$result = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Attendance Reports - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
</head>
<body class="dashboard-bg">
  <?php include "../../includes/header.php"; ?>
  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>
    <main class="main-content">
      <h2>Attendance Reports</h2>
      <form method="GET" action="reports.php" class="card" style="display: flex; gap: 1rem; align-items: center; max-width: 600px; margin-bottom: 1.5rem;">
        <label for="date_from">From:</label>
        <input type="date" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>" required>
        <label for="date_to">To:</label>
        <input type="date" id="date_to" name="date_to" value="<?php echo $dateTo; ?>" required>
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
      </form>
      <div style="margin-bottom: 1rem; display: flex; gap: 0.5rem;">
        <button id="export-csv" class="btn btn-secondary" type="button">Export CSV</button>
        <button id="print-report" class="btn btn-secondary" type="button">Print</button>
      </div>
      <div id="print-area" class="table-responsive">
        <table class="attendance-table">
          <thead>
            <tr>
              <th>Student Number</th>
              <th>Name</th>
              <th>Subject</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($result) > 0): ?>
              <?php foreach ($result as $row): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['student_number']); ?></td>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td><?php echo htmlspecialchars($row['subject']); ?></td>
                  <td><?php echo htmlspecialchars(date('M j, Y', strtotime($row['attendance_date']))); ?></td>
                  <td>
                    <?php 
                      $status = $row['status'];
                      if ($status === 'present') {
                        echo "<span class='status-present'>Present</span>";
                      } elseif ($status === 'late') {
                        echo "<span class='status-late'>Late</span>";
                      } else {
                        echo "<span class='status-absent'>Absent</span>";
                      }
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align:center; padding: 1rem;">No attendance records found for this period.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
  <?php include "../../includes/footer.php"; ?>
  <script>
  // CSV Export
  function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
  function exportTableToCSV(tableClass, filename) {
    const table = document.querySelector('.' + tableClass);
    let csv = [];
    for (let row of table.rows) {
      let rowData = [];
      for (let cell of row.cells) {
        rowData.push('"' + cell.innerText.replace(/"/g, '""') + '"');
      }
      csv.push(rowData.join(','));
    }
    downloadCSV(csv.join('\n'), filename);
  }
  document.getElementById('export-csv').addEventListener('click', function() {
    exportTableToCSV('attendance-table', 'attendance_report.csv');
  });
  // Print
  document.getElementById('print-report').addEventListener('click', function() {
    window.print();
  });
  </script>
</body>
</html>
