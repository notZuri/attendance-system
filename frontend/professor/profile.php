<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: /attendance-system/");
    exit();
}

// Dummy data, replace with real data fetch from DB
$professorName = $_SESSION['name'] ?? "Professor Name";
$email = $_SESSION['email'] ?? "professor@example.com";
$phone = $_SESSION['phone'] ?? "123-456-7890";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Professor - Attendance Monitoring</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
  <script src="/attendance-system/assets/js/script.js" defer></script>
</head>
<body class="dashboard-bg">
  <?php include "../../includes/header.php"; ?>
  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>
    <main class="main-content">
      <h2>My Profile</h2>
      <form id="profile-form" class="card" style="max-width: 600px; margin-top: 1rem;">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required autocomplete="name">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="email">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" autocomplete="tel">
        <button type="submit" class="btn btn-primary w-100" style="margin-top: 1rem;">Update Profile</button>
      </form>
      <div id="profile-message" style="margin-top: 1rem;"></div>
    </main>
  </div>
  <?php include "../../includes/footer.php"; ?>
  <script>
  // Fetch profile
  fetch('/attendance-system/backend/user/profile.php')
    .then(res => res.json())
    .then(data => {
      console.log('Profile API response:', data);
      if (data.success && data.user) {
        document.getElementById('name').value = data.user.name || '';
        document.getElementById('email').value = data.user.email || '';
        document.getElementById('phone').value = data.user.phone || '';
      } else {
        console.error('Failed to load profile:', data.error || 'Unknown error');
        showToast('Failed to load profile data.', 'error');
      }
    })
    .catch(error => {
      console.error('Error fetching profile:', error);
      showToast('Network error loading profile.', 'error');
    });
    
  // Update profile
  const form = document.getElementById('profile-form');
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
      name: document.getElementById('name').value,
      email: document.getElementById('email').value,
      phone: document.getElementById('phone').value
    };
    
    console.log('Submitting profile update:', formData);
    
    fetch('/attendance-system/backend/user/update.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
      console.log('Update API response:', data);
      const msg = document.getElementById('profile-message');
      if (data.success) {
        msg.textContent = 'Profile updated successfully!';
        msg.style.color = 'green';
        showToast('Profile updated successfully!', 'success');
      } else {
        msg.textContent = data.error || 'Failed to update profile.';
        msg.style.color = 'red';
        showToast(data.error || 'Failed to update profile.', 'error');
      }
    })
    .catch(error => {
      console.error('Error updating profile:', error);
      const msg = document.getElementById('profile-message');
      msg.textContent = 'Network error updating profile.';
      msg.style.color = 'red';
      showToast('Network error updating profile.', 'error');
    });
  });
  </script>
</body>
</html>
