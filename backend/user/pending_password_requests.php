<?php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'professor') {
        throw new Exception('Unauthorized: not logged in as professor.');
    }
    $professorId = (int)$_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT r.id, r.student_id, u.name as student_name, u.student_number, r.created_at FROM password_change_requests r JOIN users u ON r.student_id = u.id WHERE r.professor_id = ? AND r.status = "pending" ORDER BY r.created_at ASC');
    $stmt->execute([$professorId]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'requests' => $requests]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 