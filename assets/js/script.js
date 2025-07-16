// Defensive declaration to avoid redeclaration error
if (typeof userRole === 'undefined') {
  var userRole = (window.__USER_ROLE__ || (typeof USER_ROLE !== 'undefined' ? USER_ROLE : null));
}

document.addEventListener('DOMContentLoaded', () => {
  // --- Enhanced Mobile Menu Functionality ---
  const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
  const sidebar = document.querySelector('.sidebar');
  const mobileOverlay = document.getElementById('mobile-overlay');

  if (mobileMenuToggle && sidebar && mobileOverlay) {
    let isMenuOpen = false;

    // Enhanced toggle mobile menu with better UX
    const toggleMobileMenu = (open) => {
      isMenuOpen = open;
      sidebar.classList.toggle('mobile-open', open);
      mobileOverlay.classList.toggle('active', open);
      document.body.style.overflow = open ? 'hidden' : '';
      
      // Update ARIA attributes for accessibility
      mobileMenuToggle.setAttribute('aria-expanded', open);
      sidebar.setAttribute('aria-hidden', !open);
      
      // Focus management
      if (open) {
        // Focus first nav link when menu opens
        const firstNavLink = sidebar.querySelector('.nav-link');
        if (firstNavLink) {
          setTimeout(() => firstNavLink.focus(), 300);
        }
      } else {
        // Return focus to toggle button when menu closes
        mobileMenuToggle.focus();
      }
    };

    // Toggle mobile menu
    mobileMenuToggle.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleMobileMenu(!isMenuOpen);
    });

    // Close mobile menu when clicking overlay
    mobileOverlay.addEventListener('click', () => {
      toggleMobileMenu(false);
    });

    // Close mobile menu when clicking on a nav link
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        // Close the menu first
        toggleMobileMenu(false);
        
        // Small delay to ensure menu closes before navigation
        // This prevents the menu from staying open during page transition
        setTimeout(() => {
          // Allow the link to navigate naturally
          // The link will work as expected
        }, 150);
      });
    });

    // Enhanced keyboard navigation
    const handleKeyDown = (e) => {
      if (!isMenuOpen) return;
      
      switch (e.key) {
        case 'Escape':
          e.preventDefault();
          toggleMobileMenu(false);
          break;
        case 'Tab':
          // Trap focus within sidebar when open
          const focusableElements = sidebar.querySelectorAll(
            'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
          );
          const firstElement = focusableElements[0];
          const lastElement = focusableElements[focusableElements.length - 1];
          
          if (e.shiftKey && document.activeElement === firstElement) {
            e.preventDefault();
            lastElement.focus();
          } else if (!e.shiftKey && document.activeElement === lastElement) {
            e.preventDefault();
            firstElement.focus();
          }
          break;
      }
    };

    document.addEventListener('keydown', handleKeyDown);

    // Close mobile menu on window resize (if screen becomes larger)
    const handleResize = () => {
      if (window.innerWidth > 768 && isMenuOpen) {
        toggleMobileMenu(false);
      }
    };

    window.addEventListener('resize', handleResize);

    // Close mobile menu on orientation change
    window.addEventListener('orientationchange', () => {
      setTimeout(() => {
        if (window.innerWidth > 768 && isMenuOpen) {
          toggleMobileMenu(false);
        }
      }, 100);
    });

    // Touch gesture support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    const handleTouchStart = (e) => {
      touchStartX = e.changedTouches[0].screenX;
    };

    const handleTouchEnd = (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    };

    const handleSwipe = () => {
      const swipeThreshold = 50;
      const swipeDistance = touchEndX - touchStartX;
      
      // Swipe right to open menu (from left edge)
      if (swipeDistance > swipeThreshold && touchStartX < 50 && !isMenuOpen) {
        toggleMobileMenu(true);
      }
      // Swipe left to close menu
      else if (swipeDistance < -swipeThreshold && isMenuOpen) {
        toggleMobileMenu(false);
      }
    };

    document.addEventListener('touchstart', handleTouchStart, { passive: true });
    document.addEventListener('touchend', handleTouchEnd, { passive: true });

    // Initialize ARIA attributes
    mobileMenuToggle.setAttribute('aria-expanded', 'false');
    mobileMenuToggle.setAttribute('aria-controls', 'sidebar');
    mobileMenuToggle.setAttribute('aria-label', 'Toggle navigation menu');
    sidebar.setAttribute('aria-hidden', 'true');
    sidebar.setAttribute('role', 'navigation');
    sidebar.setAttribute('aria-label', 'Main navigation');
  }

  // --- Login Form Handler ---
  const loginForm = document.querySelector('form[action="backend/auth/login.php"]');
  const errorMsg = document.getElementById('login-error');

  if (loginForm) {
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (errorMsg) {
      errorMsg.style.display = 'none';
      errorMsg.textContent = '';
    }

    const formData = new FormData(loginForm);

    try {
      const response = await fetch('/attendance-system/backend/auth/login.php', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();

      if (data.success && data.redirect) {
        window.location.href = '/attendance-system/' + data.redirect;
      } else if (data.error) {
          showToast('Login Error', data.error, 'error');
      } else {
          showToast('Login Error', 'Unexpected response from server.', 'error');
      }
    } catch (err) {
        showToast('Login Error', 'Network error or server unreachable.', 'error');
      console.error('Fetch error:', err);
    }
  });
  }

  // --- Register Form Handler ---
  const registerForm = document.querySelector('form[action="backend/auth/register.php"]');
  const registerErrorMsg = document.getElementById('register-error');

  if (registerForm) {
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (registerErrorMsg) {
      registerErrorMsg.style.display = 'none';
      registerErrorMsg.textContent = '';
    }

    const formData = new FormData(registerForm);

    try {
      const response = await fetch('/attendance-system/backend/auth/register.php', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();

      if (data.success) {
        showToast('Success', data.message || 'Registration successful!', 'success');
        // Switch to login form after successful registration
        showAuthForm('login');
      } else if (data.error) {
          showToast('Registration Error', data.error, 'error');
      } else {
          showToast('Registration Error', 'Unexpected response from server.', 'error');
      }
    } catch (err) {
        showToast('Registration Error', 'Network error or server unreachable.', 'error');
      console.error('Fetch error:', err);
    }
  });
  }

  // --- Enrollment Modal ---
  const modal = document.getElementById('enrollModal');
  const message = document.getElementById('enrollMessage');
  const cancelBtn = document.getElementById('cancelEnroll');

  let pollInterval = null;

  if (modal) {
  // --- Start Enrollment Session ---
  async function startEnrollment(studentId, type) {
    try {
      const response = await fetch(`/attendance-system/backend/${type}/enroll.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: studentId, action: 'start' })
      });

      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();
      return data.session_id;
    } catch (error) {
      console.error(`Failed to start ${type} enrollment:`, error);
      alert(`Failed to start ${type} enrollment session. Please check if the prototype device is connected and powered on.`);
      return null;
    }
  }

  // --- Poll Enrollment Status ---
  async function pollStatus(sessionId, type) {
    try {
      const response = await fetch(`/attendance-system/backend/${type}/scan.php?session_id=${sessionId}`);

      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();
      return data;
    } catch (error) {
      console.error(`Error polling ${type} enrollment status:`, error);
      // Show prototype connection error in modal message
      message.textContent = `Error: Unable to connect to the prototype device. Please ensure it is connected and powered on.`;
      // Stop polling on error to avoid flooding user with messages
      if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
      }
      return null;
    }
  }

  // --- Enrollment Button Event Handlers ---
  document.querySelectorAll('.enroll-rfid-btn, .enroll-fingerprint-btn').forEach(button => {
    button.addEventListener('click', async () => {
      const studentId = button.dataset.studentId;
      const type = button.classList.contains('enroll-rfid-btn') ? 'rfid' : 'fingerprint';

      const sessionId = await startEnrollment(studentId, type);
      if (!sessionId) return;

      modal.style.display = 'block';
      message.textContent = `Please scan your ${type === 'rfid' ? 'RFID card' : 'fingerprint'} now.`;

      pollInterval = setInterval(async () => {
        const statusData = await pollStatus(sessionId, type);

        if (!statusData) return; // pollStatus already handles error display and clearing interval

        if (statusData.status === 'success') {
          clearInterval(pollInterval);
          pollInterval = null;
          message.textContent = `${type === 'rfid' ? 'RFID card' : 'Fingerprint'} enrolled successfully! ID: ${statusData.scanned_id}`;
          setTimeout(() => { modal.style.display = 'none'; }, 3000);
        } else if (statusData.status === 'error') {
          clearInterval(pollInterval);
          pollInterval = null;
          message.textContent = `Error during ${type} enrollment: ${statusData.message || 'Unknown error'}`;
          setTimeout(() => { modal.style.display = 'none'; }, 3000);
        }
        // else still waiting...
      }, 2000);
    });
  });

  // --- Cancel Enrollment ---
  cancelBtn.onclick = () => {
    if (pollInterval) {
      clearInterval(pollInterval);
      pollInterval = null;
    }
    modal.style.display = 'none';
  };
  }

  // --- Edit Student Form Handler ---
  const editForm = document.getElementById('editStudentForm');
  const editErrorMsg = document.getElementById('editErrorMsg');
  const editSuccessMsg = document.getElementById('editSuccessMsg');

  if (editForm) {
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (editErrorMsg) {
        editErrorMsg.style.display = 'none';
        editErrorMsg.textContent = '';
      }
      if (editSuccessMsg) {
        editSuccessMsg.style.display = 'none';
        editSuccessMsg.textContent = '';
      }

      const formData = new FormData(editForm);

      try {
        const response = await fetch('/attendance-system/backend/edit_student.php', {
          method: 'POST',
          body: formData
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();

        if (data.success) {
          if (editSuccessMsg) {
            editSuccessMsg.textContent = data.message || 'Student updated successfully';
            editSuccessMsg.style.display = 'block';
          }
          // Optional: refresh or update UI here after edit success
        } else {
          if (editErrorMsg) {
            editErrorMsg.textContent = data.message || 'Failed to update student';
            editErrorMsg.style.display = 'block';
          }
        }
      } catch (err) {
        if (editErrorMsg) {
          editErrorMsg.textContent = 'Network error or server unreachable.';
          editErrorMsg.style.display = 'block';
        }
        console.error('Fetch error:', err);
      }
    });
  }

// --- Professor Schedule Page Logic ---
(function() {
    const scheduleForm = document.getElementById('form-schedule-modal');
  const btnAddSchedule = document.getElementById('btn-add-schedule');
    const btnCancelSchedule = document.getElementById('modal-cancel-btn');
  const scheduleTbody = document.getElementById('schedule-table-body');
    const scheduleModal = document.getElementById('schedule-modal');
    const modalTitle = document.getElementById('schedule-modal-title');
    const closeModalBtn = document.getElementById('close-schedule-modal');
    const saveBtn = document.getElementById('modal-save-btn');
    if (scheduleForm && btnAddSchedule && btnCancelSchedule && scheduleTbody && scheduleModal && modalTitle && closeModalBtn && saveBtn) {
      let isEdit = false;
      let editingId = null;
      // Remove any previous submit handlers to prevent duplicates
      scheduleForm.onsubmit = null;
      // Show add modal
    btnAddSchedule.addEventListener('click', () => {
        isEdit = false;
        editingId = null;
        modalTitle.textContent = 'Add Schedule';
        scheduleForm.reset();
        document.getElementById('modal-schedule-id').value = '';
        scheduleModal.style.display = 'block';
    });
      // Cancel modal
    btnCancelSchedule.addEventListener('click', () => {
        scheduleModal.style.display = 'none';
      scheduleForm.reset();
      });
      closeModalBtn.addEventListener('click', () => {
        scheduleModal.style.display = 'none';
        scheduleForm.reset();
      });
      // Edit button logic
      scheduleTbody.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn') && e.target.hasAttribute('data-edit-id')) {
          isEdit = true;
          editingId = e.target.getAttribute('data-edit-id');
          modalTitle.textContent = 'Edit Schedule';
          document.getElementById('modal-schedule-id').value = editingId;
          document.getElementById('modal-subject').value = e.target.getAttribute('data-edit-subject');
          document.getElementById('modal-room').value = e.target.getAttribute('data-edit-room');
          document.getElementById('modal-date').value = e.target.getAttribute('data-edit-date');
          document.getElementById('modal-start_time').value = e.target.getAttribute('data-edit-start');
          document.getElementById('modal-end_time').value = e.target.getAttribute('data-edit-end');
          document.getElementById('modal-late_threshold').value = e.target.getAttribute('data-edit-late-threshold');
          scheduleModal.style.display = 'block';
        }
        // --- Delete button logic ---
        if (e.target.classList.contains('btn-danger') && e.target.hasAttribute('data-delete-id')) {
          const scheduleId = e.target.getAttribute('data-delete-id');
          if (!scheduleId) return;
          if (!confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) return;
          fetch('/attendance-system/backend/schedule/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: scheduleId })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              showToast('Success', data.message || 'Schedule deleted!', 'success');
              fetchSchedules();
            } else {
              showToast('Error', data.error || 'Failed to delete schedule.', 'error');
            }
          })
          .catch(() => {
            showToast('Error', 'Network error.', 'error');
          });
        }
    });
      // Save (add/edit) schedule
      scheduleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        saveBtn.disabled = true;
        const formData = new FormData(scheduleForm);
        fetch('/attendance-system/backend/schedule/save.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          saveBtn.disabled = false;
          if (data.success) {
            showToast('Success', data.message || 'Schedule saved!', 'success');
            scheduleModal.style.display = 'none';
            scheduleForm.reset();
            fetchSchedules();
          } else {
            showToast('Error', data.error || 'Failed to save schedule.', 'error');
          }
        })
        .catch(() => {
          saveBtn.disabled = false;
          showToast('Error', 'Network error.', 'error');
        });
      }, { once: true });
      // Debug fetchSchedules with logging
    function fetchSchedules(searchTerm = '') {
        console.log('[DEBUG] Fetching schedules...');
      let url = '/attendance-system/backend/schedule/read.php';
      if (searchTerm) {
        url += `?search=${encodeURIComponent(searchTerm)}`;
      }
        console.log('[DEBUG] Fetch URL:', url);
      fetch(url)
        .then(res => {
            console.log('[DEBUG] Response status:', res.status);
          return res.json();
        })
        .then(data => {
            console.log('[DEBUG] Schedule data received:', data);
          scheduleTbody.innerHTML = '';
          if (data.success && data.schedules && data.schedules.length > 0) {
              console.log('[DEBUG] Found', data.schedules.length, 'schedules');
            data.schedules.forEach(schedule => {
              const tr = document.createElement('tr');
              // Determine status (Upcoming, Ongoing, Done)
              let status = '';
              const now = new Date();
              const schedDate = new Date(schedule.date + 'T' + schedule.start_time);
              const schedEnd = new Date(schedule.date + 'T' + schedule.end_time);
              if (now < schedDate) status = 'Upcoming';
              else if (now >= schedDate && now <= schedEnd) status = 'Ongoing';
              else status = 'Done';
              tr.innerHTML = `
                <td>${schedule.subject}</td>
                <td>${schedule.room}</td>
                <td>${formatDate(schedule.date)}</td>
                <td>${formatTime12h(schedule.start_time)}</td>
                <td>${formatTime12h(schedule.end_time)}</td>
                <td>${schedule.late_threshold} min</td>
                <td>${status}</td>
                <td>
                  <button class="btn btn-sm" data-edit-id="${schedule.id}" data-edit-subject="${schedule.subject}" data-edit-room="${schedule.room}" data-edit-date="${schedule.date}" data-edit-start="${schedule.start_time}" data-edit-end="${schedule.end_time}" data-edit-late-threshold="${schedule.late_threshold}">Edit</button>
                  <button class="btn btn-sm btn-danger" data-delete-id="${schedule.id}">Delete</button>
                  <button class="btn btn-sm btn-info btn-view-record" data-schedule-id="${schedule.id}" data-schedule-label="${schedule.subject} (${schedule.room}) ${formatTime12h(schedule.start_time)}-${formatTime12h(schedule.end_time)}">View Record</button>
                </td>
              `;
              scheduleTbody.appendChild(tr);
            });
          } else {
              console.log('[DEBUG] No schedules found or error in response', data);
            scheduleTbody.innerHTML = '<tr><td colspan="8">No schedules found.</td></tr>';
          }
        })
        .catch((error) => {
            console.error('[DEBUG] Error fetching schedules:', error);
          scheduleTbody.innerHTML = '<tr><td colspan="8">Error loading schedules.</td></tr>';
        });
    }
      // Add search form handler INSIDE the IIFE so fetchSchedules is in scope
    const scheduleSearchForm = document.getElementById('schedule-search-form');
    const scheduleSearchInput = document.getElementById('schedule-search');
    const scheduleShowAllBtn = document.getElementById('schedule-show-all');
      if (scheduleSearchForm && scheduleSearchInput) {
      scheduleSearchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchSchedules(scheduleSearchInput.value.trim());
      });
      }
      if (scheduleShowAllBtn && scheduleSearchInput) {
      scheduleShowAllBtn.addEventListener('click', function() {
        scheduleSearchInput.value = '';
        fetchSchedules();
      });
    }
    // Initial fetch
    fetchSchedules();
  }
})();

// --- Professor Attendance Page Logic ---
(function() {
  const classSelect = document.getElementById('class-select');
  const attendanceForm = document.getElementById('attendance-form');
  const studentsTbody = document.getElementById('students-tbody');
  const markAllBtn = document.getElementById('mark-all-present');
  let students = [];
  let selectedScheduleId = null;
  if (classSelect && attendanceForm && studentsTbody && markAllBtn) {
    // Fetch classes
    fetch('/attendance-system/backend/schedule/read.php')
      .then(res => res.json())
      .then(data => {
        classSelect.innerHTML = '<option value="">-- Select a class --</option>';
        if (data.success && data.schedules && data.schedules.length > 0) {
          data.schedules.forEach(cls => {
            const opt = document.createElement('option');
            opt.value = cls.id;
            opt.textContent = `${cls.subject} (${cls.date} ${cls.start_time} - ${cls.end_time})`;
            classSelect.appendChild(opt);
          });
        }
      });
    // On class select, fetch students
    classSelect.addEventListener('change', () => {
      selectedScheduleId = classSelect.value;
      if (!selectedScheduleId) {
        attendanceForm.style.display = 'none';
        studentsTbody.innerHTML = '';
        return;
      }
      showLoading();
      fetch('/attendance-system/backend/student/list.php?limit=100')
        .then(res => res.json())
        .then(data => {
          hideLoading();
          students = data.students || [];
          studentsTbody.innerHTML = '';
          if (students.length === 0) {
            studentsTbody.innerHTML = '<tr><td colspan="3">No students found.</td></tr>';
            attendanceForm.style.display = 'none';
            return;
          }
          students.forEach(student => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${student.fullname}</td>
              <td>${student.student_number}</td>
              <td style="min-width:180px;">
                <label><input type="radio" name="status_${student.id}" value="present" checked> Present</label>
                <label style="margin-left:10px;"><input type="radio" name="status_${student.id}" value="late"> Late</label>
                <label style="margin-left:10px;"><input type="radio" name="status_${student.id}" value="absent"> Absent</label>
              </td>
            `;
            studentsTbody.appendChild(tr);
          });
          attendanceForm.style.display = 'block';
        })
        .catch(() => {
          hideLoading();
          showToast('Failed to load students.', 'error');
        });
    });
    // Mark all as present
    markAllBtn.addEventListener('click', () => {
      students.forEach(student => {
        const radios = document.getElementsByName(`status_${student.id}`);
        if (radios.length) radios[0].checked = true;
      });
    });
    // Submit attendance
    attendanceForm.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!selectedScheduleId) return;
      const attendanceData = students.map(student => {
        const status = document.querySelector(`input[name='status_${student.id}']:checked`).value;
        const now = new Date();
        const timeIn = now.toTimeString().slice(0,8);
        return { student_id: student.id, status, time_in: timeIn };
      });
      showLoading();
      fetch('/attendance-system/backend/attendance/manual.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          schedule_id: selectedScheduleId,
          date: new Date().toISOString().slice(0,10),
          attendance: attendanceData
        })
      })
      .then(res => res.json())
      .then(data => {
        hideLoading();
        if (data.success) {
          showToast('Success', 'Attendance recorded!', 'success');
          attendanceForm.style.display = 'none';
          classSelect.value = '';
        } else {
          showToast('Error', data.message || 'Failed to record attendance.', 'error');
        }
      })
      .catch(() => {
        hideLoading();
        showToast('Network error.', 'error');
      });
    });
  }
})();

