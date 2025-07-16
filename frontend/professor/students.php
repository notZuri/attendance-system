<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: /attendance-system/");
    exit();
}
include "../../includes/header.php";
include "../../includes/sidebar.php";
?>
<link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">
  <h2>Student Management</h2>
  <button class="btn btn-primary add-student-btn" onclick="showAddStudentModal()">Add New Student</button>
  <div class="card card-accent-lightblue table-container">
    <div class="table-responsive">
      <table id="students-table" class="data-table">
      <thead>
        <tr>
          <th>Student Number</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Course</th>
          <th>Year Level</th>
          <th>Section</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="students-tbody">
        <tr><td colspan="8" id="students-loading">Loading...</td></tr>
      </tbody>
    </table>
    </div>
  </div>
</main>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal" style="display: none;">
  <div class="modal-content student-modal">
    <span class="close" onclick="closeAddStudentModal()">&times;</span>
    <h3>Add New Student</h3>
    
    <!-- Error message container -->
    <div id="add-student-error" class="auth-error" style="display:none;"></div>
    
    <form id="addStudentForm">
      <div class="form-group">
        <label for="add-name">Full Name</label>
        <input type="text" id="add-name" name="name" required autocomplete="name">
        <span class="input-feedback" id="add-name-feedback"></span>
      </div>
      <div class="form-group">
        <label for="add-email">Email</label>
        <input type="email" id="add-email" name="email" required autocomplete="email">
        <span class="input-feedback" id="add-email-feedback"></span>
      </div>
      <div class="form-group">
        <label for="add-phone">Phone Number</label>
        <input type="text" id="add-phone" name="phone" required autocomplete="tel">
        <span class="input-feedback" id="add-phone-feedback"></span>
      </div>
      <div class="form-group">
        <label for="add-course">Course/Program</label>
        <input type="text" id="add-course" name="course" required placeholder="e.g., Bachelor of Science in Computer Science">
        <span class="input-feedback" id="add-course-feedback"></span>
      </div>
      <div class="form-group">
        <label for="add-year-level">Year Level</label>
        <select id="add-year-level" name="year_level" required>
          <option value="">Select year level</option>
          <option value="1">1st Year</option>
          <option value="2">2nd Year</option>
          <option value="3">3rd Year</option>
          <option value="4">4th Year</option>
          <option value="5">5th Year</option>
          <option value="6">6th Year</option>
        </select>
        <span class="input-feedback" id="add-year-level-feedback"></span>
      </div>
      <div class="form-group">
        <label for="add-section">Section</label>
        <input type="text" id="add-section" name="section" required placeholder="e.g., A, B, C, or specific section name">
        <span class="input-feedback" id="add-section-feedback"></span>
      </div>
      <div class="form-group">
        <label for="add-password">Password</label>
        <input type="password" id="add-password" name="password" required autocomplete="new-password">
        <span class="input-feedback" id="add-password-feedback"></span>
      </div>
      <button type="submit" class="btn btn-primary" id="add-student-btn">
        <span class="btn-text">Add Student</span>
        <span class="btn-spinner" style="display:none;"></span>
      </button>
    </form>
  </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal" style="display: none;">
  <div class="modal-content student-modal">
    <span class="close" onclick="closeEditStudentModal()">&times;</span>
    <h3>Edit Student</h3>
    
    <!-- Error message container -->
    <div id="edit-student-error" class="auth-error" style="display:none;"></div>
    
    <form id="editStudentForm">
      <input type="hidden" id="edit-id" name="id">
      <div class="form-group">
        <label for="edit-name">Full Name</label>
        <input type="text" id="edit-name" name="name" required autocomplete="name">
        <span class="input-feedback" id="edit-name-feedback"></span>
      </div>
      <div class="form-group">
        <label for="edit-student-number">Student Number</label>
        <input type="text" id="edit-student-number" name="student_number" required readonly>
        <span class="input-feedback" id="edit-student-number-feedback"></span>
      </div>
      <div class="form-group">
        <label for="edit-email">Email</label>
        <input type="email" id="edit-email" name="email" required autocomplete="email">
        <span class="input-feedback" id="edit-email-feedback"></span>
      </div>
      <div class="form-group">
        <label for="edit-phone">Phone Number</label>
        <input type="text" id="edit-phone" name="phone" required autocomplete="tel">
        <span class="input-feedback" id="edit-phone-feedback"></span>
      </div>
      <div class="form-group">
        <label for="edit-course">Course/Program</label>
        <input type="text" id="edit-course" name="course" required>
        <span class="input-feedback" id="edit-course-feedback"></span>
      </div>
      <div class="form-group">
        <label for="edit-year-level">Year Level</label>
        <select id="edit-year-level" name="year_level" required>
          <option value="">Select year level</option>
          <option value="1">1st Year</option>
          <option value="2">2nd Year</option>
          <option value="3">3rd Year</option>
          <option value="4">4th Year</option>
          <option value="5">5th Year</option>
          <option value="6">6th Year</option>
        </select>
        <span class="input-feedback" id="edit-year-level-feedback"></span>
      </div>
      <div class="form-group">
        <label for="edit-section">Section</label>
        <input type="text" id="edit-section" name="section" required>
        <span class="input-feedback" id="edit-section-feedback"></span>
      </div>
      <button type="submit" class="btn btn-primary" id="edit-student-btn">
        <span class="btn-text">Update Student</span>
        <span class="btn-spinner" style="display:none;"></span>
      </button>
    </form>
  </div>
