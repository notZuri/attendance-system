<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($data['id'] ?? 0);

if ($studentId <= 0) {
    send_json_response(400, ['error' => 'Invalid student ID']);
    exit;
}

try {
    // Verify the user exists and is a student
    $stmt = $pdo->prepare("SELECT id, name, email, student_number FROM users WHERE id = :id AND role = 'student'");
    $stmt->execute(['id' => $studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        send_json_response(404, ['error' => 'Student not found']);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete related records first (attendance, RFID, fingerprints)
    $stmt = $pdo->prepare("DELETE FROM attendance WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $studentId]);
    
    $stmt = $pdo->prepare("DELETE FROM rfid_cards WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $studentId]);
    
    $stmt = $pdo->prepare("DELETE FROM fingerprints WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $studentId]);
    
    // Delete from students table (if exists)
    if ($student['student_number']) {
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_number = :student_number");
        $stmt->execute(['student_number' => $student['student_number']]);
    }
    
    // Delete from users table
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'student'");
    $stmt->execute(['id' => $studentId]);
    
    // Commit transaction
    $pdo->commit();
    
    send_json_response(200, [
        'success' => true,
        'message' => 'Student deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Delete student error: " . $e->getMessage());
    send_json_response(500, ['error' => 'Failed to delete student']);
}
?>
