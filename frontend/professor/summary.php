<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

// Sample dummy stats; replace with DB queries to get real data
$totalClasses = 15;
$totalStudents = 120;
$totalAttendance = 1100;
$totalLate = 75;
$totalAbsent = 45;
?>

<?php include "../../includes/header.php"; ?>
<?php include "../../includes/sidebar.php"; ?>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">
  <h2>Attendance Summary</h2>

  <div class="summary-cards" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
    <div class="card" style="flex:1; padding: 1.5rem; background: #f5f5f5; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
      <h3>Total Classes</h3>
      <p style="font-size: 2rem; font-weight: bold;"><?php echo $totalClasses; ?></p>
    </div>
    <div class="card" style="flex:1; padding: 1.5rem; background: #f5f5f5; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
      <h3>Total Students</h3>
      <p style="font-size: 2rem; font-weight: bold;"><?php echo $totalStudents; ?></p>
    </div>
    <div class="card" style="flex:1; padding: 1.5rem; background: #d4edda; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
      <h3>Total Attendance</h3>
      <p style="font-size: 2rem; font-weight: bold; color: #155724;"><?php echo $totalAttendance; ?></p>
    </div>
    <div class="card" style="flex:1; padding: 1.5rem; background: #fff3cd; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
      <h3>Late Count</h3>
      <p style="font-size: 2rem; font-weight: bold; color: #856404;"><?php echo $totalLate; ?></p>
    </div>
    <div class="card" style="flex:1; padding: 1.5rem; background: #f8d7da; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
      <h3>Absent</h3>
      <p style="font-size: 2rem; font-weight: bold; color: #721c24;"><?php echo $totalAbsent; ?></p>
    </div>
  </div>
</main>

<?php include "../../includes/footer.php"; ?>
