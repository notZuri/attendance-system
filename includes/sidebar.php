<?php
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page) {
  global $currentPage;
  return $currentPage === $page ? 'active' : '';
}
?>

<div class="sidebar">
  <br>
  <h2 style="color: white;">Attendance System</h2>
  <nav>
    <?php if ($role === 'professor'): ?>
      <a href="/attendance-system/frontend/professor/dashboard.php" class="<?=isActive('dashboard.php')?>">
        &#x1F4C8; Dashboard
      </a>
      <a href="/attendance-system/frontend/professor/students.php" class="<?=isActive('students.php')?>">
        &#x1F465; Manage Students
      </a>
      <a href="/attendance-system/frontend/professor/schedule.php" class="<?=isActive('schedule.php')?>">
        &#x1F4C5; Schedule
      </a>
      <a href="/attendance-system/frontend/professor/profile.php" class="<?=isActive('profile.php')?>">
        &#x1F464; Profile
      </a>
      <a href="/attendance-system/frontend/professor/attendance.php" class="<?=isActive('attendance.php')?>">
        &#x1F4CB; Attendance
      </a>
      <a href="/attendance-system/frontend/professor/summary.php" class="<?=isActive('summary.php')?>">
        &#x1F4CA; Summary
      </a>
      <a href="/attendance-system/frontend/professor/reports.php" class="<?=isActive('reports.php')?>">
        &#x1F4C4; Reports
      </a>
    <?php elseif ($role === 'student'): ?>
      <a href="/attendance-system/frontend/student/dashboard.php" class="<?=isActive('dashboard.php')?>">
        &#x1F4C8; Dashboard
      </a>
      <a href="/attendance-system/frontend/student/attendance.php" class="<?=isActive('attendance.php')?>">
        &#x2705; My Attendance
      </a>
      <a href="/attendance-system/frontend/student/profile.php" class="<?=isActive('profile.php')?>">
        &#x1F464; Profile
      </a>
      
    <?php endif; ?>
    <a href="/attendance-system/backend/auth/logout.php" style="margin-top: auto; color: #f44336;">&#x1F6AA; Logout</a>
  </nav>
</div>

