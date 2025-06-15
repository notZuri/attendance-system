<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../auth/auth_helpers.php';

session_start();

if (!isUserLoggedIn() || !userHasRole(['professor'])) {
    sendJsonResponse(401, ['error' => 'Unauthorized']);
    exit;
}

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare('
        SELECT u.id, u.full_name, COUNT(a.id) AS attendance_count
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND DATE(a.timestamp) BETWEEN :start_date AND :end_date
        GROUP BY u.id, u.full_name
        ORDER BY u.full_name ASC
    ');

    $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(200, [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'summary' => $summary,
    ]);
} catch (PDOException $e) {
    error_log('Attendance summary error: ' . $e->getMessage());
    sendJsonResponse(500, ['error' => 'Internal server error']);
}
