<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\Queue;

class MailService
{
    /**
     * Dispatch Email to Queue for Async Sending
     */
    public static function sendEmailAsync(
        string $to,
        string $subject,
        string $htmlBody,
        ?string $fromEmail = null,
        ?string $fromName = null,
        array $attachments = []
    ): bool {
        try {
            $queue = \App\Core\Queue::getInstance('queue:mail');
            if (!$queue->isAvailable()) {
                error_log('Mail queue not available. Sending synchronously as fallback.');
                return self::sendEmail($to, $subject, $htmlBody, $fromEmail, $fromName, $attachments);
            }

            $jobData = [
                'to' => $to,
                'subject' => $subject,
                'htmlBody' => $htmlBody,
                'fromEmail' => $fromEmail,
                'fromName' => $fromName,
                'attachments' => $attachments
            ];

            $queue->push('send_email', $jobData);
            return true;
        } catch (\Exception $e) {
            error_log('Failed to queue email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Email using SMTP (PHPMailer) - Synchronous
     */
    public static function sendEmail(
        string $to,
        string $subject,
        string $htmlBody,
        ?string $fromEmail = null,
        ?string $fromName = null,
        array $attachments = []
    ): bool {

        $mail = new PHPMailer(true);

        try {

            // SMTP SETTINGS
            $mail->isSMTP();
            $mail->Timeout = 10; // Connection timeout in seconds
            $mail->Host       = getenv('MAIL_HOST') ?: 'localhost';
            $mail->Port       = (int)(getenv('MAIL_PORT') ?: 587);
            // Optional EHLO/Hostname override
            $mail->Hostname   = getenv('MAIL_EHLO_DOMAIN') ?: 'localhost';
            if (!empty($mail->Hostname)) {
                $mail->Helo = $mail->Hostname;
            }
            $mail->CharSet    = 'UTF-8';
            
            // Configure authentication (only if credentials are provided)
            $mailUsername = getenv('MAIL_USERNAME') ?: '';
            $mailPassword = getenv('MAIL_PASSWORD') ?: '';
            if ($mailUsername !== '' && $mailPassword !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $mailUsername;
                $mail->Password = $mailPassword;
            } else {
                $mail->SMTPAuth = false;
            }
            
            // Configure encryption based on env or port and openssl availability
            $mailPort = (int)(getenv('MAIL_PORT') ?: 587);
            $hasOpenssl = extension_loaded('openssl');
            $enc = strtolower((string)(getenv('MAIL_ENCRYPTION') ?: ''));
            if ($enc === 'ssl' || $enc === 'smtps') {
                if ($hasOpenssl) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    error_log("Mail Error: SSL encryption requested but openssl extension is missing");
                    $mail->SMTPAutoTLS = false;
                    $mail->SMTPSecure = false;
                }
            } elseif ($enc === 'tls' || $enc === 'starttls') {
                if ($hasOpenssl) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    error_log("Mail Warning: TLS requested but openssl missing, attempting without encryption");
                    $mail->SMTPAutoTLS = false;
                    $mail->SMTPSecure = false;
                }
            } else {
                // Fallback to port-based defaults
                if ($mailPort == 465 && $hasOpenssl) {
                     $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($mailPort == 587 && $hasOpenssl) {
                     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPAutoTLS = false;
                    $mail->SMTPSecure = false;
                }
            }

            // FROM DETAILS
            $fromEmail = $fromEmail ?: (getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@example.com');
            $fromName  = $fromName  ?: (getenv('MAIL_FROM_NAME') ?: 'Job Portal');

            $mail->setFrom($fromEmail, $fromName);
            // Several recipients allowed: "a@x.com,b@y.com" (e.g. AdminMail::to()).
            foreach (array_filter(array_map('trim', explode(',', $to))) as $toAddr) {
                $mail->addAddress($toAddr);
            }
            error_log("MailService - Attempting to send email to: {$to}, Subject: {$subject}");

            // EMAIL CONTENT
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            // Optional debug output to PHP error log
            $mailDebug = getenv('MAIL_DEBUG') === 'true';
            if (!$mailDebug && strtolower((string)(getenv('APP_ENV') ?: 'local')) === 'local') {
                $mailDebug = true;
            }
            if ($mailDebug) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function ($str, $level) {
                    error_log("Mail Debug [{$level}]: " . $str);
                };
            }

            // Attachments (local path or URL content)
            if (!empty($attachments) && is_array($attachments)) {
                foreach ($attachments as $att) {
                    $name = (string)($att['name'] ?? '');
                    if (!empty($att['path'])) {
                        $path = (string)$att['path'];
                        if (is_file($path)) {
                            $mail->addAttachment($path, $name ?: basename($path));
                        }
                    } elseif (!empty($att['url'])) {
                        // For local/dev reliability, skip remote URL attachments by default.
                        // HR emails already include document links in the body.
                        $allowRemote = strtolower((string)($_ENV['MAIL_ATTACH_REMOTE'] ?? 'false')) === 'true';
                        if ($allowRemote) {
                            $url = (string)$att['url'];
                            $mime = (string)($att['mime'] ?? 'application/octet-stream');
                            $timeout = (int)($_ENV['MAIL_REMOTE_ATTACH_TIMEOUT'] ?? 5);
                            $ctx = stream_context_create([
                                'http' => [
                                    'timeout' => $timeout,
                                    'user_agent' => 'JobsenceMailer/1.0',
                                ],
                                'ssl' => [
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                ]
                            ]);
                            $content = @file_get_contents($url, false, $ctx);
                            if ($content !== false) {
                                $mail->addStringAttachment($content, $name ?: basename(parse_url($url, PHP_URL_PATH) ?: 'document'), $encoding = 'base64', $mime);
                            } else {
                                error_log("Mail Warning: Skipping unreachable remote attachment: {$url}");
                            }
                        } else {
                            error_log("Mail Info: Remote attachment skipped (MAIL_ATTACH_REMOTE=false)");
                        }
                    }
                }
            }

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
            <h2>Jobsence Admin Login</h2>
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
