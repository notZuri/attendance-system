<?php
require_once __DIR__ . '/../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
$success = $stmt->execute([$id]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete student']);
}
?>
