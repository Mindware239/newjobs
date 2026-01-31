<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    /**
     * Send Email using SMTP (PHPMailer)
     */
    public static function sendEmail(
        string $to,
        string $subject,
        string $htmlBody,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {

        $mail = new PHPMailer(true);

        try {

            // SMTP SETTINGS
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? 'localhost';
            $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
            $mail->CharSet    = 'UTF-8';
            
            // Configure authentication (only if credentials are provided)
            $mailUsername = $_ENV['MAIL_USERNAME'] ?? '';
            $mailPassword = $_ENV['MAIL_PASSWORD'] ?? '';
            if (!empty($mailUsername) && !empty($mailPassword)) {
                $mail->SMTPAuth = true;
                $mail->Username = $mailUsername;
                $mail->Password = $mailPassword;
            } else {
                $mail->SMTPAuth = false;
            }
            
            // Configure encryption based on port and openssl availability
            $mailPort = (int)($_ENV['MAIL_PORT'] ?? 587);
            $hasOpenssl = extension_loaded('openssl');
            
            if ($mailPort == 465) {
                // Port 465 requires SSL/TLS
                if ($hasOpenssl) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    error_log("Mail Error: Port 465 requires SSL/TLS but openssl extension is missing");
                    throw new Exception("Port 465 requires openssl extension");
                }
            } elseif ($mailPort == 587) {
                // Port 587 typically uses STARTTLS
                if ($hasOpenssl) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    // Try without encryption for local development
                    $mail->SMTPAutoTLS = false;
                    $mail->SMTPSecure = false;
                    error_log("Mail Warning: openssl extension not available, attempting to send without encryption on port 587");
                }
            } elseif ($mailPort == 25) {
                // Port 25 is typically unencrypted
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = false;
            } else {
                // For other ports, try STARTTLS if openssl is available
                if ($hasOpenssl) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPAutoTLS = false;
                    $mail->SMTPSecure = false;
                }
            }

            // FROM DETAILS
            $fromEmail = $fromEmail ?: ($_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@example.com');
            $fromName  = $fromName  ?: ($_ENV['MAIL_FROM_NAME'] ?? 'Job Portal');

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            // EMAIL CONTENT
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            // SEND EMAIL
            $mail->send();
            return true;

        } catch (Exception $e) {

            // LOG ERROR with more details
            $errorMsg = $mail->ErrorInfo ?? $e->getMessage();
            error_log("Mail Error: " . $errorMsg);
            error_log("Mail Error Details - To: {$to}, Subject: {$subject}");
            
            // If openssl is missing, provide helpful message
            if (strpos($errorMsg, 'openssl') !== false || !extension_loaded('openssl')) {
                error_log("Mail Error: openssl extension is missing. Install it or configure mail without encryption.");
            }

            return false;
        }
    }

    /**
     * Send Admin OTP Email
     */
    public static function sendAdminOtp(string $to, string $otp): bool
    {
        $subject = "Admin Login OTP";

        $body = "
            <h2>Mindware InfoTech Admin Login</h2>
            <p>Your OTP code is:</p>
            <h1 style='color:#2563eb;'>$otp</h1>
            <p>This OTP is valid for 10 minutes.</p>
            <p>If you did not request this, ignore this email.</p>
            <hr>
            <p>Job Portal Team</p>
        ";

        return self::sendEmail($to, $subject, $body);
    }

    public static function sendPasswordReset(string $to, string $resetLink): bool
    {
        $subject = "Password Reset Instructions";
        $body = "
            <h2>Password Reset Requested</h2>
            <p>We received a request to reset the password for your account.</p>
            <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
            <p><a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "' style='color:#2563eb;'>Reset your password</a></p>
            <p>If you did not request this, you can ignore this email.</p>
            <hr>
            <p>Job Portal Team</p>
        ";
        return self::sendEmail($to, $subject, $body);
    }
}
