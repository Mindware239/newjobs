<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Core\Database;
use App\Models\Candidate;
use App\Models\SystemSetting;
use App\Workers\EmailWorker;
use App\Core\RedisClient;
use Google\Client;

class NotificationService
{
    public static function notify(int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        Notification::create($userId, $type, $title, $message, $link);
    }

    /**
     * Unified send method supporting multi-channel delivery based on user preferences.
     * 
     * @param int $userId Target User ID
     * @param string $type Notification type (used for template selection and preference check)
     * @param string $title Notification title
     * @param string $message Notification message/body
     * @param array $data Additional data for templates
     * @param string|null $link Action link
     * @param array|null $allowedChannels Specific channels to send to (e.g. ['email','push']). If null, try all enabled.
     */
    public static function send(int $userId, string $type, string $title, string $message, array $data = [], ?string $link = null, ?array $allowedChannels = null): void
    {
        try {
            $user = User::find($userId);
            if (!$user) return;

            $prefs = $user->getNotificationPreferences();

            // Helper to decide if a channel should be used
            $shouldSend = function (string $channel) use ($prefs, $allowedChannels): bool {
                if ($allowedChannels !== null && !in_array($channel, $allowedChannels, true)) {
                    return false;
                }
                return !isset($prefs[$channel]) || (bool)$prefs[$channel];
            };

            // 1. In-App Notification (Default: ON)
            if ($shouldSend('in_app')) {
                self::notify($userId, $type, $title, $message, $link);
            }

            // 2. Email Notification (Default: ON)
            if ($shouldSend('email') && !empty($user->attributes['email'])) {
                // Use specific template if provided in data, otherwise use type as key
                $templateKey = $data['email_template'] ?? $type;
                
                // Merge standard data
                $emailData = array_merge($data, [
                    'title' => $title,
                    'message' => $message,
                    'link' => $link ?? ($data['link'] ?? null),
                    'candidate_name' => $user->attributes['first_name'] ?? $user->attributes['name'] ?? 'User' // Fallback name
                ]);
                
                self::queueEmail($user->attributes['email'], $templateKey, $emailData, $title);
            }

            // 3. WhatsApp Notification (Default: OFF unless enabled)
            if ($shouldSend('whatsapp') && !empty($user->attributes['phone'])) {
                 $templateKey = $data['whatsapp_template'] ?? $type;
                 self::queueWhatsApp($user->attributes['phone'], $templateKey, $data, $userId);
            }
            
            // 4. Push Notification (Default: OFF unless enabled)
            if ($shouldSend('push')) {
                self::sendPush($userId, $title, $message, $link);
            }

        } catch (\Throwable $e) {
            error_log("NotificationService::send failed: " . $e->getMessage());
        }
    }

    public static function sendEmail(string $to, string $subject, string $templateKey, array $templateData = []): bool
    {
        // 1. Log as pending to get ID
        $logId = self::logEmail($templateKey, $subject, '', $templateData, false, 'sending', $templateData['employer_id'] ?? null, $templateData['candidate_user_id'] ?? null);
        
        // 2. Render with tracking pixel
        $rendered = self::renderTemplate($templateKey, $templateData, $logId);
        $subject = $subject ?: ($rendered['subject'] ?? '');
        $body = $rendered['body'] ?? '';

        // 3. Update log with content
        self::updateLogContent($logId, $subject, $body);

        // 4. Send
        $success = MailService::sendEmail($to, $subject, $body);
        
        // 5. Update status
        self::updateLogStatus($logId, $success ? 'sent' : 'failed', $success ? null : 'send_failed');

        return $success;
    }

    private static function updateLogContent(int $id, string $subject, string $content): void
    {
        if (!$id) return;
        try {
            $db = Database::getInstance();
            $db->query("UPDATE notification_logs SET subject = :subject, content = :content WHERE id = :id", [
                'id' => $id,
                'subject' => $subject,
                'content' => $content
            ]);
        } catch (\Throwable $t) {}
    }

    private static function updateLogStatus(int $id, string $status, ?string $error): void
    {
        if (!$id) return;
        try {
            $db = Database::getInstance();
            $db->query("UPDATE notification_logs SET status = :status, error_message = :error WHERE id = :id", [
                'id' => $id,
                'status' => $status,
                'error' => $error
            ]);
        } catch (\Throwable $t) {}
    }

