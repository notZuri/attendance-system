<?php
namespace Notify;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; // PHPMailer autoload

class GmailNotifier {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'your-email@gmail.com'; // Replace with your Gmail
        $this->mail->Password = 'your-app-password'; // Use app password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;

        // Sender info
        $this->mail->setFrom('your-email@gmail.com', 'Attendance System');
    }

    public function sendEmail(string $toEmail, string $subject, string $body): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            return $this->mail->send();
        } catch (Exception $e) {
            error_log('Gmail send error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }
}
