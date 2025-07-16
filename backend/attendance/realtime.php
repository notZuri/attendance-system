<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../auth/auth_helpers.php';

session_start();

if (!is_logged_in() || !user_has_role(['professor'])) {
    send_json_response(401, ['error' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT a.id, u.full_name, a.method, a.scan_value, a.timestamp
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        WHERE DATE(a.timestamp) = CURDATE()
        ORDER BY a.timestamp DESC
    ');
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    send_json_response(200, ['attendance' => $records]);
} catch (PDOException $e) {
    error_log('Realtime attendance fetch error: ' . $e->getMessage());
    send_json_response(500, ['error' => 'Internal server error']);
}
