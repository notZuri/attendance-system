<?php
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page) {
  global $currentPage;
  return $currentPage === $page ? 'active' : '';
}
?>

<div class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <i class="fas fa-chart-line"></i>
    </div>
    <h2 class="sidebar-title">Attendance System</h2>
  </div>
  
  <nav class="sidebar-nav">
    <?php if ($role === 'professor'): ?>
      <div class="nav-section">
        <h3 class="nav-section-title">Dashboard</h3>
        <a href="/attendance-system/frontend/professor/dashboard.php" class="nav-link <?=isActive('dashboard.php')?>">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </div>
      
      <div class="nav-section">
        <h3 class="nav-section-title">Management</h3>
        <a href="/attendance-system/frontend/professor/students.php" class="nav-link <?=isActive('students.php')?>">
          <i class="fas fa-users"></i>
          <span>Manage Students</span>
        </a>
        <a href="/attendance-system/frontend/professor/schedule.php" class="nav-link <?=isActive('schedule.php')?>">
          <i class="fas fa-calendar-alt"></i>
          <span>Schedule</span>
        </a>
      </div>
      
      <div class="nav-section">
        <h3 class="nav-section-title">Attendance</h3>
        <a href="/attendance-system/frontend/professor/attendance.php" class="nav-link <?=isActive('attendance.php')?>">
          <i class="fas fa-clipboard-check"></i>
          <span>Attendance</span>
        </a>
        <a href="/attendance-system/frontend/professor/summary.php" class="nav-link <?=isActive('summary.php')?>">
          <i class="fas fa-chart-bar"></i>
          <span>Summary</span>
        </a>
        <a href="/attendance-system/frontend/professor/reports.php" class="nav-link <?=isActive('reports.php')?>">
          <i class="fas fa-file-alt"></i>
          <span>Reports</span>
        </a>
      </div>
      
      <div class="nav-section">
        <h3 class="nav-section-title">Account</h3>
        <a href="/attendance-system/frontend/professor/profile.php" class="nav-link <?=isActive('profile.php')?>">
          <i class="fas fa-user-cog"></i>
          <span>Profile</span>
        </a>
      </div>
      
    <?php elseif ($role === 'student'): ?>
      <div class="nav-section">
        <h3 class="nav-section-title">Dashboard</h3>
        <a href="/attendance-system/frontend/student/dashboard.php" class="nav-link <?=isActive('dashboard.php')?>">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </div>
      
      <div class="nav-section">
        <h3 class="nav-section-title">Attendance</h3>
        <a href="/attendance-system/frontend/student/attendance.php" class="nav-link <?=isActive('attendance.php')?>">
          <i class="fas fa-clipboard-list"></i>
          <span>My Attendance</span>
        </a>
      </div>
      
      <div class="nav-section">
        <h3 class="nav-section-title">Account</h3>
        <a href="/attendance-system/frontend/student/profile.php" class="nav-link <?=isActive('profile.php')?>">
          <i class="fas fa-user-cog"></i>
          <span>Profile</span>
        </a>
      </div>
    <?php endif; ?>
    
    <div class="nav-section nav-section-bottom">
      <a href="/attendance-system/backend/auth/logout.php" class="nav-link nav-link-logout">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </a>
    </div>
  </nav>
</div>