// --- Auth Form Logic (Guarded) ---
function showAuthForm(formType) {
  const form = document.getElementById(formType + '-form');
  if (!form) return;
  const loginTab = document.getElementById('login-tab');
  const registerTab = document.getElementById('register-tab');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');

  // Clear any existing error messages when switching forms
  clearAllErrors();

  if (formType === 'login') {
    loginTab.classList.add('active');
    registerTab.classList.remove('active');
    loginTab.setAttribute('aria-selected', 'true');
    registerTab.setAttribute('aria-selected', 'false');
    
    registerForm.style.display = 'none';
    loginForm.style.display = 'flex';
    
    // Smooth animation for form switch
    setTimeout(() => {
      loginForm.style.opacity = '0';
      loginForm.style.transform = 'translateX(-20px)';
      loginForm.style.display = 'flex';
      
      requestAnimationFrame(() => {
        loginForm.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        loginForm.style.opacity = '1';
        loginForm.style.transform = 'translateX(0)';
      });
    }, 50);
    
  } else {
    registerTab.classList.add('active');
    loginTab.classList.remove('active');
    registerTab.setAttribute('aria-selected', 'true');
    loginTab.setAttribute('aria-selected', 'false');
    
    loginForm.style.display = 'none';
    registerForm.style.display = 'flex';
    
    // Smooth animation for form switch
    setTimeout(() => {
      registerForm.style.opacity = '0';
      registerForm.style.transform = 'translateX(20px)';
      registerForm.style.display = 'flex';
      
      requestAnimationFrame(() => {
        registerForm.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        registerForm.style.opacity = '1';
        registerForm.style.transform = 'translateX(0)';
      });
    }, 50);
      
      // Ensure role change logic is always attached
      setupRoleChange();
  }
}
  window.showAuthForm = showAuthForm;

