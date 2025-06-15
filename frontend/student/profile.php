<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . "/../../backend/config/config.php");

$userId = $_SESSION['user_id'];

// Fetch student info using PDO
$sql = "SELECT student_number, name, email, phone FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($email) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $updateSql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
        $stmtUpdate = $pdo->prepare($updateSql);
        if ($stmtUpdate->execute([$name, $email, $phone, $userId])) {
            $success = "Profile updated successfully.";
            $_SESSION['name'] = $name;
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone'] = $phone;
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>

<?php include "../../includes/header.php"; ?>
<?php include "../../includes/sidebar.php"; ?>
<link rel="stylesheet" href="/attendance-system/assets/css/style.css" />

<main class="main-content">
  <h2>Your Profile</h2>

  <?php if ($success): ?>
    <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 0.75rem; margin-bottom: 1rem; border-radius: 4px;">
      <?php echo $success; ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 0.75rem; margin-bottom: 1rem; border-radius: 4px;">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="profile.php" style="max-width: 500px;">
    <label for="student_number">Student Number</label>
    <input type="text" id="student_number" name="student_number" value="<?php echo htmlspecialchars($user['student_number']); ?>" disabled style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">

    <label for="name">Full Name</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">

    <label for="phone">Phone Number</label>
    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem;">

    <button type="submit" style="background-color: #007bff; color: white; border:none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Update Profile</button>
  </form>
</main>

<?php include "../../includes/footer.php"; ?>
