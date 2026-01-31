<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;

class WhatsAppService
{
    /**
     * Check if WhatsApp integration is enabled
     */
    public static function isEnabled(): bool
    {
        return (bool) ($_ENV['WHATSAPP_ENABLED'] ?? SystemSetting::get('whatsapp_enabled', '0'));
    }

    /**
     * Send a text message via WhatsApp
     */
    public static function sendText(string $to, string $message): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp disabled'];
        }

        // Clean phone number (remove +, spaces, dashes)
        $to = preg_replace('/[^0-9]/', '', $to);

        // Twilio Configuration
        $sid    = $_ENV['TWILIO_SID'] ?? SystemSetting::get('twilio_sid');
        $token  = $_ENV['TWILIO_TOKEN'] ?? SystemSetting::get('twilio_token');
        $from   = $_ENV['TWILIO_WHATSAPP_FROM'] ?? SystemSetting::get('twilio_whatsapp_from'); // e.g., "whatsapp:+14155238886"

        if (empty($sid) || empty($token) || empty($from)) {
            error_log('WhatsApp Service: Missing credentials');
            return ['success' => false, 'error' => 'Missing credentials'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        
        // Ensure "whatsapp:" prefix
        if (!str_starts_with($to, 'whatsapp:')) {
            // If number doesn't have country code, might need to add it. 
            // Assuming input is international format for now or standardizing.
            // For Twilio, destination also needs "whatsapp:" prefix
            $to = 'whatsapp:+' . ltrim($to, '+'); 
        }

        $data = [
            'From' => $from,
            'To'   => $to,
            'Body' => $message,
        ];

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                return ['success' => true, 'response' => json_decode((string)$response, true)];
            } else {
                error_log("WhatsApp Send Error ({$httpCode}): {$response}");
                return ['success' => false, 'error' => "HTTP {$httpCode}: {$response}"];
            }

        } catch (\Throwable $e) {
            error_log("WhatsApp Service Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a template message (if using Twilio Templates)
     * For now, we fallback to text since sandbox only supports templates or free text in 24h window.
     */
    public static function sendTemplate(string $to, string $templateName, array $variables = []): array
    {
        // Placeholder for template logic
        // Construct message from template
        // For simple integration, we'll just send text.
        return self::sendText($to, "Template: $templateName " . json_encode($variables));
    }
}