// Toast notification system
function showToast(title, message, type = 'info') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  let icon = '';
  switch(type) {
    case 'success': icon = '✅'; break;
    case 'error': icon = '❌'; break;
    case 'warning': icon = '⚠️'; break;
    default: icon = 'ℹ️';
  }
  toast.innerHTML = `
    <div class="toast-header">
      <span class="toast-icon">${icon}</span>
      <span class="toast-title">${title}</span>
    </div>
    <div class="toast-message">${message}</div>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('removing');
    setTimeout(() => toast.remove(), 400);
  }, 4000);
}
  window.showToast = showToast;

function removeToast(toast) {
  toast.classList.add('removing');
  setTimeout(() => {
    if (toast.parentElement) {
      toast.parentElement.removeChild(toast);
    }
  }, 300);
}

document.addEventListener('DOMContentLoaded', function() {
  // Only run auth logic if login/register forms exist
  if (document.getElementById('login-form')) {
    showAuthForm('login');
    setupValidation('login');
    setupFormSubmit('login');
  }
  if (document.getElementById('register-form')) {
    setupValidation('register');
    setupFormSubmit('register');
    setupRoleChange(); // Add role change functionality
  }
  // Add subtle animation to logo glow
  const logoGlow = document.querySelector('.auth-logo-glow');
  if (logoGlow) {
    logoGlow.addEventListener('mouseenter', function() {
      this.style.animationDuration = '1.5s';
    });
    
    logoGlow.addEventListener('mouseleave', function() {
      this.style.animationDuration = '2s';
    });
  }

  // Add focus effects to tabs
  const tabs = document.querySelectorAll('.auth-tab');
  tabs.forEach(tab => {
    tab.addEventListener('focus', function() {
      this.style.transform = 'scale(1.02)';
      this.style.transition = 'transform 0.2s ease';
    });
    
    tab.addEventListener('blur', function() {
      this.style.transform = 'scale(1)';
    });
  });
});

function setupValidation(type) {
  const form = document.getElementById(type + '-form');
  const email = form.querySelector('input[type="email"]');
  const password = form.querySelector('input[type="password"]');
  const name = form.querySelector('input[type="text"]');
  const phone = form.querySelector('input[type="tel"]');
  const role = form.querySelector('select');

  // Function to clear form error message
  const clearFormError = () => {
    const errorDiv = document.getElementById(type + '-error');
    if (errorDiv && errorDiv.classList.contains('show')) {
      errorDiv.classList.remove('show');
      setTimeout(() => {
        errorDiv.style.display = 'none';
      }, 300);
    }
  };

  if (email) {
    email.addEventListener('input', function() {
      clearFormError(); // Clear error when user starts typing
      const feedback = document.getElementById(type + '-email-feedback');
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
  if (password) {
    password.addEventListener('input', function() {
      clearFormError(); // Clear error when user starts typing
      const feedback = document.getElementById(type + '-password-feedback');
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
  if (name) {
    name.addEventListener('input', function() {
      clearFormError(); // Clear error when user starts typing
      const feedback = document.getElementById(type + '-name-feedback');
      if (!name.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      } else if (name.value.length < 2) {
        feedback.textContent = 'Name too short';
        feedback.className = 'input-feedback invalid';
      } else {
        feedback.textContent = 'Looks good!';
        feedback.className = 'input-feedback valid';
      }
    });
  }
  if (phone) {
    phone.addEventListener('input', function() {
      clearFormError(); // Clear error when user starts typing
      const feedback = document.getElementById(type + '-phone-feedback');
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
  if (role) {
    role.addEventListener('change', function() {
      clearFormError(); // Clear error when user changes role
      const feedback = document.getElementById(type + '-role-feedback');
      const studentFields = document.getElementById('student-fields');
      
      console.log('Role changed to:', this.value);
      console.log('Student fields element:', studentFields);
      
      if (!role.value) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
        if (studentFields) {
          console.log('Hiding student fields (no role selected)');
          studentFields.style.display = 'none';
        }
      } else {
        feedback.textContent = 'Role selected';
        feedback.className = 'input-feedback valid';
        
        // Show/hide student fields based on role selection
        if (studentFields) {
          if (role.value === 'student') {
            console.log('Showing student fields for student role');
            studentFields.style.display = 'block';
            
            // Make student fields required
            const phoneField = document.getElementById('register-phone');
            const courseField = document.getElementById('register-course');
            const yearLevelField = document.getElementById('register-year-level');
            const sectionField = document.getElementById('register-section');
            
            if (phoneField) phoneField.required = true;
            if (courseField) courseField.required = true;
            if (yearLevelField) yearLevelField.required = true;
            if (sectionField) sectionField.required = true;
            
            // Improved animation that doesn't affect layout
            studentFields.style.opacity = '0';
            studentFields.style.transform = 'translateY(-5px)'; // Reduced movement
            studentFields.style.transition = 'all 0.3s ease';
            
            // Use requestAnimationFrame for smoother animation
            requestAnimationFrame(() => {
              studentFields.style.opacity = '1';
              studentFields.style.transform = 'translateY(0)';
            });
          } else {
            console.log('Hiding student fields for professor role');
            
            // Smooth hide animation
            studentFields.style.transition = 'all 0.3s ease';
            studentFields.style.opacity = '0';
            studentFields.style.transform = 'translateY(-5px)';
            
            // Hide after animation completes
            setTimeout(() => {
              studentFields.style.display = 'none';
            }, 300);
            
            // Remove required from student fields
            const phoneField = document.getElementById('register-phone');
            const courseField = document.getElementById('register-course');
            const yearLevelField = document.getElementById('register-year-level');
            const sectionField = document.getElementById('register-section');
            
            if (phoneField) phoneField.required = false;
            if (courseField) courseField.required = false;
            if (yearLevelField) yearLevelField.required = false;
            if (sectionField) sectionField.required = false;
          }
        } else {
          console.error('Student fields element not found!');
        }
      }
    });
  }
  
  // Add validation for student-specific fields
  if (type === 'register') {
    const phone = document.getElementById('register-phone');
    const course = document.getElementById('register-course');
    const yearLevel = document.getElementById('register-year-level');
    const section = document.getElementById('register-section');
    
    if (phone) {
      phone.addEventListener('input', function() {
        const feedback = document.getElementById('register-phone-feedback');
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
    
    if (course) {
      course.addEventListener('input', function() {
        const feedback = document.getElementById('register-course-feedback');
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
    
    if (yearLevel) {
      yearLevel.addEventListener('change', function() {
        const feedback = document.getElementById('register-year-level-feedback');
        if (!yearLevel.value) {
          feedback.textContent = '';
          feedback.className = 'input-feedback';
        } else {
          feedback.textContent = 'Year level selected';
          feedback.className = 'input-feedback valid';
        }
      });
    }
    
    if (section) {
      section.addEventListener('input', function() {
        const feedback = document.getElementById('register-section-feedback');
        if (!section.value) {
          feedback.textContent = '';
          feedback.className = 'input-feedback';
        } else if (section.value.length < 1) {
          feedback.textContent = 'Section too short';
          feedback.className = 'input-feedback invalid';
        } else {
          feedback.textContent = 'Looks good!';
          feedback.className = 'input-feedback valid';
        }
      });
    }
  }
}

function setupFormSubmit(type) {
  const form = document.getElementById(type + '-form');
  const btn = document.getElementById(type + '-btn');
  const spinner = btn.querySelector('.btn-spinner');
  const btnText = btn.querySelector('.btn-text');
  const card = document.querySelector('.auth-card');
  
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Function to restore button state
    const restoreButtonState = () => {
      console.log(`[${type}] Restoring button state`);
      btnText.style.display = '';
      spinner.style.display = 'none';
      btn.disabled = false;
      console.log(`[${type}] Button text display:`, btnText.style.display);
      console.log(`[${type}] Spinner display:`, spinner.style.display);
      console.log(`[${type}] Button disabled:`, btn.disabled);
    };
    
    // Function to set loading state
    const setLoadingState = () => {
      console.log(`[${type}] Setting loading state`);
      btnText.style.display = 'none';
      spinner.style.display = 'inline-block';
      btn.disabled = true;
      console.log(`[${type}] Button text display:`, btnText.style.display);
      console.log(`[${type}] Spinner display:`, spinner.style.display);
      console.log(`[${type}] Button disabled:`, btn.disabled);
    };
    
    // Client-side validation
    let valid = true;
    let firstInvalidField = null;
    let firstInvalidMsg = '';
    const requiredFields = form.querySelectorAll('input[required],select[required]');
    requiredFields.forEach(function(input) {
      // Remove previous invalid state
      input.classList.remove('invalid');
      let feedback = null;
      if (input.type === 'email') feedback = document.getElementById(type + '-email-feedback');
      else if (input.type === 'password') feedback = document.getElementById(type + '-password-feedback');
      else if (input.type === 'text' && input.name === 'name') feedback = document.getElementById(type + '-name-feedback');
      else if (input.type === 'tel') feedback = document.getElementById(type + '-phone-feedback');
      else if (input.tagName === 'SELECT') feedback = document.getElementById(type + '-role-feedback');
      if (feedback) {
        feedback.textContent = '';
        feedback.className = 'input-feedback';
      }
      // Validation
      if (!input.value.trim()) {
        valid = false;
        if (!firstInvalidField) {
          firstInvalidField = input;
          if (input.type === 'email') firstInvalidMsg = 'Email is required.';
          else if (input.type === 'password') firstInvalidMsg = 'Password is required.';
          else if (input.type === 'text' && input.name === 'name') firstInvalidMsg = 'Name is required.';
          else if (input.type === 'tel') firstInvalidMsg = 'Phone number is required for students.';
          else if (input.tagName === 'SELECT') firstInvalidMsg = 'Role is required.';
          else firstInvalidMsg = 'This field is required.';
        }
        return;
      }
      if (input.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(input.value)) {
        valid = false;
        if (!firstInvalidField) {
          firstInvalidField = input;
          firstInvalidMsg = 'Invalid email format.';
        }
        return;
      }
      if (input.type === 'password' && input.value.length < 6) {
        valid = false;
        if (!firstInvalidField) {
          firstInvalidField = input;
          firstInvalidMsg = 'Password must be at least 6 characters.';
        }
        return;
      }
      if (input.type === 'text' && input.name === 'name' && input.value.length < 2) {
        valid = false;
        if (!firstInvalidField) {
          firstInvalidField = input;
          firstInvalidMsg = 'Name must be at least 2 characters.';
        }
        return;
      }
      if (input.type === 'tel' && input.value && !/^\d{10,}$/.test(input.value)) {
        valid = false;
        if (!firstInvalidField) {
          firstInvalidField = input;
          firstInvalidMsg = 'Phone must be at least 10 digits.';
        }
        return;
      }
    });
    
    // Additional validation for phone field when role is student
    if (type === 'register') {
      const roleSelect = form.querySelector('select[name="role"]');
      const phoneInput = form.querySelector('input[name="phone"]');
      if (roleSelect && roleSelect.value === 'student' && phoneInput && !phoneInput.value.trim()) {
        valid = false;
        if (!firstInvalidField) {
          firstInvalidField = phoneInput;
          firstInvalidMsg = 'Phone number is required for students.';
        }
      }
    }
    
    if (!valid) {
      if (firstInvalidField) {
        firstInvalidField.classList.add('invalid');
        let feedback = null;
        if (firstInvalidField.type === 'email') feedback = document.getElementById(type + '-email-feedback');
        else if (firstInvalidField.type === 'password') feedback = document.getElementById(type + '-password-feedback');
        else if (firstInvalidField.type === 'text' && firstInvalidField.name === 'name') feedback = document.getElementById(type + '-name-feedback');
        else if (firstInvalidField.type === 'tel') feedback = document.getElementById(type + '-phone-feedback');
        else if (firstInvalidField.tagName === 'SELECT') feedback = document.getElementById(type + '-role-feedback');
        if (feedback) {
          feedback.textContent = firstInvalidMsg;
          feedback.className = 'input-feedback invalid';
        }
        
        // Show error message at top of form
        const errorDiv = document.getElementById(type + '-error');
        if (errorDiv) {
          errorDiv.textContent = firstInvalidMsg;
          errorDiv.className = 'auth-error show';
          
          // Auto-scroll to error message for better visibility
          setTimeout(() => {
            errorDiv.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start',
              inline: 'nearest'
            });
          }, 100);
        }
        
        // Show a field-specific toast
        let toastTitle = 'Validation Error';
        if (firstInvalidField.type === 'email') toastTitle = 'Invalid Email';
        else if (firstInvalidField.type === 'password') toastTitle = 'Invalid Password';
        else if (firstInvalidField.type === 'text' && firstInvalidField.name === 'name') toastTitle = 'Invalid Name';
        else if (firstInvalidField.type === 'tel') toastTitle = 'Invalid Phone';
        else if (firstInvalidField.tagName === 'SELECT') toastTitle = 'Invalid Role';
        showToast(toastTitle, firstInvalidMsg, 'error');
      } else {
        showToast('Error', 'Validation Error', 'Please fill all required fields correctly.');
      }
      return;
    }
    
    // Set loading state
    setLoadingState();
    
    // Prepare form data
    const formData = new FormData(form);
    
    // AJAX submission
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
      console.log(`[${type}] Response received:`, response.status);
      return response.json();
    })
    .then(data => {
      console.log(`[${type}] Data received:`, data);
      // Always restore button state first
      restoreButtonState();
      
      if (data.success) {
        // Remove card shake animation to prevent button appearing to disappear
        // card.classList.remove('card-success');
        // card.classList.add('card-success');
        // setTimeout(() => card.classList.remove('card-success'), 800);
        
        if (type === 'login') {
          showToast('Success', 'Login Successful', 'Redirecting to dashboard...', 3000);
        setTimeout(() => {
            window.location.href = data.redirect;
        }, 1500);
      } else {
          showToast('Success', 'Registration Successful', data.message || 'You can now log in.', 4000);
          setTimeout(() => {
            showAuthForm('login');
            form.reset();
            // Clear all feedback
            form.querySelectorAll('.input-feedback').forEach(feedback => {
              feedback.textContent = '';
              feedback.className = 'input-feedback';
            });
            // Clear error messages
            clearAllErrors();
          }, 2000);
        }
      } else {
        // Enhanced error handling for field-specific feedback and toasts
        let error = data.error || 'An error occurred. Please try again.';
        let title = 'Error';
        let field = null;
        let feedbackMsg = error;
        // Determine which field the error is about
        if (/email.*register|already.*register|email.*used|email.*exist/i.test(error)) {
          title = 'Email Already Used';
          field = form.querySelector('input[type="email"]');
          feedbackMsg = 'Email is already registered.';
        } else if (/valid email|invalid email/i.test(error)) {
          title = 'Invalid Email';
          field = form.querySelector('input[type="email"]');
          feedbackMsg = 'Please enter a valid email address.';
        } else if (/password/i.test(error)) {
          title = 'Invalid Password';
          field = form.querySelector('input[type="password"]');
          feedbackMsg = error;
        } else if (/name/i.test(error)) {
          title = 'Invalid Name';
          field = form.querySelector('input[type="text"]');
          feedbackMsg = error;
        } else if (/phone/i.test(error)) {
          title = 'Invalid Phone';
          field = form.querySelector('input[type="tel"]');
          feedbackMsg = error;
        } else if (/role/i.test(error)) {
          title = 'Invalid Role';
          field = form.querySelector('select');
          feedbackMsg = 'Please select a valid role.';
        } else if (/required|fill|empty/i.test(error)) {
          title = 'Incomplete Form';
        }
        // Highlight the field if found
        if (field) {
          field.classList.add('invalid');
          let feedback = null;
          if (field.type === 'email') feedback = document.getElementById(type + '-email-feedback');
          else if (field.type === 'password') feedback = document.getElementById(type + '-password-feedback');
          else if (field.type === 'text' && field.name === 'name') feedback = document.getElementById(type + '-name-feedback');
          else if (field.type === 'tel') feedback = document.getElementById(type + '-phone-feedback');
          else if (field.tagName === 'SELECT') feedback = document.getElementById(type + '-role-feedback');
          if (feedback) {
            feedback.textContent = feedbackMsg;
            feedback.className = 'input-feedback invalid';
          }
        }
        
        // Show error message in form
        const errorDiv = document.getElementById(type + '-error');
        if (errorDiv) {
          errorDiv.textContent = error;
          errorDiv.className = 'auth-error show';
          
          // Auto-scroll to error message for better visibility
          setTimeout(() => {
            errorDiv.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start',
              inline: 'nearest'
            });
          }, 100);
        }
        
        // Show toast notification
        showToast(title, error, 'error');
      }
    })
    .catch(error => {
      console.error(`[${type}] Fetch error:`, error);
      // Always restore button state
      restoreButtonState();
      
      // Remove card shake animation to prevent button appearing to disappear
      // card.classList.remove('card-success');
      // card.classList.add('card-error');
      // setTimeout(() => card.classList.remove('card-error'), 500);
      
      showToast('Error', 'Network Error', 'Unable to connect to server. Please check your connection.');
    });
  });
}

function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  if (input.type === 'password') {
    input.type = 'text';
    btn.setAttribute('aria-label', 'Hide password');
  } else {
    input.type = 'password';
    btn.setAttribute('aria-label', 'Show password');
  }
}
window.togglePassword = togglePassword;

// ===============================
// LOADING SPINNER OVERLAY
// ===============================
function showLoading() {
  let overlay = document.querySelector('.loading-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
  }
  overlay.style.display = 'flex';
}
  window.showLoading = showLoading;

function hideLoading() {
  const overlay = document.querySelector('.loading-overlay');
  if (overlay) overlay.style.display = 'none';
}
  window.hideLoading = hideLoading;

// === Professor Profile Page Logic ===
if (document.getElementById('profile-form')) {
  document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('profile-message').textContent = "Profile updated successfully!";
    document.getElementById('profile-message').style.color = 'green';
  });
}

// === Professor Students Page Logic ===
if (document.getElementById('form-add-student')) {
  // Show/hide Add Student form
  const btnAdd = document.getElementById('btn-add-student');
  const formAdd = document.getElementById('form-add-student');
  const btnCancel = document.getElementById('btn-cancel-add-student');
  btnAdd.addEventListener('click', () => {
    formAdd.style.display = 'block';
    btnAdd.style.display = 'none';
  });
  btnCancel.addEventListener('click', () => {
    formAdd.style.display = 'none';
    btnAdd.style.display = 'inline-block';
  });
  // Delete student
  document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', () => {
      const studentId = button.dataset.id;
      if (confirm("Are you sure you want to delete this student?")) {
        fetch('/attendance-system/backend/student/delete_student.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: studentId })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("Student deleted successfully.");
            location.reload();
          } else {
            alert("Failed to delete student.");
          }
        })
        .catch(() => alert("Network error."));
      }
    });
  });
  // Edit student modal
  const editModal = document.getElementById('editModal');
  const closeEditModal = document.getElementById('closeEditModal');
  const formEdit = document.getElementById('form-edit-student');
  document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', () => {
      const studentId = button.dataset.id;
      fetch(`/attendance-system/backend/student/get_student.php?id=${studentId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const student = data.student;
            document.getElementById('edit_student_id').value = student.id;
            document.getElementById('edit_student_fullname').value = student.full_name;
            document.getElementById('edit_student_number').value = student.student_number;
            document.getElementById('edit_student_email').value = student.email;
            document.getElementById('edit_student_phone').value = student.phone;
            editModal.style.display = 'block';
          } else {
            alert('Student not found.');
          }
        });
    });
  });
  closeEditModal.addEventListener('click', () => {
    editModal.style.display = 'none';
  });
  window.addEventListener('click', e => {
    if (e.target === editModal) {
      editModal.style.display = 'none';
    }
  });
}

