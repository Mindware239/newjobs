<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Core\RedisClient;

class VerificationService
{
    /**
     * Generate and send email verification code
     */
    public static function sendEmailVerification(int $userId, string $email): string
    {
        $code = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $redis = RedisClient::getInstance();
        $redis->set("email_verify:{$userId}", $code, 600);
        
        // Send email with verification code using NotificationService
        try {
            \App\Services\NotificationService::send(
                $userId,
                'email_verification',
                'Verify your email address',
                'Your verification code is: ' . $code,
                ['code' => $code],
                null,
                ['email'] // Force email channel
            );
        } catch (\Throwable $e) {
            error_log("Failed to send verification email to user {$userId}: " . $e->getMessage());
        }
        
        return $code;
    }

    /**
     * Verify email code
     */
    public static function verifyEmail(int $userId, string $code): bool
    {
        $redis = RedisClient::getInstance();
        $storedCode = $redis->get("email_verify:{$userId}");
        if ($storedCode === $code) {
            // Mark email as verified
            $user = User::find($userId);
            if ($user) {
                $user->fill(['is_email_verified' => 1]);
                $user->save();
            }
            
            // Delete code
            $redis->delete("email_verify:{$userId}");
            return true;
        }
        
        return false;
    }

    /**
     * Generate and send OTP for phone verification
     */
    public static function sendPhoneOTP(int $userId, string $phone): array
    {
        $normalizedPhone = self::normalizePhoneNumber($phone);
        if ($normalizedPhone === '') {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        $otp = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        $redis = RedisClient::getInstance();

        $message = "Your verification OTP is {$otp}. It is valid for 5 minutes.";
        $smsResult = self::deliverOtp($normalizedPhone, $otp, $message);
        if (empty($smsResult['success'])) {
            return [
                'success' => false,
                'error' => $smsResult['error'] ?? 'Failed to send OTP'
            ];
        }

        $redis->set("phone_otp:{$userId}", $otp, 300);

        return [
            'success' => true,
            'phone' => $normalizedPhone,
            'provider_message_id' => $smsResult['id'] ?? null,
            'mode' => $smsResult['mode'] ?? 'sms',
            'otp_preview' => $smsResult['otp_preview'] ?? null,
        ];
    }

    public static function sendAuthPhoneOTP(string $phone, string $purpose = 'auth', array $context = []): array
    {
        $normalizedPhone = self::normalizePhoneNumber($phone);
        if ($normalizedPhone === '') {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        $otp = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $redis = RedisClient::getInstance();
        $redis->set(self::authOtpKey($purpose, $normalizedPhone), [
            'otp' => $otp,
            'phone' => $normalizedPhone,
            'context' => $context,
        ], 300);

        $message = "Your login OTP is {$otp}. It is valid for 5 minutes.";
        $delivery = self::deliverOtp($normalizedPhone, $otp, $message);
        if (empty($delivery['success'])) {
            $redis->delete(self::authOtpKey($purpose, $normalizedPhone));
            return [
                'success' => false,
                'error' => $delivery['error'] ?? 'Failed to send OTP'
            ];
        }

        return [
            'success' => true,
            'phone' => $normalizedPhone,
            'purpose' => $purpose,
            'mode' => $delivery['mode'] ?? 'sms',
            'otp_preview' => $delivery['otp_preview'] ?? null,
        ];
    }

    public static function verifyAuthPhoneOTP(string $phone, string $otp, string $purpose = 'auth'): array
    {
        $normalizedPhone = self::normalizePhoneNumber($phone);
        if ($normalizedPhone === '') {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        $redis = RedisClient::getInstance();
        $stored = $redis->get(self::authOtpKey($purpose, $normalizedPhone));
        if (!is_array($stored) || (string)($stored['otp'] ?? '') !== trim($otp)) {
            return ['success' => false, 'error' => 'Invalid or expired OTP'];
        }

        $redis->delete(self::authOtpKey($purpose, $normalizedPhone));

        return [
            'success' => true,
            'phone' => $normalizedPhone,
            'context' => is_array($stored['context'] ?? null) ? $stored['context'] : [],
        ];
    }

    /**
     * Verify phone OTP
     */
    public static function verifyPhone(int $userId, string $otp): bool
    {
        $redis = RedisClient::getInstance();
        $storedOTP = $redis->get("phone_otp:{$userId}");
        if ($storedOTP === $otp) {
            // Mark phone as verified
            $user = User::find($userId);
            if ($user) {
                $user->fill(['is_phone_verified' => 1]);
                $user->save();
            }
            
            // Delete OTP
            $redis->delete("phone_otp:{$userId}");
            return true;
        }
        
        return false;
    }

    /**
     * Generate secure token for password reset
     */
    public static function generateResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        
        $redis = RedisClient::getInstance();
        $redis->set("password_reset:{$token}", json_encode([
            'user_id' => $userId,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ]), 3600);
        
        return $token;
    }

    /**
     * Verify reset token
     */
    public static function verifyResetToken(string $token): ?int
    {
        $redis = RedisClient::getInstance();
        $data = $redis->get("password_reset:{$token}");
        if ($data) {
            $tokenData = json_decode($data, true);
            return $tokenData['user_id'] ?? null;
        }
        
        return null;
    }

    private static function normalizePhoneNumber(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return '';
        }

        if ($hasPlus) {
            return '+' . $digits;
        }

        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }

        if (strlen($digits) > 10) {
            return '+' . $digits;
        }

        return '';
    }

    private static function authOtpKey(string $purpose, string $phone): string
    {
        return 'auth_phone_otp:' . strtolower(trim($purpose)) . ':' . $phone;
    }

    private static function deliverOtp(string $phone, string $otp, string $message): array
    {
        if (!SmsService::isEnabled() || self::isFreeOtpMode()) {
            $result = [
                'success' => true,
                'mode' => 'free',
            ];

            if (!self::isProduction()) {
                $result['otp_preview'] = $otp;
            }

            return $result;
        }

        $smsResult = SmsService::send($phone, $message);
        if (!empty($smsResult['success'])) {
            $smsResult['mode'] = 'sms';
        }

        return $smsResult;
    }

    private static function isFreeOtpMode(): bool
    {
        $value = strtolower(trim((string)($_ENV['OTP_FREE_MODE'] ?? getenv('OTP_FREE_MODE') ?: '')));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private static function isProduction(): bool
    {
        $env = strtolower(trim((string)($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'local')));
        return $env === 'production';
    }
}