    public static function queueEmail(string $to, string $templateKey, array $data = [], ?string $subjectOverride = null): void
    {
        $queueDriver = $_ENV['QUEUE_DRIVER'] ?? 'sync';

        // Check if Redis is available for queuing AND driver is set to redis
        if ($queueDriver === 'redis' && RedisClient::getInstance()->isAvailable()) {
            EmailWorker::enqueue([
                'to' => $to,
                'subject' => $subjectOverride ?? '',
                'template' => $templateKey,
                'data' => $data,
            ]);
        } else {
            // Fallback: Send immediately (synchronous)
            self::sendEmail($to, $subjectOverride ?? '', $templateKey, $data);
        }
    }

    public static function queueWhatsApp(string $phone, string $templateKey, array $data = [], ?int $userId = null): void
    {
        $queueDriver = $_ENV['QUEUE_DRIVER'] ?? 'sync';

        // Check if Redis is available for queuing AND driver is set to redis
        if ($queueDriver === 'redis' && RedisClient::getInstance()->isAvailable()) {
            // Placeholder for WhatsAppWorker
            // WhatsAppWorker::enqueue(...)
            // For now, fall back to sync as we haven't created WhatsAppWorker yet
            self::sendWhatsApp($phone, "Notification: {$templateKey}", $userId);
        } else {
            // Fallback: Send immediately (synchronous)
            // Use template logic to generate message body
            $message = "New notification: {$templateKey}";
            
            // Simple mapping for now - in production use a TemplateService
            if ($templateKey === 'interview_reminder_24h' || $templateKey === 'interview_reminder_2h') {
                 $jobTitle = $data['job_title'] ?? 'a job';
                 $message = "Reminder: You have an interview for {$jobTitle} coming up soon! Check your dashboard for details.";
            } elseif ($templateKey === 'upgrade_reminder') {
                 $message = "Your interview is about to end! Upgrade to Premium to continue interviewing without interruption: " . ($data['upgrade_link'] ?? '');
            } elseif ($templateKey === 'job_match') {
                 $jobTitle = $data['job_title'] ?? 'New Job';
                 $link = $data['link'] ?? '';
                 $message = "New Match: {$jobTitle} matches your profile! Apply now: " . ($_ENV['APP_URL'] ?? 'http://localhost:8000') . $link;
            } elseif ($templateKey === 'marketing_broadcast' && isset($data['message'])) {
                 $message = $data['message']; // Use direct message for broadcasts
            }

            self::sendWhatsApp($phone, $message, $userId);
        }
    }

    public static function sendWhatsApp(string $phone, string $message, ?int $userId = null): bool
    {
        if (WhatsAppService::isEnabled()) {
            $result = WhatsAppService::sendText($phone, $message);
            
            // Log the channel attempt
            self::logChannel(
                'whatsapp',
                'generic_whatsapp',
                "To: $phone\nBody: $message",
                ['response' => $result],
                $result['success'],
                $result['success'] ? null : ($result['error'] ?? 'Unknown error'),
                null, // employerId (not easily resolved here)
                $userId // candidateId
            );

            return $result['success'];
        }
        return false;
    }

