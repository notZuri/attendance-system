<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
  header('Location: index.php');
  exit();
}

if ($_SESSION['role'] === 'professor') {
  header('Location: professor/dashboard.php');
} elseif ($_SESSION['role'] === 'student') {
  header('Location: student/dashboard.php');
} else {
  header('Location: index.php');
}
exit();
