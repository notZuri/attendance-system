<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
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
$sql = "SELECT u.student_number, u.name, a.attendance_date, a.status 
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        WHERE a.attendance_date BETWEEN ? AND ?
        ORDER BY a.attendance_date DESC, u.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$dateFrom, $dateTo]);
$result = $stmt->fetchAll();

?>  

<?php include __DIR__ . "/../../includes/header.php"; ?>
<?php include __DIR__ . "/../../includes/sidebar.php"; ?>
<link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">
  <h2>Attendance Reports</h2>

  <form method="GET" action="reports.php" style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center;">
    <label for="date_from">From:</label>
    <input type="date" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>" required>

    <label for="date_to">To:</label>
    <input type="date" id="date_to" name="date_to" value="<?php echo $dateTo; ?>" required>

    <button type="submit" style="padding: 0.5rem 1rem; background-color: #007bff; border:none; color:#fff; border-radius: 4px; cursor:pointer;">
      Filter
    </button>
  </form>

  <div class="table-responsive" style="overflow-x:auto;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead style="background-color: #007bff; color: #fff;">
        <tr>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Student Number</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Name</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Date</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($result) > 0): ?>
          <?php foreach ($result as $row): ?>
            <tr>
              <td style="padding: 0.5rem; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['student_number']); ?></td>
              <td style="padding: 0.5rem; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['name']); ?></td>
              <td style="padding: 0.5rem; border: 1px solid #ddd;"><?php echo htmlspecialchars($row['attendance_date']); ?></td>
              <td style="padding: 0.5rem; border: 1px solid #ddd;">
                <?php 
                  $status = $row['status'];
                  if ($status === 'present') {
                    echo "<span style='color:green; font-weight:bold;'>Present</span>";
                  } elseif ($status === 'late') {
                    echo "<span style='color:orange; font-weight:bold;'>Late</span>";
                  } else {
                    echo "<span style='color:red; font-weight:bold;'>Absent</span>";
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

<?php include __DIR__ . "/../../includes/footer.php"; ?>
