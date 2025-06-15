<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: /attendance-system/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Attendance - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
  <style>
    /* Layout wrapper */
    .layout {
      display: flex;
      height: calc(100vh - 60px); /* Adjust 60px if your header height differs */
    }

    /* Main content grows, scrolls vertically */
    .main-content {
      flex-grow: 1;
      padding: 2rem;
      background-color: #f4f6f8;
      overflow-y: auto;
    }

    /* If your sidebar is not sticky, add this or adjust your sidebar CSS */
    .sidebar {
      width: 240px; /* or your sidebar width */
      height: calc(100vh - 60px);
      position: sticky;
      top: 60px; /* same as header height */
      overflow-y: auto;
    }

    /* Example attendance table styling */
    .attendance-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      background: #fff;
      border-radius: 0.5rem;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .attendance-table th,
    .attendance-table td {
      padding: 0.75rem;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    .status-present {
      color: green;
      font-weight: bold;
    }

    .status-absent {
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <?php include "../../includes/header.php"; ?>

  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>

    <main class="main-content">
      <h2>Attendance Records</h2>

      <table class="attendance-table">
        <thead>
          <tr>
            <th>Student Name</th>
            <th>Student Number</th>
            <th>Class</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <!-- Example static data: replace with dynamic from DB -->
          <tr>
            <td>Juan Dela Cruz</td>
            <td>20230001</td>
            <td>IT101</td>
            <td>2025-06-01</td>
            <td><span class="status-present">Present</span></td>
          </tr>
          <tr>
            <td>Maria Clara</td>
            <td>20230002</td>
            <td>IT101</td>
            <td>2025-06-01</td>
            <td><span class="status-absent">Absent</span></td>
          </tr>
        </tbody>
      </table>

    </main>
  </div>
</body>
</html>