// === Professor Summary Page Logic (Chart.js) ===
if (document.getElementById('attendancePieChart')) {
  const ctx = document.getElementById('attendancePieChart').getContext('2d');
  if (window.Chart) {
    const attendancePieChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Present', 'Late', 'Absent'],
        datasets: [{
          data: [
            typeof totalAttendance !== 'undefined' && typeof totalLate !== 'undefined' && typeof totalAbsent !== 'undefined'
              ? [Math.max(0, totalAttendance - totalLate - totalAbsent), totalLate, totalAbsent]
              : [0, 0, 0]
          ],
          backgroundColor: [
            '#27ae60', // Present
            '#f39c12', // Late
            '#e74c3c'  // Absent
          ],
          borderWidth: 2,
          borderColor: '#fff',
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              color: '#2980b9',
              font: { size: 15, weight: 'bold' }
            }
          }
        }
      }
    });
  }
}

// Role change functionality for registration form
function setupRoleChange() {
  const roleSelect = document.getElementById('register-role');
  const studentFields = document.getElementById('student-fields');
  
  if (roleSelect && studentFields) {
    roleSelect.addEventListener('change', function() {
      console.log('Role changed to:', this.value);
      
      if (this.value === 'student') {
        console.log('Showing student fields');
        studentFields.style.display = 'block';
        studentFields.style.opacity = '0';
        studentFields.style.transform = 'translateY(-10px)';
        
        // Smooth animation for student fields appearance
        setTimeout(() => {
          studentFields.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
          studentFields.style.opacity = '1';
          studentFields.style.transform = 'translateY(0)';
        }, 10);
        
        // Auto-scroll to student fields for better UX
        setTimeout(() => {
          studentFields.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start',
            inline: 'nearest'
          });
        }, 200);
      } else {
        console.log('Hiding student fields');
        studentFields.style.transition = 'all 0.3s ease';
        studentFields.style.opacity = '0';
        studentFields.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
          studentFields.style.display = 'none';
        }, 300);
      }
    });
  }
}

