<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: /attendance-system/");
    exit();
}
include "../../includes/header.php";
include "../../includes/sidebar.php";
$userId = $_SESSION['user_id'];
?>
<link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">
  <h2>Your Profile</h2>
  <div id="enrollment-status-section" style="max-width:500px; margin-bottom:2rem;"></div>
  <div id="profile-container">
    <div id="profile-loading">Loading...</div>
  </div>
  <div id="change-password-section" style="max-width:500px; margin-top:2.5rem;">
    <h3>Change Password</h3>
    <form id="change-password-form" autocomplete="off">
      <label for="current_password">Current Password</label>
      <input type="password" id="current_password" name="current_password" required style="width:100%;padding:0.5rem;margin-bottom:1rem;" autocomplete="current-password">
      <label for="new_password">New Password</label>
      <input type="password" id="new_password" name="new_password" required minlength="6" style="width:100%;padding:0.5rem;margin-bottom:1rem;" autocomplete="new-password">
      <label for="confirm_password">Confirm New Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required minlength="6" style="width:100%;padding:0.5rem;margin-bottom:1rem;" autocomplete="new-password">
      <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
  </div>
</main>
<script>
const userId = <?php echo json_encode($userId); ?>;
const container = document.getElementById('profile-container');
const enrollmentSection = document.getElementById('enrollment-status-section');

function renderProfileForm(user) {
  container.innerHTML = `
    <form id="profile-form" style="max-width: 500px;">
      <label for="student_number">Student Number</label>
      <input type="text" id="student_number" name="student_number" value="${user.student_number || ''}" disabled style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" value="${user.name || ''}" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">
      <div class="input-feedback" id="name-feedback"></div>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="${user.email || ''}" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">
      <div class="input-feedback" id="email-feedback"></div>
      <label for="phone">Phone Number</label>
      <input type="text" id="phone" name="phone" value="${user.phone || ''}" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">
      <div class="input-feedback" id="phone-feedback"></div>
      <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
  `;
  
  // Real-time validation
  const nameInput = document.getElementById('name');
  const emailInput = document.getElementById('email');
  const phoneInput = document.getElementById('phone');
  
  nameInput.addEventListener('input', function() {
    const feedback = document.getElementById('name-feedback');
    if (!nameInput.value) {
      feedback.textContent = '';
      feedback.className = 'input-feedback';
    } else if (nameInput.value.length < 2) {
      feedback.textContent = 'Name must be at least 2 characters';
      feedback.className = 'input-feedback invalid';
    } else {
      feedback.textContent = 'Looks good!';
      feedback.className = 'input-feedback valid';
    }
  });
  
  emailInput.addEventListener('input', function() {
    const feedback = document.getElementById('email-feedback');
    if (!emailInput.value) {
      feedback.textContent = '';
      feedback.className = 'input-feedback';
    } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(emailInput.value)) {
      feedback.textContent = 'Invalid email format';
      feedback.className = 'input-feedback invalid';
    } else {
      feedback.textContent = 'Looks good!';
      feedback.className = 'input-feedback valid';
    }
  });
  
  phoneInput.addEventListener('input', function() {
    const feedback = document.getElementById('phone-feedback');
    if (!phoneInput.value) {
      feedback.textContent = '';
      feedback.className = 'input-feedback';
    } else if (!/^\d{10,}$/.test(phoneInput.value)) {
      feedback.textContent = 'Phone must be at least 10 digits';
      feedback.className = 'input-feedback invalid';
    } else {
      feedback.textContent = 'Looks good!';
      feedback.className = 'input-feedback valid';
    }
  });
  
  // Form submission
  document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Client-side validation
    let valid = true;
    if (nameInput.value.length < 2) {
      valid = false;
      document.getElementById('name-feedback').textContent = 'Name must be at least 2 characters';
      document.getElementById('name-feedback').className = 'input-feedback invalid';
    }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(emailInput.value)) {
      valid = false;
      document.getElementById('email-feedback').textContent = 'Invalid email format';
      document.getElementById('email-feedback').className = 'input-feedback invalid';
    }
    if (!/^\d{10,}$/.test(phoneInput.value)) {
      valid = false;
      document.getElementById('phone-feedback').textContent = 'Phone must be at least 10 digits';
      document.getElementById('phone-feedback').className = 'input-feedback invalid';
    }
    
    if (!valid) {
      if (typeof showToast === 'function') {
        showToast('Validation Error', 'Please fix the errors in the form.', 'error');
      } else {
        alert('Please fix the errors in the form.');
      }
      return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Updating...';
    submitBtn.disabled = true;
    
    // AJAX update
    fetch('/attendance-system/backend/user/update.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        name: nameInput.value, 
        email: emailInput.value, 
        phone: phoneInput.value 
      })
    })
    .then(res => res.json())
    .then(data => {
      // Restore button state
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      
      if (data.success) {
        if (typeof showToast === 'function') {
          showToast('Success', 'Profile updated successfully!', 'success');
        } else {
          alert('Profile updated successfully!');
        }
      } else {
        if (typeof showToast === 'function') {
          showToast('Error', data.error || 'Failed to update profile.', 'error');
        } else {
          alert(data.error || 'Failed to update profile.');
        }
      }
    })
    .catch((error) => {
      // Restore button state
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      
      console.error('Profile update error:', error);
      if (typeof showToast === 'function') {
        showToast('Error', 'Network error. Please try again.', 'error');
      } else {
        alert('Network error. Please try again.');
      }
    });
  });
}

