<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../utils/validation.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        throw new InvalidArgumentException('User ID is required.');
    }

    $userId = (int)$data['id'];
    $fieldsToUpdate = [];

    // Allow updating name and email only for simplicity
    if (isset($data['name'])) {
        $name = trim($data['name']);
        if ($name === '') {
            throw new InvalidArgumentException('Name cannot be empty.');
        }
        $fieldsToUpdate['name'] = $name;
    }

    if (isset($data['email'])) {
        $email = strtolower(trim($data['email']));
        if (!isValidEmail($email)) {
            throw new InvalidArgumentException('Invalid email format.');
        }
        // Check if email belongs to another user
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
        $stmt->execute([':email' => $email, ':id' => $userId]);
        if ($stmt->fetch()) {
            throw new RuntimeException('Email already in use by another account.');
        }
        $fieldsToUpdate['email'] = $email;
    }

    if (empty($fieldsToUpdate)) {
        throw new InvalidArgumentException('No valid fields provided for update.');
    }

    $setParts = [];
    $params = [];
    foreach ($fieldsToUpdate as $field => $value) {
        $setParts[] = "$field = :$field";
        $params[":$field"] = $value;
    }
    $params[':id'] = $userId;

    $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