// Function to clear all error messages
function clearAllErrors() {
  const loginError = document.getElementById('login-error');
  const registerError = document.getElementById('register-error');
  
  if (loginError) {
    loginError.className = 'auth-error';
    loginError.style.display = 'none';
  }
  if (registerError) {
    registerError.className = 'auth-error';
    registerError.style.display = 'none';
  }
}

// Format a date string (YYYY-MM-DD) as 'Mon DD, YYYY'
function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Format a time string (HH:MM:SS or HH:MM) as 12-hour with AM/PM
// Standard: Always use formatDate and formatTime12h for all date/time displays in the UI.
function formatTime12h(timeStr) {
  if (!timeStr) return '';
    const [h, m] = timeStr.split(':');
    let hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;
    return `${hour}:${m} ${ampm}`;
}
  window.formatTime12h = formatTime12h;

// Back to Top Button
function initBackToTopButton() {
  const btn = document.getElementById('back-to-top');
  if (!btn) {
    console.warn('Back to top button not found');
    return;
  }

  // Scroll event listener to show/hide button
  window.addEventListener('scroll', function() {
    if (window.scrollY > 200) {
      btn.classList.add('show');
    } else {
      btn.classList.remove('show');
    }
  });

  // Click event listener to scroll to top
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('Back to top button clicked'); // Debug log
    
    // Smooth scroll to top with fallback
    try {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    } catch (error) {
      // Fallback for browsers that don't support smooth scrolling
      console.log('Smooth scroll not supported, using fallback');
      document.documentElement.scrollTop = 0;
      document.body.scrollTop = 0;
    }
  });

  // Ensure button is clickable when visible
  btn.style.pointerEvents = 'auto';
  
  console.log('Back to top button initialized'); // Debug log
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBackToTopButton);
} else {
  initBackToTopButton();
}

// Notification Modal Logic
const notificationBell = document.querySelector('.notification-bell');
const notificationModal = document.getElementById('notification-modal');
const notificationList = document.getElementById('notification-list');
const closeNotificationModal = document.getElementById('close-notification-modal');
let notificationBadge = null;
let markingNotificationsAsRead = false;

