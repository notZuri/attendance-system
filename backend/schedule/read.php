<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
  send_json_response(403, ['success' => false, 'error' => 'Unauthorized']);
  exit();
}
$professor_id = $_SESSION['user_id'];

// New: support status, search, limit, offset
$status = isset($_GET['status']) ? $_GET['status'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
  $where = ['professor_id = :professor_id'];
  $params = [':professor_id' => $professor_id];
  if ($status) {
    $where[] = 'current_status = :status';
    $params[':status'] = $status;
  }
  if ($search) {
    $where[] = '(LOWER(subject) LIKE :search1 OR LOWER(room) LIKE :search2)';
    $params[':search1'] = "%" . strtolower($search) . "%";
    $params[':search2'] = "%" . strtolower($search) . "%";
  }
  $sql = "SELECT id, subject, room, date, start_time, end_time, late_threshold, current_status FROM schedules WHERE ".implode(' AND ', $where)." ORDER BY date DESC, start_time DESC";
  if ($limit > 0) {
    $sql .= " LIMIT " . intval($limit);
    if ($offset > 0) {
      $sql .= " OFFSET " . intval($offset);
    }
  }

  // Debug log for SQL and params
  error_log('[Schedule Read] SQL: ' . $sql);
  error_log('[Schedule Read] Params: ' . json_encode($params));

  $stmt = $pdo->prepare($sql);
  // Only bind parameters that are present in the SQL
  foreach ($params as $k => $v) {
    if (strpos($sql, $k) !== false) {
      $stmt->bindValue($k, $v);
    }
  }
  $stmt->execute();
  $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
  send_json_response(200, ['success' => true, 'schedules' => $schedules]);
} catch (PDOException $e) {
  error_log('Schedule read error: ' . $e->getMessage());
  error_log('SQL: ' . $sql);
  error_log('Params: ' . json_encode($params));
  send_json_response(500, ['success' => false, 'error' => 'Failed to fetch schedules: ' . $e->getMessage()]);
}
