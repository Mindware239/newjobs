<?php declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\RedisClient;
use App\Models\Candidate;
use App\Models\Notification;
use App\Models\SystemSetting;
use App\Models\User;
use App\Workers\EmailWorker;
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
        // 0. Global Kill Switches (Admin Controlled)
        // If specific channel is disabled globally, we remove it from allowed list
        $globalEmail = SystemSetting::get('notifications_email', '1') === '1';
        $globalPush = SystemSetting::get('notifications_push', '1') === '1';
        $globalInApp = SystemSetting::get('notifications_in_app', '1') === '1';
        $globalWhatsapp = SystemSetting::get('notifications_whatsapp', '0') === '1';

        // Reference ID for dedup and cooldown checks
        $referenceId = $data['reference_id'] ?? null;  // e.g. Job ID or Application ID

        try {
            $user = User::find($userId);
            if (!$user)
                return;

            $prefs = $user->getNotificationPreferences();

            // Strict priority flow:
            // 1) Global admin switch -> 2) User preference -> 3) Cooldown/Rate -> 4) Send
            // Cooldown/rate-block (event-level) before any channel work
            if (self::isRateLimited($userId, $type, $referenceId)) {
                return;
            }

            // Helper to decide if a channel should be used
            $shouldSend = function (string $channel) use ($prefs, $allowedChannels, $globalEmail, $globalPush, $globalInApp, $globalWhatsapp): bool {
                // 1. Check Global Admin Switch
                if ($channel === 'email' && !$globalEmail)
                    return false;
                if ($channel === 'push' && !$globalPush)
                    return false;
                if ($channel === 'in_app' && !$globalInApp)
                    return false;
                if ($channel === 'whatsapp' && !$globalWhatsapp)
                    return false;

                // 2. Check Caller Constraints
                if ($allowedChannels !== null && !in_array($channel, $allowedChannels, true)) {
                    return false;
                }

                // 3. Check User Preference (Default to TRUE if not set, except WhatsApp)
                if ($channel === 'whatsapp') {
                    return isset($prefs['whatsapp']) && (bool) $prefs['whatsapp'];
                }
                return !isset($prefs[$channel]) || (bool) $prefs[$channel];
            };

            // 1. In-App Notification
            if ($shouldSend('in_app')) {
                if (!self::isDuplicate($userId, $type, 'in_app', $referenceId)) {
                    self::notify($userId, $type, $title, $message, $link);
                    self::logNotification($userId, $type, 'in_app', $referenceId, 'sent');
                }
            }

            // 2. Email Notification
            if ($shouldSend('email') && !empty($user->attributes['email'])) {
                // Hard daily safety cap for emails
                if (self::isDailyEmailCapped($userId)) {
                    // Do not send further emails for today
                } else {
                if (!self::isDuplicate($userId, $type, 'email', $referenceId)) {
                    $templateKey = $data['email_template'] ?? $type;
                    $emailData = array_merge($data, [
                        'title' => $title,
                        'message' => $message,
                        'link' => $link ?? ($data['link'] ?? null),
                        'candidate_name' => $user->attributes['first_name'] ?? $user->attributes['name'] ?? 'User'
                    ]);
                        
                        self::queueEmail($user->attributes['email'], $templateKey, $emailData, $title);
                        self::logNotification($userId, $type, 'email', $referenceId, 'sent');
                    }
                }
            }

            // 3. WhatsApp Notification
            if ($shouldSend('whatsapp') && !empty($user->attributes['phone'])) {
                if (!self::isDuplicate($userId, $type, 'whatsapp', $referenceId)) {
                    $templateKey = $data['whatsapp_template'] ?? $type;
                    self::queueWhatsApp($user->attributes['phone'], $templateKey, $data, $userId);
                    self::logNotification($userId, $type, 'whatsapp', $referenceId, 'sent');
                }
            }

            // 4. Push Notification
            if ($shouldSend('push')) {
                if (!self::isDuplicate($userId, $type, 'push', $referenceId)) {
                    self::sendPush($userId, $title, $message, $link);
                    self::logNotification($userId, $type, 'push', $referenceId, 'sent');
                }
            }
        } catch (\Throwable $e) {
            error_log('NotificationService::send failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if exact notification was sent recently (Deduplication)
     */
    private static function isDuplicate(int $userId, string $type, string $channel, ?string $refId): bool
    {
        try {
            $db = Database::getInstance();
            // Check for exact same event within last 1 hour (short term dedup)
            $sql = 'SELECT id FROM notification_logs 
                    WHERE user_id = :uid 
                    AND event_type = :type 
                    AND channel = :channel 
                    AND sent_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)';

            $params = ['uid' => $userId, 'type' => $type, 'channel' => $channel];

            if ($refId) {
                $sql .= ' AND reference_id = :ref';
                $params['ref'] = $refId;
            }

            $result = $db->fetchOne($sql, $params);
            return !empty($result);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check rate limits (1 per 24h per event type)
     */
    private static function isRateLimited(int $userId, string $type, ?string $refId): bool
    {
        // Only rate limit these specific noisy events
        $rateLimitedEvents = ['job_match', 'profile_view', 'profile_update', 'application_status'];
        if (!in_array($type, $rateLimitedEvents)) {
            return false;
        }

        try {
            $db = Database::getInstance();
            // Check if ANY channel sent this event in last 24h
            $sql = 'SELECT id FROM notification_logs 
                    WHERE user_id = :uid 
                    AND event_type = :type 
                    AND sent_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)';

            $params = ['uid' => $userId, 'type' => $type];

            // If reference ID exists (e.g. Job ID), allow same event type but different reference
            if ($refId) {
                $sql .= ' AND reference_id = :ref';
                $params['ref'] = $refId;
            }

            $result = $db->fetchOne($sql, $params);
            return !empty($result);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Hard daily safety cap for emails per user (spam protection)
     * Blocks email channel if user has received >= limit in the last 24 hours
     */
    private static function isDailyEmailCapped(int $userId, int $limit = 5): bool
    {
        try {
            $db = Database::getInstance();
            $count = (int)($db->fetchOne(
                "SELECT COUNT(*) as c FROM notification_logs 
                 WHERE user_id = :uid 
                 AND channel = 'email' 
                 AND sent_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                ['uid' => $userId]
            )['c'] ?? 0);
            return $count >= $limit;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function logNotification(int $userId, string $type, string $channel, ?string $refId, string $status): void
    {
        try {
            $db = Database::getInstance();
            // Resolve role to choose correct foreign key columns used in notification_logs
            $roleRow = $db->fetchOne('SELECT role FROM users WHERE id = :id', ['id' => $userId]);
            $role = strtolower((string)($roleRow['role'] ?? ''));
            $candidateId = null;
            $employerId = null;
            if ($role === 'candidate') {
                $candidateId = $userId;
            } elseif ($role === 'employer') {
                $emp = $db->fetchOne('SELECT id FROM employers WHERE user_id = :uid', ['uid' => $userId]);
                $employerId = (int)($emp['id'] ?? 0) ?: null;
            }
            $params = [
                'employer_id' => $employerId,
                'candidate_id' => $candidateId,
                'channel' => $channel,
                'template_key' => $type,
                'subject' => strtoupper($channel) . ' ' . $type,
                'content' => '',
                'status' => $status,
                'metadata' => json_encode(['reference_id' => $refId], JSON_UNESCAPED_UNICODE),
                'error_message' => null
            ];
            $sql = 'INSERT INTO notification_logs (employer_id, candidate_id, channel, template_key, subject, content, status, metadata, error_message, created_at) 
                    VALUES (:employer_id, :candidate_id, :channel, :template_key, :subject, :content, :status, :metadata, :error_message, NOW())';
            $db->query($sql, $params);
        } catch (\Throwable $e) {
            error_log('Failed to log notification: ' . $e->getMessage());
        }
    }

    public static function sendEmail(string $to, string $subject, string $templateKey, array $templateData = []): bool
    {
        // 1. Log as pending to get ID
        $logId = self::logEmail($templateKey, $subject, '', $templateData, false, 'sending', $templateData['employer_id'] ?? null, $templateData['candidate_user_id'] ?? null);

        // 2. Render
        $templateData['log_id'] = $logId;
        $rendered = self::renderTemplate($templateKey, $templateData);
        $subject = $subject ?: ($rendered['subject'] ?? '');
        $body = $rendered['body'] ?? '';

        // 3. Update log with content
        self::updateLogContent($logId, $subject, $body);

        // 4. Send
        $attachments = is_array($templateData['attachments'] ?? null) ? $templateData['attachments'] : [];
        $success = MailService::sendEmail($to, $subject, $body, null, null, $attachments);

        // 5. Update status
        self::updateLogStatus($logId, $success ? 'sent' : 'failed', $success ? null : 'send_failed');

        return $success;
    }

    private static function updateLogContent(int $id, string $subject, string $content): void
    {
        if (!$id)
            return;
        try {
            $db = Database::getInstance();
            $db->query('UPDATE notification_logs SET subject = :subject, content = :content WHERE id = :id', [
                'id' => $id,
                'subject' => $subject,
                'content' => $content
            ]);
        } catch (\Throwable $t) {
        }
    }

    private static function updateLogStatus(int $id, string $status, ?string $error): void
    {
        if (!$id)
            return;
        try {
            $db = Database::getInstance();
            $db->query('UPDATE notification_logs SET status = :status, error_message = :error WHERE id = :id', [
                'id' => $id,
                'status' => $status,
                'error' => $error
            ]);
        } catch (\Throwable $t) {
        }
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
                $message = 'Your interview is about to end! Upgrade to Premium to continue interviewing without interruption: ' . ($data['upgrade_link'] ?? '');
            } elseif ($templateKey === 'job_match') {
                $jobTitle = $data['job_title'] ?? 'New Job';
                $link = $data['link'] ?? '';
                $message = "New Match: {$jobTitle} matches your profile! Apply now: " . ($_ENV['APP_URL'] ?? 'http://localhost:8000') . $link;
            } elseif ($templateKey === 'marketing_broadcast' && isset($data['message'])) {
                $message = $data['message'];  // Use direct message for broadcasts
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
                null,  // employerId (not easily resolved here)
                $userId  // candidateId
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
            $rows = $db->fetchAll('SELECT token FROM user_push_tokens WHERE user_id = :uid AND is_active = 1', ['uid' => (int) $userId]);
            $tokens = array_map(function ($r) {
                return (string) $r['token'];
            }, $rows);
            if (empty($tokens) && !empty($user->attributes['fcm_token'])) {
                $tokens = [(string) $user->attributes['fcm_token']];
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
                        $db->query('UPDATE user_push_tokens SET is_active = 0, updated_at = NOW() WHERE user_id = :uid AND token = :token', [
                            'uid' => (int) $userId,
                            'token' => $t
                        ]);
                    } catch (\Throwable $e) {
                    }
                }
            }
            return $allOk;
        } catch (\Throwable $e) {
            error_log('NotificationService::sendPush failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendPushToken(string $targetToken, string $title, string $message, ?string $link, ?int $userIdForLog = null): bool
    {
        try {
            $envPath = $_ENV['FCM_SERVICE_ACCOUNT'] ?? null;
            if ($envPath) {
                $credentialsPath = $envPath;
                if (!preg_match('/^([A-Za-z]:\\\\|\/)/', (string) $credentialsPath)) {
                    $credentialsPath = __DIR__ . '/../../' . ltrim((string) $credentialsPath, '/\\');
                }
            } else {
                $credentialsPath = __DIR__ . '/../../storage/firebase.json';
            }
            if (!file_exists($credentialsPath)) {
                error_log('FCM Credentials not found at: ' . $credentialsPath);
                return false;
            }
            $client = new Client();

            // SSL Fix: Use local cacert.pem if available
            $cacert = realpath(__DIR__ . '/../../storage/cacert.pem');
            $verifySsl = false;  // Default to false for local dev fallback

            if ($cacert && file_exists($cacert)) {
                $verifySsl = $cacert;
            }

            // Manually fetch token using Firebase JWT to control SSL verification
            // This bypasses Google\Client's internal HTTP handler which can be problematic on XAMPP
            $jsonKey = json_decode(file_get_contents($credentialsPath), true);

            // Check if firebase/php-jwt is available (it should be via composer)
            if (class_exists('Firebase\JWT\JWT')) {
                $now = time();
                $jwtPayload = [
                    'iss' => $jsonKey['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'exp' => $now + 3600,
                    'iat' => $now
                ];

                $jwt = \Firebase\JWT\JWT::encode($jwtPayload, $jsonKey['private_key'], 'RS256');

                // Exchange JWT for access token using Guzzle with explicit verify setting
                $httpClient = new \GuzzleHttp\Client(['verify' => $verifySsl]);
                $response = $httpClient->post('https://oauth2.googleapis.com/token', [
                    'form_params' => [
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                        'assertion' => $jwt
                    ]
                ]);

                $tokenData = json_decode((string) $response->getBody(), true);
                $accessToken = $tokenData['access_token'] ?? null;
            } else {
                // Fallback to Google Client (might fail on XAMPP)
                $client = new Client();
                $client->setAuthConfig($credentialsPath);
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

                // Fix for local SSL certificate issues (cURL error 60)
                $guzzleClient = new \GuzzleHttp\Client([
                    'verify' => false,  // Disable SSL verification for local development
                ]);
                $client->setHttpClient($guzzleClient);

                $t = $client->fetchAccessTokenWithAssertion();
                $accessToken = $t['access_token'] ?? null;
            }

            if (!$accessToken) {
                return false;
            }

            // Extract project ID (already have it)
            $projectId = $jsonKey['project_id'] ?? '';
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
            // Use Guzzle for HTTP POST to FCM
            $verify = self::resolveCaBundle();
            $clientHttp = new \GuzzleHttp\Client([
                'timeout' => 10,
                'verify'  => $verify,
            ]);
            $resp = $clientHttp->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);
            $httpCode = $resp->getStatusCode();
            $result = (string) $resp->getBody();
            $curlError = '';
            $success = ($httpCode >= 200 && $httpCode < 300);
            self::logChannel(
                'push',
                'generic_push',
                "Title: $title\nBody: $message",
                ['link' => $link, 'response' => $result],
                $success,
                $success ? null : "HTTP $httpCode: $result $curlError",
                null,
                $userIdForLog
            );
            return $success;
        } catch (\Throwable $e) {
            error_log('FCM Send Error: ' . $e->getMessage());
            return false;
        }
    }

    private static function resolveCaBundle(): string|bool
    {
        $envPath = $_ENV['CA_BUNDLE_PATH'] ?? getenv('CA_BUNDLE_PATH') ?: null;
        $possible = [
            $envPath,
            __DIR__ . '/../../vendor/guzzlehttp/guzzle/src/cacert.pem',
            'E:/xampp/php/extras/ssl/cacert.pem',
            'C:/xampp/php/extras/ssl/cacert.pem',
            'E:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
        ];
        foreach ($possible as $p) {
            if ($p && file_exists((string)$p)) {
                return (string)$p;
            }
        }
        return true;
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
        return '&copy; ' . date('Y') . ' Mindware Infotech. All rights reserved.';
    }

    public static function queueChatNotification(int $employerId, int $candidateUserId, string $message): void
    {
        try {
            $db = Database::getInstance();

            // Find or create conversation
            $sql = 'SELECT id FROM conversations WHERE employer_id = :employer_id AND candidate_user_id = :candidate_user_id';
            $conversation = $db->fetchOne($sql, ['employer_id' => $employerId, 'candidate_user_id' => $candidateUserId]);

            $conversationId = 0;
            if ($conversation) {
                $conversationId = $conversation['id'];
            } else {
                // Create conversation
                $db->query(
                    'INSERT INTO conversations (employer_id, candidate_user_id, created_at, updated_at) VALUES (:employer_id, :candidate_user_id, NOW(), NOW())',
                    ['employer_id' => $employerId, 'candidate_user_id' => $candidateUserId]
                );
                $conversationId = $db->lastInsertId();
            }

            // Get employer's user_id for sender
            $empUser = $db->fetchOne('SELECT user_id FROM employers WHERE id = :id', ['id' => $employerId]);
            $senderUserId = $empUser['user_id'] ?? 0;

            if ($conversationId && $senderUserId) {
                // Insert message
                $db->query(
                    'INSERT INTO messages (conversation_id, sender_user_id, body, created_at, updated_at) VALUES (:conversation_id, :sender_user_id, :body, NOW(), NOW())',
                    ['conversation_id' => $conversationId, 'sender_user_id' => $senderUserId, 'body' => $message]
                );

                // Update conversation
                $db->query(
                    'UPDATE conversations SET last_message_id = LAST_INSERT_ID(), unread_candidate = unread_candidate + 1, updated_at = NOW() WHERE id = :id',
                    ['id' => $conversationId]
                );
            }
        } catch (\Throwable $e) {
            error_log('Chat notification failed: ' . $e->getMessage());
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
        $footerText = str_replace(['+91 123 456 7890', '123 456 7890', '+911234567890', '12345678'], $supportNumber, $footerText);
        $pixel = '';
        if (!empty($data['log_id'])) {
            $pixel = '<img src="' . self::signOpenPixel((int)$data['log_id']) . '" width="1" height="1" alt="" style="display:none" />';
        }

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
                        {$pixel}
                    </div>
                </div>
            </body>
            </html>
            HTML;
    }

    private static function renderTemplate(string $key, array $data): array
    {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $candidateName = htmlspecialchars((string) ($data['candidate_name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8');
        $logId = (int)($data['log_id'] ?? 0);

        $external = self::tryExternalTemplate($key, $data);
        if ($external) {
            return $external;
        }

        switch ($key) {
            case 'hr_verification_request':
                $subject = (string)($data['subject'] ?? 'Employment Verification Request');
                $candidateName = htmlspecialchars((string)($data['candidate_name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8');
                $companyName = htmlspecialchars((string)($data['company_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $employeeId = htmlspecialchars((string)($data['employee_id'] ?? ''), ENT_QUOTES, 'UTF-8');
                $designation = htmlspecialchars((string)($data['designation'] ?? ''), ENT_QUOTES, 'UTF-8');
                $period = htmlspecialchars((string)($data['period'] ?? ''), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string)($data['secure_link'] ?? ($appUrl . '/hr/verify')), ENT_QUOTES, 'UTF-8');
                $tracked = $link;
                if ($logId > 0) {
                    $tracked = self::signClickUrl($logId, $link);
                }
                $docsHtml = '';
                if (!empty($data['documents']) && is_array($data['documents'])) {
                    $docsHtml .= "<ul style='padding-left:18px;'>";
                    foreach ($data['documents'] as $doc) {
                        $t = htmlspecialchars((string)($doc['type'] ?? 'Document'), ENT_QUOTES, 'UTF-8');
                        $u = htmlspecialchars((string)($doc['url'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $docsHtml .= "<li><a href='{$u}' target='_blank'>{$t}</a></li>";
                    }
                    $docsHtml .= "</ul>";
                }
                $content = "
                    <p>Dear Sir/Ma’am,</p>
                    <p>Greetings of the day.</p>
                    <p>This email is regarding the employment verification of <strong>{$candidateName}</strong>, who has declared employment with <strong>{$companyName}</strong>. We kindly request your support in authenticating the details provided below. Relevant documents shared by the candidate are attached for your reference.</p>
                    <p style='color:#b91c1c; font-weight:600;'>Important: Please use the secure VERIFY link below to submit your response. Email replies are not processed by our system.</p>
                    <div class='info-box' style='margin-top:20px'>
                        <h3 style='margin:0 0 8px 0'>Employment Verification Details</h3>
                        <table style='width:100%; border-collapse:collapse; font-size:14px'>
                            <thead>
                                <tr>
                                    <th style='text-align:left; border-bottom:1px solid #e5e7eb; padding:8px'>Particulars</th>
                                    <th style='text-align:left; border-bottom:1px solid #e5e7eb; padding:8px'>Details Provided by Candidate</th>
                                    <th style='text-align:left; border-bottom:1px solid #e5e7eb; padding:8px'>Details as per Your Records</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td style='padding:8px'>Respondent Name</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Designation & Department</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Date of Verification</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Contact Details</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Company Name</td><td style='padding:8px'>{$companyName}</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Employee ID</td><td style='padding:8px'>{$employeeId}</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Designation</td><td style='padding:8px'>{$designation}</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Period of Employment</td><td style='padding:8px'>{$period}</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Remuneration</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Reporting Manager</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Reason for Leaving</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Resigned / Serving Notice Period (if active)</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Eligible for Rehire (If no, specify reason)</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Exit Formalities Completed (Yes/No)</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Remarks on Behaviour</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Are the attached documents genuine? (Yes/No)</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>If No, reason (forged/fake/manipulated/other)</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Any issues during tenure (Ethics, credibility & reputation)</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                                <tr><td style='padding:8px'>Additional Comments</td><td style='padding:8px'>—</td><td style='padding:8px'>—</td></tr>
                            </tbody>
                        </table>
                        <div style='margin-top:12px'>
                            <strong>Documents:</strong>
                            {$docsHtml}
                        </div>
                    </div>
                    <p>Kindly review the above and provide confirmation or corrections. For secure submission, please use the link below:</p>
                    <center><a href='{$tracked}' class='btn' target='_blank'>Submit Verification</a></center>
                    <p style='margin-top:20px'>Your inputs and feedback are highly valuable and will play a significant role in completing the verification process. We look forward to your response at the earliest.</p>
                    <p>Thank you for your cooperation.<br>Warm regards,<br><strong>{$companyName}</strong><br>Employment Verification Team</p>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];
            case 'application_update':
                $job = htmlspecialchars((string) ($data['job_title'] ?? 'Application'), ENT_QUOTES, 'UTF-8');
                $status = htmlspecialchars((string) ($data['status'] ?? 'updated'), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string) ($data['link'] ?? ($appUrl . '/candidate/applications')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $link = self::signClickUrl($logId, $link); }
                $subject = "Application Update – {$job}";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Your application has been {$status}</h2>
                    <p style='margin:8px 0 16px;'>Great news! Your application for <strong>{$job}</strong> has been {$status}.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Role:</strong> {$job}</p>
                        <p style='margin:5px 0;'><strong>Status:</strong> {$status}</p>
                    </div>
                    <center><a href='{$link}' class='btn'>View Application</a></center>
                    <p style='margin-top:16px; font-size:12px; color:#6b7280;'>Keep your profile updated to improve match scores and speed up decisions.</p>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'candidate_invite':
                $subject = 'Verify Your Account – Complete Your Profile';
                $verifyLink = htmlspecialchars((string) ($data['verify_link'] ?? ($appUrl . '/verify-account')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $verifyLink = self::signClickUrl($logId, $verifyLink); }
                $resetLink = htmlspecialchars((string) ($data['reset_link'] ?? ($appUrl . '/reset-password')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $resetLink = self::signClickUrl($logId, $resetLink); }
                $company = htmlspecialchars((string) ($_ENV['PORTAL_NAME'] ?? 'Mindware Infotech'), ENT_QUOTES, 'UTF-8');
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

            case 'message':
                $from = htmlspecialchars((string) ($data['from_name'] ?? 'Employer'), ENT_QUOTES, 'UTF-8');
                $preview = htmlspecialchars((string) ($data['preview'] ?? 'You have a new message'), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string) ($data['link'] ?? ($appUrl . '/candidate/chat')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $link = self::signClickUrl($logId, $link); }
                $subject = "New Message from {$from}";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>You have a new message</h2>
                    <p><strong>{$from}</strong> sent you a message.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'>{$preview}</p>
                    </div>
                    <center><a href='{$link}' class='btn'>Open Messages</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'profile_view':
                $viewer = htmlspecialchars((string) ($data['employer_name'] ?? 'An employer'), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string) ($data['link'] ?? ($appUrl . '/candidate/profile/complete')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $link = self::signClickUrl($logId, $link); }
                $subject = 'Your Profile Was Viewed';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>{$viewer} viewed your profile</h2>
                    <p style='margin:8px 0 16px;'>Increase your chances by keeping your profile complete and up-to-date.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'>Add missing education, skills, or recent experience to improve match scores.</p>
                    </div>
                    <center><a href='{$link}' class='btn'>Update Profile</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'profile_nudge':
                $fields = $data['missing_fields'] ?? [];
                $fields = is_array($fields) ? $fields : [];
                $items = '';
                foreach ($fields as $f) {
                    $label = htmlspecialchars((string) $f, ENT_QUOTES, 'UTF-8');
                    $items .= "<li>{$label}</li>";
                }
                $strength = (int) ($data['profile_strength'] ?? 0);
                $link = htmlspecialchars((string) ($data['link'] ?? ($appUrl . '/candidate/profile/edit')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $link = self::signClickUrl($logId, $link); }
                $variant = (int)($data['variant'] ?? 0);
                $subjects = [
                    'Complete Your Profile – Unlock Better Matches',
                    'Boost Visibility – Add Missing Details',
                    'Improve Your Match Score – Update Profile',
                    'Get Noticed – Finish Profile Setup'
                ];
                $subject = $subjects[$variant % count($subjects)];
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Hi {$candidateName}, boost your career today</h2>
                    <p style='margin:8px 0;'>Profiles with complete details see up to <strong>3x more job matches</strong> and faster responses from recruiters.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Current Profile Strength:</strong> {$strength}%</p>
                        <p style='margin:8px 0;'>Add the following to strengthen your profile:</p>
                        <ul style='margin:8px 0 0 18px; color:#4b5563;'>{$items}</ul>
                    </div>
                    <center><a href='{$link}' class='btn'>Complete My Profile</a></center>
                    <p style='margin-top:16px; font-size:12px; color:#6b7280;'>Tip: Upload your latest resume and add recent experience for higher match scores.</p>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'job_match':
                $job = htmlspecialchars((string) ($data['job_title'] ?? 'New Job'), ENT_QUOTES, 'UTF-8');
                $score = htmlspecialchars((string) ($data['match_score'] ?? ''), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string) ($data['link'] ?? $appUrl), ENT_QUOTES, 'UTF-8');
                $subject = "New Match – {$job}";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>New job matches your profile</h2>
                    <p><strong>{$job}</strong> is a good fit for you.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Match Score:</strong> {$score}%</p>
                    </div>
                    <center><a href='{$link}' class='btn'>View Job</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'low_match_suggestion':
                $job = htmlspecialchars((string) ($data['job_title'] ?? 'Job'), ENT_QUOTES, 'UTF-8');
                $score = htmlspecialchars((string) ($data['match_score'] ?? ''), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string) ($data['link'] ?? ($appUrl . '/candidate/profile/edit')), ENT_QUOTES, 'UTF-8');
                $subject = "Improve Your Match for {$job}";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Improve your match score</h2>
                    <p>Your match score for <strong>{$job}</strong> is {$score}%.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'>Update skills and experience to get better results.</p>
                    </div>
                    <center><a href='{$link}' class='btn'>Update Profile</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'abandoned_job_view':
                $job = htmlspecialchars((string) ($data['job_title'] ?? 'Job'), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string) ($data['link'] ?? $appUrl), ENT_QUOTES, 'UTF-8');
                $subject = "Still interested in {$job}?";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>You viewed this job recently</h2>
                    <p>You can apply in minutes. Stand out by completing your profile.</p>
                    <center><a href='{$link}' class='btn'>Apply Now</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'generic_notification':
                $subject = $data['title'] ?? 'Notification';
                $message = $data['message'] ?? '';
                // Ensure absolute link
                $rawLink = (string)($data['link'] ?? $appUrl);
                if (preg_match('#^https?://#i', $rawLink)) {
                    $link = htmlspecialchars($rawLink, ENT_QUOTES, 'UTF-8');
                } else {
                    $link = htmlspecialchars(rtrim($appUrl, '/') . (str_starts_with($rawLink, '/') ? $rawLink : ('/' . $rawLink)), ENT_QUOTES, 'UTF-8');
                }
                $linkText = $data['link_text'] ?? 'View Details';

                $content = "
                    <h2 style='color:#111827; margin-top:0;'>{$subject}</h2>
                    <p>{$message}</p>
                    <center><a href='{$link}' class='btn'>{$linkText}</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'candidate_published':
                $companyName = htmlspecialchars((string)($_ENV['PORTAL_NAME'] ?? 'Mindware Infotech'), ENT_QUOTES, 'UTF-8');
                $candName = htmlspecialchars((string)($data['candidate_name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8');
                $candEmail = htmlspecialchars((string)($data['candidate_email'] ?? ''), ENT_QUOTES, 'UTF-8');
                $candMobile = htmlspecialchars((string)($data['candidate_mobile'] ?? ''), ENT_QUOTES, 'UTF-8');
                $city = htmlspecialchars((string)($data['candidate_city'] ?? ''), ENT_QUOTES, 'UTF-8');
                $state = htmlspecialchars((string)($data['candidate_state'] ?? ''), ENT_QUOTES, 'UTF-8');
                $country = htmlspecialchars((string)($data['candidate_country'] ?? ''), ENT_QUOTES, 'UTF-8');
                $skillsSummary = htmlspecialchars((string)($data['skills_summary'] ?? ''), ENT_QUOTES, 'UTF-8');
                $expYears = htmlspecialchars((string)($data['experience_years'] ?? ''), ENT_QUOTES, 'UTF-8');
                $eduSummary = htmlspecialchars((string)($data['education_summary'] ?? ''), ENT_QUOTES, 'UTF-8');
                $prefLoc = htmlspecialchars((string)($data['preferred_job_location'] ?? ''), ENT_QUOTES, 'UTF-8');
                $salaryRange = htmlspecialchars((string)($data['expected_salary_range'] ?? ''), ENT_QUOTES, 'UTF-8');
                $notice = htmlspecialchars((string)($data['notice_period'] ?? ''), ENT_QUOTES, 'UTF-8');
                // Absolute links
                $setPwdRaw = (string)($data['set_password_url'] ?? ($appUrl . '/reset-password'));
                $profileRaw = (string)($data['profile_url'] ?? ($appUrl . '/candidate/profile'));
                $setPwd = preg_match('#^https?://#i', $setPwdRaw) ? $setPwdRaw : (rtrim($appUrl, '/') . (str_starts_with($setPwdRaw, '/') ? $setPwdRaw : ('/' . $setPwdRaw)));
                $profileUrl = preg_match('#^https?://#i', $profileRaw) ? $profileRaw : (rtrim($appUrl, '/') . (str_starts_with($profileRaw, '/') ? $profileRaw : ('/' . $profileRaw)));
                $setPwd = htmlspecialchars($setPwd, ENT_QUOTES, 'UTF-8');
                $profileUrl = htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8');

                $subject = 'Profile Published';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Hello {$candName},</h2>
                    <p>This email is to inform you that your candidate profile has been successfully created and published on {$companyName} after processing the resume/CV that was uploaded on your behalf.</p>
                    
                    <div class='info-box'>
                        <p><strong>Profile Status:</strong> Published</p>
                    </div>
                    
                    <p>Your resume was processed automatically by our system, and the following details were extracted and added to your profile:</p>
                    
                    <h3 style='margin:16px 0 8px;'>Candidate Details</h3>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Name:</strong> {$candName}</p>
                        <p style='margin:5px 0;'><strong>Email:</strong> {$candEmail}</p>
                        <p style='margin:5px 0;'><strong>Phone:</strong> {$candMobile}</p>
                        <p style='margin:5px 0;'><strong>Location:</strong> {$city}" . (($state || $country) ? ", {$state}, {$country}" : "") . "</p>
                    </div>
                    
                    <h3 style='margin:16px 0 8px;'>Professional Information</h3>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Skills:</strong> {$skillsSummary}</p>
                        <p style='margin:5px 0;'><strong>Total Experience:</strong> {$expYears} years</p>
                        <p style='margin:5px 0;'><strong>Education:</strong> {$eduSummary}</p>
                        <p style='margin:5px 0;'><strong>Preferred Job Location:</strong> {$prefLoc}</p>
                        <p style='margin:5px 0;'><strong>Expected Salary:</strong> {$salaryRange}</p>
                        <p style='margin:5px 0;'><strong>Notice Period:</strong> {$notice}</p>
                    </div>
                    
                    <h3 style='margin:16px 0 8px;'>Important: Set Your Password to Access Your Profile</h3>
                    <p>Since your profile was created automatically using your resume, no password has been set yet.</p>
                    <p>To log in and access your profile, please create your password using the link below:</p>
                    <center><a href='{$setPwd}' class='btn' style='background-color:#10b981;'>Set Password & Activate Account</a></center>
                    
                    <h3 style='margin:16px 0 8px;'>View or Update Your Profile</h3>
                    <p>After setting your password, you can view, edit, or complete your profile using this link:</p>
                    <center><a href='{$profileUrl}' class='btn'>View Candidate Profile</a></center>
                    <p style='margin-top:12px; font-size:12px; color:#6b7280;'>We strongly recommend completing any missing sections to improve your profile visibility and strength.</p>
                    
                    <h3 style='margin:16px 0 8px;'>What Happens Next?</h3>
                    <ul style='margin:8px 0 0 18px; color:#4b5563;'>
                        <li>Your profile is now visible to verified employers.</li>
                        <li>You may start receiving job alerts and interview opportunities.</li>
                        <li>Our system will automatically match your profile with relevant job openings.</li>
                    </ul>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'daily_digest':
                $summary = $data['summary'] ?? [];
                $summary = is_array($summary) ? $summary : [];
                $items = '';
                $labels = [
                    'job_match' => 'Job matches',
                    'profile_view' => 'Profile views',
                    'application_status' => 'Application updates',
                    'message' => 'New messages',
                ];
                foreach ($summary as $key => $count) {
                    $name = htmlspecialchars((string)($labels[$key] ?? $key), ENT_QUOTES, 'UTF-8');
                    $items .= "<li><strong>{$count}</strong> {$name}</li>";
                }
                $link = htmlspecialchars((string)($data['link'] ?? ($appUrl . '/candidate/notifications')), ENT_QUOTES, 'UTF-8');
                $subject = 'Your Daily Summary';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Here’s your activity summary</h2>
                    <p>Last 24 hours:</p>
                    <ul style='margin:8px 0 0 18px; color:#4b5563;'>{$items}</ul>
                    <center><a href='{$link}' class='btn'>Open Notifications</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'admin_new_candidate':
                $name = htmlspecialchars((string) ($data['candidate_name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars((string) ($data['candidate_email'] ?? ''), ENT_QUOTES, 'UTF-8');
                $link = htmlspecialchars((string) ($data['link'] ?? ($appUrl . '/admin/candidates')), ENT_QUOTES, 'UTF-8');
                $subject = 'New Candidate Registered';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>New Candidate Registered</h2>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Name:</strong> {$name}</p>
                        <p style='margin:5px 0;'><strong>Email:</strong> {$email}</p>
                    </div>
                    <center><a href='{$link}' class='btn'>View Candidates</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'email_verification':
                $code = htmlspecialchars((string) ($data['code'] ?? ''), ENT_QUOTES, 'UTF-8');
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
                $link = htmlspecialchars((string) ($data['reset_link'] ?? ($appUrl . '/reset')), ENT_QUOTES, 'UTF-8');
                $subject = 'Reset your password';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Password Reset</h2>
                    <p>We received a request to reset your password. Click the button below to choose a new password:</p>
                    <center><a href='{$link}' class='btn'>Reset Password</a></center>
                    <p style='margin-top:20px; font-size:12px; color:#6b7280;'>If you didn't request this change, please ignore this email.</p>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];
            
            case 'payment_receipt':
                $paymentId = htmlspecialchars((string)($data['payment_id'] ?? ''), ENT_QUOTES, 'UTF-8');
                $amount = (float)($data['amount'] ?? 0);
                $amountStr = '₹' . number_format($amount, 2);
                $invoiceUrl = htmlspecialchars((string)($data['invoice_url'] ?? ($appUrl . '/employer/invoices')), ENT_QUOTES, 'UTF-8');
                $subject = 'Payment Receipt';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Thank you for your payment</h2>
                    <p>Your payment has been received successfully.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Payment ID:</strong> {$paymentId}</p>
                        <p style='margin:5px 0;'><strong>Amount:</strong> {$amountStr}</p>
                    </div>
                    <center><a href='{$invoiceUrl}' class='btn'>View Invoice</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'interview_scheduled':
                $job = htmlspecialchars((string) ($data['job_title'] ?? 'Interview'), ENT_QUOTES, 'UTF-8');
                $time = htmlspecialchars((string) ($data['scheduled_time'] ?? ''), ENT_QUOTES, 'UTF-8');
                $company = htmlspecialchars((string) ($data['company_name'] ?? 'Mindware Infotech'), ENT_QUOTES, 'UTF-8');
                $location = htmlspecialchars((string) ($data['location'] ?? 'Remote/Online'), ENT_QUOTES, 'UTF-8');
                $meetingLink = htmlspecialchars((string) ($data['meeting_link'] ?? ''), ENT_QUOTES, 'UTF-8');
                $companyWebsite = htmlspecialchars((string) ($data['company_website'] ?? ''), ENT_QUOTES, 'UTF-8');

                $subject = "Interview Scheduled: {$job} at {$company}";

                $meetingHtml = '';
                if ($meetingLink) {
                    $meetingHtml = "<p><strong>Meeting Link:</strong> <a href='{$meetingLink}'>{$meetingLink}</a></p>";
                }

                $companyHtml = $companyWebsite
                    ? "<p style='margin:5px 0;'><strong>Company:</strong> <a href='{$companyWebsite}' target='_blank'>{$company}</a></p>"
                    : "<p style='margin:5px 0;'><strong>Company:</strong> {$company}</p>";

                $cta = rtrim($appUrl, '/') . '/candidate/applications';
                if ($logId > 0) { $cta = self::signClickUrl($logId, $cta); }
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
                    <center><a href='{$cta}' class='btn'>View Application</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'application_status':
                $job = htmlspecialchars((string) ($data['job_title'] ?? 'Job'), ENT_QUOTES, 'UTF-8');
                $status = htmlspecialchars((string) ($data['status'] ?? 'updated'), ENT_QUOTES, 'UTF-8');
                $company = htmlspecialchars((string) ($data['company_name'] ?? 'Mindware Infotech'), ENT_QUOTES, 'UTF-8');

                $subject = "Application Update: {$job} at {$company}";

                $statusColor = '#2563eb';  // Default blue
                if ($status === 'shortlisted')
                    $statusColor = '#059669';
                if ($status === 'rejected')
                    $statusColor = '#dc2626';
                if ($status === 'hired')
                    $statusColor = '#7c3aed';

                $cta = rtrim($appUrl, '/') . '/candidate/applications';
                if ($logId > 0) { $cta = self::signClickUrl($logId, $cta); }
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Application Status Update</h2>
                    <p>Hi {$candidateName},</p>
                    <p>The status of your application for <strong>{$job}</strong> at <strong>{$company}</strong> has been updated.</p>
                    
                    <div style='text-align:center; margin:30px 0;'>
                        <span style='background-color:{$statusColor}; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;'>
                            {$status}
                        </span>
                    </div>
                    <center><a href='{$cta}' class='btn'>View Details</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'job_match':
                $jobTitle = htmlspecialchars((string) ($data['job_title'] ?? 'Job'), ENT_QUOTES, 'UTF-8');
                $matchScore = htmlspecialchars((string) ($data['match_score'] ?? '0'), ENT_QUOTES, 'UTF-8');
                $jobId = $data['job_id'] ?? 0;

                $subject = "New Job Match: {$jobTitle} ({$matchScore}% Match)";
                $cta = rtrim($appUrl, '/') . '/candidate/jobs/' . (int)$jobId;
                if ($logId > 0) { $cta = self::signClickUrl($logId, $cta); }
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>New Job Match Found!</h2>
                    <p>Hi {$candidateName},</p>
                    <p>We found a new job that matches your profile.</p>
                    
                    <div class='info-box'>
                        <h3 style='margin-top:0;'>{$jobTitle}</h3>
                        <p><strong>Match Score:</strong> <span style='color:#059669; font-weight:bold;'>{$matchScore}%</span></p>
                    </div>
                    
                    <center><a href='{$cta}' class='btn'>View Job</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            default:
                // Fallback for other templates
                $subject = $data['subject'] ?? 'Notification';
                $bodyRaw = $data['body'] ?? 'You have a new notification.';
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $bodyRaw, $data)];
        }
    }

    private static function tryExternalTemplate(string $key, array $data): ?array
    {
        $path = __DIR__ . '/../../email_templates/' . $key . '.html';
        if (!is_file($path)) {
            return null;
        }
        $html = @file_get_contents($path);
        if ($html === false) {
            return null;
        }
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $placeholders = [
            '{{brand_name}}'     => (string)($_ENV['PORTAL_NAME'] ?? 'Mindware Infotech'),
            '{{logo_url}}'       => $appUrl . '/assets/images/logo.png',
            '{{user_name}}'      => (string)($data['candidate_name'] ?? $data['user_name'] ?? 'User'),
            '{{company_name}}'   => (string)($data['company_name'] ?? 'Company'),
            '{{job_title}}'      => (string)($data['job_title'] ?? ''),
            '{{job_location}}'   => (string)($data['job_location'] ?? ''),
            '{{interview_date}}' => (string)($data['interview_date'] ?? ''),
            '{{interview_time}}' => (string)($data['interview_time'] ?? ''),
            '{{interview_mode}}' => (string)($data['interview_mode'] ?? ''),
            '{{match_score}}'    => (string)($data['match_score'] ?? ''),
            '{{cta_link}}'       => (string)($data['link'] ?? $data['cta_link'] ?? $appUrl),
            '{{support_email}}'  => (string)($_ENV['SUPPORT_EMAIL'] ?? 'gm@mindwareinfotech.com'),
            '{{support_phone}}'  => (string)($_ENV['SUPPORT_NUMBER'] ?? '+91 8527522688'),
            '{{unsubscribe_link}}' => $appUrl . '/settings/notifications',
            '{{year}}'           => date('Y'),
            '{{jobs_html}}'      => (string)($data['jobs_html'] ?? ''),
            '{{featured_jobs_html}}' => (string)($data['featured_jobs_html'] ?? ''),
            '{{digest_items_html}}' => (string)($data['digest_items_html'] ?? ''),
            '{{status_label}}'   => (string)($data['status'] ?? ''),
            '{{status_color}}'   => (string)($data['status_color'] ?? '#2563eb'),
            '{{view_details_link}}' => (string)($data['view_details_link'] ?? $appUrl),
            '{{add_calendar_link}}' => (string)($data['add_calendar_link'] ?? $appUrl),
            '{{alert_activity}}' => (string)($data['alert_activity'] ?? ''),
            '{{alert_time}}'     => (string)($data['alert_time'] ?? ''),
            '{{alert_location}}' => (string)($data['alert_location'] ?? ''),
            '{{headline}}'       => (string)($data['headline'] ?? ''),
        ];
        $body = strtr($html, $placeholders);
        $subject = (string)($data['subject'] ?? ucwords(str_replace('_', ' ', $key)));
        return ['subject' => $subject, 'body' => $body];
    }

    private static function logEmail(string $templateKey, string $subject, string $content, array $data, bool $success, ?string $error, ?int $employerId, ?int $candidateUserId): int
    {
        try {
            $db = Database::getInstance();
            $status = $success ? 'sent' : 'failed';
            // If error is 'sending', it means it's a pending state we just invented
            if ($error === 'sending')
                $status = 'pending';

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
            $sql = 'INSERT INTO notification_logs (employer_id, candidate_id, channel, template_key, subject, content, status, metadata, error_message, created_at) VALUES (:employer_id, :candidate_id, :channel, :template_key, :subject, :content, :status, :metadata, :error_message, NOW())';
            $db->query($sql, $params);
            return (int) $db->lastInsertId();
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
        $msg = "Your application for '{$jobTitle}' has been " . (isset($statusLabels[$status]) ? $statusLabels[$status] : $status) . '.';
        self::send($userId, 'application_update', 'Application Update', $msg, [
            'job_title' => $jobTitle,
            'status' => $status,
            'link' => '/candidate/applications'
        ], '/candidate/applications', ['in_app', 'email', 'push']);
    }

    public static function notifyInterviewScheduled(int $userId, string $jobTitle, string $dateTime): void
    {
        $msg = "Your interview for '{$jobTitle}' is scheduled for {$dateTime}.";
        self::send($userId, 'interview_scheduled', 'Interview Scheduled', $msg, [
            'job_title' => $jobTitle,
            'scheduled_time' => $dateTime,
            'link' => '/candidate/applications'
        ], '/candidate/applications', ['in_app', 'email', 'push']);
    }

    public static function notifyNewMessage(int $userId, string $employerName): void
    {
        $msg = "You have a new message from {$employerName}.";
        self::send($userId, 'message', 'New Message', $msg, [
            'from_name' => $employerName,
            'preview' => $msg,
            'link' => '/candidate/chat'
        ], '/candidate/chat', ['in_app', 'email', 'push']);
    }

    public static function notifyProfileView(int $userId, string $employerName): void
    {
        $msg = "Your profile was viewed by {$employerName}.";
        self::send($userId, 'profile_view', 'Profile Viewed', $msg, [
            'employer_name' => $employerName,
            'link' => '/candidate/profile/complete'
        ], '/candidate/profile/complete', ['in_app', 'email', 'push']);
    }

    private static function signClickUrl(int $logId, string $targetUrl): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $secret = $_ENV['APP_KEY'] ?? 'secret';
        $hash = hash_hmac('sha256', $logId . '|' . $targetUrl, $secret);
        return rtrim($appUrl, '/') . '/notifications/track/click?id=' . $logId . '&h=' . $hash . '&url=' . urlencode($targetUrl);
    }

    private static function signOpenPixel(int $logId): string
    {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $secret = $_ENV['APP_KEY'] ?? 'secret';
        $hash = hash_hmac('sha256', (string)$logId, $secret);
        return rtrim($appUrl, '/') . '/notifications/track/open?id=' . $logId . '&h=' . $hash;
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
            $sql = 'INSERT INTO notification_logs (employer_id, candidate_id, channel, template_key, subject, content, status, metadata, error_message, created_at) VALUES (:employer_id, :candidate_id, :channel, :template_key, :subject, :content, :status, :metadata, :error_message, NOW())';
            $db->query($sql, $params);
            return (int) $db->lastInsertId();
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
        if (!$redis->isAvailable())
            return null;
        $data = $redis->get("interview_join:{$token}");
        if (!$data)
            return null;
        $payload = json_decode($data, true);
        return is_array($payload) ? $payload : null;
    }
}
