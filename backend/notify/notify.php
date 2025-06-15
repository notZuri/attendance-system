<?php
namespace Notify;

require_once __DIR__ . '/send_gmail.php';
require_once __DIR__ . '/send_sms.php';

use Notify\GmailNotifier;
use Notify\SmsNotifier;

class Notifier {
    private GmailNotifier $gmailNotifier;
    private SmsNotifier $smsNotifier;

    public function __construct() {
        $this->gmailNotifier = new GmailNotifier();
        $this->smsNotifier = new SmsNotifier();
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
            $emailSent = $this->gmailNotifier->sendEmail($user['email'], $subject, $message);
            $status['email'] = $emailSent;
        }

        // Send SMS
        if (!empty($user['phone'])) {
            $smsSent = $this->smsNotifier->sendSms($user['phone'], $message);
            $status['sms'] = $smsSent;
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
