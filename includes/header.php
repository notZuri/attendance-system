<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Automated Attendance System</title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  
  <!-- Main stylesheet -->
  <link rel="stylesheet" href="/assets/css/style.css" />
  <script defer src="/assets/js/main.js"></script>
</head>
<body>
<header>
  <div class="container">
    <h1 style="color: white;">Automated Attendance System</h1>
    <?php if(isset($_SESSION['fullname'])): ?>
      <div class="user-info" style="color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></div>
    <?php endif; ?>
  </div>
</header>
<div class="wrapper">