    public static function sendPush(int $userId, string $title, string $message, ?string $link = null): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }
            $db = Database::getInstance();
            $rows = $db->fetchAll("SELECT token FROM user_push_tokens WHERE user_id = :uid AND is_active = 1", ['uid' => (int)$userId]);
            $tokens = array_map(function($r) { return (string)$r['token']; }, $rows);
            if (empty($tokens) && !empty($user->attributes['fcm_token'])) {
                $tokens = [(string)$user->attributes['fcm_token']];
            }
            if (empty($tokens)) {
                return false;
            }
            $allOk = true;
            foreach ($tokens as $t) {
                $ok = self::sendPushToken($t, $title, $message, $link, $userId);
                if (!$ok) {
                    $allOk = false;
                    try {
                        $db->query("UPDATE user_push_tokens SET is_active = 0, updated_at = NOW() WHERE user_id = :uid AND token = :token", [
                            'uid' => (int)$userId,
                            'token' => $t
                        ]);
                    } catch (\Throwable $e) {}
                }
            }
            return $allOk;
        } catch (\Throwable $e) {
            error_log("NotificationService::sendPush failed: " . $e->getMessage());
            return false;
        }
    }

    private static function sendPushToken(string $targetToken, string $title, string $message, ?string $link, ?int $userIdForLog = null): bool
    {
        try {
            $envPath = $_ENV['FCM_SERVICE_ACCOUNT'] ?? null;
            if ($envPath) {
                $credentialsPath = $envPath;
                if (!preg_match('/^([A-Za-z]:\\\\|\/)/', (string)$credentialsPath)) {
                    $credentialsPath = __DIR__ . '/../../' . ltrim((string)$credentialsPath, '/\\');
                }
            } else {
                $credentialsPath = __DIR__ . '/../../storage/firebase.json';
            }
            if (!file_exists($credentialsPath)) {
                error_log("FCM Credentials not found at: " . $credentialsPath);
                return false;
            }
            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $t = $client->fetchAccessTokenWithAssertion();
            $accessToken = $t['access_token'] ?? null;
            if (!$accessToken) {
                return false;
            }
            $json = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $json['project_id'] ?? '';
            if (empty($projectId)) {
                return false;
            }
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
            $payload = [
                'message' => [
                    'token' => $targetToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $message
                    ],
                    'data' => [
                        'link' => $link ?? '',
                        'click_action' => $link ?? '',
                        'url' => $link ?? ''
                    ]
                ]
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            $success = ($httpCode >= 200 && $httpCode < 300);
            self::logChannel(
                'push',
                'generic_push',
                "Title: $title\nBody: $message",
                ['link' => $link, 'response' => $result],
                $success,
                $success ? null : "HTTP $httpCode: $result . $curlError",
                null,
                $userIdForLog
            );
            return $success;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function getAdminFooter(): string
    {
        try {
            $footer = SystemSetting::get('email_footer');
            if ($footer) {
                return nl2br(htmlspecialchars($footer));
            }
        } catch (\Throwable $e) {
        }
        return "&copy; " . date('Y') . " Mindware Infotech. All rights reserved.";
    }

    public static function queueChatNotification(int $employerId, int $candidateUserId, string $message): void
    {
        try {
            $db = Database::getInstance();
            
            // Find or create conversation
            $sql = "SELECT id FROM conversations WHERE employer_id = :employer_id AND candidate_user_id = :candidate_user_id";
            $conversation = $db->fetchOne($sql, ['employer_id' => $employerId, 'candidate_user_id' => $candidateUserId]);
            
            $conversationId = 0;
            if ($conversation) {
                $conversationId = $conversation['id'];
            } else {
                // Create conversation
                $db->query(
                    "INSERT INTO conversations (employer_id, candidate_user_id, created_at, updated_at) VALUES (:employer_id, :candidate_user_id, NOW(), NOW())",
                    ['employer_id' => $employerId, 'candidate_user_id' => $candidateUserId]
                );
                $conversationId = $db->lastInsertId();
            }

            // Get employer's user_id for sender
            $empUser = $db->fetchOne("SELECT user_id FROM employers WHERE id = :id", ['id' => $employerId]);
            $senderUserId = $empUser['user_id'] ?? 0;

            if ($conversationId && $senderUserId) {
                // Insert message
                $db->query(
                    "INSERT INTO messages (conversation_id, sender_user_id, body, created_at, updated_at) VALUES (:conversation_id, :sender_user_id, :body, NOW(), NOW())",
                    ['conversation_id' => $conversationId, 'sender_user_id' => $senderUserId, 'body' => $message]
                );
                
                // Update conversation
                $db->query(
                    "UPDATE conversations SET last_message_id = LAST_INSERT_ID(), unread_candidate = unread_candidate + 1, updated_at = NOW() WHERE id = :id",
                    ['id' => $conversationId]
                );
            }
        } catch (\Throwable $e) {
            error_log("Chat notification failed: " . $e->getMessage());
        }
    }

    private static function wrapHtml(string $title, string $content, array $data = []): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $logo = $appUrl . '/assets/images/logo.png';
        
        // Use employer company logo if available, otherwise default
        $companyName = $data['company_name'] ?? 'Mindware Infotech';
        $companyLogo = !empty($data['company_logo']) ? $data['company_logo'] : null;
        
        if ($companyLogo && !str_starts_with($companyLogo, 'http')) {
             $companyLogo = $appUrl . '/storage/' . ltrim($companyLogo, '/');
        }
        
        // If no specific company logo (e.g. system notification), use site logo
        $headerLogo = $companyLogo ?: $logo;
        $footerText = self::getAdminFooter();
        $supportEmail = $_ENV['SUPPORT_EMAIL'] ?? 'gm@mindwareinfotech.com';
        $supportNumber = $_ENV['SUPPORT_NUMBER'] ?? '+91 8527522688';
        $footerText = str_replace(['+91 123 456 7890','123 456 7890','+911234567890','12345678'], $supportNumber, $footerText);
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }
        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }
        .header img { max-height: 50px; object-fit: contain; }
        .content { padding: 30px; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .btn:hover { background-color: #1d4ed8; }
        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{$headerLogo}" alt="{$companyName}">
        </div>
        <div class="content">
            {$content}
        </div>
        <div class="footer">
            <p>{$footerText}</p>
            <p><strong>Support:</strong> {$supportEmail} &nbsp;|&nbsp; {$supportNumber}</p>
            <p><small>You are receiving this email because you are registered on {$companyName}.</small></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private static function renderTemplate(string $key, array $data): array
    {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $candidateName = htmlspecialchars((string)($data['candidate_name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8');
        
        switch ($key) {
            case 'candidate_invite':
                $subject = 'Verify Your Account – Complete Your Profile';
                $verifyLink = htmlspecialchars((string)($data['verify_link'] ?? ($appUrl . '/verify-account')), ENT_QUOTES, 'UTF-8');
                $resetLink = htmlspecialchars((string)($data['reset_link'] ?? ($appUrl . '/reset-password')), ENT_QUOTES, 'UTF-8');
                $company = htmlspecialchars((string)($_ENV['PORTAL_NAME'] ?? 'Mindware Infotech'), ENT_QUOTES, 'UTF-8');
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Hello {$candidateName},</h2>
                    <p>You have been added to our platform by the administrator.</p>
                    <p>To activate your account, please complete the steps below:</p>
                    <ol style='margin: 0 0 16px 18px; color:#4b5563;'>
                        <li>Verify your email address</li>
                        <li>Set your password</li>
                        <li>Complete your profile</li>
                    </ol>
                    <center>
                        <a href='{$verifyLink}' class='btn' style='margin-right:8px;'>Verify Email</a>
                        <a href='{$resetLink}' class='btn' style='background-color:#10b981;'>Set Password</a>
                    </center>
                    <p style='margin-top:24px; font-size:12px; color:#6b7280;'>If you did not request this, you can safely ignore this email.</p>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];
            case 'candidate_welcome':
                $subject = 'Welcome to Mindware Infotech';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Welcome, {$candidateName}!</h2>
                    <p>Thanks for joining Mindware Infotech. We're excited to help you find your next career opportunity.</p>
                    <p>Complete your profile to get matched with top employers.</p>
                    <center><a href='{$appUrl}/login' class='btn'>Login to Your Account</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'employer_welcome':
                $subject = 'Welcome to Mindware Infotech';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Welcome to Mindware Infotech!</h2>
                    <p>Thank you for registering as an employer. We are here to help you hire the best talent.</p>
                    <p>Start by posting your first job.</p>
                    <center><a href='{$appUrl}/employer/jobs/create' class='btn'>Post a Job</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'generic_notification':
                $subject = $data['title'] ?? 'Notification';
                $message = $data['message'] ?? '';
                $link = $data['link'] ?? $appUrl;
                $linkText = $data['link_text'] ?? 'View Details';
                
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>{$subject}</h2>
                    <p>{$message}</p>
                    <center><a href='{$link}' class='btn'>{$linkText}</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'email_verification':
                $code = htmlspecialchars((string)($data['code'] ?? ''), ENT_QUOTES, 'UTF-8');
                $subject = 'Verify your email address';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Verify Your Email</h2>
                    <p>Please use the verification code below to confirm your email address:</p>
                    <div style='background:#f3f4f6; padding:20px; text-align:center; font-size:24px; font-weight:bold; letter-spacing:5px; border-radius:8px; margin:20px 0;'>
                        {$code}
                    </div>
                    <p>If you didn't request this, you can safely ignore this email.</p>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'password_reset':
                $link = htmlspecialchars((string)($data['reset_link'] ?? ($appUrl . '/reset')), ENT_QUOTES, 'UTF-8');
                $subject = 'Reset your password';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Password Reset</h2>
                    <p>We received a request to reset your password. Click the button below to choose a new password:</p>
                    <center><a href='{$link}' class='btn'>Reset Password</a></center>
                    <p style='margin-top:20px; font-size:12px; color:#6b7280;'>If you didn't request this change, please ignore this email.</p>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'interview_scheduled':
                $job = htmlspecialchars((string)($data['job_title'] ?? 'Interview'), ENT_QUOTES, 'UTF-8');
                $time = htmlspecialchars((string)($data['scheduled_time'] ?? ''), ENT_QUOTES, 'UTF-8');
                $company = htmlspecialchars((string)($data['company_name'] ?? 'Mindware Infotech'), ENT_QUOTES, 'UTF-8');
                $location = htmlspecialchars((string)($data['location'] ?? 'Remote/Online'), ENT_QUOTES, 'UTF-8');
                $meetingLink = htmlspecialchars((string)($data['meeting_link'] ?? ''), ENT_QUOTES, 'UTF-8');
                $companyWebsite = htmlspecialchars((string)($data['company_website'] ?? ''), ENT_QUOTES, 'UTF-8');
                
                $subject = "Interview Scheduled: {$job} at {$company}";
                
                $meetingHtml = '';
                if ($meetingLink) {
                    $meetingHtml = "<p><strong>Meeting Link:</strong> <a href='{$meetingLink}'>{$meetingLink}</a></p>";
                }

                $companyHtml = $companyWebsite 
                    ? "<p style='margin:5px 0;'><strong>Company:</strong> <a href='{$companyWebsite}' target='_blank'>{$company}</a></p>" 
                    : "<p style='margin:5px 0;'><strong>Company:</strong> {$company}</p>";

                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Interview Confirmed</h2>
                    <p>Hi {$candidateName},</p>
                    <p>Your interview for the <strong>{$job}</strong> position at <strong>{$company}</strong> has been scheduled.</p>
                    
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Date & Time:</strong> {$time}</p>
                        <p style='margin:5px 0;'><strong>Location:</strong> {$location}</p>
                        {$meetingHtml}
                        {$companyHtml}
                    </div>
                    
                    <p>Please make sure to be ready 5 minutes before the scheduled time.</p>
                    <center><a href='{$appUrl}/candidate/applications' class='btn'>View Application</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'application_status':
                $job = htmlspecialchars((string)($data['job_title'] ?? 'Job'), ENT_QUOTES, 'UTF-8');
                $status = htmlspecialchars((string)($data['status'] ?? 'updated'), ENT_QUOTES, 'UTF-8');
                $company = htmlspecialchars((string)($data['company_name'] ?? 'Mindware Infotech'), ENT_QUOTES, 'UTF-8');
                
                $subject = "Application Update: {$job} at {$company}";
                
                $statusColor = '#2563eb'; // Default blue
                if ($status === 'shortlisted') $statusColor = '#059669';
                if ($status === 'rejected') $statusColor = '#dc2626';
                if ($status === 'hired') $statusColor = '#7c3aed';
                
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Application Status Update</h2>
                    <p>Hi {$candidateName},</p>
                    <p>The status of your application for <strong>{$job}</strong> at <strong>{$company}</strong> has been updated.</p>
                    
                    <div style='text-align:center; margin:30px 0;'>
                        <span style='background-color:{$statusColor}; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;'>
                            {$status}
                        </span>
                    </div>
                    
                    <center><a href='{$appUrl}/candidate/applications' class='btn'>View Details</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'job_match':
                $jobTitle = htmlspecialchars((string)($data['job_title'] ?? 'Job'), ENT_QUOTES, 'UTF-8');
                $matchScore = htmlspecialchars((string)($data['match_score'] ?? '0'), ENT_QUOTES, 'UTF-8');
                $jobId = $data['job_id'] ?? 0;
                
                $subject = "New Job Match: {$jobTitle} ({$matchScore}% Match)";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>New Job Match Found!</h2>
                    <p>Hi {$candidateName},</p>
                    <p>We found a new job that matches your profile.</p>
                    
                    <div class='info-box'>
                        <h3 style='margin-top:0;'>{$jobTitle}</h3>
                        <p><strong>Match Score:</strong> <span style='color:#059669; font-weight:bold;'>{$matchScore}%</span></p>
                    </div>
                    
                    <center><a href='{$appUrl}/candidate/jobs/{$jobId}' class='btn'>View Job</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];
                
            default:
                // Fallback for other templates
                $subject = $data['subject'] ?? 'Notification';
                $bodyRaw = $data['body'] ?? 'You have a new notification.';
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $bodyRaw, $data)];
        }
    }

    private static function logEmail(string $templateKey, string $subject, string $content, array $data, bool $success, ?string $error, ?int $employerId, ?int $candidateUserId): int
    {
        try {
            $db = Database::getInstance();
            $status = $success ? 'sent' : 'failed';
            // If error is 'sending', it means it's a pending state we just invented
            if ($error === 'sending') $status = 'pending';

            $params = [
                'employer_id' => $employerId,
                'candidate_id' => $candidateUserId,
                'channel' => 'email',
                'template_key' => $templateKey,
                'subject' => $subject,
                'content' => $content,
                'status' => $status,
                'metadata' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'error_message' => $error === 'sending' ? null : $error
            ];
            $sql = "INSERT INTO notification_logs (employer_id, candidate_id, channel, template_key, subject, content, status, metadata, error_message, created_at) VALUES (:employer_id, :candidate_id, :channel, :template_key, :subject, :content, :status, :metadata, :error_message, NOW())";
            $db->query($sql, $params);
            return (int)$db->lastInsertId();
        } catch (\Throwable $t) {
            return 0;
        }
    }

    public static function notifyJobMatch(int $userId, array $job): void
    {
        self::notify(
            $userId,
            'job_match',
            'New Job Match Found!',
            "A new job '{$job['title']}' matches your profile ({$job['match_score']}% match).",
            "/candidate/jobs/{$job['id']}"
        );
    }

    public static function notifyApplicationUpdate(int $userId, string $jobTitle, string $status): void
    {
        $statusLabels = [
            'shortlisted' => 'shortlisted',
            'interview' => 'interview scheduled',
            'offer' => 'offer received',
            'rejected' => 'rejected'
        ];
        
        self::notify(
            $userId,
            'application_update',
            'Application Update',
            "Your application for '{$jobTitle}' has been " . (isset($statusLabels[$status]) ? $statusLabels[$status] : $status) . ".",
            '/candidate/applications'
        );
    }

    public static function notifyInterviewScheduled(int $userId, string $jobTitle, string $dateTime): void
    {
        self::notify(
            $userId,
            'interview_scheduled',
            'Interview Scheduled',
            "Your interview for '{$jobTitle}' is scheduled for {$dateTime}.",
            '/candidate/applications'
        );
    }

    public static function notifyNewMessage(int $userId, string $employerName): void
    {
        self::notify(
            $userId,
            'message',
            'New Message',
            "You have a new message from {$employerName}.",
            '/candidate/chat'
        );
    }

    public static function notifyProfileView(int $userId, string $employerName): void
    {
        $candidate = Candidate::findByUserId($userId);
        if ($candidate && !$candidate->isPremium()) {
            return;
        }
        self::notify(
            $userId,
            'profile_view',
            'Profile Viewed',
            "Your profile was viewed by {$employerName}.",
            '/candidate/profile/complete'
        );
    }

    /* ========================== ✅ WHATSAPP & SMS ========================== */

    private static function logChannel(string $channel, string $templateKey, string $content, array $data, bool $success, ?string $error, ?int $employerId, ?int $candidateUserId): int
    {
        try {
            $db = Database::getInstance();
            $params = [
                'employer_id' => $employerId,
                'candidate_id' => $candidateUserId,
                'channel' => $channel,
                'template_key' => $templateKey,
                'subject' => strtoupper($channel) . ' ' . $templateKey,
                'content' => $content,
                'status' => $success ? 'sent' : 'failed',
                'metadata' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'error_message' => $error
            ];
            $sql = "INSERT INTO notification_logs (employer_id, candidate_id, channel, template_key, subject, content, status, metadata, error_message, created_at) VALUES (:employer_id, :candidate_id, :channel, :template_key, :subject, :content, :status, :metadata, :error_message, NOW())";
            $db->query($sql, $params);
            return (int)$db->lastInsertId();
        } catch (\Throwable $t) {
            return 0;
        }
    }

    /* ========================== ✅ SECURE JOIN TOKENS ========================== */

    public static function generateJoinToken(int $interviewId, string $role, int $userId, int $ttlSeconds = 7200): string
    {
        $token = bin2hex(random_bytes(16));
        $redis = \App\Core\RedisClient::getInstance();
        $payload = json_encode([
            'interview_id' => $interviewId,
            'role' => $role,
            'user_id' => $userId,
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlSeconds)
        ]);
        if ($redis->isAvailable()) {
            $redis->set("interview_join:{$token}", $payload, $ttlSeconds);
        }
        return $token;
    }

    public static function validateJoinToken(string $token): ?array
    {
        $redis = \App\Core\RedisClient::getInstance();
        if (!$redis->isAvailable()) return null;
        $data = $redis->get("interview_join:{$token}");
        if (!$data) return null;
        $payload = json_decode($data, true);
        return is_array($payload) ? $payload : null;
    }
}
