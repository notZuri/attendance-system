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
  <title>Professor Dashboard - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
  <style>
    .layout {
      display: flex;
      height: calc(100vh - 60px); /* Adjust if your header height differs */
    }

    .main-content {
      flex-grow: 1;
      padding: 2rem;
      background-color: #f4f6f8;
      overflow-y: auto;
    }

    .stats-cards {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .card {
      background: #fff;
      padding: 1rem 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      flex: 1;
      min-width: 200px;
    }

    .card-teal { border-left: 5px solid teal; }
    .card-blue { border-left: 5px solid #007bff; }
    .card-orange { border-left: 5px solid orange; }

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

  <!-- ✅ FIX: Wrap sidebar and main content inside .layout -->
  <div class="layout">
    <?php include "../../includes/sidebar.php"; ?>

    <main class="main-content">
      <h2>Welcome, <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong></h2>

      <section class="stats-cards" style="margin-top: 2rem;">
        <div class="card card-teal">
          <h3>Students Enrolled</h3>
          <p class="stat-number">120</p>
        </div>
        <div class="card card-blue">
          <h3>Classes Scheduled</h3>
          <p class="stat-number">8</p>
        </div>
        <div class="card card-orange">
          <h3>Attendances Today</h3>
          <p class="stat-number">58</p>
        </div>
      </section>

      <section style="margin-top: 3rem;">
        <h3>Recent Attendance Logs</h3>
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
            <!-- TODO: Dynamically fetch from DB -->
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
      </section>
    </main>
  </div>
</body>
</html>
