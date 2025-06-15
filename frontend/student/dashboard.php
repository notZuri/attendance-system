<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: /frontend/login.php");
    exit();
}
include "../../includes/header.php";
include "../../includes/sidebar.php";
?>
 <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
<main class="main-content">

  <!-- Student List Table -->
  <table id="students-table" class="students-table">
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
      <!-- TODO: Dynamically load students from DB -->
      <tr>
        <td>Juan Dela Cruz</td>
        <td>20230001</td>
        <td>juan@example.com</td>
        <td>09123456789</td>
        <td>✔️</td>
        <td>✔️</td>
        <td>
          <button class="btn btn-edit">Edit</button>
          <button class="btn btn-delete">Delete</button>
          <button class="btn btn-enroll-rfid">Enroll RFID</button>
          <button class="btn btn-enroll-fingerprint">Enroll Fingerprint</button>
        </td>
      </tr>
    </tbody>
  </table>
</main>


<?php include "../../includes/footer.php"; ?>
