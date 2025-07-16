<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/sanitizer.php';
require_once __DIR__ . '/../utils/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = clean_email($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';
$phone = trim($_POST['phone'] ?? '');

// Student-specific fields
$course = trim($_POST['course'] ?? '');
$yearLevel = (int)($_POST['year_level'] ?? 0);
$section = trim($_POST['section'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($password) || empty($role)) {
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

// Name validation
if (strlen($name) < 2) {
    echo json_encode(['error' => 'Name must be at least 2 characters long']);
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Please enter a valid email address']);
    exit;
}

// Password validation
if (strlen($password) < 6) {
    echo json_encode(['error' => 'Password must be at least 6 characters long']);
    exit;
}

// Role validation
if (!in_array($role, ['professor', 'student'])) {
    echo json_encode(['error' => 'Please select a valid role']);
    exit;
}

// Phone validation (optional for professors, required for students)
if ($role === 'student' && empty($phone)) {
    echo json_encode(['error' => 'Phone number is required for students']);
    exit;
}

if (!empty($phone) && !preg_match('/^\d{10,}$/', $phone)) {
    echo json_encode(['error' => 'Phone must be at least 10 digits']);
    exit;
}

// Student-specific validation
if ($role === 'student') {
    if (empty($course)) {
        echo json_encode(['error' => 'Course/Program is required for students']);
        exit;
    }
    
    if ($yearLevel < 1 || $yearLevel > 6) {
        echo json_encode(['error' => 'Please select a valid year level (1-6)']);
        exit;
    }
    
    if (empty($section)) {
        echo json_encode(['error' => 'Section is required for students']);
        exit;
    }
}

try {
    // Check if email already exists in users table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Email is already registered']);
        exit;
    }

    // Check if email already exists in students table (for students)
    if ($role === 'student') {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Email is already registered as a student']);
            exit;
        }
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Generate student number if registering as student
    $studentNumber = null;
    if ($role === 'student') {
        // Generate unique student number (format: YYYY + 4-digit sequence)
        $currentYear = date('Y');
        $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(student_number, 5) AS UNSIGNED)) as max_num FROM users WHERE student_number LIKE ? AND role = 'student'");
        $stmt->execute([$currentYear . '%']);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_num'] ?? 0) + 1;
        $studentNumber = $currentYear . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Start transaction to ensure data consistency
    $pdo->beginTransaction();

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, student_number, phone) VALUES (:name, :email, :password_hash, :role, :student_number, :phone)");
    $result = $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => $role,
        'student_number' => $studentNumber,
        'phone' => $phone
    ]);

    if (!$result) {
        throw new Exception('Failed to create user account');
    }

    $userId = $pdo->lastInsertId();

    // If registering as student, also create entry in students table
    if ($role === 'student') {
        $stmt = $pdo->prepare("INSERT INTO students (fullname, student_number, course, year_level, section, email) VALUES (:fullname, :student_number, :course, :year_level, :section, :email)");
        $result = $stmt->execute([
            'fullname' => $name,
            'student_number' => $studentNumber,
            'course' => $course,
            'year_level' => $yearLevel,
            'section' => $section,
            'email' => $email
        ]);

        if (!$result) {
            throw new Exception('Failed to create student record');
        }
    }

    // Commit transaction
    $pdo->commit();

    $message = $role === 'student' 
        ? "Registration successful! Your student number is: $studentNumber. You can now log in."
        : "Registration successful! You can now log in.";
        
    echo json_encode([
        'success' => true,
        'message' => $message,
        'redirect' => 'index.php',
        'student_number' => $studentNumber
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['error' => 'Registration failed. Please try again.']);
}
