<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID']);
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if ($student) {
    echo json_encode(['success' => true, 'student' => $student]);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
}
?>
