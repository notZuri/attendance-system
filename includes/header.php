<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Automated Attendance System</title>
  
  <!-- Favicon -->
  <link rel="icon" href="/attendance-system/assets/images/capslogo.png" type="image/x-icon" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Main stylesheet -->
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
  <script defer src="/attendance-system/assets/js/script.js"></script>
  <?php if (isset($_SESSION['role'])): ?>
  <script>window.__USER_ROLE__ = <?php echo json_encode($_SESSION['role']); ?>;</script>
  <?php endif; ?>
</head>
<body>
<div class="toast-container"></div>

<header class="main-header">
  <div class="header-container">
    <div class="header-left">
      <button class="mobile-menu-toggle" id="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
      </button>
      <div class="logo-container">
        <a href="/attendance-system/frontend/professor/dashboard.php" class="logo-link" aria-label="Go to Dashboard">
        <img src="/attendance-system/assets/images/capslogo.png" alt="CAPS Logo" class="header-logo" />
        <div class="logo-text">
          <h1 class="system-title">Automated Attendance System</h1>
          <span class="system-subtitle">CAPS - College of Computer Studies</span>
        </div>
        </a>
      </div>
    </div>
    
    <div class="header-right">
      <button class="notification-bell" aria-label="Notifications">
        <i class="fas fa-bell"></i>
      </button>
      <?php if(isset($_SESSION['name'])): ?>
        <div class="user-menu">
          <div class="user-info">
            <div class="user-avatar">
              <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
              <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
              <span class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'User')); ?></span>
            </div>
            <div class="user-dropdown-toggle">
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>
          <div class="user-dropdown">
            <a href="/attendance-system/frontend/<?php echo $_SESSION['role']; ?>/profile.php" class="dropdown-item">
              <i class="fas fa-user"></i>
              <span>Profile</span>
            </a>
            <a href="/attendance-system/backend/auth/logout.php" class="dropdown-item logout-item">
              <i class="fas fa-sign-out-alt"></i>
              <span>Logout</span>
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Notification Modal -->
<div id="notification-modal" class="modal" style="display:none;">
  <div class="modal-content" style="max-width:500px;">
    <span class="close" id="close-notification-modal">&times;</span>
    <h3>Notifications</h3>
    <div id="notification-list">
      <p>Loading notifications...</p>
    </div>
  </div>
</div>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobile-overlay"></div>

<div class="wrapper">
