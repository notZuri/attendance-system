<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/db.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        throw new InvalidArgumentException('User ID is required.');
    }

    $userId = (int)$data['id'];

    // Check if user exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('User not found.');
    }

    // Delete user
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);

    echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
