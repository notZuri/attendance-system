<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/db.php';

header('Content-Type: application/json');

try {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $role = isset($_GET['role']) ? strtolower(trim($_GET['role'])) : null;

    $query = 'SELECT id, role, name, email, created_at FROM users WHERE 1=1';
    $params = [];

    if ($userId) {
        $query .= ' AND id = :id';
        $params[':id'] = $userId;
    }

    if ($role) {
        if (!in_array($role, ['student', 'professor'], true)) {
            throw new InvalidArgumentException('Invalid role filter.');
        }
        $query .= ' AND role = :role';
        $params[':role'] = $role;
    }

    $query .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $users = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $users]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