</div>

<!-- Student Attendance Summary Modal -->
<div id="studentSummaryModal" class="modal" style="display: none;">
  <div class="modal-content student-modal" style="max-width: 500px;">
    <span class="close" onclick="closeStudentSummaryModal()">&times;</span>
    <h3 id="student-summary-title">Attendance Summary</h3>
    <div id="student-summary-body">
      <p>Loading...</p>
    </div>
  </div>
</div>

<!-- Add Enrollment Modal HTML after other modals -->
<div id="enrollmentModal" class="modal" style="display:none;">
  <div class="modal-content student-modal" style="max-width: 400px;">
    <span class="close" onclick="closeEnrollmentModal()">&times;</span>
    <h3 id="enrollment-modal-title">Enrollment</h3>
    <div id="enrollment-modal-body">
      <p id="enrollment-modal-message">Initializing...</p>
      <div id="enrollment-modal-spinner" class="spinner" style="margin: 1rem auto;"></div>
    </div>
    <div style="text-align:right; margin-top:1rem;">
      <button class="btn btn-secondary" onclick="closeEnrollmentModal()">Cancel</button>
    </div>
  </div>
</div>

<script>
// Global variables
let currentStudents = [];

// Load students from users table where role = 'student'
function loadStudents() {
  const tbody = document.getElementById('students-tbody');
  tbody.innerHTML = '<tr><td colspan="8" id="students-loading">Loading...</td></tr>';
  
  fetch('/attendance-system/backend/student/list.php')
    .then(res => res.json())
    .then(data => {
      tbody.innerHTML = '';
      if (data.success && data.students) {
        currentStudents = data.students;
        data.students.forEach(student => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${student.student_number || ''}</td>
            <td>${student.name || ''}</td>
            <td>${student.email || ''}</td>
            <td>${student.phone || ''}</td>
            <td>${student.course || 'N/A'}</td>
            <td>${student.year_level ? student.year_level + getOrdinalSuffix(student.year_level) + ' Year' : 'N/A'}</td>
            <td>${student.section || 'N/A'}</td>
            <td>
              <button class="btn btn-edit" onclick="editStudent(${student.id})">✎ Edit</button>
              <button class="btn btn-delete" onclick="deleteStudent(${student.id})">🗑️ Delete</button>
              <button class="btn btn-info" onclick="viewStudentSummary(${student.id}, '${student.name.replace(/'/g, "&#39;")}')">👁️‍🗨️ View Record</button>
              <button class="btn btn-enroll-rfid" onclick="startRFIDEnrollment(${student.id}, '${student.name.replace(/'/g, "&#39;")}')">🔑 Enroll RFID</button>
              <button class="btn btn-enroll-fingerprint" onclick="startFingerprintEnrollment(${student.id}, '${student.name.replace(/'/g, "&#39;")}')">🖐️ Enroll Fingerprint</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        tbody.innerHTML = `<tr><td colspan='8'>${data.error || 'No students found.'}</td></tr>`;
      }
    })
    .catch((error) => {
      console.error('Error loading students:', error);
      tbody.innerHTML = '<tr><td colspan="8">Failed to load students.</td></tr>';
    });
}

// Helper function to get ordinal suffix
function getOrdinalSuffix(num) {
  const j = num % 10;
  const k = num % 100;
  if (j == 1 && k != 11) {
    return "st";
  }
  if (j == 2 && k != 12) {
    return "nd";
  }
  if (j == 3 && k != 13) {
    return "rd";
  }
  return "th";
}

// Modal functions
function showAddStudentModal() {
  document.getElementById('addStudentModal').style.display = 'block';
  // Clear any previous errors
  clearFormErrors('add');
}

function closeAddStudentModal() {
  document.getElementById('addStudentModal').style.display = 'none';
  document.getElementById('addStudentForm').reset();
  clearFormErrors('add');
}

function showEditStudentModal() {
  document.getElementById('editStudentModal').style.display = 'block';
  // Clear any previous errors
  clearFormErrors('edit');
}

function closeEditStudentModal() {
  document.getElementById('editStudentModal').style.display = 'none';
  document.getElementById('editStudentForm').reset();
  clearFormErrors('edit');
}

// Clear form errors
function clearFormErrors(type) {
  const errorDiv = document.getElementById(type + '-student-error');
  if (errorDiv) {
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
  }
  
  // Clear all feedback
  const form = document.getElementById(type + 'StudentForm');
  if (form) {
    form.querySelectorAll('.input-feedback').forEach(feedback => {
      feedback.textContent = '';
      feedback.className = 'input-feedback';
    });
    form.querySelectorAll('input, select').forEach(input => {
      input.classList.remove('invalid');
    });
  }
}

// Setup validation for add student form
function setupAddStudentValidation() {
  const form = document.getElementById('addStudentForm');
  const name = form.querySelector('#add-name');
  const email = form.querySelector('#add-email');
  const phone = form.querySelector('#add-phone');
  const course = form.querySelector('#add-course');
  const yearLevel = form.querySelector('#add-year-level');
  const section = form.querySelector('#add-section');
  const password = form.querySelector('#add-password');

  // Function to clear form error message
  const clearFormError = () => {
    const errorDiv = document.getElementById('add-student-error');
    if (errorDiv && errorDiv.style.display !== 'none') {
      errorDiv.style.display = 'none';
      errorDiv.textContent = '';
    }
  };

  // Name validation
  if (name) {
    name.addEventListener('input', function() {
      clearFormError();
      const feedback = document.getElementById('add-name-feedback');
      if (!name.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else if (name.value.length < 2) {
        feedback.textContent = 'Name must be at least 2 characters';
        feedback.className = 'input-feedback invalid';
      } else {
        feedback.textContent = 'Looks good!';
        feedback.className = 'input-feedback valid';
      }
    });
  }

  // Email validation
  if (email) {
    email.addEventListener('input', function() {
      clearFormError();
      const feedback = document.getElementById('add-email-feedback');
      if (!email.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email.value)) {
        feedback.textContent = 'Invalid email format';
        feedback.className = 'input-feedback invalid';
      } else {
        feedback.textContent = 'Looks good!';
        feedback.className = 'input-feedback valid';
      }
    });
  }

  // Phone validation
  if (phone) {
    phone.addEventListener('input', function() {
      clearFormError();
      const feedback = document.getElementById('add-phone-feedback');
      if (!phone.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else if (!/^\d{10,}$/.test(phone.value)) {
        feedback.textContent = 'Phone must be at least 10 digits';
        feedback.className = 'input-feedback invalid';
      } else {
        feedback.textContent = 'Looks good!';
        feedback.className = 'input-feedback valid';
      }
    });
  }

  // Course validation
  if (course) {
    course.addEventListener('input', function() {
      clearFormError();
      const feedback = document.getElementById('add-course-feedback');
      if (!course.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else if (course.value.length < 3) {
        feedback.textContent = 'Course name too short';
        feedback.className = 'input-feedback invalid';
      } else {
        feedback.textContent = 'Looks good!';
        feedback.className = 'input-feedback valid';
      }
    });
  }

  // Year level validation
  if (yearLevel) {
    yearLevel.addEventListener('change', function() {
      clearFormError();
      const feedback = document.getElementById('add-year-level-feedback');
      if (!yearLevel.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else {
        feedback.textContent = 'Year level selected';
        feedback.className = 'input-feedback valid';
      }
    });
  }

  // Section validation
  if (section) {
    section.addEventListener('input', function() {
      clearFormError();
      const feedback = document.getElementById('add-section-feedback');
      if (!section.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else if (section.value.length < 1) {
        feedback.textContent = 'Section is required';
        feedback.className = 'input-feedback invalid';
      } else {
        feedback.textContent = 'Looks good!';
        feedback.className = 'input-feedback valid';
      }
    });
  }

  // Password validation
  if (password) {
    password.addEventListener('input', function() {
      clearFormError();
      const feedback = document.getElementById('add-password-feedback');
      if (!password.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else if (password.value.length < 6) {
        feedback.textContent = 'Password must be at least 6 characters';
        feedback.className = 'input-feedback invalid';
      } else {
        feedback.textContent = 'Strong password';
        feedback.className = 'input-feedback valid';
      }
    });
  }
}

// Edit student function
function editStudent(studentId) {
  const student = currentStudents.find(s => s.id == studentId);
  if (!student) {
    showMessage('Error', 'Student not found.', 'error');
    return;
  }
  
  // Populate edit form
  document.getElementById('edit-id').value = student.id;
  document.getElementById('edit-name').value = student.name || '';
  document.getElementById('edit-student-number').value = student.student_number || '';
  document.getElementById('edit-email').value = student.email || '';
  document.getElementById('edit-phone').value = student.phone || '';
  document.getElementById('edit-course').value = student.course || '';
  document.getElementById('edit-year-level').value = student.year_level || '';
  document.getElementById('edit-section').value = student.section || '';
  
  showEditStudentModal();
}

// Add student form handler with comprehensive validation
document.getElementById('addStudentForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const form = this;
  const btn = document.getElementById('add-student-btn');
  const spinner = btn.querySelector('.btn-spinner');
  const btnText = btn.querySelector('.btn-text');
  
  // Function to restore button state
  const restoreButtonState = () => {
    btnText.style.display = '';
    spinner.style.display = 'none';
    btn.disabled = false;
  };
  
  // Function to set loading state
  const setLoadingState = () => {
    btnText.style.display = 'none';
    spinner.style.display = 'inline-block';
    btn.disabled = true;
  };
  
  // Client-side validation
  let valid = true;
  let firstInvalidField = null;
  let firstInvalidMsg = '';
  
  const requiredFields = form.querySelectorAll('input[required], select[required]');
  requiredFields.forEach(function(input) {
    // Remove previous invalid state
    input.classList.remove('invalid');
    const feedback = document.getElementById('add-' + input.name + '-feedback');
    if (feedback) {
      feedback.textContent = '';
      feedback.className = 'input-feedback';
    }
    
    // Validation
    if (!input.value.trim()) {
      valid = false;
      if (!firstInvalidField) {
        firstInvalidField = input;
        if (input.name === 'name') firstInvalidMsg = 'Full name is required.';
        else if (input.name === 'email') firstInvalidMsg = 'Email is required.';
        else if (input.name === 'phone') firstInvalidMsg = 'Phone number is required.';
        else if (input.name === 'course') firstInvalidMsg = 'Course/Program is required.';
        else if (input.name === 'year_level') firstInvalidMsg = 'Year level is required.';
        else if (input.name === 'section') firstInvalidMsg = 'Section is required.';
        else if (input.name === 'password') firstInvalidMsg = 'Password is required.';
        else firstInvalidMsg = 'This field is required.';
      }
      return;
    }
    
    // Specific validations
    if (input.name === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(input.value)) {
      valid = false;
      if (!firstInvalidField) {
        firstInvalidField = input;
        firstInvalidMsg = 'Invalid email format.';
      }
      return;
    }
    
    if (input.name === 'password' && input.value.length < 6) {
      valid = false;
      if (!firstInvalidField) {
        firstInvalidField = input;
        firstInvalidMsg = 'Password must be at least 6 characters.';
      }
      return;
    }
    
    if (input.name === 'name' && input.value.length < 2) {
      valid = false;
      if (!firstInvalidField) {
        firstInvalidField = input;
        firstInvalidMsg = 'Name must be at least 2 characters.';
      }
      return;
    }
    
    if (input.name === 'phone' && !/^\d{10,}$/.test(input.value)) {
      valid = false;
      if (!firstInvalidField) {
        firstInvalidField = input;
        firstInvalidMsg = 'Phone must be at least 10 digits.';
      }
      return;
    }
    
    if (input.name === 'course' && input.value.length < 3) {
      valid = false;
      if (!firstInvalidField) {
        firstInvalidField = input;
        firstInvalidMsg = 'Course name must be at least 3 characters.';
      }
      return;
    }
  });
  
  if (!valid) {
    if (firstInvalidField) {
      firstInvalidField.classList.add('invalid');
      const feedback = document.getElementById('add-' + firstInvalidField.name + '-feedback');
      if (feedback) {
        feedback.textContent = firstInvalidMsg;
        feedback.className = 'input-feedback invalid';
      }
      
      // Show error message at top of form
      const errorDiv = document.getElementById('add-student-error');
      if (errorDiv) {
        errorDiv.textContent = firstInvalidMsg;
        errorDiv.className = 'auth-error show';
        errorDiv.style.display = 'block';
        
        // Auto-scroll to error message
        setTimeout(() => {
          errorDiv.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start',
            inline: 'nearest'
          });
        }, 100);
      }
      
      showMessage('Validation Error', firstInvalidMsg, 'error');
    }
    return;
  }
  
  // Set loading state
  setLoadingState();
  
  const formData = new FormData(form);
  formData.append('role', 'student'); // Always set role as student
  
  fetch('/attendance-system/backend/auth/register.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    restoreButtonState();
    
    if (data.success) {
      showMessage('Success', 'Student added successfully!', 'success');
      closeAddStudentModal();
      loadStudents();
    } else {
      // Handle specific backend errors
      let errorMessage = data.error || 'Failed to add student.';
      
      // Show error message at top of form
      const errorDiv = document.getElementById('add-student-error');
      if (errorDiv) {
        errorDiv.textContent = errorMessage;
        errorDiv.className = 'auth-error show';
        errorDiv.style.display = 'block';
      }
      
      showMessage('Error', errorMessage, 'error');
    }
  })
  .catch((error) => {
    restoreButtonState();
    console.error('Add student error:', error);
    
    const errorMessage = 'Network error. Please try again.';
    const errorDiv = document.getElementById('add-student-error');
    if (errorDiv) {
      errorDiv.textContent = errorMessage;
      errorDiv.className = 'auth-error show';
      errorDiv.style.display = 'block';
    }
    
    showMessage('Error', errorMessage, 'error');
  });
});

// Edit student form handler
document.getElementById('editStudentForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Updating...';
  submitBtn.disabled = true;
  
  const formData = new FormData(this);
  
  fetch('/attendance-system/backend/student/update_student.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
    
    if (data.success) {
      showMessage('Success', 'Student updated successfully!', 'success');
      closeEditStudentModal();
      loadStudents();
    } else {
      showMessage('Error', data.error || 'Failed to update student.', 'error');
    }
  })
  .catch((error) => {
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
    console.error('Update student error:', error);
    showMessage('Error', 'Network error. Please try again.', 'error');
  });
});

