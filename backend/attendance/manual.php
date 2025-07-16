<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    send_json_response(403, ['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$schedule_id = (int)($input['schedule_id'] ?? 0);
$date = $input['date'] ?? date('Y-m-d');
$attendance = $input['attendance'] ?? [];

if (!$schedule_id || !$date || !is_array($attendance) || count($attendance) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

try {
    $pdo->beginTransaction();
    $failed = [];
    foreach ($attendance as $entry) {
        $student_id = (int)($entry['student_id'] ?? 0);
        $status = $entry['status'] ?? 'present';
        $time_in = $entry['time_in'] ?? null;
        if (!$student_id) {
            $failed[] = $student_id;
            continue;
        }
        try {
            // Check if record exists
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = :user_id AND schedule_id = :schedule_id AND attendance_date = :date");
            $stmt->execute([
                ':user_id' => $student_id,
                ':schedule_id' => $schedule_id,
                ':date' => $date
            ]);
            $existing = $stmt->fetch();
            if ($status === '' || $status === null) {
                // If status is empty, delete the record if it exists
                if ($existing) {
                    $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = :id");
                    $stmt->execute([':id' => $existing['id']]);
                }
            } elseif (in_array($status, ['present','late','absent'])) {
                if ($existing) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE attendance SET status = :status, time_in = :time_in WHERE id = :id");
                    $stmt->execute([
                        ':status' => $status,
                        ':time_in' => $time_in ?? date('H:i:s'),
                        ':id' => $existing['id']
                    ]);
                } else {
                    // Insert
                    $stmt = $pdo->prepare("INSERT INTO attendance (user_id, schedule_id, attendance_date, status, time_in) VALUES (:user_id, :schedule_id, :date, :status, :time_in)");
                    $stmt->execute([
                        ':user_id' => $student_id,
                        ':schedule_id' => $schedule_id,
                        ':date' => $date,
                        ':status' => $status,
                        ':time_in' => $time_in ?? date('H:i:s')
                    ]);
                }
            } else {
                $failed[] = $student_id;
            }
        } catch (PDOException $ex) {
            $failed[] = $student_id;
        }
    }
    $pdo->commit();
    if (count($failed) > 0) {
        echo json_encode(['success' => false, 'failed_students' => $failed, 'message' => 'Some students failed to record attendance.']);
    } else {
        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Manual attendance error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
} 