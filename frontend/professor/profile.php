<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
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
</head>
<body>
<?php include "../../includes/header.php"; ?>
<?php include "../../includes/sidebar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Professor Profile - Attendance System</title>
  <link rel="stylesheet" href="/attendance-system/assets/css/style.css" />
</head>
<body>
<main class="main-content">
  <h2>My Profile</h2>

  <form id="profile-form" style="max-width: 600px; margin-top: 1rem;">
    <label for="name">Full Name</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($professorName); ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

    <label for="phone">Phone Number</label>
    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">

    <button type="submit" class="btn" style="margin-top: 1rem;">Update Profile</button>
  </form>

  <div id="profile-message" style="margin-top: 1rem;"></div>
</main>

<script>
document.getElementById('profile-form').addEventListener('submit', function(e) {
  e.preventDefault();

  // For demo, show a success message (replace with real AJAX update)
  document.getElementById('profile-message').textContent = "Profile updated successfully!";
  document.getElementById('profile-message').style.color = 'green';
});
</script>

<?php include "../../includes/footer.php"; ?>
