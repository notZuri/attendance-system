document.addEventListener('DOMContentLoaded', () => {
  // --- Login Form Handler ---
  const loginForm = document.querySelector('form[action="backend/auth/login.php"]');
  const errorMsg = document.getElementById('error-msg');

  if (!loginForm) {
    console.error('Login form not found!');
    return;
  }

  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (errorMsg) {
      errorMsg.style.display = 'none';
      errorMsg.textContent = '';
    }

    const formData = new FormData(loginForm);

    try {
      const response = await fetch('backend/auth/login.php', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();

      if (data.success && data.redirect) {
        window.location.href = data.redirect;
      } else if (data.error) {
        if (errorMsg) {
          errorMsg.textContent = data.error;
          errorMsg.style.display = 'block';
        }
      } else {
        if (errorMsg) {
          errorMsg.textContent = 'Unexpected response from server.';
          errorMsg.style.display = 'block';
        }
      }
    } catch (err) {
      if (errorMsg) {
        errorMsg.textContent = 'Network error or server unreachable.';
        errorMsg.style.display = 'block';
      }
      console.error('Fetch error:', err);
    }
  });

  // --- Enrollment Modal ---
  const modal = document.getElementById('enrollModal');
  const message = document.getElementById('enrollMessage');
  const cancelBtn = document.getElementById('cancelEnroll');

  let pollInterval = null;

  // --- Start Enrollment Session ---
  async function startEnrollment(studentId, type) {
    try {
      const response = await fetch(`backend/${type}/enroll.php`, {
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
      const response = await fetch(`backend/${type}/scan.php?session_id=${sessionId}`);

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
        const response = await fetch('backend/edit_student.php', {
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
});

// --- Schedule Form Handler ---
const scheduleForm = document.getElementById('scheduleForm');
const scheduleModal = document.getElementById('scheduleModal');
const scheduleMessage = document.getElementById('scheduleMessage');

if (scheduleForm) {
  scheduleForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(scheduleForm);

    try {
      const response = await fetch('backend/schedule/save.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        scheduleMessage.style.color = 'green';
        scheduleMessage.textContent = result.message || 'Schedule saved!';
        setTimeout(() => {
          scheduleModal.style.display = 'none';
          location.reload(); // Refresh to show new schedule
        }, 1500);
      } else {
        scheduleMessage.style.color = 'red';
        scheduleMessage.textContent = result.message || 'Failed to save schedule.';
      }
    } catch (err) {
      console.error('Error saving schedule:', err);
      scheduleMessage.style.color = 'red';
      scheduleMessage.textContent = 'An error occurred while saving.';
    }
  });
}
