<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
  header("Location: /attendance-system/index.php");
  exit();
}

require_once __DIR__ . '/../../backend/config/config.php';

// Handle form submission for adding new student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $fullname = $_POST['student_fullname'];
    $student_number = $_POST['student_number'];
    $email = $_POST['student_email'];
    $phone = $_POST['student_phone'];

    $stmt = $pdo->prepare("INSERT INTO students (full_name, student_number, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fullname, $student_number, $email, $phone]);

    header("Location: students.php");
    exit();
}

// Fetch all students
$stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Students - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
  <style>
    /* Your existing styles here */
    .layout { display: flex; height: calc(100vh - 60px); }
    .main-content { flex-grow: 1; padding: 2rem; background-color: #f4f6f8; overflow-y: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; border-radius: 0.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    th, td { padding: 0.75rem; border-bottom: 1px solid #eee; text-align: left; }
    form { background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 2rem; }
    form label { display: block; margin-top: 1rem; font-weight: bold; }
    form input { width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem; }
    .btn { margin-top: 1rem; cursor: pointer; }
    .btn-small { padding: 4px 10px; font-size: 0.8rem; margin-right: 5px; border: none; border-radius: 3px; color: white; }
    .btn-edit { background-color: #27ae60; }
    .btn-delete { background-color: #e74c3c; }
    .btn-rfid { background-color: #3498db; }
    .btn-fingerprint { background-color: #2980b9; }

    /* Modal styles */
    .modal {
      display: none; 
      position: fixed; 
      z-index: 1000; 
      left: 0; top: 0; 
      width: 100%; height: 100%; 
      overflow: auto; 
      background-color: rgba(0,0,0,0.5); 
    }
    .modal-content {
      background-color: #fefefe;
      margin: 10% auto; 
      padding: 2rem;
      border: 1px solid #888;
      border-radius: 0.5rem;
      width: 400px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      position: relative;
    }
    .close-modal {
      color: #aaa;
      float: right;
      font-size: 1.5rem;
      font-weight: bold;
      cursor: pointer;
      position: absolute;
      top: 10px;
      right: 15px;
    }
    .close-modal:hover, .close-modal:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <?php include "../../includes/header.php"; ?>
  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>
    <main class="main-content">
      <h2>Manage Students</h2>

      <button class="btn" id="btn-add-student" style="margin-bottom: 1rem;">Add New Student</button>

      <!-- Add Student Form -->
      <form id="form-add-student" method="POST" style="display:none;">
        <input type="hidden" name="action" value="add" />
        <h3>Add Student</h3>
        <label for="student_fullname">Full Name</label>
        <input type="text" id="student_fullname" name="student_fullname" placeholder="Enter full name" required />

        <label for="student_number">Student Number</label>
        <input type="text" id="student_number" name="student_number" placeholder="Enter student number" required />

        <label for="student_email">Email</label>
        <input type="email" id="student_email" name="student_email" placeholder="Enter email" required />

        <label for="student_phone">Phone Number</label>
        <input type="tel" id="student_phone" name="student_phone" placeholder="Enter phone number" required />

        <button type="submit" class="btn">Save Student</button>
        <button type="button" class="btn" id="btn-cancel-add-student" style="background:#e74c3c;">Cancel</button>
      </form>

      <!-- Edit Student Modal -->
      <div id="editModal" class="modal">
        <div class="modal-content">
          <span class="close-modal" id="closeEditModal">&times;</span>
          <h3>Edit Student</h3>
          <form id="form-edit-student">
            <input type="hidden" id="edit_student_id" name="id" />
            <label for="edit_student_fullname">Full Name</label>
            <input type="text" id="edit_student_fullname" name="full_name" required />

            <label for="edit_student_number">Student Number</label>
            <input type="text" id="edit_student_number" name="student_number" required />

            <label for="edit_student_email">Email</label>
            <input type="email" id="edit_student_email" name="email" required />

            <label for="edit_student_phone">Phone Number</label>
            <input type="tel" id="edit_student_phone" name="phone" required />

            <button type="submit" class="btn">Save Changes</button>
          </form>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Student Number</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>RFID Enrolled</th>
            <th>Fingerprint Enrolled</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($students) > 0): ?>
            <?php foreach ($students as $student): ?>
              <tr id="student-row-<?= $student['id'] ?>">
                <td><?= htmlspecialchars($student['full_name']) ?></td>
                <td><?= htmlspecialchars($student['student_number']) ?></td>
                <td><?= htmlspecialchars($student['email']) ?></td>
                <td><?= htmlspecialchars($student['phone']) ?></td>
                <td><?= $student['rfid_tag'] ? '✔️' : '❌' ?></td>
                <td><?= $student['fingerprint_id'] ? '✔️' : '❌' ?></td>
                <td>
                  <button class="btn-small btn-edit" data-id="<?= $student['id'] ?>">Edit</button>
                  <button class="btn-small btn-delete" data-id="<?= $student['id'] ?>">Delete</button>
                  <button class="btn-small btn-rfid enroll-rfid-btn" data-student-id="<?= $student['id'] ?>">Enroll RFID</button>
                  <button class="btn-small btn-fingerprint enroll-fingerprint-btn" data-student-id="<?= $student['id'] ?>">Enroll Fingerprint</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7">No students found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </main>
  </div>

  <script>
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

    formEdit.addEventListener('submit', e => {
      e.preventDefault();
      const formData = new FormData(formEdit);
      fetch('/attendance-system/backend/student/edit_student.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Student updated successfully.');
          location.reload();
        } else {
          alert('Failed to update student.');
        }
      })
      .catch(() => alert('Network error.'));
    });

    // Enroll RFID button handler
    document.querySelectorAll('.enroll-rfid-btn').forEach(button => {
      button.addEventListener('click', () => {
        const studentId = button.dataset.studentId;
        if (confirm("Make sure the card is ready. Proceed with RFID enrollment?")) {
          fetch(`/attendance-system/backend/rfid/enroll.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId })
          })
          .then(res => res.json())
          .then(data => {
            alert(data.message);
            if (data.success) location.reload();
          })
          .catch(() => alert("Error during RFID enrollment."));
        }
      });
    });

    // Enroll Fingerprint button handler
    document.querySelectorAll('.enroll-fingerprint-btn').forEach(button => {
      button.addEventListener('click', () => {
        const studentId = button.dataset.studentId;
        if (confirm("Place finger on scanner to enroll.")) {
          fetch(`/attendance-system/backend/fingerprint/enroll.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId })
          })
          .then(res => res.json())
          .then(data => {
            alert(data.message);
            if (data.success) location.reload();
          })
          .catch(() => alert("Error during fingerprint enrollment."));
        }
      });
    });
  </script>
</body>
</html>
