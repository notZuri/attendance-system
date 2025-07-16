<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/sanitizer.php';
require_once __DIR__ . '/../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method not allowed']);
    exit;
}

// Get and sanitize input data
$fullname = trim($_POST['fullname'] ?? '');
$email = clean_email($_POST['email'] ?? '');
$course = trim($_POST['course'] ?? '');
$yearLevel = (int)($_POST['year_level'] ?? 1);
$section = trim($_POST['section'] ?? '');
$studentNumber = trim($_POST['student_number'] ?? '');

// Validation
if (empty($fullname) || empty($email)) {
    send_json_response(400, ['error' => 'Full name and email are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json_response(400, ['error' => 'Please enter a valid email address']);
    exit;
}

if (strlen($fullname) < 2) {
    send_json_response(400, ['error' => 'Full name must be at least 2 characters long']);
    exit;
}

if ($yearLevel < 1 || $yearLevel > 6) {
    send_json_response(400, ['error' => 'Year level must be between 1 and 6']);
    exit;
}

try {
    // Check if email already exists in students table
    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        send_json_response(400, ['error' => 'Email is already registered as a student']);
        exit;
    }

    // Check if student number already exists (if provided)
    if (!empty($studentNumber)) {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = :student_number");
        $stmt->execute(['student_number' => $studentNumber]);
        if ($stmt->fetch()) {
            send_json_response(400, ['error' => 'Student number is already in use']);
            exit;
        }
    } else {
        // Generate unique student number if not provided
        $currentYear = date('Y');
        $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(student_number, 5) AS UNSIGNED)) as max_num FROM students WHERE student_number LIKE ?");
        $stmt->execute([$currentYear . '%']);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_num'] ?? 0) + 1;
        $studentNumber = $currentYear . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Insert new student
    $stmt = $pdo->prepare("
        INSERT INTO students (fullname, student_number, course, year_level, section, email, created_at) 
        VALUES (:fullname, :student_number, :course, :year_level, :section, :email, NOW())
    ");
    
    $result = $stmt->execute([
        'fullname' => $fullname,
        'student_number' => $studentNumber,
        'course' => $course,
        'year_level' => $yearLevel,
        'section' => $section,
        'email' => $email
    ]);

    if ($result) {
        $studentId = $pdo->lastInsertId();
        
        // Get the created student data
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute(['id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        send_json_response(200, [
            'success' => true,
            'message' => 'Student created successfully',
            'student' => $student
        ]);
    } else {
        send_json_response(500, ['error' => 'Failed to create student']);
    }
} catch (PDOException $e) {
    error_log("Student creation error: " . $e->getMessage());
    send_json_response(500, ['error' => 'Failed to create student. Please try again.']);
} 