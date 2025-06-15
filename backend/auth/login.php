<?php
// backend/auth/login.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/sanitizer.php';
require_once __DIR__ . '/../utils/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = clean_email($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, password_hash, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['error' => 'Invalid email or password']);
        exit;
    }

    // Start session and store user data
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['fullname'] = $user['name'];

    // Return success with RELATIVE redirect path
    echo json_encode([
        'success' => true,
        'role' => $user['role'],
        'redirect' => 'frontend/' . $user['role'] . '/dashboard.php'
    ]);
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}
