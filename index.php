<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login & Register - Attendance System</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    #register-form {
      display: none;
      margin-top: 20px;
      border-top: 1px solid #ccc;
      padding-top: 20px;
    }

    .toggle-button {
      margin-top: 20px;
      background-color: #007bff;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .toggle-button:hover {
      background-color: #0056b3;
    }

    .error {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    
    <!-- ✅ FIXED: Added id="login-form" and added error-msg display -->
    <form id="login-form" action="backend/auth/login.php" method="POST">
      <label for="email">Email:</label>
      <input type="email" name="email" required />

      <label for="password">Password:</label>
      <input type="password" name="password" required />

      <label for="role">Role:</label>
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="professor">Professor</option>
        <option value="student">Student</option>
      </select>

      <button type="submit">Login</button>

      <!-- ✅ FIXED: Added this for JS error display -->
      <p id="error-msg" class="error" style="display: none;"></p>
    </form>

    <button class="toggle-button" onclick="toggleRegister()">Click to Register</button>

    <div id="register-form">
      <h2>Register</h2>
      <form action="backend/auth/register.php" method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required />

        <label for="email">Email:</label>
        <input type="email" name="email" required />

        <label for="password">Password:</label>
        <input type="password" name="password" required />

        <label for="role">Role:</label>
        <select name="role" required>
          <option value="">Select Role</option>
          <option value="professor">Professor</option>
          <option value="student">Student</option>
        </select>

        <button type="submit">Register</button>
      </form>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
      <p class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
  </div>

  <script>
    function toggleRegister() {
      const registerForm = document.getElementById('register-form');
      registerForm.style.display = registerForm.style.display === 'none' ? 'block' : 'none';
    }
  </script>

  <!-- ✅ Include main JavaScript file -->
  <script src="assets/js/script.js"></script>
</body>
</html>
