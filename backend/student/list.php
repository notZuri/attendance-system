<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['professor', 'admin'])) {
    send_json_response(403, ['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Get query parameters
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    
    $search = trim($_GET['search'] ?? '');
    $course = trim($_GET['course'] ?? '');
    $yearLevel = trim($_GET['year_level'] ?? '');
    $section = trim($_GET['section'] ?? '');
    
    // Build WHERE clause for filtering
    $whereConditions = ["u.role = 'student'"];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(u.name LIKE :search OR u.email LIKE :search OR u.student_number LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($course)) {
        $whereConditions[] = "s.course = :course";
        $params[':course'] = $course;
    }
    
    if (!empty($yearLevel)) {
        $whereConditions[] = "s.year_level = :year_level";
        $params[':year_level'] = $yearLevel;
    }
    
    if (!empty($section)) {
        $whereConditions[] = "s.section = :section";
        $params[':section'] = $section;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Get total count with JOIN
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM users u 
        LEFT JOIN students s ON u.student_number = s.student_number 
        WHERE $whereClause
    ";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalCount = $stmt->fetch()['total'];
    
    // Get students with pagination and JOIN
    $query = "
        SELECT u.id, u.name, u.email, u.phone, u.student_number, u.role, u.created_at,
               s.course, s.year_level, s.section, s.fullname
        FROM users u
        LEFT JOIN students s ON u.student_number = s.student_number
        WHERE $whereClause
        ORDER BY u.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    // Bind other parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure each student has a 'fullname' property
    foreach ($students as &$student) {
        if (empty($student['fullname'])) {
            $student['fullname'] = $student['name'];
        }
    }
    unset($student);
    
    // Check for schedule_id parameter to include attendance status
    $scheduleId = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;
    if ($scheduleId > 0 && count($students) > 0) {
        // Get all user_ids in this page
        $userIds = array_column($students, 'id');
        // Fetch attendance for these students for the given schedule
        $inQuery = implode(',', array_fill(0, count($userIds), '?'));
        $attendanceQuery = "SELECT user_id, status, time_in FROM attendance WHERE schedule_id = ? AND user_id IN ($inQuery)";
        $stmt = $pdo->prepare($attendanceQuery);
        $stmt->execute(array_merge([$scheduleId], $userIds));
        $attendanceRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $attendanceMap[$row['user_id']] = [
                'attendance_status' => $row['status'],
                'attendance_time_in' => $row['time_in']
            ];
        }
        // Attach attendance info to each student
        foreach ($students as &$student) {
            if (isset($attendanceMap[$student['id']])) {
                $student['attendance_status'] = $attendanceMap[$student['id']]['attendance_status'];
                $student['attendance_time_in'] = $attendanceMap[$student['id']]['attendance_time_in'];
            } else {
                $student['attendance_status'] = null;
                $student['attendance_time_in'] = null;
            }
        }
        unset($student);
    }
    
    // Get filter options (if no filters are applied)
    $filterOptions = [];
    if (empty($search) && empty($course) && empty($yearLevel) && empty($section)) {
        $courses = $pdo->query("SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != '' ORDER BY course")->fetchAll(PDO::FETCH_COLUMN);
        $yearLevels = $pdo->query("SELECT DISTINCT year_level FROM students WHERE year_level IS NOT NULL ORDER BY year_level")->fetchAll(PDO::FETCH_COLUMN);
        $sections = $pdo->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL AND section != '' ORDER BY section")->fetchAll(PDO::FETCH_COLUMN);
        
        $filterOptions = [
            'courses' => $courses,
            'year_levels' => $yearLevels,
            'sections' => $sections
        ];
    }
    
    // Calculate pagination info
    $totalPages = ceil($totalCount / $limit);
    $hasNextPage = $page < $totalPages;
    $hasPrevPage = $page > 1;
    
    send_json_response(200, [
        'success' => true,
        'students' => $students,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'limit' => $limit,
            'has_next' => $hasNextPage,
            'has_prev' => $hasPrevPage
        ],
        'filters' => $filterOptions
    ]);
    
} catch (Exception $e) {
    error_log("Student list error: " . $e->getMessage());
    send_json_response(500, ['error' => 'Failed to fetch students']);
} 