function renderNotificationModal(actionableRequests, notifications) {
  // Deduplicate actionable requests by id
  const seenIds = new Set();
  const uniqueActionable = [];
  actionableRequests.forEach(req => {
    if (!seenIds.has(req.id)) {
      uniqueActionable.push(req);
      seenIds.add(req.id);
    }
  });
  // Only filter out notifications that match *pending* actionable requests
  let filteredNotifications = notifications;
  if (uniqueActionable.length > 0) {
    filteredNotifications = notifications.filter(n => {
      // Only filter password change request notifications for this student and time if the request is still pending
      if (n.title && n.title.toLowerCase().includes('password change request') && n.title.toLowerCase().includes('request')) {
        // Try to match by student name/number in message and created_at (to the minute)
        return !uniqueActionable.some(req => {
          const reqTime = new Date(req.created_at);
          const notifTime = new Date(n.created_at);
          const sameMinute = reqTime.getFullYear() === notifTime.getFullYear() &&
            reqTime.getMonth() === notifTime.getMonth() &&
            reqTime.getDate() === notifTime.getDate() &&
            reqTime.getHours() === notifTime.getHours() &&
            reqTime.getMinutes() === notifTime.getMinutes();
          return sameMinute && n.message.includes(req.student_name) && n.message.includes(req.student_number);
        });
      }
      return true;
    });
  }
  // Always show the latest 'Password Change Declined' notification (with the reason)
  // Sort notifications by created_at descending
  filteredNotifications = filteredNotifications.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  let html = '';
  if (uniqueActionable.length > 0) {
    html += uniqueActionable.map(req => `
      <div class="notification-item" style="background:#fffbe6;padding:1rem;border-bottom:1px solid #eee;">
        <div style="font-weight:600;color:#e67e22;">Password Change Request</div>
        <div>Student: <strong>${req.student_name}</strong> (${req.student_number})</div>
        <div>Requested: <span style="color:#888;">${new Date(req.created_at).toLocaleString()}</span></div>
        <div style="margin-top:0.7rem;display:flex;gap:0.5rem;">
          <button class="btn btn-sm btn-success approve-pw-btn" data-id="${req.id}">Approve</button>
          <button class="btn btn-sm btn-danger decline-pw-btn" data-id="${req.id}">Decline</button>
        </div>
      </div>
    `).join('');
  }
  if (filteredNotifications.length > 0) {
    html += filteredNotifications.map(n => `
      <div class="notification-item" style="padding:0.8rem 0;border-bottom:1px solid #eee;${n.is_read ? '' : 'background:#f0f6ff;'}">
        <div style="font-weight:600;color:#2980b9;">${n.title}</div>
        <div style="margin:0.3rem 0 0.5rem 0;">${n.message}</div>
        <div style="font-size:0.85rem;color:#888;">${new Date(n.created_at).toLocaleString()}</div>
      </div>
    `).join('');
  }
  if (uniqueActionable.length === 0 && filteredNotifications.length === 0) {
    html = '<p style="text-align:center;">No notifications.</p>';
  }
  notificationList.innerHTML = html;
}

function fetchAllNotificationsAndRender() {
  let actionableRequests = [];
  let notifications = [];
  let actionableDone = false;
  let notificationsDone = false;

  function tryRender() {
    if (actionableDone && notificationsDone) {
      // For badge: count only notifications from the notifications table where is_read is false (unseen)
      // This applies to both professors and students. No deduplication or actionable request logic for badge.
      const unseenCount = notifications.filter(n => !n.is_read).length;
      if (!notificationBadge) {
        notificationBadge = document.createElement('span');
        notificationBadge.className = 'badge';
        notificationBell.appendChild(notificationBadge);
      }
      notificationBadge.textContent = unseenCount > 0 ? unseenCount : '';
      notificationBadge.style.display = unseenCount > 0 ? 'inline-block' : 'none';
      renderNotificationModal(actionableRequests, notifications);
    }
  }

  // Fetch actionable requests (professor only)
  if (userRole === 'professor') {
    fetch('/attendance-system/backend/user/pending_password_requests.php')
      .then(res => res.json())
      .then(data => {
        actionableRequests = (data.success && Array.isArray(data.requests)) ? data.requests : [];
        actionableDone = true;
        tryRender();
      })
      .catch(() => {
        actionableRequests = [];
        actionableDone = true;
        tryRender();
      });
  } else {
    actionableDone = true;
  }

  // Fetch standard notifications
  fetch('/attendance-system/backend/notify/notifications.php')
    .then(res => res.json())
    .then(data => {
      if (data.success && Array.isArray(data.notifications)) {
        notifications = data.notifications;
        // For professors, filter out password change request notifications only for modal deduplication, not for badge
        if (userRole === 'professor') {
          // notifications = notifications.filter(n => !n.message.includes('has requested a password change.'));
        }
      }
      notificationsDone = true;
      tryRender();
    })
    .catch(() => {
      notifications = [];
      notificationsDone = true;
      tryRender();
    });
}

if (notificationBell && notificationModal && notificationList) {
  notificationBell.addEventListener('click', () => {
    notificationModal.style.display = 'block';
    fetchAllNotificationsAndRender();
    // Mark all unseen notifications as read immediately when modal is opened
    fetch('/attendance-system/backend/notify/notifications.php')
      .then(res => res.json())
      .then(data => {
        if (data.success && Array.isArray(data.notifications)) {
          const unseen = data.notifications.filter(n => !n.is_read);
          if (unseen.length > 0) {
            unseen.forEach(n => {
              fetch('/attendance-system/backend/notify/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: n.id })
              });
            });
          }
        }
      });
  });
  closeNotificationModal.addEventListener('click', () => {
    notificationModal.style.display = 'none';
    // Optimistically set badge to 0 immediately
    if (notificationBadge) {
      notificationBadge.textContent = '';
      notificationBadge.style.display = 'none';
    }
    // Re-fetch notifications in the background to confirm
    fetchAllNotificationsAndRender();
  });
  window.addEventListener('click', (event) => {
    if (event.target === notificationModal) {
      notificationModal.style.display = 'none';
      if (notificationBadge) {
        notificationBadge.textContent = '';
        notificationBadge.style.display = 'none';
      }
      fetchAllNotificationsAndRender();
    }
  });
  // Fetch notifications on page load for badge
  fetchAllNotificationsAndRender();
}

if (notificationList) {
  notificationList.addEventListener('click', function(e) {
    if (e.target.classList.contains('approve-pw-btn')) {
      const id = e.target.getAttribute('data-id');
      fetch('/attendance-system/backend/user/review_password_change.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id: id, action: 'approve', reason: '' })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showToast('Success', data.message, 'success');
          fetchAllNotificationsAndRender();
        } else {
          showToast('Error', data.error || 'Failed to process request.', 'error');
        }
      })
      .catch(() => {
        showToast('Error', 'Network error. Please try again.', 'error');
      });
    }
    if (e.target.classList.contains('decline-pw-btn')) {
      pendingDeclineRequestId = e.target.getAttribute('data-id');
      // Try to get student info from DOM
      const parent = e.target.closest('.notification-item');
      let studentName = '', studentNumber = '';
      if (parent) {
        const nameMatch = parent.innerHTML.match(/Student: <strong>(.*?)<\/strong> \((.*?)\)/);
        if (nameMatch) {
          studentName = nameMatch[1];
          studentNumber = nameMatch[2];
        }
      }
      pendingDeclineStudent = { name: studentName, number: studentNumber };
      showDeclineModal(studentName, studentNumber);
    }
  });
}

// Decline Reason Modal logic
let declineReasonModal = document.getElementById('declineReasonModal');
let closeDeclineReasonModal = document.getElementById('closeDeclineReasonModal');
let declineReasonTextarea = document.getElementById('declineReasonTextarea');
let submitDeclineReasonBtn = document.getElementById('submitDeclineReasonBtn');
let cancelDeclineReasonBtn = document.getElementById('cancelDeclineReasonBtn');
let declineStudentInfo = document.getElementById('declineStudentInfo');
let declineReasonError = document.getElementById('declineReasonError');
let declineConfirmationMsg = document.getElementById('declineConfirmationMsg');
let pendingDeclineRequestId = null;
let pendingDeclineStudent = null;

function showDeclineModal(studentName, studentNumber) {
  declineStudentInfo.textContent = studentName && studentNumber ? `Student: ${studentName} (${studentNumber})` : '';
  declineReasonTextarea.value = '';
  declineReasonError.textContent = '';
  declineConfirmationMsg.style.display = 'none';
  submitDeclineReasonBtn.disabled = false;
  submitDeclineReasonBtn.querySelector('.btn-spinner').style.display = 'none';
  declineReasonModal.style.display = 'flex';
  declineReasonModal.classList.add('fade-in');
  setTimeout(() => declineReasonTextarea.focus(), 100);
  trapModalFocus(declineReasonModal);
}

function hideDeclineModal() {
  declineReasonModal.classList.remove('fade-in');
  declineReasonModal.style.display = 'none';
  pendingDeclineRequestId = null;
  pendingDeclineStudent = null;
  releaseModalFocus();
}

