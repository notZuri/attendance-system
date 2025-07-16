<?php
namespace Notify;

class Notifier {
    public function __construct() {
    }

    /**
     * Send attendance notification to user via email and/or SMS.
     * @param array $user Array with keys: email, phone, name
     * @param string $message Notification message content
     * @return array Status of sent notifications
     */
    public function sendAttendanceNotification(array $user, string $message): array {
        $status = [
            'email' => false,
            'sms' => false,
        ];

        // Send Email
        if (!empty($user['email'])) {
            $subject = "Attendance Notification";
            $status['email'] = true;
        }

        // Send SMS
        if (!empty($user['phone'])) {
            $status['sms'] = true;
        }

        return $status;
    }
}

// If notify.php is called directly (e.g., via AJAX), handle input here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notifier = new Notifier();

    $user = $input['user'] ?? [];
    $message = $input['message'] ?? '';

    $result = $notifier->sendAttendanceNotification($user, $message);
    header('Content-Type: application/json');
    echo json_encode($result);
}
