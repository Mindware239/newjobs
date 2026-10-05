<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Core\RedisClient;

class VerificationService
{
    private const EMAIL_AUTH_OTP_TTL = 600; // 10 minutes

    /** New company / mentor enrolment: max 3 wrong OTPs, then blocked for 30 minutes; Jobsence-branded mail. */
    private const STRICT_OTP_PURPOSES = ['register_employer', 'portal_provider', 'mentoring_agreement'];
    private const STRICT_OTP_MAX_ATTEMPTS = 3;
    private const STRICT_OTP_BLOCK_SECONDS = 1800;

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
     * Send email OTP for auth flows (login/register).
     */
    public static function sendEmailAuthOTP(string $email, string $purpose = 'auth', array $context = []): array
    {
        $normalizedEmail = strtolower(trim($email));
        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Valid email is required'];
        }

        $strict = self::isStrictOtpPurpose($purpose);
        if ($strict && self::readAuthEmailOtpPayload(self::authEmailOtpBlockKey($purpose, $normalizedEmail))) {
            return ['success' => false, 'error' => self::blockedMessage(), 'blocked' => true];
        }

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = self::authEmailOtpKey($purpose, $normalizedEmail);
        $payload = [
            'attempts' => 0,
            'otp' => $otp,
            'email' => $normalizedEmail,
            'context' => $context,
            'expires_at' => time() + self::EMAIL_AUTH_OTP_TTL,
        ];
        $stored = self::storeAuthEmailOtpPayload($key, $payload, self::EMAIL_AUTH_OTP_TTL);
        if (!$stored) {
            error_log('Email OTP store failed for key: ' . $key);
            return ['success' => false, 'error' => 'Unable to generate OTP right now. Please try again.'];
        }

        if ($strict) {
            $subject = 'Jobsence Email OTP: ' . $otp;
            $html = self::jobsenceOtpEmail($otp);
            $sent = \App\Services\MailService::sendEmail($normalizedEmail, $subject, $html, (string)($_ENV['SKILL_MAIL_FROM'] ?? 'gm@jobsence.com'), 'Team Jobsence');
        } else {
            $subject = 'Your verification OTP';
            $html = '<p>Your OTP is <strong>' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
                . '<p>This OTP is valid for 10 minutes.</p>';
            $sent = \App\Services\MailService::sendEmail($normalizedEmail, $subject, $html);
        }
        if (!$sent) {
            self::deleteAuthEmailOtpPayload($key);
            return ['success' => false, 'error' => 'Failed to send OTP email'];
        }