// Trap focus inside modal for accessibility
let lastFocusedElement = null;
function trapModalFocus(modal) {
  lastFocusedElement = document.activeElement;
  const focusable = modal.querySelectorAll('button, [tabindex]:not([tabindex="-1"]), textarea');
  if (focusable.length) focusable[0].focus();
  function handleTab(e) {
    if (e.key === 'Tab') {
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey) {
        if (document.activeElement === first) { e.preventDefault(); last.focus(); }
      } else {
        if (document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    }
    // Ctrl+Enter submits
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      submitDeclineReasonBtn.click();
    }
    // Escape closes
    if (e.key === 'Escape') {
      hideDeclineModal();
    }
  }
  modal.addEventListener('keydown', handleTab);
  modal._trapHandler = handleTab;
}
function releaseModalFocus() {
  if (declineReasonModal._trapHandler) {
    declineReasonModal.removeEventListener('keydown', declineReasonModal._trapHandler);
    declineReasonModal._trapHandler = null;
  }
  if (lastFocusedElement) lastFocusedElement.focus();
}

if (closeDeclineReasonModal) {
  closeDeclineReasonModal.onclick = function() { hideDeclineModal(); };
  closeDeclineReasonModal.onkeydown = function(e) { if (e.key === 'Enter' || e.key === ' ') hideDeclineModal(); };
}
if (cancelDeclineReasonBtn) {
  cancelDeclineReasonBtn.onclick = function() { hideDeclineModal(); };
}
if (submitDeclineReasonBtn) {
  submitDeclineReasonBtn.onclick = function() {
    if (!pendingDeclineRequestId) return;
    const reason = declineReasonTextarea.value.trim();
    declineReasonError.textContent = '';
    submitDeclineReasonBtn.disabled = true;
    submitDeclineReasonBtn.querySelector('.btn-spinner').style.display = 'inline-block';
    fetch('/attendance-system/backend/user/review_password_change.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ request_id: pendingDeclineRequestId, action: 'decline', reason })
    })
    .then(res => res.json())
    .then(data => {
      submitDeclineReasonBtn.disabled = false;
      submitDeclineReasonBtn.querySelector('.btn-spinner').style.display = 'none';
      if (data.success) {
        declineConfirmationMsg.textContent = 'Request declined. Student will be notified.';
        declineConfirmationMsg.style.display = 'block';
        setTimeout(() => {
          hideDeclineModal();
          fetchAllNotificationsAndRender();
        }, 1200);
      } else {
        declineReasonError.textContent = data.error || 'Failed to process request.';
      }
    })
    .catch(() => {
      submitDeclineReasonBtn.disabled = false;
      submitDeclineReasonBtn.querySelector('.btn-spinner').style.display = 'none';
      declineReasonError.textContent = 'Network error. Please try again.';
    });
  };
}
// Close modal on outside click
window.addEventListener('click', function(event) {
  if (event.target === declineReasonModal) {
    hideDeclineModal();
  }
  });
});

// ===== Hardware Status Component JS =====
class HardwareStatusManager {
    constructor() {
        this.updateInterval = 15000; // Update every 15 seconds
        this.isUpdating = false;
        this.autoRefresh = true;
        this.websocketConnected = false;
        this.init();
    }
    
    init() {
        this.loadHardwareStatus();
        this.setupEventListeners();
        this.startAutoUpdate();
        this.checkWebSocketStatus();
    }
    
    setupEventListeners() {
        const refreshBtn = document.getElementById('refresh-hardware');
        const autoRefreshToggle = document.getElementById('auto-refresh-hardware');
        
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadHardwareStatus();
            });
        }
        
        if (autoRefreshToggle) {
            autoRefreshToggle.addEventListener('change', (e) => {
                this.autoRefresh = e.target.checked;
                if (this.autoRefresh) {
                    this.startAutoUpdate();
                } else {
                    this.stopAutoUpdate();
                }
            });
        }
    }
    
    async loadHardwareStatus() {
        if (this.isUpdating) return;
        this.isUpdating = true;
        
        try {
            const response = await fetch('/attendance-system/backend/hardware/status.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateDisplay(data.data);
            } else {
                this.showError('Failed to load hardware status');
            }
        } catch (error) {
            console.error('Error loading hardware status:', error);
            this.showError('Network error loading hardware status');
        } finally {
            this.isUpdating = false;
        }
    }
    
    updateDisplay(devices) {
        const content = document.getElementById('hardware-status-content');
        
        let html = '';
        
        // Add WebSocket status
        html += this.createWebSocketStatus();
        
        // Add summary statistics
        if (devices && devices.length > 0) {
            const onlineCount = devices.filter(d => d.status === 'online').length;
            const offlineCount = devices.filter(d => d.status === 'offline').length;
            const errorCount = devices.filter(d => d.status === 'error').length;
            
            html += '<div class="hardware-summary">';
            html += `<div class="summary-item">
                <div class="summary-number">${devices.length}</div>
                <div class="summary-label">Total</div>
            </div>`;
            html += `<div class="summary-item">
                <div class="summary-number">${onlineCount}</div>
                <div class="summary-label">Online</div>
            </div>`;
            html += `<div class="summary-item">
                <div class="summary-number">${offlineCount}</div>
                <div class="summary-label">Offline</div>
            </div>`;
            html += `<div class="summary-item">
                <div class="summary-number">${errorCount}</div>
                <div class="summary-label">Error</div>
            </div>`;
            html += '</div>';
            
            // Add device list
            devices.forEach(device => {
                html += this.createDeviceItem(device);
            });
        } else {
            html += `
                <div class="no-devices">
                    <p>No hardware devices found</p>
                    <small>Devices will appear here when they connect to the system</small>
                </div>
            `;
        }
        
        content.innerHTML = html;
    }
    
    createWebSocketStatus() {
        const status = this.websocketConnected ? 'connected' : 'disconnected';
        const text = this.websocketConnected ? 'WebSocket Server Running' : 'WebSocket Server Offline';
        
        return `
            <div class="websocket-status ${status}">
                <div class="websocket-indicator ${status}"></div>
                <div>
                    <strong>${text}</strong><br>
                    <small>Port 8080 2 Real-time communication ${this.websocketConnected ? 'active' : 'inactive'}</small>
                </div>
            </div>
        `;
    }
    
    createDeviceItem(device) {
        const status = device.status || 'offline';
        const lastHeartbeat = device.last_heartbeat ? formatTime12h(new Date(device.last_heartbeat).toTimeString().slice(0,5)) : 'Never';
        const deviceType = device.device_type || 'unknown';
        
        return `
            <div class="hardware-device ${status}">
                <div class="device-icon">
                    ${this.getDeviceIcon(deviceType)}
                </div>
                <div class="device-info">
                    <div class="device-name">${device.device_id}</div>
                    <div class="device-details">
                        ${deviceType.toUpperCase()} 2 ${device.location || 'Unknown location'}
                    </div>
                    <div class="device-meta">
                        IP: ${device.ip_address || 'Unknown'} 2 Firmware: ${device.firmware_version || 'Unknown'}
                    </div>
                </div>
                <div class="device-status">
                    <div class="status-indicator">
                        <span class="status-dot ${status}"></span>
                        <span class="status-text ${status}">${status}</span>
                    </div>
                    <div class="last-heartbeat">Last: ${lastHeartbeat}</div>
                </div>
            </div>
        `;
    }
    
    getDeviceIcon(deviceType) {
        switch (deviceType.toLowerCase()) {
            case 'rfid':
                return ' [32m💳 [0m';
            case 'fingerprint':
                return ' [32m👆 [0m';
            case 'combined':
                return ' [32m🔌 [0m';
            default:
                return ' [32m📱 [0m';
        }
    }
    
    async checkWebSocketStatus() {
        try {
            const response = await fetch('/attendance-system/backend/hardware/status.php');
            this.websocketConnected = response.ok;
        } catch (error) {
            this.websocketConnected = false;
        }
        
        // Update display if it exists
        const content = document.getElementById('hardware-status-content');
        if (content && content.innerHTML.includes('websocket-status')) {
            this.loadHardwareStatus();
        }
    }
    
    showError(message) {
        const content = document.getElementById('hardware-status-content');
        content.innerHTML = `
            <div class="hardware-error">
                <p>⚠️ ${message}</p>
                <button onclick="hardwareStatusManager.loadHardwareStatus()" class="btn btn-sm btn-primary">Retry</button>
            </div>
        `;
    }
    
    startAutoUpdate() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.updateTimer = setInterval(() => {
            if (this.autoRefresh) {
                this.loadHardwareStatus();
                this.checkWebSocketStatus();
            }
        }, this.updateInterval);
    }
    
    stopAutoUpdate() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
            this.updateTimer = null;
        }
    }
}

// ===== Attendance Feed Component JS =====
class AttendanceFeedManager {
    constructor() {
        this.updateInterval = 10000; // Update every 10 seconds
        this.isUpdating = false;
        this.autoRefresh = true;
        this.lastUpdate = null;
        this.init();
    }
    
    init() {
        this.loadAttendanceFeed();
        this.setupEventListeners();
        this.startAutoUpdate();
    }
    
