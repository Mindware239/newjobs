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
use App\Helpers\SslHelper;

class NotificationService
{
    public static function registerToken(int $userId, string $token, string $device = '', string $browser = ''): bool
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO user_push_tokens (user_id, token, device, browser, is_active, created_at, updated_at)
                 VALUES (:user_id, :token, :device, :browser, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE device = VALUES(device), browser = VALUES(browser), is_active = 1, updated_at = NOW()",
                [
                    'user_id' => $userId,
                    'token' => $token,
                    'device' => mb_substr($device, 0, 50),
                    'browser' => mb_substr($browser, 0, 50)
                ]
            );
            $db->query("UPDATE users SET fcm_token = :token WHERE id = :id", ['token' => $token, 'id' => $userId]);
            return true;
        } catch (\Throwable $t) {
            error_log("NotificationService::registerToken error: " . $t->getMessage());
            return false;
        }
    }

    public static function unregisterToken(int $userId, string $token): bool
    {
        try {
            $db = Database::getInstance();
            $db->query("UPDATE user_push_tokens SET is_active = 0, updated_at = NOW() WHERE user_id = :uid AND token = :token", [
                'uid' => $userId,
                'token' => $token
            ]);
            return true;
        } catch (\Throwable $t) {
            error_log("NotificationService::unregisterToken error: " . $t->getMessage());
            return false;
        }
    }

    public static function updatePreferences(int $userId, array $prefs): bool
    {
        try {
            $db = Database::getInstance();
            $db->query("UPDATE users SET notification_preferences = :prefs WHERE id = :id", [
                'prefs' => json_encode($prefs, JSON_UNESCAPED_UNICODE),
                'id' => $userId
            ]);
            return true;
        } catch (\Throwable $t) {
            error_log("NotificationService::updatePreferences error: " . $t->getMessage());
            return false;
        }
    }

    public static function notify(int $userId, string $type, string $title, string $message, ?string $link = null, array $data = []): void
    {
        Notification::create($userId, $type, $title, $message, $link, $data);
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
                    self::notify($userId, $type, $title, $message, $link, $data);
                    self::logNotification($userId, $type, 'in_app', $referenceId, 'sent');
                }
            }

            // 2. Email Notification
            if ($shouldSend('email') && !empty($user->attributes['email'])) {
                if (self::isDailyEmailCapped($userId)) {
                    // Do not send further emails for today
                } else {
                    if (!self::isDuplicate($userId, $type, 'email', $referenceId)) {
                        $templateKey = $data['email_template'] ?? $type;
                        
                        // Prepare full data set for templates
                        $appUrl = rtrim(getenv('APP_URL') ?: 'https://jobsence.com', '/');
                        $brandName = getenv('PORTAL_NAME') ?: 'Jobsence';
                        
                        $emailData = array_merge([
                            'title' => $title,
                            'message' => $message,
                            'link' => $link ?? ($data['link'] ?? null),
                            'app_url' => $appUrl,
                            'brand_name' => $brandName,
                            'user_name' => $user->attributes['first_name'] ?? $user->attributes['name'] ?? 'User',
                            'year' => date('Y'),
                            'support_email' => getenv('SUPPORT_EMAIL') ?: 'gm@jobsence.com',
                            'support_phone' => getenv('SUPPORT_NUMBER') ?: '+91 9717122688',
                            'unsubscribe_link' => $appUrl . '/unsubscribe'
                        ], $data);
                        
                        // Clean up links and photos to be absolute
                        if (isset($emailData['candidate_photo']) && !str_starts_with($emailData['candidate_photo'], 'http')) {
                            $emailData['candidate_photo'] = $appUrl . '/' . ltrim($emailData['candidate_photo'], '/');
                        }
                        if (isset($emailData['company_logo']) && !str_starts_with($emailData['company_logo'], 'http')) {
                            $emailData['company_logo'] = $appUrl . '/' . ltrim($emailData['company_logo'], '/');
                        }
                        
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
            
            // Resolve correct ID for notification_logs
            $user = $db->fetchOne('SELECT role FROM users WHERE id = :id', ['id' => $userId]);
            if (!$user) return false;
            
            $role = strtolower((string)$user['role']);
            $whereClause = '';
            $params = ['type' => $type, 'channel' => $channel];

            if ($role === 'candidate') {
                $cand = $db->fetchOne('SELECT id FROM candidates WHERE user_id = :uid', ['uid' => $userId]);
                $cid = (int)($cand['id'] ?? 0);
                if (!$cid) return false;
                $whereClause = 'candidate_id = :cid';
                $params['cid'] = $cid;
            } elseif ($role === 'employer') {
                $emp = $db->fetchOne('SELECT id FROM employers WHERE user_id = :uid', ['uid' => $userId]);
                $eid = (int)($emp['id'] ?? 0);
                if (!$eid) return false;
                $whereClause = 'employer_id = :eid';
                $params['eid'] = $eid;
            } else {
                return false;
            }

            // Check for exact same event within last 1 hour (short term dedup)
            $sql = "SELECT id FROM notification_logs 
                    WHERE {$whereClause} 
                    AND event_type = :type 
                    AND channel = :channel 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";

            if ($refId) {
                $sql .= ' AND reference_id = :ref';
                $params['ref'] = $refId;
            }

            $result = $db->fetchOne($sql, $params);
            return !empty($result);
        } catch (\Throwable $e) {
            error_log('NotificationService::isDuplicate error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check rate limits (1 per 24h per event type)
     */
    public static function isRateLimited(int $userId, string $type, ?string $refId): bool
    {
        // Only rate limit these specific noisy events
        $rateLimitedEvents = ['job_match', 'profile_view', 'profile_update', 'application_status', 'profile_reminder'];
        if (!in_array($type, $rateLimitedEvents)) {
            return false;
        }

        try {
            $db = Database::getInstance();
            
            // Resolve correct ID for notification_logs
            $user = $db->fetchOne('SELECT role FROM users WHERE id = :id', ['id' => $userId]);
            if (!$user) return false;
            
            $role = strtolower((string)$user['role']);
            $whereClause = '';
            $params = ['type' => $type];

            if ($role === 'candidate') {
                $cand = $db->fetchOne('SELECT id FROM candidates WHERE user_id = :uid', ['uid' => $userId]);
                $cid = (int)($cand['id'] ?? 0);
                if (!$cid) return false;
                $whereClause = 'candidate_id = :cid';
                $params['cid'] = $cid;
            } elseif ($role === 'employer') {
                $emp = $db->fetchOne('SELECT id FROM employers WHERE user_id = :uid', ['uid' => $userId]);
                $eid = (int)($emp['id'] ?? 0);
                if (!$eid) return false;
                $whereClause = 'employer_id = :eid';
                $params['eid'] = $eid;
            } else {
                return false;
            }

            // Check if ANY channel sent this event in last 24h
            $sql = "SELECT id FROM notification_logs 
                    WHERE {$whereClause} 
                    AND event_type = :type 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";

            // If reference ID exists (e.g. Job ID), allow same event type but different reference
            if ($refId) {
                $sql .= ' AND reference_id = :ref';
                $params['ref'] = $refId;
            }

            $result = $db->fetchOne($sql, $params);
            return !empty($result);
        } catch (\Throwable $e) {
            error_log('NotificationService::isRateLimited error: ' . $e->getMessage());
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
            
            // Resolve correct ID
            $user = $db->fetchOne('SELECT role FROM users WHERE id = :id', ['id' => $userId]);
            if (!$user) return false;
            
            $role = strtolower((string)$user['role']);
            $whereClause = '';
            $params = [];

            if ($role === 'candidate') {
                $cand = $db->fetchOne('SELECT id FROM candidates WHERE user_id = :uid', ['uid' => $userId]);
                $cid = (int)($cand['id'] ?? 0);
                if (!$cid) return false;
                $whereClause = 'candidate_id = :cid';
                $params['cid'] = $cid;
            } elseif ($role === 'employer') {
                $emp = $db->fetchOne('SELECT id FROM employers WHERE user_id = :uid', ['uid' => $userId]);
                $eid = (int)($emp['id'] ?? 0);
                if (!$eid) return false;
                $whereClause = 'employer_id = :eid';
                $params['eid'] = $eid;
            } else {
                return false;
            }

            $count = (int)($db->fetchOne(
                "SELECT COUNT(*) as c FROM notification_logs 
                 WHERE {$whereClause} 
                 AND channel = 'email' 
                 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                $params
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
                $cand = $db->fetchOne('SELECT id FROM candidates WHERE user_id = :uid', ['uid' => $userId]);
                $candidateId = (int)($cand['id'] ?? 0) ?: null;
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

        // 4. Send Asynchronously
        $attachments = is_array($templateData['attachments'] ?? null) ? $templateData['attachments'] : [];
        $success = MailService::sendEmailAsync($to, $subject, $body, null, null, $attachments);

        // 5. Update status (will be updated by worker, but we can mark as 'queued')
        self::updateLogStatus($logId, $success ? 'queued' : 'failed', $success ? null : 'queue_failed');

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
        $queueDriver = getenv('QUEUE_DRIVER') ?: 'sync';

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
        $queueDriver = getenv('QUEUE_DRIVER') ?: 'sync';

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
                $message = "New Match: {$jobTitle} matches your profile! Apply now: " . (getenv('APP_URL') ?: 'http://localhost:8000') . $link;
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

            // SSL Fix: Use SslHelper
            $verifySsl = SslHelper::resolveCaBundle();

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
                    'verify' => $verifySsl,
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
            $verify = SslHelper::resolveCaBundle();
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
        return SslHelper::resolveCaBundle() ?: true;
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
        return '&copy; ' . date('Y') . ' Jobsence. All rights reserved.';
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
        $appUrl = rtrim(getenv('APP_URL') ?: 'https://jobsence.com', '/');
        $logo = $appUrl . '/uploads/jobsence.png';

        // Use employer company logo if available, otherwise default
        $companyName = $data['company_name'] ?? 'Jobsence';
        $companyLogo = !empty($data['company_logo']) ? $data['company_logo'] : null;

        if ($companyLogo && !str_starts_with($companyLogo, 'http')) {
            $companyLogo = $appUrl . '/storage/' . ltrim($companyLogo, '/');
        }

        $headerLogo = $companyLogo ?: $logo;
        $logoWhite = $appUrl . '/uploads/jobsence.png'; // Assume this exists or will be styled
        $supportEmail = getenv('SUPPORT_EMAIL') ?: 'gm@jobsence.com';
        $supportNumber = getenv('SUPPORT_NUMBER') ?: '+91 9717122688';
        
        $pixel = '';
        if (!empty($data['log_id'])) {
            $pixel = '<img src="' . self::signOpenPixel((int)$data['log_id']) . '" width="1" height="1" alt="" style="display:none" />';
        }

        // Use standard layout file if it exists, otherwise use fallback
        $layoutPath = __DIR__ . '/../../email_templates/layout.html';
        if (is_file($layoutPath)) {
            $layout = file_get_contents($layoutPath);
            
            // Build placeholder map
            $placeholders = [];
            foreach ($data as $k => $v) {
                if (is_scalar($v)) {
                    $placeholders["{{{$k}}}"] = (string)$v;
                }
            }
            
            // Global defaults
            $globalPlaceholders = [
                '{{subject}}' => $title,
                '{{preheader}}' => $data['preheader'] ?? substr(strip_tags($content), 0, 150),
                '{{logo_url}}' => $headerLogo,
                '{{logo_url_white}}' => $logoWhite,
                '{{app_url}}' => $appUrl,
                '{{brand_name}}' => 'Jobsence',
                '{{content_html}}' => $content,
                '{{support_email}}' => $supportEmail,
                '{{support_phone}}' => $supportNumber,
                '{{unsubscribe_link}}' => $appUrl . '/unsubscribe',
                '{{year}}' => date('Y'),
                '{{headline}}' => $title
            ];
            
            $allPlaceholders = array_merge($globalPlaceholders, $placeholders);
            return strtr($layout, $allPlaceholders) . $pixel;
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
        .footer { background: #111827; padding: 30px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #ff5a36; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .btn:hover { background-color: #e44d2d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{$headerLogo}" alt="Jobsence">
        </div>
        <div class="content">
            {$content}
        </div>
        <div class="footer">
            <strong style="color:#ffffff; font-size: 16px;">Jobsence</strong>
            <p>Your Trusted Job Portal for Career Opportunities</p>
            <p>Email: {$supportEmail} | Phone: {$supportNumber}</p>
            <p>&copy; 2026 Jobsence. All rights reserved.</p>
            {$pixel}
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function notifyApplicationSubmitted(int $applicationId): void
    {
        try {
            $db = Database::getInstance();
            $app = $db->fetchOne(
                "SELECT a.*, j.title as job_title, j.category as job_category, j.job_type, j.salary_min, j.salary_max, j.currency, j.min_experience, j.max_experience, j.locations as job_locations, j.slug as job_slug,
                        e.company_name, e.logo_url as company_logo, e.description as company_description, e.user_id as employer_user_id,
                        c.id as candidate_id, c.full_name, c.profile_strength, c.resume_url, c.city, c.state, c.skills_data, c.education_data, c.experience_data, c.profile_picture,
                        c.portfolio_url, c.github_url, c.linkedin_url, c.website_url, c.is_verified, c.self_introduction, c.preferred_job_location, 
                        c.expected_salary_min, c.expected_salary_max, c.notice_period, c.preferences_data, c.professional_title,
                        u.email as candidate_email, COALESCE(NULLIF(u.phone, ''), NULLIF(c.mobile, '')) as candidate_mobile
                 FROM applications a
                 JOIN jobs j ON a.job_id = j.id
                 JOIN employers e ON j.employer_id = e.id
                 JOIN candidates c ON a.candidate_user_id = c.user_id
                 JOIN users u ON c.user_id = u.id
                 WHERE a.id = :id",
                ['id' => $applicationId]
            );

            if (!$app) {
                error_log("NotificationService::notifyApplicationSubmitted - App not found for ID: " . $applicationId);
                return;
            }

            $appUrl = rtrim(getenv('APP_URL') ?: 'https://jobsence.com', '/');
            
            // Format Salary
            $salaryStr = 'Negotiable';
            if (!empty($app['salary_min']) || !empty($app['salary_max'])) {
                $currency = $app['currency'] ?: 'INR';
                $salaryStr = $currency . ' ' . number_format((int)$app['salary_min']) . ' - ' . number_format((int)$app['salary_max']);
            }

            // Format Experience
            $expStr = 'Not specified';
            if (isset($app['min_experience']) || isset($app['max_experience'])) {
                $expStr = (int)$app['min_experience'] . ' - ' . (int)$app['max_experience'] . ' Years';
            }

            // 1. Candidate Notification
            $profileWarningHtml = '';
            if ((int)$app['profile_strength'] < 80) {
                $missing = [];
                if (empty($app['resume_url'])) $missing[] = 'Resume';
                if (empty($app['skills_data']) || $app['skills_data'] == '[]') $missing[] = 'Skills';
                if (empty($app['education_data']) || $app['education_data'] == '[]') $missing[] = 'Education';
                
                $missingStr = !empty($missing) ? implode(', ', $missing) : 'Profile Details';
                $profileWarningHtml = "
                    <div style='background-color: #fff7ed; border-left: 4px solid #ff5a36; padding: 20px; margin: 25px 0; border-radius: 4px;'>
                        <p style='margin: 0; font-weight: 700; color: #c2410c;'>Boost your chances!</p>
                        <p style='margin: 5px 0 0 0; font-size: 14px; color: #9a3412;'>Candidates with complete profiles are 4x more likely to get shortlisted. <strong>Missing: {$missingStr}</strong></p>
                        <a href='{$appUrl}/candidate/profile/edit' style='display: inline-block; margin-top: 10px; color: #ff5a36; font-weight: 700; font-size: 14px; text-decoration: none;'>Complete Profile &rarr;</a>
                    </div>";
            }

            $candData = [
                'job_title' => $app['job_title'],
                'company_name' => $app['company_name'],
                'application_id' => 'APP-' . date('Y') . '-' . $app['id'],
                'application_status' => strtoupper($app['status']),
                'resume_status' => !empty($app['resume_url']) ? 'Uploaded & Verified' : 'Missing',
                'applied_at' => date('d M Y, h:i A', strtotime($app['applied_at'])),
                'job_location' => $app['job_locations'] ?: 'Multiple Locations',
                'job_type' => $app['job_type'],
                'salary_range' => $salaryStr,
                'experience_required' => $expStr,
                'profile_warning_html' => $profileWarningHtml,
                'job_slug' => $app['job_slug'],
                'company_logo' => $app['company_logo'] ? ($appUrl . $app['company_logo']) : ($appUrl . '/uploads/jobsence.png'),
                'company_description' => substr($app['company_description'] ?? 'Verified Employer on Jobsence Recruitment Platform.', 0, 200) . '...',
                'email_template' => 'candidate_application_submitted'
            ];

            self::send((int)$app['candidate_user_id'], 'application_submitted', "Application Submitted: {$app['job_title']}", "Your application has been received.", $candData);

            // 2. Employer Notification
            
            // Skills formatting
            $skills = json_decode($app['skills_data'] ?? '[]', true);
            $skillsHtml = '';
            if (!empty($skills) && is_array($skills)) {
                foreach (array_slice($skills, 0, 10) as $skill) {
                    $name = is_array($skill) ? ($skill['name'] ?? '') : $skill;
                    if ($name) {
                        $skillsHtml .= "<span style='display:inline-block; background:#f1f5f9; padding:5px 12px; border-radius:6px; margin-right:6px; margin-bottom:6px; font-size:12px; color:#334155; font-weight:600; border:1px solid #e2e8f0;'>{$name}</span>";
                    }
                }
            } else {
                $skillsHtml = "<span style='color: #64748b; font-size: 13px;'>No specific skills listed.</span>";
            }

            // Experience Summary
            $expData = json_decode($app['experience_data'] ?? '[]', true);
            $latestExp = !empty($expData) ? $expData[0] : null;
            $expSummary = 'Not specified';
            if ($latestExp) {
                $expSummary = ($latestExp['job_title'] ?? $latestExp['title'] ?? 'Role') . " at " . ($latestExp['company_name'] ?? $latestExp['company'] ?? 'Company');
            }

            // Preferences
            $prefs = json_decode($app['preferences_data'] ?? '[]', true);
            $workMode = $prefs['work_mode'] ?? 'Not specified';
            $prefRole = $prefs['preferred_role'] ?? $app['professional_title'] ?? 'Not specified';

            // Social Links
            $socialLinksHtml = '';
            if ($app['linkedin_url']) $socialLinksHtml .= "<a href='{$app['linkedin_url']}' style='margin-right:10px; display:inline-block;'><img src='https://cdn-icons-png.flaticon.com/32/174/174857.png' width='20' height='20' alt='LinkedIn'></a>";
            if ($app['github_url']) $socialLinksHtml .= "<a href='{$app['github_url']}' style='margin-right:10px; display:inline-block;'><img src='https://cdn-icons-png.flaticon.com/32/25/25231.png' width='20' height='20' alt='GitHub'></a>";
            if ($app['portfolio_url'] || $app['website_url']) $socialLinksHtml .= "<a href='" . ($app['portfolio_url'] ?: $app['website_url']) . "' style='margin-right:10px; display:inline-block;'><img src='https://cdn-icons-png.flaticon.com/32/542/542638.png' width='20' height='20' alt='Portfolio'></a>";

            // Notice Period
            $notice = 'Immediate';
            if ((int)$app['notice_period'] > 0) {
                $notice = (int)$app['notice_period'] . ' Days';
            }

            $empData = [
                'candidate_name' => $app['full_name'],
                'candidate_photo' => $app['profile_picture'] ? ($appUrl . '/' . ltrim($app['profile_picture'], '/')) : ($appUrl . '/assets/images/avatar-placeholder.png'),
                'job_title' => $app['job_title'],
                'job_id' => $app['job_id'],
                'application_id' => $app['id'], // Raw ID for links
                'app_id_display' => 'APP-' . date('Y') . '-' . $app['id'],
                'applied_at' => date('d M Y, h:i A', strtotime($app['applied_at'])),
                'match_score' => $app['match_score'] ?? 0,
                'candidate_email' => $app['candidate_email'],
                'candidate_mobile' => !empty($app['candidate_mobile']) ? $app['candidate_mobile'] : 'Not Provided',
                'experience' => self::formatExperienceStatic($app['experience_data']), 
                'latest_role' => $expSummary,
                'location' => $app['city'] . ($app['state'] ? ", {$app['state']}" : ""),
                'preferred_location' => $app['preferred_job_location'] ?: 'Anywhere',
                'education' => self::formatEducationStatic($app['education_data']),
                'skills' => $skillsHtml,
                'profile_completion' => $app['profile_strength'],
                'resume_status' => !empty($app['resume_url']) ? 'Uploaded' : 'Missing',
                'candidate_id' => $app['candidate_id'],
                'resume_link' => $app['resume_url'] ? ($appUrl . '/employer/candidates/' . $app['candidate_id'] . '/resume') : '#',
                'is_verified' => (int)$app['is_verified'] ? 'VERIFIED' : 'PENDING',
                'verification_color' => (int)$app['is_verified'] ? '#059669' : '#94a3b8',
                'profile_summary' => substr($app['self_introduction'] ?? 'No summary provided.', 0, 250) . (strlen($app['self_introduction'] ?? '') > 250 ? '...' : ''),
                'expected_salary' => $app['expected_salary_min'] ? ($app['currency'] ?: 'INR') . ' ' . number_format((int)$app['expected_salary_min']) . ($app['expected_salary_max'] ? ' - ' . number_format((int)$app['expected_salary_max']) : '') : 'Negotiable',
                'notice_period' => $notice,
                'work_mode' => $workMode,
                'preferred_role' => $prefRole,
                'social_links_html' => $socialLinksHtml,
                'email_template' => 'employer_application_received'
            ];

            self::send((int)$app['employer_user_id'], 'application_received', "New Applicant: {$app['full_name']} - {$app['job_title']}", "New application received.", $empData);

            // 3. Admin Notification
            $adminData = [
                'job_title' => $app['job_title'],
                'candidate_name' => $app['full_name'],
                'candidate_email' => $app['candidate_email'],
                'company_name' => $app['company_name'],
                'applied_at' => date('d M Y, h:i A', strtotime($app['applied_at'])),
                'match_score' => $app['match_score'] ?? 0,
                'profile_completion' => $app['profile_strength'],
                'resume_status' => !empty($app['resume_url']) ? 'Yes' : 'No',
                'application_id' => $app['id'],
                'email_template' => 'admin_application_alert'
            ];

            $admins = $db->fetchAll("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
            foreach ($admins as $admin) {
                self::send((int)$admin['id'], 'admin_application_alert', "Global Alert: New Application for {$app['job_title']}", "A new application has been submitted on the portal.", $adminData);
            }

        } catch (\Throwable $t) {
            error_log("NotificationService::notifyApplicationSubmitted error: " . $t->getMessage());
        }
    }

    private static function renderTemplate(string $key, array $data): array
    {
        $appUrl = rtrim(getenv('APP_URL') ?: 'https://jobsence.com', '/');
        
        // Ensure standard keys are present
        $data['app_url'] = $appUrl;
        $data['year'] = date('Y');
        $data['brand_name'] = getenv('PORTAL_NAME') ?: 'Jobsence';
        
        $candidateName = htmlspecialchars((string) ($data['candidate_name'] ?? $data['user_name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8');
        $data['candidate_name'] = $candidateName;
        $data['user_name'] = $candidateName;
        $logId = (int)($data['log_id'] ?? 0);

        // Try high-priority application templates first
        $specialTemplates = [
            'candidate_application_submitted',
            'employer_application_received',
            'admin_application_alert'
        ];

        if (in_array($key, $specialTemplates) || in_array($data['email_template'] ?? '', $specialTemplates)) {
            $tplKey = $data['email_template'] ?? $key;
            $external = self::tryExternalTemplate($tplKey, $data);
            if ($external) return $external;
        }

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
                $company = htmlspecialchars((string) (getenv('PORTAL_NAME') ?: 'Jobsence'), ENT_QUOTES, 'UTF-8');
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
                $subject = 'Welcome to Jobsence';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Welcome, {$candidateName}!</h2>
                    <p>Thanks for joining Jobsence. We're excited to help you find your next career opportunity.</p>
                    <p>Complete your profile to get matched with top employers.</p>
                    <center><a href='{$appUrl}/login' class='btn'>Login to Your Account</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'employer_welcome':
                $subject = 'Welcome to Jobsence';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Welcome to Jobsence!</h2>
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
                $data['missing_fields_list'] = "<ul>{$items}</ul>"; // Inject into data for HTML template
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

            case 'employer_job_match':
                $job = htmlspecialchars((string)($data['job_title'] ?? 'your job'), ENT_QUOTES, 'UTF-8');
                $count = (int)($data['count'] ?? 0);
                $link = htmlspecialchars((string)($data['details_url'] ?? $data['link'] ?? ($appUrl . '/employer/jobs')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $link = self::signClickUrl($logId, $link); }
                $subject = "Relevant candidates found for {$job}";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Relevant candidates found</h2>
                    <p>Your job post <strong>{$job}</strong> has matching candidates.</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Matching candidates:</strong> {$count}</p>
                    </div>
                    <center><a href='{$link}' class='btn'>Review Candidates</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'candidate_match_employer':
                $candidate = htmlspecialchars((string)($data['candidate_name'] ?? 'Candidate'), ENT_QUOTES, 'UTF-8');
                $candidateTitle = htmlspecialchars((string)($data['candidate_title'] ?? ''), ENT_QUOTES, 'UTF-8');
                $candidateLocation = htmlspecialchars((string)($data['candidate_location'] ?? ''), ENT_QUOTES, 'UTF-8');
                $candidateSkills = htmlspecialchars((string)($data['candidate_skills_text'] ?? ''), ENT_QUOTES, 'UTF-8');
                $job = htmlspecialchars((string)($data['job_title'] ?? 'your job'), ENT_QUOTES, 'UTF-8');
                $profileUrl = htmlspecialchars((string)($data['candidate_profile_url'] ?? $data['details_url'] ?? $appUrl), ENT_QUOTES, 'UTF-8');
                $resumeUrl = htmlspecialchars((string)($data['candidate_resume_url'] ?? ''), ENT_QUOTES, 'UTF-8');
                $photoUrl = htmlspecialchars((string)($data['candidate_photo_url'] ?? ''), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $profileUrl = self::signClickUrl($logId, $profileUrl); }
                $resumeHtml = $resumeUrl ? "<a href='{$resumeUrl}' class='btn' style='background:#10b981; margin-left:8px;'>View Resume</a>" : '';
                $photoHtml = $photoUrl ? "<img src='{$photoUrl}' alt='{$candidate}' style='width:64px;height:64px;border-radius:50%;object-fit:cover;margin-bottom:10px;'>" : '';
                $subject = "Candidate match for {$job}: {$candidate}";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>New candidate match</h2>
                    <p>A relevant candidate matches your job post <strong>{$job}</strong>.</p>
                    <div class='info-box'>
                        {$photoHtml}
                        <p style='margin:5px 0;'><strong>Name:</strong> {$candidate}</p>
                        <p style='margin:5px 0;'><strong>Title:</strong> {$candidateTitle}</p>
                        <p style='margin:5px 0;'><strong>Location:</strong> {$candidateLocation}</p>
                        <p style='margin:5px 0;'><strong>Skills:</strong> {$candidateSkills}</p>
                    </div>
                    <center><a href='{$profileUrl}' class='btn'>View Candidate Profile</a>{$resumeHtml}</center>
                    <p style='margin-top:14px; font-size:12px; color:#6b7280;'>Open the profile to review full details and invite or shortlist the candidate.</p>
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
                $companyName = htmlspecialchars((string)(getenv('PORTAL_NAME') ?: 'Jobsence'), ENT_QUOTES, 'UTF-8');
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
                    'job_match' => 'New job matches identified',
                    'profile_view' => 'Employers viewed your profile',
                    'application_status' => 'Application status updates',
                    'message' => 'New professional messages',
                ];
                foreach ($summary as $type => $count) {
                    $name = htmlspecialchars((string)($labels[$type] ?? $type), ENT_QUOTES, 'UTF-8');
                    $items .= "<li><strong>{$count}</strong> {$name}</li>";
                }
                $link = htmlspecialchars((string)($data['link'] ?? ($appUrl . '/candidate/notifications')), ENT_QUOTES, 'UTF-8');
                $subject = 'Daily Activity Summary';
                $data['preheader'] = 'Your professional activity summary for the last 24 hours.';
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Daily Activity Overview</h2>
                    <p>Here is a summary of your professional activity on our platform for the last 24 hours:</p>
                    <ul style='margin:16px 0 24px 18px; color:#4b5563; line-height:1.8;'>{$items}</ul>
                    <p>Click below to view all notifications and take action.</p>
                    <center><a href='{$link}' class='btn'>View All Notifications</a></center>
                ";
                return ['subject' => $subject, 'body' => self::wrapHtml($subject, $content, $data)];

            case 'admin_new_candidate':
                $name = htmlspecialchars((string) ($data['candidate_name'] ?? 'A user'), ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars((string) ($data['candidate_email'] ?? ''), ENT_QUOTES, 'UTF-8');
                $source = htmlspecialchars((string) ($data['source'] ?? 'Website'), ENT_QUOTES, 'UTF-8');
                $mobile = htmlspecialchars((string) ($data['candidate_mobile'] ?? 'Not Provided'), ENT_QUOTES, 'UTF-8');
                $date = date('d M Y, h:i A');
                $link = htmlspecialchars((string) ($data['link'] ?? ($appUrl . '/admin/candidates')), ENT_QUOTES, 'UTF-8');
                if ($logId > 0) { $link = self::signClickUrl($logId, $link); }
                $subject = "New Candidate Registered: {$name}";
                $content = "
                    <h1 style='color: #111827; font-size: 22px; font-weight: 700; margin-bottom: 20px;'>New Candidate Alert</h1>
                    <p style='color: #4b5563; font-size: 16px;'>A new candidate has just registered on the Jobsence portal.</p>
                    
                    <div class='info-box' style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 25px 0;'>
                        <table width='100%' style='border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; color: #64748b; font-size: 14px;' width='40%'>Name:</td>
                                <td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600;'>{$name}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #64748b; font-size: 14px;'>Email:</td>
                                <td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600;'>{$email}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #64748b; font-size: 14px;'>Mobile:</td>
                                <td style='padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600;'>{$mobile}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #64748b; font-size: 14px;'>Source:</td>
                                <td style='padding: 8px 0; color: #ff5a36; font-size: 14px; font-weight: 700;'>{$source}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; color: #64748b; font-size: 14px;'>Date:</td>
                                <td style='padding: 8px 0; color: #111827; font-size: 14px;'>{$date}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <center><a href='{$link}' class='btn' style='background: #0f172a; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: 600;'>View Candidate Profile</a></center>
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
                $company = htmlspecialchars((string) ($data['company_name'] ?? 'Jobsence'), ENT_QUOTES, 'UTF-8');
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
                $company = htmlspecialchars((string) ($data['company_name'] ?? 'Jobsence'), ENT_QUOTES, 'UTF-8');

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

            case 'application_withdrawn':
                $job = htmlspecialchars((string) ($data['job_title'] ?? 'Job'), ENT_QUOTES, 'UTF-8');
                $candidate = htmlspecialchars((string) ($data['candidate_name'] ?? 'A candidate'), ENT_QUOTES, 'UTF-8');
                $appId = htmlspecialchars((string) ($data['application_id'] ?? ''), ENT_QUOTES, 'UTF-8');
                $subject = "Application Withdrawn: {$candidate} for {$job}";
                $content = "
                    <h2 style='color:#111827; margin-top:0;'>Application Withdrawn</h2>
                    <p>A candidate has withdrawn their application for the following position:</p>
                    <div class='info-box'>
                        <p style='margin:5px 0;'><strong>Candidate:</strong> {$candidate}</p>
                        <p style='margin:5px 0;'><strong>Job:</strong> {$job}</p>
                        <p style='margin:5px 0;'><strong>Application ID:</strong> {$appId}</p>
                    </div>
                    <p>This application will no longer appear in your active candidates list.</p>
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

        $appUrl = rtrim(getenv('APP_URL') ?: 'https://jobsence.com', '/');
        
        // 1. Build dynamic placeholders from $data
        $placeholders = [];
        foreach ($data as $k => $v) {
            if (is_scalar($v)) {
                $placeholders["{{{$k}}}"] = (string)$v;
            }
        }

        // 2. Global defaults and overrides
        $defaults = [
            '{{app_url}}'         => $appUrl,
            '{{brand_name}}'     => (string)(getenv('PORTAL_NAME') ?: 'Jobsence'),
            '{{logo_url}}'       => $appUrl . '/uploads/jobsence.png',
            '{{support_email}}'  => (string)(getenv('SUPPORT_EMAIL') ?: 'gm@jobsence.com'),
            '{{support_phone}}'  => (string)(getenv('SUPPORT_NUMBER') ?: '+91 9717122688'),
            '{{unsubscribe_link}}' => $appUrl . '/unsubscribe',
            '{{year}}'           => date('Y'),
        ];
        
        // 3. Map common aliases if not present
        if (!isset($placeholders['{{user_name}}'])) {
            $placeholders['{{user_name}}'] = (string)($data['candidate_name'] ?? $data['user_name'] ?? 'User');
        }
        if (!isset($placeholders['{{cta_link}}'])) {
            $placeholders['{{cta_link}}'] = (string)($data['link'] ?? $data['cta_link'] ?? $appUrl);
        }
        if (!isset($placeholders['{{headline}}'])) {
            $placeholders['{{headline}}'] = (string)($data['title'] ?? $data['subject'] ?? '');
        }

        $allPlaceholders = array_merge($defaults, $placeholders);
        $content = strtr($html, $allPlaceholders);

        $subject = (string)($data['subject'] ?? $data['title'] ?? ucwords(str_replace('_', ' ', $key)));
        
        // 4. Wrap in layout
        $body = self::wrapHtml($subject, $content, $data);

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

    public static function notifyJobMatchExtended(int $userId, array $jobData): void
    {
        try {
            $score = (int)($jobData['match_score'] ?? 0);
            $matchLevel = 'Weak Match';
            $color = '#ef4444'; // Red
            $bg = '#fef2f2';
            $border = '#fecaca';

            if ($score >= 90) {
                $matchLevel = 'Excellent Match';
                $color = '#059669'; // Green
                $bg = '#f0fdf4';
                $border = '#bbf7d0';
            } elseif ($score >= 75) {
                $matchLevel = 'Strong Match';
                $color = '#d97706'; // Orange
                $bg = '#fffbeb';
                $border = '#fef3c7';
            } elseif ($score >= 60) {
                $matchLevel = 'Moderate Match';
                $color = '#2563eb'; // Blue
                $bg = '#eff6ff';
                $border = '#dbeafe';
            }

            $appUrl = rtrim(getenv('APP_URL') ?: 'https://jobsence.com', '/');
            
            $data = [
                'match_score' => $score,
                'match_label' => $matchLevel,
                'match_color_text' => $color,
                'match_color_bg' => $bg,
                'match_color_border' => $border,
                'job_title' => $jobData['title'],
                'company_name' => $jobData['company_name'],
                'company_logo' => $jobData['company_logo'] ?: ($appUrl . '/uploads/jobsence.png'),
                'job_location' => $jobData['location'],
                'salary_range' => $jobData['salary_range'],
                'job_type' => $jobData['job_type'],
                'experience_required' => $jobData['experience_required'],
                'job_summary' => substr($jobData['description'] ?? '', 0, 180) . '...',
                'matching_skills_list' => $jobData['matching_skills'] ?? 'Matching your core expertise',
                'preferred_location' => $jobData['location'],
                'experience_years' => $jobData['experience_required'],
                'job_slug' => $jobData['slug'] ?? 'job-details',
                'cta_link' => $appUrl . '/jobs/' . ($jobData['slug'] ?? $jobData['id']),
                'email_template' => 'job_match_candidate'
            ];

            self::send($userId, 'job_match', "Match Found: {$jobData['title']}", "We found a {$matchLevel} opportunity matching your profile.", $data);
        } catch (\Throwable $t) {
            error_log("NotificationService::notifyJobMatchExtended error: " . $t->getMessage());
        }
    }

    public static function notifyEmployerOfCandidateMatch(int $employerUserId, array $candidateData, array $jobData): void
    {
        try {
            $score = (int)($candidateData['match_score'] ?? 0);
            $matchLevel = 'Match Found';
            $color = '#2563eb';
            $bg = '#eff6ff';
            $border = '#dbeafe';

            if ($score >= 90) {
                $color = '#059669'; $bg = '#f0fdf4'; $border = '#bbf7d0';
            } elseif ($score >= 75) {
                $color = '#d97706'; $bg = '#fffbeb'; $border = '#fef3c7';
            }

            $appUrl = rtrim(getenv('APP_URL') ?: 'https://jobsence.com', '/');
            
            $skills = is_array($candidateData['skills']) ? $candidateData['skills'] : explode(',', (string)$candidateData['skills']);
            $skillsHtml = '';
            foreach (array_slice($skills, 0, 6) as $skill) {
                $skillsHtml .= "<span style='display:inline-block; background:#f3f4f6; padding:4px 10px; border-radius:4px; margin-right:5px; margin-bottom:5px; font-size:12px; color:#1f2937;'>".trim($skill)."</span>";
            }

            $data = [
                'match_score' => $score,
                'match_color_text' => $color,
                'match_color_bg' => $bg,
                'match_color_border' => $border,
                'job_title' => $jobData['title'],
                'candidate_name' => $candidateData['full_name'],
                'candidate_photo' => $candidateData['profile_picture'] ?: ($appUrl . '/assets/images/default-avatar.png'),
                'professional_title' => $candidateData['professional_title'] ?? 'Professional Candidate',
                'experience_years' => $candidateData['experience_years'] ?? 'Experienced',
                'candidate_location' => $candidateData['city'] ?? 'Location Available',
                'skills_badges_html' => $skillsHtml,
                'education_summary' => $candidateData['education'] ?? 'Degree Holder',
                'resume_status' => $candidateData['resume_url'] ? 'Attached' : 'Profile Only',
                'top_skills' => implode(', ', array_slice($skills, 0, 3)),
                'candidate_id' => $candidateData['id'],
                'email_template' => 'job_match_employer'
            ];

            self::send($employerUserId, 'candidate_match', "Strong Match: {$candidateData['full_name']} for {$jobData['title']}", "A highly relevant candidate has been matched to your job post.", $data);
        } catch (\Throwable $t) {
            error_log("NotificationService::notifyEmployerOfCandidateMatch error: " . $t->getMessage());
        }
    }

    public static function notifyJobMatch(int $userId, array $job): void
    {
        // Wrapper for the new extended method
        self::notifyJobMatchExtended($userId, $job);
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
        $appUrl = getenv('APP_URL') ?: 'http://localhost:8000';
        $secret = $_ENV['APP_KEY'] ?? 'secret';
        $hash = hash_hmac('sha256', $logId . '|' . $targetUrl, $secret);
        return rtrim($appUrl, '/') . '/notifications/track/click?id=' . $logId . '&h=' . $hash . '&url=' . urlencode($targetUrl);
    }

    private static function signOpenPixel(int $logId): string
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost:8000';
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
        $payload = [
            'interview_id' => $interviewId,
            'role' => $role,
            'user_id' => $userId,
            'expires_at' => time() + $ttlSeconds,
            'nonce' => bin2hex(random_bytes(8))
        ];
        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', (string)$payloadJson, self::joinTokenSecret(), true);
        $signedToken = self::base64UrlEncode((string)$payloadJson) . '.' . self::base64UrlEncode($signature);

        $redis = \App\Core\RedisClient::getInstance();
        if ($redis->isAvailable()) {
            $redis->set("interview_join:{$signedToken}", $payloadJson, $ttlSeconds);
        }
        return $signedToken;
    }

    public static function validateJoinToken(string $token): ?array
    {
        if ($token === '' || strlen($token) > 2048) {
            return null;
        }

        $redis = \App\Core\RedisClient::getInstance();
        if ($redis->isAvailable()) {
            $data = $redis->get("interview_join:{$token}");
            if ($data) {
                $payload = json_decode($data, true);
                if (is_array($payload) && self::joinTokenNotExpired($payload)) {
                    return $payload;
                }
            }
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        $payloadJson = self::base64UrlDecode($parts[0]);
        $signature = self::base64UrlDecode($parts[1]);
        if ($payloadJson === null || $signature === null) {
            return null;
        }

        $expected = hash_hmac('sha256', $payloadJson, self::joinTokenSecret(), true);
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload) || !self::joinTokenNotExpired($payload)) {
            return null;
        }

        return $payload;
    }

    private static function joinTokenSecret(): string
    {
        $secret = (string)($_ENV['JITSI_JOIN_TOKEN_SECRET'] ?? $_ENV['JWT_SECRET'] ?? $_ENV['CSRF_SECRET'] ?? '');
        return $secret !== '' ? $secret : 'change-this-interview-token-secret';
    }

    private static function joinTokenNotExpired(array $payload): bool
    {
        $expiresAt = $payload['expires_at'] ?? 0;
        if (is_string($expiresAt) && !ctype_digit($expiresAt)) {
            $expiresAt = strtotime($expiresAt) ?: 0;
        }
        return (int)$expiresAt >= time();
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }

    private static function formatExperienceStatic(?string $data): string
    {
        $exp = json_decode($data ?? '[]', true);
        if (empty($exp)) return 'Fresher';
        
        $totalMonths = 0;
        foreach ($exp as $e) {
            $start = isset($e['start_date']) ? strtotime($e['start_date']) : null;
            $end = isset($e['end_date']) && $e['end_date'] !== 'Present' ? strtotime($e['end_date']) : time();
            
            if ($start && $end) {
                $totalMonths += (int)(($end - $start) / (30 * 24 * 3600));
            }
        }

        if ($totalMonths === 0) return count($exp) . " role(s)";
        
        $years = floor($totalMonths / 12);
        $months = $totalMonths % 12;
        
        $result = [];
        if ($years > 0) $result[] = $years . " Year" . ($years > 1 ? "s" : "");
        if ($months > 0) $result[] = $months . " Month" . ($months > 1 ? "s" : "");
        
        return !empty($result) ? implode(" ", $result) : "Fresher";
    }

    private static function formatEducationStatic(?string $data): string
    {
        $edu = json_decode($data ?? '[]', true);
        if (empty($edu)) return 'N/A';
        
        $latest = $edu[0];
        return ($latest['degree'] ?? '') . (!empty($latest['field_of_study']) ? " in " . $latest['field_of_study'] : "");
    }
}