// Delete student
function deleteStudent(studentId) {
  if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
    fetch('/attendance-system/backend/student/delete_student.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: studentId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showMessage('Success', 'Student deleted successfully!', 'success');
        loadStudents();
      } else {
        showMessage('Error', data.error || 'Failed to delete student.', 'error');
      }
    })
    .catch((error) => {
      console.error('Delete student error:', error);
      showMessage('Error', 'Network error. Please try again.', 'error');
    });
  }
}

// Message display function with fallback
function showMessage(title, message, type = 'info') {
  if (typeof showToast === 'function') {
    showToast(title, message, type);
  } else {
    alert(`${title}: ${message}`);
  }
}

// Close modals when clicking outside
window.onclick = function(event) {
  const addModal = document.getElementById('addStudentModal');
  const editModal = document.getElementById('editStudentModal');
  const enrollmentModal = document.getElementById('enrollmentModal');
  
  if (event.target === addModal) {
    closeAddStudentModal();
  }
  if (event.target === editModal) {
    closeEditStudentModal();
  }
  if (event.target === enrollmentModal) {
    closeEnrollmentModal();
  }
}

function viewStudentSummary(studentId, studentName) {
  const modal = document.getElementById('studentSummaryModal');
  const title = document.getElementById('student-summary-title');
  const body = document.getElementById('student-summary-body');
  title.textContent = `Attendance Summary for ${studentName}`;
  body.innerHTML = '<p>Loading...</p>';
  modal.style.display = 'block';
  fetch(`/attendance-system/backend/attendance/student_summary.php?student_id=${studentId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.stats) {
        let html = `<div class="summary-stats-grid">
          <div class="summary-card total"><div class="summary-label"><strong>Total Records</strong></div><div class="summary-value">${data.stats.total}</div></div>
          <div class="summary-card present"><div class="summary-label"><strong>Present</strong></div><div class="summary-value">${data.stats.present}</div></div>
          <div class="summary-card late"><div class="summary-label"><strong>Late</strong></div><div class="summary-value">${data.stats.late}</div></div>
          <div class="summary-card absent"><div class="summary-label"><strong>Absent</strong></div><div class="summary-value">${data.stats.absent}</div></div>
          <div class="summary-card percent"><div class="summary-label"><strong>Attendance %</strong></div><div class="summary-value">${data.stats.percentage}%</div></div>
        </div>`;
        if (data.recent_records && data.recent_records.length > 0) {
          html += '<h4>Recent Records</h4>';
          html += '<table class="attendance-table"><thead><tr><th>Date</th><th>Status</th><th>Time In</th><th>Subject</th><th>Room</th></tr></thead><tbody>';
          data.recent_records.forEach(r => {
            html += `<tr><td>${r.attendance_date_formatted}</td><td>${r.status.charAt(0).toUpperCase() + r.status.slice(1)}</td><td>${r.time_in_12h || ''}</td><td>${r.subject}</td><td>${r.room}</td></tr>`;
          });
          html += '</tbody></table>';
        } else {
          html += '<p>No recent attendance records found.</p>';
        }
        body.innerHTML = html;
      } else {
        body.innerHTML = `<p style="color:#e74c3c;">${data.error || 'Failed to load summary.'}</p>`;
      }
    })
    .catch(() => {
      body.innerHTML = '<p style="color:#e74c3c;">Failed to load summary.</p>';
    });
}

function closeStudentSummaryModal() {
  document.getElementById('studentSummaryModal').style.display = 'none';
}

// --- Enrollment Modal HTML and Functions ---
if (!document.getElementById('enrollmentModal')) {
  const enrollmentModalHtml = `
    <div id="enrollmentModal" class="modal" style="display:none;">
      <div class="modal-content student-modal" style="max-width: 400px;">
        <span class="close" onclick="closeEnrollmentModal()">&times;</span>
        <h3 id="enrollment-modal-title">Enrollment</h3>
        <div id="enrollment-modal-body">
          <p id="enrollment-modal-message">Initializing...</p>
          <div id="enrollment-modal-spinner" class="spinner" style="margin: 1rem auto;"></div>
        </div>
        <div style="text-align:right; margin-top:1rem;">
          <button class="btn btn-secondary" onclick="closeEnrollmentModal()">Cancel</button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', enrollmentModalHtml);
}
function showEnrollmentModal(title, message) {
  document.getElementById('enrollment-modal-title').textContent = title;
  document.getElementById('enrollment-modal-message').textContent = message;
  document.getElementById('enrollmentModal').style.display = 'block';
}
function closeEnrollmentModal() {
  document.getElementById('enrollmentModal').style.display = 'none';
  if (window.enrollmentPollInterval) {
    clearInterval(window.enrollmentPollInterval);
    window.enrollmentPollInterval = null;
  }
}

// Enrollment Logic
async function startRFIDEnrollment(studentId, studentName) {
  showEnrollmentModal('RFID Enrollment', `Starting RFID enrollment for ${studentName}...`);
  try {
    const res = await fetch('/attendance-system/backend/rfid/enroll.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ student_id: studentId })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Failed to start RFID enrollment.');
    pollEnrollmentStatus(data.session_id, 'rfid');
    document.getElementById('enrollment-modal-message').textContent = 'Please scan the RFID card on the device...';
  } catch (err) {
    document.getElementById('enrollment-modal-message').textContent = err.message;
  }
}
async function startFingerprintEnrollment(studentId, studentName) {
  showEnrollmentModal('Fingerprint Enrollment', `Starting fingerprint enrollment for ${studentName}...`);
  try {
    const res = await fetch('/attendance-system/backend/fingerprint/enroll.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ student_id: studentId })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Failed to start fingerprint enrollment.');
    pollEnrollmentStatus(data.session_id, 'fingerprint');
    document.getElementById('enrollment-modal-message').textContent = 'Please scan the fingerprint on the device...';
  } catch (err) {
    document.getElementById('enrollment-modal-message').textContent = err.message;
  }
}
function pollEnrollmentStatus(sessionId, type) {
  if (window.enrollmentPollInterval) clearInterval(window.enrollmentPollInterval);
  window.enrollmentPollInterval = setInterval(async () => {
    try {
      const res = await fetch(`/attendance-system/backend/${type}/enrollment_status.php?session_id=${sessionId}`);
      const data = await res.json();
      if (data.status === 'success') {
        clearInterval(window.enrollmentPollInterval);
        window.enrollmentPollInterval = null;
        document.getElementById('enrollment-modal-message').textContent = `${type === 'rfid' ? 'RFID card' : 'Fingerprint'} enrolled successfully!`;
        setTimeout(closeEnrollmentModal, 2000);
      } else if (data.status === 'error') {
        clearInterval(window.enrollmentPollInterval);
        window.enrollmentPollInterval = null;
        document.getElementById('enrollment-modal-message').textContent = `Error: ${data.message || 'Enrollment failed.'}`;
      }
      // else still waiting
    } catch (err) {
      document.getElementById('enrollment-modal-message').textContent = 'Error polling enrollment status.';
    }
  }, 2000);
}

// Initialize validation when page loads
document.addEventListener('DOMContentLoaded', function() {
  setupAddStudentValidation();
  loadStudents();
  setupMobileTableInteraction();
});

// Enhanced mobile table interaction
function setupMobileTableInteraction() {
  const tableContainer = document.querySelector('.table-container');
  const tableResponsive = document.querySelector('.table-responsive');
  
  if (!tableContainer || !tableResponsive) return;
  
  let startX = 0;
  let startY = 0;
  let isScrolling = false;
  let swipeHintShown = false;
  
  // Touch event handlers for swipe detection
  tableResponsive.addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
    isScrolling = false;
  });
  
  tableResponsive.addEventListener('touchmove', function(e) {
    if (!isScrolling) {
      const deltaX = Math.abs(e.touches[0].clientX - startX);
      const deltaY = Math.abs(e.touches[0].clientY - startY);
      
      if (deltaX > deltaY && deltaX > 10) {
        isScrolling = true;
        // Show swipe hint on first interaction
        if (!swipeHintShown) {
          showSwipeHint();
          swipeHintShown = true;
        }
      }
    }
  });
  
  // Scroll event handler for scroll indicators
  tableResponsive.addEventListener('scroll', function() {
    updateScrollIndicators();
  });
  
  // Mouse wheel event for desktop
  tableResponsive.addEventListener('wheel', function(e) {
    if (e.deltaX !== 0) {
      updateScrollIndicators();
    }
  });
  
  // Update scroll indicators based on scroll position
  function updateScrollIndicators() {
    const scrollLeft = tableResponsive.scrollLeft;
    const maxScrollLeft = tableResponsive.scrollWidth - tableResponsive.clientWidth;
    
    tableContainer.classList.toggle('scrollable-left', scrollLeft > 0);
    tableContainer.classList.toggle('scrollable-right', scrollLeft < maxScrollLeft);
  }
  
  // Show swipe hint for mobile users
  function showSwipeHint() {
    if (window.innerWidth <= 768) {
      tableContainer.classList.add('show-swipe-hint');
      setTimeout(() => {
        tableContainer.classList.remove('show-swipe-hint');
      }, 3000);
    }
  }
  
  // Initial check for scroll indicators
  setTimeout(updateScrollIndicators, 100);
  
  // Add keyboard navigation for accessibility
  tableResponsive.addEventListener('keydown', function(e) {
    const scrollAmount = 200;
    
    switch(e.key) {
      case 'ArrowLeft':
        e.preventDefault();
        tableResponsive.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        break;
      case 'ArrowRight':
        e.preventDefault();
        tableResponsive.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        break;
      case 'Home':
        e.preventDefault();
        tableResponsive.scrollTo({ left: 0, behavior: 'smooth' });
        break;
      case 'End':
        e.preventDefault();
        tableResponsive.scrollTo({ left: tableResponsive.scrollWidth, behavior: 'smooth' });
        break;
    }
  });
  
  // Add focus management for better accessibility
  tableResponsive.setAttribute('tabindex', '0');
  tableResponsive.setAttribute('role', 'region');
  tableResponsive.setAttribute('aria-label', 'Student table with horizontal scrolling');
}
</script>
<?php include "../../includes/footer.php"; ?>