    setupEventListeners() {
        const refreshBtn = document.getElementById('refresh-feed');
        const autoRefreshToggle = document.getElementById('auto-refresh');
        
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadAttendanceFeed();
            });
        }
        
        if (autoRefreshToggle) {
            autoRefreshToggle.addEventListener('change', (e) => {
                this.autoRefresh = e.target.checked;
                if (this.autoRefresh) {
                    this.startAutoUpdate();
                } else {
                    this.stopAutoUpdate();
                }
            });
        }
    }
    
    async loadAttendanceFeed() {
        if (this.isUpdating) return;
        this.isUpdating = true;
        
        try {
            const response = await fetch('/attendance-system/backend/attendance/feed.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateDisplay(data.data);
                this.lastUpdate = new Date();
            } else {
                this.showError('Failed to load attendance feed');
            }
        } catch (error) {
            console.error('Error loading attendance feed:', error);
            this.showError('Network error loading attendance feed');
        } finally {
            this.isUpdating = false;
        }
    }
    
    updateDisplay(data) {
        const content = document.getElementById('attendance-feed-content');
        
        let html = '';
        
        // Add active schedule info
        if (data.active_schedule) {
            html += `
                <div class="active-schedule-info">
                    <h4>🟢 Active Schedule</h4>
                    <div class="active-schedule-details">
                        <strong>${data.active_schedule.subject}</strong> in ${data.active_schedule.room}<br>
                        Time: ${data.active_schedule.start_time} - ${data.active_schedule.end_time}
                    </div>
                </div>
            `;
        }
        
        // Add summary statistics
        if (data.summary) {
            html += '<div class="attendance-summary">';
            html += `<div class="summary-item">
                <div class="summary-number">${data.summary.total_attendance || 0}</div>
                <div class="summary-label">Total</div>
            </div>`;
            html += `<div class="summary-item">
                <div class="summary-number">${data.summary.present_count || 0}</div>
                <div class="summary-label">Present</div>
            </div>`;
            html += `<div class="summary-item">
                <div class="summary-number">${data.summary.late_count || 0}</div>
                <div class="summary-label">Late</div>
            </div>`;
            html += `<div class="summary-item">
                <div class="summary-number">${data.summary.rfid_count || 0}</div>
                <div class="summary-label">RFID</div>
            </div>`;
            html += `<div class="summary-item">
                <div class="summary-number">${data.summary.fingerprint_count || 0}</div>
                <div class="summary-label">Fingerprint</div>
            </div>`;
            html += '</div>';
        }
        
        // Add attendance items
        if (data.attendance && data.attendance.length > 0) {
            data.attendance.forEach(record => {
                const isNew = this.isNewRecord(record.created_at);
                html += this.createAttendanceItem(record, isNew);
            });
        } else {
            html += `
                <div class="no-attendance">
                    <p>No attendance records for today</p>
                    <small>Attendance will appear here when students scan their cards or fingerprints</small>
                </div>
            `;
        }
        
        content.innerHTML = html;
    }
    
    createAttendanceItem(record, isNew = false) {
        const initials = this.getInitials(record.student_name);
        // Use formatted date and time side by side
        const dateTime = record.attendance_date_formatted && record.time_in_12h ? `${record.attendance_date_formatted} ${record.time_in_12h}` : '';
        return `
            <div class="attendance-item ${record.status} ${isNew ? 'new' : ''}">
                <div class="attendance-avatar">${initials}</div>
                <div class="attendance-info">
                    <div class="student-name">${record.student_name}</div>
                    <div class="student-details">${record.student_number} 2 ${record.schedule_info.subject}</div>
                    <div class="attendance-meta">
                        Room: ${record.schedule_info.room} 2 Date & Time: ${dateTime}
                    </div>
                </div>
                <div class="attendance-status">
                    <div class="status-badge ${record.status}">${record.status}</div>
                    <div class="method-badge">${record.method}</div>
                    <div class="attendance-time">${dateTime}</div>
                </div>
            </div>
        `;
    }
    
    getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    }
    
    isNewRecord(createdAt) {
        if (!this.lastUpdate) return false;
        const recordTime = new Date(createdAt);
        return recordTime > this.lastUpdate;
    }
    
    showError(message) {
        const content = document.getElementById('attendance-feed-content');
        content.innerHTML = `
            <div class="feed-error">
                <p>⚠️ ${message}</p>
                <button onclick="attendanceFeedManager.loadAttendanceFeed()" class="btn btn-sm btn-primary">Retry</button>
            </div>
        `;
    }
    
    startAutoUpdate() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.updateTimer = setInterval(() => {
            if (this.autoRefresh) {
                this.loadAttendanceFeed();
            }
        }, this.updateInterval);
    }
    
    stopAutoUpdate() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
            this.updateTimer = null;
        }
    }
}

// ===== Schedule Status Component JS =====
function formatTime12h(timeStr) {
    if (!timeStr) return '';
    const [h, m] = timeStr.split(":");
    const date = new Date();
    date.setHours(h, m);
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 should be 12
    return `${hours}:${minutes} ${ampm}`;
}

class ScheduleStatusManager {
    constructor() {
        this.updateInterval = 30000; // Update every 30 seconds
        this.isUpdating = false;
        this.init();
    }
    
    init() {
        this.loadScheduleStatus();
        this.startAutoUpdate();
    }
    
    async loadScheduleStatus() {
        if (this.isUpdating) return;
        this.isUpdating = true;
        
        try {
            const response = await fetch('/attendance-system/backend/schedule/status.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateDisplay(data.data);
            } else {
                this.showError('Failed to load schedule status');
            }
        } catch (error) {
            console.error('Error loading schedule status:', error);
            this.showError('Network error loading schedule status');
        } finally {
            this.isUpdating = false;
        }
    }
    
    updateDisplay(data) {
        const content = document.getElementById('schedule-status-content');
        const indicator = document.getElementById('schedule-status-indicator');
        
        // Update status indicator
        const statusDot = indicator.querySelector('.status-dot');
        const statusText = indicator.querySelector('.status-text');
        
        if (data.has_active_schedule) {
            statusDot.className = 'status-dot active';
            statusText.textContent = 'Active';
        } else if (data.upcoming_schedules.length > 0) {
            statusDot.className = 'status-dot pending';
            statusText.textContent = 'Pending';
        } else {
            statusDot.className = 'status-dot';
            statusText.textContent = 'No Schedules';
        }
        
        // Update content
        let html = '';
        
        // Add statistics
        if (data.stats) {
            html += '<div class="schedule-stats">';
            html += `<div class="stat-item">
                <div class="stat-number">${data.stats.total_schedules || 0}</div>
                <div class="stat-label">Total</div>
            </div>`;
            html += `<div class="stat-item">
                <div class="stat-number">${data.stats.active_schedules || 0}</div>
                <div class="stat-label">Active</div>
            </div>`;
            html += `<div class="stat-item">
                <div class="stat-number">${data.stats.pending_schedules || 0}</div>
                <div class="stat-label">Pending</div>
            </div>`;
            html += `<div class="stat-item">
                <div class="stat-number">${data.stats.completed_schedules || 0}</div>
                <div class="stat-label">Completed</div>
            </div>`;
            html += '</div>';
        }
        
        // Add active schedules
        if (data.active_schedules && data.active_schedules.length > 0) {
            html += '<h4>🟢 Active Schedules</h4>';
            data.active_schedules.forEach(schedule => {
                html += this.createScheduleItem(schedule, 'active');
            });
        }
        
        // Add upcoming schedules
        if (data.upcoming_schedules && data.upcoming_schedules.length > 0) {
            html += '<h4>🟡 Upcoming Schedules</h4>';
            data.upcoming_schedules.forEach(schedule => {
                html += this.createScheduleItem(schedule, 'pending');
            });
        }
        
        // Show message if no schedules
        if ((!data.active_schedules || data.active_schedules.length === 0) && 
            (!data.upcoming_schedules || data.upcoming_schedules.length === 0)) {
            html += '<div class="no-schedules">';
            html += '<p>No upcoming schedules</p>';
            html += '<small>Create a new schedule to get started</small>';
            html += '</div>';
        }
        
        content.innerHTML = html;
    }
    
    createScheduleItem(schedule, status) {
        const startTime = formatTime12h(schedule.start_time);
        const endTime = formatTime12h(schedule.end_time);
        
        // Format date for display
        const scheduleDate = new Date(schedule.date);
        const today = new Date();
        const isToday = scheduleDate.toDateString() === today.toDateString();
        const dateDisplay = isToday ? 'Today' : scheduleDate.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        
        return `
            <div class="schedule-item ${status}">
                <div class="schedule-time">${startTime} - ${endTime}</div>
                <div class="schedule-subject">${schedule.subject}</div>
                <div class="schedule-room">Room: ${schedule.room}</div>
                <div class="schedule-date">Date: ${dateDisplay}</div>
                <div class="schedule-status ${status}">${status}</div>
            </div>
        `;
    }
    
    showError(message) {
        const content = document.getElementById('schedule-status-content');
        const indicator = document.getElementById('schedule-status-indicator');
        
        indicator.querySelector('.status-dot').className = 'status-dot error';
        indicator.querySelector('.status-text').textContent = 'Error';
        
        content.innerHTML = `
            <div class="no-schedules">
                <p>⚠️ ${message}</p>
                <button onclick="scheduleStatusManager.loadScheduleStatus()" class="btn btn-sm btn-primary">Retry</button>
            </div>
        `;
    }
    
    startAutoUpdate() {
        setInterval(() => {
            this.loadScheduleStatus();
        }, this.updateInterval);
    }
}

// ===== Initialization for All Components =====
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('hardware-status-content')) {
        window.hardwareStatusManager = new HardwareStatusManager();
    }
    if (document.getElementById('attendance-feed-content')) {
        window.attendanceFeedManager = new AttendanceFeedManager();
    }
    if (document.getElementById('schedule-status-content')) {
        window.scheduleStatusManager = new ScheduleStatusManager();
    }
});
