<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/db.php';
require_once __DIR__ . '/../utils/validation.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $requiredFields = ['role', 'name', 'email', 'password'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new InvalidArgumentException("$field is required.");
        }
    }

    $role = strtolower(trim($data['role']));
    if (!in_array($role, ['student', 'professor'], true)) {
        throw new InvalidArgumentException('Role must be either student or professor.');
    }

    $name = trim($data['name']);
    $email = strtolower(trim($data['email']));
    $password = $data['password'];

    if (!isValidEmail($email)) {
        throw new InvalidArgumentException('Invalid email format.');
    }

    if (!isStrongPassword($password)) {
        throw new InvalidArgumentException('Password does not meet strength requirements.');
    }

    // Check if email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        throw new RuntimeException('Email already registered.');
    }

    // Hash password securely
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare('
        INSERT INTO users (role, name, email, password_hash, created_at)
        VALUES (:role, :name, :email, :password_hash, NOW())
    ');
    $stmt->execute([
        ':role' => $role,
        ':name' => $name,
        ':email' => $email,
        ':password_hash' => $passwordHash,
    ]);

    echo json_encode(['status' => 'success', 'message' => 'User created successfully.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
