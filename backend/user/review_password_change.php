<?php
session_start();
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'professor') {
        throw new Exception('Unauthorized: not logged in as professor.');
    }
    $professorId = (int)$_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $requestId = (int)($data['request_id'] ?? 0);
    $action = $data['action'] ?? '';
    $reason = $data['reason'] ?? '';
    if (!$requestId || !in_array($action, ['approve', 'decline'])) {
        throw new Exception('Invalid request.');
    }
    // Fetch the request
    $stmt = $pdo->prepare('SELECT * FROM password_change_requests WHERE id = ? AND professor_id = ? AND status = "pending"');
    $stmt->execute([$requestId, $professorId]);
    $req = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$req) {
        throw new Exception('Request not found or already processed.');
    }
    $studentId = $req['student_id'];
    // Fetch student info for notification
    $studentData = $pdo->prepare('SELECT name, student_number FROM users WHERE id = ?');
    $studentData->execute([$studentId]);
    $s = $studentData->fetch();
    $studentLabel = htmlspecialchars($s['name']) . ' (' . htmlspecialchars($s['student_number']) . ')';
    if ($action === 'approve') {
        // Update student's password
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$req['new_password_hash'], $studentId]);
        // Mark request as approved
        $stmt = $pdo->prepare('UPDATE password_change_requests SET status = "approved", reviewed_at = NOW(), reviewed_by = ?, reason = ? WHERE id = ?');
        $stmt->execute([$professorId, $reason, $requestId]);
        // Notify student
        $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
        $notif->execute([$studentId, 'Password Change Approved', 'Your password change request has been approved by your professor.']);
        // Notify professor (record of action)
        $profMsg = 'You approved the password change request for ' . $studentLabel . ' on ' . date('M j, Y g:i A') . '.';
        $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
        $notif->execute([$professorId, 'Password Change Request Approved', $profMsg]);
        echo json_encode(['success' => true, 'message' => 'Password change approved and student notified.']);
    } else {
        // Mark request as declined
        $stmt = $pdo->prepare('UPDATE password_change_requests SET status = "declined", reviewed_at = NOW(), reviewed_by = ?, reason = ? WHERE id = ?');
        $stmt->execute([$professorId, $reason, $requestId]);
        // Notify student
        $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
        $msg = 'Your password change request was declined by your professor.' . ($reason ? ' Reason: ' . htmlspecialchars($reason) : '');
        $notif->execute([$studentId, 'Password Change Declined', $msg]);
        // Notify professor (record of action)
        $profMsg = 'You declined the password change request for ' . $studentLabel . ' on ' . date('M j, Y g:i A') . '.';
        if ($reason) {
            $profMsg .= ' Reason: ' . htmlspecialchars($reason);
        }
        $notif = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
        $notif->execute([$professorId, 'Password Change Request Declined', $profMsg]);
        echo json_encode(['success' => true, 'message' => 'Password change declined and student notified.']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 