        return [
            'success' => true,
            'email' => $normalizedEmail,
            'purpose' => strtolower(trim($purpose)),
            'ttl' => self::EMAIL_AUTH_OTP_TTL,
        ];
    }

    /**
     * Verify email OTP for auth flows (login/register).
     */
    public static function verifyEmailAuthOTP(string $email, string $otp, string $purpose = 'auth'): array
    {
        $normalizedEmail = strtolower(trim($email));
        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Valid email is required'];
        }

        $code = trim($otp);
        if (!preg_match('/^\d{6}$/', $code)) {
            return ['success' => false, 'error' => 'Invalid OTP format'];
        }

        $key = self::authEmailOtpKey($purpose, $normalizedEmail);
        $strict = self::isStrictOtpPurpose($purpose);
        $blockKey = self::authEmailOtpBlockKey($purpose, $normalizedEmail);
        if ($strict && self::readAuthEmailOtpPayload($blockKey)) {
            return ['success' => false, 'error' => self::blockedMessage(), 'blocked' => true];
        }

        $stored = self::readAuthEmailOtpPayload($key);

        if (!is_array($stored) || !hash_equals((string)($stored['otp'] ?? ''), $code)) {
            error_log('Email OTP verify failed for key: ' . $key);
            if ($strict && is_array($stored)) {
                $attempts = (int)($stored['attempts'] ?? 0) + 1;
                if ($attempts >= self::STRICT_OTP_MAX_ATTEMPTS) {
                    self::deleteAuthEmailOtpPayload($key);
                    self::storeAuthEmailOtpPayload($blockKey, ['expires_at' => time() + self::STRICT_OTP_BLOCK_SECONDS], self::STRICT_OTP_BLOCK_SECONDS);
                    return ['success' => false, 'error' => self::blockedMessage(), 'blocked' => true];
                }
                $stored['attempts'] = $attempts;
                $ttl = max(1, (int)($stored['expires_at'] ?? time()) - time());
                self::storeAuthEmailOtpPayload($key, $stored, $ttl);
                $left = self::STRICT_OTP_MAX_ATTEMPTS - $attempts;
                return ['success' => false, 'error' => "गलत OTP – {$left} प्रयास बाकी / Wrong OTP – {$left} attempt(s) left", 'attempts_left' => $left];
            }
            return ['success' => false, 'error' => $strict ? 'OTP गलत है या समय समाप्त हो गया / Invalid or expired OTP' : 'Invalid or expired OTP'];
        }

        self::deleteAuthEmailOtpPayload($key);

        return [
            'success' => true,
            'email' => $normalizedEmail,
            'context' => is_array($stored['context'] ?? null) ? $stored['context'] : [],
        ];
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
        ], 600);

        $message = "Your login OTP is {$otp}. It is valid for 10 minutes.";
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

    private static function isStrictOtpPurpose(string $purpose): bool
    {
        return in_array(strtolower(trim($purpose)), self::STRICT_OTP_PURPOSES, true);
    }

    private static function authEmailOtpBlockKey(string $purpose, string $email): string
    {
        return 'auth_email_otp_block:' . strtolower(trim($purpose)) . ':' . strtolower(trim($email));
    }

    private static function blockedMessage(): string
    {
        return '3 बार गलत OTP – 30 मिनट बाद पुनः प्रयास करें / 3 wrong OTP attempts – please try again after 30 minutes';
    }

    private static function jobsenceOtpEmail(string $otp): string
    {
        $otp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
        return "<div style='font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:0 auto;color:#1f2937'>
            <div style='background:#f05537;color:#fff;padding:16px 20px;border-radius:8px 8px 0 0'>
                <b style='font-size:18px'>Jobsence – भारत का Job Portal</b><br><span style='font-size:13px'>भारत को कुशल बनाने की Jobsence पहल</span>
            </div>
            <div style='border:1px solid #e5e7eb;border-top:0;padding:20px;border-radius:0 0 8px 8px'>
                <p>आपका ईमेल सत्यापन OTP / Your email verification OTP:</p>
                <p style='font-size:30px;font-weight:bold;letter-spacing:6px;margin:12px 0'>{$otp}</p>
                <p>यह OTP 10 मिनट के लिए मान्य है। 3 बार गलत OTP डालने पर 30 मिनट के लिए रोक लग जाएगी।<br>
                This OTP is valid for 10 minutes. After 3 wrong attempts, verification is blocked for 30 minutes.</p>
                <p style='color:#6b7280;font-size:13px'>यह OTP किसी के साथ साझा न करें। अगर आपने यह अनुरोध नहीं किया, तो इस ईमेल को अनदेखा करें।<br>
                Do not share this OTP. If you did not request it, please ignore this email.</p>
                <p style='font-size:13px'>Team Jobsence · gm@jobsence.com</p>
            </div>
        </div>";
    }

    private static function authEmailOtpKey(string $purpose, string $email): string
    {
        return 'auth_email_otp:' . strtolower(trim($purpose)) . ':' . strtolower(trim($email));
    }

    private static function authEmailOtpFilePath(string $key): string
    {
        // Use a hash-based filename to be fully cross-platform (Windows forbids ":" in filenames).
        $safeName = 'otp_' . sha1($key);
        return dirname(__DIR__, 2) . '/storage/cache/otp/' . $safeName . '.json';
    }

    private static function storeAuthEmailOtpPayload(string $key, array $payload, int $ttl): bool
    {
        $redis = RedisClient::getInstance();
        if ($redis->isAvailable()) {
            $ok = $redis->set($key, $payload, $ttl);
            if (!$ok) {
                error_log('Redis set failed for OTP key: ' . $key);
            }
            return (bool)$ok;
        }

        $path = self::authEmailOtpFilePath($key);
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            error_log('OTP fallback dir create failed: ' . $dir);
            return false;
        }
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if (!is_string($json)) {
            error_log('OTP fallback JSON encode failed for key: ' . $key);
            return false;
        }

        $written = file_put_contents($path, $json, LOCK_EX);
        if ($written === false) {
            error_log('OTP fallback write failed: ' . $path);
            return false;
        }

        return true;
    }

    private static function readAuthEmailOtpPayload(string $key): ?array
    {
        $redis = RedisClient::getInstance();
        if ($redis->isAvailable()) {
            $value = $redis->get($key);
            return is_array($value) ? $value : null;
        }

        $path = self::authEmailOtpFilePath($key);
        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($decoded)) {
            @unlink($path);
            error_log('OTP fallback payload decode failed: ' . $path);
            return null;
        }

        $expiresAt = (int)($decoded['expires_at'] ?? 0);
        if ($expiresAt > 0 && $expiresAt < time()) {
            @unlink($path);
            return null;
        }

        return $decoded;
    }

    private static function deleteAuthEmailOtpPayload(string $key): void
    {
        $redis = RedisClient::getInstance();
        if ($redis->isAvailable()) {
            $redis->delete($key);
            return;
        }

        $path = self::authEmailOtpFilePath($key);
        if (is_file($path)) {
            @unlink($path);
        }
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

