<?php
namespace Notify;

class SmsNotifier {
    private $apiUrl;
    private $apiKey;

    public function __construct() {
        // For example, set your SMS gateway API credentials here
        $this->apiUrl = 'https://api.yoursmsgateway.com/send';
        $this->apiKey = 'your-api-key';
    }

    /**
     * Send SMS message to a phone number.
     * @param string $phoneNumber The recipient's phone number
     * @param string $message The SMS message content
     * @return bool True on success, false on failure
     */
    public function sendSms(string $phoneNumber, string $message): bool {
        // Example of HTTP POST request to SMS gateway
        $postData = [
            'apikey' => $this->apiKey,
            'number' => $phoneNumber,
            'message' => $message,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("SMS send error: $err");
            return false;
        }

        // Assuming API returns JSON with success status
        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'] === true;
    }
}
