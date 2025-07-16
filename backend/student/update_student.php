<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/sanitizer.php';
require_once __DIR__ . '/../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method not allowed']);
    exit;
}

// Get and sanitize input data
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? ''); // Frontend sends 'name'
$studentNumber = trim($_POST['student_number'] ?? '');
$email = clean_email($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$course = trim($_POST['course'] ?? '');
$yearLevel = (int)($_POST['year_level'] ?? 1);
$section = trim($_POST['section'] ?? '');

// Validation
if ($id <= 0) {
    send_json_response(400, ['error' => 'Invalid student ID']);
    exit;
}

if (empty($name) || empty($email) || empty($studentNumber)) {
    send_json_response(400, ['error' => 'Full name, email, and student number are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json_response(400, ['error' => 'Please enter a valid email address']);
    exit;
}

if (strlen($name) < 2) {
    send_json_response(400, ['error' => 'Full name must be at least 2 characters long']);
    exit;
}

if ($yearLevel < 1 || $yearLevel > 6) {
    send_json_response(400, ['error' => 'Year level must be between 1 and 6']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if student exists in users table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id AND role = 'student'");
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        send_json_response(404, ['error' => 'Student not found']);
        exit;
    }

    // Check if email already exists for other students
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id AND role = 'student'");
    $stmt->execute(['email' => $email, 'id' => $id]);
    if ($stmt->fetch()) {
        send_json_response(400, ['error' => 'Email is already registered by another student']);
        exit;
    }

    // Check if student number already exists for other students
    $stmt = $pdo->prepare("SELECT id FROM users WHERE student_number = :student_number AND id != :id AND role = 'student'");
    $stmt->execute(['student_number' => $studentNumber, 'id' => $id]);
    if ($stmt->fetch()) {
        send_json_response(400, ['error' => 'Student number is already in use by another student']);
        exit;
    }

    // Update user record
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = :name, 
            email = :email, 
            student_number = :student_number,
            phone = :phone
        WHERE id = :id AND role = 'student'
    ");
    
    $result = $stmt->execute([
        'name' => $name,
        'email' => $email,
        'student_number' => $studentNumber,
        'phone' => $phone,
        'id' => $id
    ]);

    if (!$result) {
        throw new Exception('Failed to update user record');
    }

    // Update or insert student record
    $stmt = $pdo->prepare("SELECT id FROM students WHERE student_number = :student_number");
    $stmt->execute(['student_number' => $studentNumber]);
    $studentExists = $stmt->fetch();
    
    if ($studentExists) {
        // Update existing student record
        $stmt = $pdo->prepare("
            UPDATE students 
            SET fullname = :fullname, 
                email = :email, 
                course = :course, 
                year_level = :year_level, 
                section = :section
            WHERE student_number = :student_number
        ");
    } else {
        // Insert new student record
        $stmt = $pdo->prepare("
            INSERT INTO students (fullname, student_number, email, course, year_level, section) 
            VALUES (:fullname, :student_number, :email, :course, :year_level, :section)
        ");
    }
    
    $result = $stmt->execute([
        'fullname' => $name,
        'student_number' => $studentNumber,
        'email' => $email,
        'course' => $course,
        'year_level' => $yearLevel,
        'section' => $section
    ]);

    if (!$result) {
        throw new Exception('Failed to update student record');
    }

    // Commit transaction
    $pdo->commit();
    
    // Get the updated student data
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.phone, u.student_number, u.role, u.created_at,
               s.course, s.year_level, s.section
        FROM users u
        LEFT JOIN students s ON u.student_number = s.student_number
        WHERE u.id = :id
    ");
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    send_json_response(200, [
        'success' => true,
        'message' => 'Student updated successfully',
        'student' => $student
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Student update error: " . $e->getMessage());
    send_json_response(500, ['error' => 'Failed to update student. Please try again.']);
}
