<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
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
?>

<?php include "../../includes/header.php"; ?>
<?php include "../../includes/sidebar.php"; ?>
<link rel="stylesheet" href="/attendance-system/assets/css/style.css" />

<main class="main-content">
  <h2>Your Attendance Records</h2>

  <div class="table-responsive" style="overflow-x:auto; margin-top: 1rem;">
    <table style="width:100%; border-collapse: collapse;">
      <thead style="background-color: #17a2b8; color: white;">
        <tr>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Date</th>
          <th style="padding: 0.75rem; border: 1px solid #ddd;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($records) > 0): ?>
          <?php foreach ($records as $row): ?>
            <tr>
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
            <td colspan="2" style="text-align:center; padding: 1rem;">No attendance records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include "../../includes/footer.php"; ?>
