<?php

declare(strict_types=1);

namespace App\Services;

class SmsService
{
    public static function isEnabled(): bool
    {
        return !empty($_ENV['SMS_PROVIDER']) && !empty($_ENV['SMS_API_KEY']);
    }

    /**
     * Send SMS via Twilio or generic provider
     */
    public static function send(string $to, string $message): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'SMS provider not configured'];
        }

        $provider = strtolower($_ENV['SMS_PROVIDER'] ?? 'twilio');
        
        if ($provider === 'twilio') {
            return self::sendTwilio($to, $message);
        }
        
        // Add other providers here (e.g., Msg91, TextLocal)
        
        return ['success' => false, 'error' => 'Unsupported SMS provider'];
    }

    private static function sendTwilio(string $to, string $message): array
    {
        $sid = $_ENV['SMS_ACCOUNT_SID'] ?? '';
        $token = $_ENV['SMS_API_KEY'] ?? ''; // Auth Token
        $from = $_ENV['SMS_FROM_NUMBER'] ?? '';

        if (empty($sid) || empty($token) || empty($from)) {
            return ['success' => false, 'error' => 'Twilio credentials missing'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $data = [
            'From' => $from,
            'To' => $to,
            'Body' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'id' => $result['sid'] ?? null];
        }

        return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
    }
}
