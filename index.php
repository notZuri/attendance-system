<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login & Register - Attendance System</title>
  <link rel="icon" href="assets/images/capslogo.png" type="image/x-icon">
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="auth-bg">
  <!-- Toast Notifications Container -->
  <div id="toast-container" class="toast-container"></div>
  
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logo-container">
          <img src="assets/images/capslogo.png" alt="Attendance System Logo" class="auth-logo" />
          <div class="auth-logo-glow"></div>
        </div>
        <h1 class="auth-title">Attendance System</h1>
        <p class="auth-welcome">Welcome! Please log in or register to continue.</p>
      </div>
      
      <div class="auth-tabs">
        <button id="login-tab" class="auth-tab active" onclick="showAuthForm('login')" aria-controls="login-form" aria-selected="true" tabindex="0">
          <span class="tab-icon">🔐</span>
          <span class="tab-text">Login</span>
        </button>
        <button id="register-tab" class="auth-tab" onclick="showAuthForm('register')" aria-controls="register-form" aria-selected="false" tabindex="0">
          <span class="tab-icon">📝</span>
          <span class="tab-text">Register</span>
        </button>
      </div>
      
      <!-- Error messages container - outside scrollable forms -->
      <div class="auth-errors">
        <div id="login-error" class="auth-error" style="display:none;"></div>
        <div id="register-error" class="auth-error" style="display:none;"></div>
      </div>
      
      <div class="auth-forms">
        <form id="login-form" class="auth-form" action="backend/auth/login.php" method="POST" autocomplete="on" aria-labelledby="login-tab" novalidate>
          <!-- Login Information Section -->
          <div class="form-section">
            <div class="section-header">
              <span>🔐</span>
              Login Information
            </div>
            <div class="input-group">
              <label for="login-email">Email Address</label>
              <input type="email" id="login-email" name="email" required placeholder="Enter your email" aria-required="true" autocomplete="email" />
              <span class="input-feedback" id="login-email-feedback"></span>
            </div>
            <div class="input-group">
              <label for="login-password">Password</label>
              <div class="input-icon-group">
                <input type="password" id="login-password" name="password" required placeholder="Enter your password" aria-required="true" autocomplete="current-password" />
                <button type="button" class="toggle-password" tabindex="0" aria-label="Show password" onclick="togglePassword('login-password', this)"><i class="icon-eye"></i></button>
              </div>
              <span class="input-feedback" id="login-password-feedback"></span>
            </div>
            <div class="input-group">
              <label for="login-role">Account Type</label>
              <select id="login-role" name="role" required aria-required="true" autocomplete="off">
                <option value="">Select your role</option>
                <option value="professor">Professor</option>
                <option value="student">Student</option>
              </select>
              <span class="input-feedback" id="login-role-feedback"></span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100" id="login-btn">
            <span class="btn-text">Login</span>
            <span class="btn-spinner" style="display:none;"></span>
          </button>
    </form>

        <form id="register-form" class="auth-form" action="backend/auth/register.php" method="POST" style="display:none;" autocomplete="on" aria-labelledby="register-tab" novalidate>
          <!-- Basic Information Section -->
          <div class="form-section">
            <div class="section-header">
              <span>👤</span>
              Basic Information
            </div>
            <div class="input-group">
              <label for="register-name">Full Name</label>
              <input type="text" id="register-name" name="name" required placeholder="Enter your full name" aria-required="true" autocomplete="name" />
              <span class="input-feedback" id="register-name-feedback"></span>
            </div>
            <div class="input-group">
              <label for="register-email">Email Address</label>
              <input type="email" id="register-email" name="email" required placeholder="Enter your email" aria-required="true" autocomplete="email" />
              <span class="input-feedback" id="register-email-feedback"></span>
            </div>
          </div>
          
          <!-- Account Security Section -->
          <div class="form-section">
            <div class="section-header">
              <span>🔐</span>
              Account Security
            </div>
            <div class="input-group">
              <label for="register-password">Password</label>
              <div class="input-icon-group">
                <input type="password" id="register-password" name="password" required placeholder="Create a strong password" aria-required="true" autocomplete="new-password" />
                <button type="button" class="toggle-password" tabindex="0" aria-label="Show password" onclick="togglePassword('register-password', this)"><i class="icon-eye"></i></button>
              </div>
              <span class="input-feedback" id="register-password-feedback"></span>
            </div>
            <div class="input-group">
              <label for="register-role">Account Type</label>
              <select id="register-role" name="role" required aria-required="true" autocomplete="off">
                <option value="">Select your role</option>
                <option value="professor">Professor</option>
                <option value="student">Student</option>
              </select>
              <span class="input-feedback" id="register-role-feedback"></span>
            </div>
          </div>
          
          <!-- Student-specific fields (hidden by default) -->
          <div id="student-fields" class="student-fields" style="display:none;">
            <div class="section-header">
              <span>🎓</span>
              Student Information
            </div>
            <div class="student-fields-grid">
              <div class="input-group">
                <label for="register-phone">Phone Number</label>
                <input type="tel" id="register-phone" name="phone" required placeholder="Enter your phone number" autocomplete="tel" />
                <span class="input-feedback" id="register-phone-feedback"></span>
              </div>
              <div class="input-group">
                <label for="register-course">Course/Program</label>
                <input type="text" id="register-course" name="course" required placeholder="e.g., BSIT, BSCS, etc." />
                <span class="input-feedback" id="register-course-feedback"></span>
              </div>
              <div class="input-group">
                <label for="register-year-level">Year Level</label>
                <select id="register-year-level" name="year_level" required>
                  <option value="">Select year level</option>
                  <option value="1">1st Year</option>
                  <option value="2">2nd Year</option>
                  <option value="3">3rd Year</option>
                  <option value="4">4th Year</option>
                  <option value="5">5th Year</option>
                  <option value="6">6th Year</option>
                </select>
                <span class="input-feedback" id="register-year-level-feedback"></span>
              </div>
              <div class="input-group">
                <label for="register-section">Section</label>
                <input type="text" id="register-section" name="section" required placeholder="e.g., A006, N007, etc." />
                <span class="input-feedback" id="register-section-feedback"></span>
              </div>
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary w-100" id="register-btn">
            <span class="btn-text">Create Account</span>
            <span class="btn-spinner" style="display:none;"></span>
          </button>
      </form>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="auth-error show">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    </div>
  </div>
  <script src="assets/js/script.js"></script>
</body>
</html>