function renderEnrollmentStatus(status) {
  let html = `<h3>Enrollment Status</h3>`;
  // RFID
  html += `<div style="margin-bottom:1rem;">
    <strong>RFID:</strong> `;
  if (status.rfid.status === 'enrolled') {
    html += `<span style='color:green;font-weight:bold;'>Enrolled</span> <br/><small>ID: ${status.rfid.id}</small>`;
  } else {
    html += `<span style='color:red;font-weight:bold;'>Not Enrolled</span> <br/>`;
    html += `<button id='request-rfid-btn' class='btn btn-secondary' style='margin-top:0.5rem;'>Request RFID Enrollment</button>`;
  }
  html += `</div>`;
  // Fingerprint
  html += `<div style="margin-bottom:1rem;">
    <strong>Fingerprint:</strong> `;
  if (status.fingerprint.status === 'enrolled') {
    html += `<span style='color:green;font-weight:bold;'>Enrolled</span> <br/><small>ID: ${status.fingerprint.id}</small>`;
  } else {
    html += `<span style='color:red;font-weight:bold;'>Not Enrolled</span> <br/>`;
    html += `<button id='request-fingerprint-btn' class='btn btn-secondary' style='margin-top:0.5rem;'>Request Fingerprint Enrollment</button>`;
  }
  html += `</div>`;
  enrollmentSection.innerHTML = html;

  // Add event listeners for request buttons
  if (status.rfid.status !== 'enrolled') {
    document.getElementById('request-rfid-btn').onclick = function() {
      submitEnrollmentRequest('rfid');
    };
  }
  if (status.fingerprint.status !== 'enrolled') {
    document.getElementById('request-fingerprint-btn').onclick = function() {
      submitEnrollmentRequest('fingerprint');
    };
  }
}

function submitEnrollmentRequest(type) {
  const btn = type === 'rfid' ? document.getElementById('request-rfid-btn') : document.getElementById('request-fingerprint-btn');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Requesting...';
  }
  fetch('/attendance-system/backend/student/request_enrollment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ type })
  })
  .then(res => res.json())
  .then(data => {
    if (btn) {
      btn.disabled = false;
      btn.textContent = type === 'rfid' ? 'Request RFID Enrollment' : 'Request Fingerprint Enrollment';
    }
    if (data.success) {
      alert('Enrollment request submitted. Please wait for your professor to process your request.');
    } else {
      alert(data.error || 'Failed to submit request.');
    }
  })
  .catch(() => {
    if (btn) {
      btn.disabled = false;
      btn.textContent = type === 'rfid' ? 'Request RFID Enrollment' : 'Request Fingerprint Enrollment';
    }
    alert('Network error. Please try again.');
  });
}

// Load profile data
fetch(`/attendance-system/backend/user/profile.php`)
  .then(res => res.json())
  .then(data => {
    if (data.success && data.user) {
      renderProfileForm(data.user);
    } else {
      container.innerHTML = `<div style='color:red;'>${data.error || 'Failed to load profile.'}</div>`;
    }
  })
  .catch((error) => {
    console.error('Profile load error:', error);
    container.innerHTML = `<div style='color:red;'>Failed to load profile.</div>`;
  });

// Fetch and render enrollment status
fetch('/attendance-system/backend/student/get_enrollment_status.php')
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      renderEnrollmentStatus(data);
    } else {
      enrollmentSection.innerHTML = `<div style='color:red;'>${data.error || 'Failed to load enrollment status.'}</div>`;
    }
  })
  .catch(() => {
    enrollmentSection.innerHTML = `<div style='color:red;'>Failed to load enrollment status.</div>`;
  });

// Add JS for change password form
const changePasswordForm = document.getElementById('change-password-form');
if (changePasswordForm) {
  changePasswordForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const currentPass = document.getElementById('current_password').value.trim();
    const newPass = document.getElementById('new_password').value.trim();
    const confirm = document.getElementById('confirm_password').value.trim();
    if (!currentPass || !newPass || !confirm) {
      showToast('Validation Error', 'All fields are required.', 'error');
      return;
    }
    if (newPass.length < 6) {
      showToast('Validation Error', 'New password must be at least 6 characters.', 'error');
      return;
    }
    if (newPass !== confirm) {
      showToast('Validation Error', 'New password and confirm password do not match.', 'error');
      return;
    }
    fetch('/attendance-system/backend/user/request_password_change.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        current_password: currentPass,
        new_password: newPass,
        confirm_password: confirm
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('Pending', 'Password change request submitted. Waiting for professor approval.', 'info');
        changePasswordForm.reset();
      } else {
        showToast('Error', data.error || 'Failed to submit password change request.', 'error');
      }
    })
    .catch(() => {
      showToast('Error', 'Network error. Please try again.', 'error');
    });
  });
}
</script>
<?php include "../../includes/footer.php"; ?>
