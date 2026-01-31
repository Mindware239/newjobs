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
        
        // Store in Redis with 10 minute expiry
        $redis = RedisClient::getInstance();
        if ($redis->isAvailable()) {
            $redis->set("email_verify:{$userId}", $code, 600);
        }
        
        // TODO: Send email with verification code
        // $mailService = new MailService();
        // $mailService->sendVerificationCode($email, $code);
        
        error_log("Email verification code for user {$userId}: {$code}");
        return $code;
    }

    /**
     * Verify email code
     */
    public static function verifyEmail(int $userId, string $code): bool
    {
        $redis = RedisClient::getInstance();
        if (!$redis->isAvailable()) {
            return false;
        }
        
        $storedCode = $redis->get("email_verify:{$userId}");
        if ($storedCode === $code) {
            // Mark email as verified
            $user = User::find($userId);
            if ($user) {
                $user->fill(['is_email_verified' => 1]);
                $user->save();
            }
            
            // Delete code
            $redis->del("email_verify:{$userId}");
            return true;
        }
        
        return false;
    }

    /**
     * Generate and send OTP for phone verification
     */
    public static function sendPhoneOTP(int $userId, string $phone): string
    {
        $otp = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in Redis with 5 minute expiry
        $redis = RedisClient::getInstance();
        if ($redis->isAvailable()) {
            $redis->set("phone_otp:{$userId}", $otp, 300);
        }
        
        // TODO: Send SMS with OTP
        // $smsService = new SMSService();
        // $smsService->sendOTP($phone, $otp);
        
        error_log("Phone OTP for user {$userId}: {$otp}");
        return $otp;
    }

    /**
     * Verify phone OTP
     */
    public static function verifyPhone(int $userId, string $otp): bool
    {
        $redis = RedisClient::getInstance();
        if (!$redis->isAvailable()) {
            return false;
        }
        
        $storedOTP = $redis->get("phone_otp:{$userId}");
        if ($storedOTP === $otp) {
            // Mark phone as verified
            $user = User::find($userId);
            if ($user) {
                $user->fill(['is_phone_verified' => 1]);
                $user->save();
            }
            
            // Delete OTP
            $redis->del("phone_otp:{$userId}");
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
        if ($redis->isAvailable()) {
            $redis->set("password_reset:{$token}", json_encode([
                'user_id' => $userId,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
            ]), 3600);
        }
        
        return $token;
    }

    /**
     * Verify reset token
     */
    public static function verifyResetToken(string $token): ?int
    {
        $redis = RedisClient::getInstance();
        if (!$redis->isAvailable()) {
            return null;
        }
        
        $data = $redis->get("password_reset:{$token}");
        if ($data) {
            $tokenData = json_decode($data, true);
            return $tokenData['user_id'] ?? null;
        }
        
        return null;
    }
}

