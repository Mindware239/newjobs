<?php

declare(strict_types=1);

namespace App\Controllers\Interview;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\InterviewEvent;
use App\Models\SubscriptionUsageLog;
use App\Services\JitsiService;

class InterviewRoomController extends BaseController
{
    public function room(Request $request, Response $response): void
    {
        $jitsi = new JitsiService();
        if (!$this->ensureSecureMediaOrigin($request, $response, $jitsi, false)) {
            return;
        }

        if (!$this->requireAuth($request, $response)) {
            return;
        }

        $interviewId = (int)$request->param('id');
        if ($interviewId <= 0) {
            $response->view('errors/404', ['message' => 'Interview not found'], 404);
            return;
        }

        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT 
                i.*,
                a.candidate_user_id,
                a.job_id,
                a.status AS application_status,
                j.title AS job_title,
                j.slug AS job_slug,
                e.id AS employer_id_real,
                e.company_name,
                e.logo_url AS company_logo,
                u.email AS candidate_email,
                COALESCE(c.full_name, u.google_name, u.apple_name, u.email) AS candidate_name
             FROM interviews i
             INNER JOIN applications a ON a.id = i.application_id
             INNER JOIN jobs j ON j.id = a.job_id
             INNER JOIN employers e ON e.id = i.employer_id
             INNER JOIN users u ON u.id = a.candidate_user_id
             LEFT JOIN candidates c ON c.user_id = u.id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $interviewId]
        );

        if (!$row) {
            $response->view('errors/404', ['message' => 'Interview not found'], 404);
            return;
        }

        // Fix logo URL - convert localhost URLs to relative paths or use APP_URL
        if (!empty($row['company_logo'])) {
            $logoUrl = (string)$row['company_logo'];
            // Remove localhost URLs and convert to relative path
            $logoUrl = preg_replace('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/#', '/', $logoUrl);
            // If it doesn't start with / or http, make it relative
            if (!preg_match('#^(/|https?://)#', $logoUrl)) {
                $logoUrl = '/' . ltrim($logoUrl, '/');
            }
            $row['company_logo'] = $logoUrl;
        }

        $userId = (int)$this->currentUser->id;
        $role = (string)($this->currentUser->role ?? '');

        $isAdmin = $this->currentUser->isAdmin();
        $isEmployerOwner = $role === 'employer' && (int)$row['employer_id'] === (int)($this->currentUser->employer()?->id ?? 0);
        $isCandidate = $role === 'candidate' && (int)$row['candidate_user_id'] === $userId;

        if (!$isAdmin && !$isEmployerOwner && !$isCandidate) {
            $response->view('errors/403', [], 403);
            return;
        }

        $employerId = (int)$row['employer_id'];
        $premiumNow = $jitsi->isPremiumForEmployer($employerId);

        $capabilities = [
            'role' => $isAdmin ? 'admin' : ($isEmployerOwner ? 'employer' : 'candidate'),
            'can_start' => $isAdmin || $isEmployerOwner,
            'can_end' => $isAdmin || $isEmployerOwner,
            'can_moderate' => $isAdmin || $isEmployerOwner,
            'can_mute_all' => ($isAdmin && $premiumNow) || ($isEmployerOwner && $jitsi->canUseMuteAll($employerId)),
            'can_kick' => $isAdmin || $isEmployerOwner,
            'can_record' => ($isAdmin && $premiumNow && $jitsi->isRecordingEnabled()) || ($isEmployerOwner && $jitsi->canUseRecording($employerId)),
            'can_admin_intervention' => $isAdmin || ($isEmployerOwner && $jitsi->canUseAdminIntervention($employerId)),
            'can_priority_quality' => $isEmployerOwner && $jitsi->canUsePriorityQuality($employerId),
            'can_brand_room' => $isEmployerOwner && $jitsi->canUseBranding($employerId),
            'can_analytics' => ($isAdmin && $premiumNow) || ($isEmployerOwner && $jitsi->canUseAnalytics($employerId)),
            'can_screen_share' => true
        ];

        $status = (string)($row['status'] ?? 'scheduled');
        $roomExists = !empty($row['room_name']);
        $roomCanStillJoin = $roomExists && empty($row['ended_at']) && !in_array($status, ['cancelled', 'completed'], true);
        if ($capabilities['can_start'] || $isAdmin) {
            $canLoadJitsi = true;
        } elseif ($isCandidate) {
            $canLoadJitsi = $status === 'live' || $roomCanStillJoin;
        } else {
            $canLoadJitsi = $status === 'live' && $roomExists;
        }

        $displayName = $this->currentUser->attributes['name']
            ?? $this->currentUser->attributes['full_name']
            ?? $this->currentUser->attributes['google_name']
            ?? $this->currentUser->attributes['apple_name']
            ?? $this->currentUser->attributes['email']
            ?? 'User';

        $response->view('interviews/room', [
            'title' => 'Interview Room',
            'interview' => $row,
            'capabilities' => $capabilities,
            'jitsi_domain' => $jitsi->getDomain(),
            'jitsi_app_name' => $jitsi->getAppName(),
            'jitsi_config' => $jitsi->getClientConfig(),
            'display_name' => (string)$displayName,
            'can_load_jitsi' => $canLoadJitsi
        ], 200, 'interviews/layout');
    }

    public function state(Request $request, Response $response): void
    {
        $jitsi = new JitsiService();
        if (!$this->ensureSecureMediaOrigin($request, $response, $jitsi, true)) {
            return;
        }

        if (!$this->requireAuth($request, $response)) {
            return;
        }

        $interviewId = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT i.id, i.status, i.started_at, i.ended_at, i.application_id, i.employer_id, i.room_name, i.room_password_enc,
                    a.candidate_user_id
             FROM interviews i
             INNER JOIN applications a ON a.id = i.application_id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $interviewId]
        );

        if (!$row) {
            $response->json(['error' => 'Not found'], 404);
            return;
        }

        $userId = (int)$this->currentUser->id;
        $role = (string)($this->currentUser->role ?? '');
        $isAdmin = $this->currentUser->isAdmin();
        $isEmployerOwner = $role === 'employer' && (int)$row['employer_id'] === (int)($this->currentUser->employer()?->id ?? 0);
        $isCandidate = $role === 'candidate' && (int)$row['candidate_user_id'] === $userId;

        if (!$isAdmin && !$isEmployerOwner && !$isCandidate) {
            $response->json(['error' => 'Forbidden'], 403);
            return;
        }

        $status = (string)($row['status'] ?? 'scheduled');
        $roomNameVal = (string)($row['room_name'] ?? '');
        $roomCanStillJoin = $roomNameVal !== '' && empty($row['ended_at']) && !in_array($status, ['cancelled', 'completed'], true);
        $canJoin = $isAdmin || $isEmployerOwner || ($isCandidate && ($status === 'live' || $roomCanStillJoin));

        $roomName = null;
        $roomPassword = null;
        if ($canJoin) {
            if ($roomNameVal !== '') {
                $roomName = $roomNameVal;
                $enc = (string)($row['room_password_enc'] ?? '');
                if ($enc !== '') {
                    $roomPassword = (new JitsiService())->decrypt($enc);
                }
            }
        }
        
        $response->json([
            'success' => true,
            'status' => $status,
            'can_join' => $canJoin,
            'started_at' => $row['started_at'],
            'ended_at' => $row['ended_at'],
            'room_name' => $roomName,
            'room_password' => $roomPassword
        ]);
    }

    public function start(Request $request, Response $response): void
    {
        $jitsi = new JitsiService();
        if (!$this->ensureSecureMediaOrigin($request, $response, $jitsi, true)) {
            return;
        }

        if (!$this->requireAuth($request, $response)) {
            return;
        }

        $interviewId = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT i.*, a.candidate_user_id
             FROM interviews i
             INNER JOIN applications a ON a.id = i.application_id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $interviewId]
        );
        if (!$row) {
            $response->json(['error' => 'Not found'], 404);
            return;
        }

        $role = (string)($this->currentUser->role ?? '');
        $isAdmin = $this->currentUser->isAdmin();
        $isEmployerOwner = $role === 'employer' && (int)$row['employer_id'] === (int)($this->currentUser->employer()?->id ?? 0);
        if (!$isAdmin && !$isEmployerOwner) {
            $response->json(['error' => 'Forbidden'], 403);
            return;
        }

        $roomName = (string)($row['room_name'] ?? '');
        $roomPassEnc = (string)($row['room_password_enc'] ?? '');
        $password = $jitsi->decrypt($roomPassEnc);
        $updates = [];

        if ($roomName === '') {
            $roomName = $jitsi->generateRoomName();
            $updates['room_name'] = $roomName;
        }

        if (!$password) {
            $password = $jitsi->generateRoomPassword();
            $enc = $jitsi->encrypt($password);
            if ($enc) {
                $updates['room_password_enc'] = $enc;
            }
        }

        if (($row['status'] ?? '') !== 'live') {
            $updates['status'] = 'live';
            $updates['started_at'] = date('Y-m-d H:i:s');
        }

        if (!empty($updates)) {
            $set = [];
            $params = ['id' => $interviewId];
            foreach ($updates as $k => $v) {
                $set[] = "{$k} = :{$k}";
                $params[$k] = $v;
            }
            $db->query("UPDATE interviews SET " . implode(', ', $set) . " WHERE id = :id", $params);
        }

        $this->logEvent($interviewId, 'meeting_started', [
            'by_role' => $isAdmin ? 'admin' : 'employer'
        ], $request);

        $response->json([
            'success' => true,
            'room_name' => $roomName,
            'room_password' => $password
        ]);
    }

    public function end(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) {
            return;
        }

        $interviewId = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM interviews WHERE id = :id LIMIT 1", ['id' => $interviewId]);
        if (!$row) {
            $response->json(['error' => 'Not found'], 404);
            return;
        }

        $role = (string)($this->currentUser->role ?? '');
        $isAdmin = $this->currentUser->isAdmin();
        $isEmployerOwner = $role === 'employer' && (int)$row['employer_id'] === (int)($this->currentUser->employer()?->id ?? 0);
        if (!$isAdmin && !$isEmployerOwner) {
            $response->json(['error' => 'Forbidden'], 403);
            return;
        }

        $db->query(
            "UPDATE interviews 
             SET status = 'completed', ended_at = :ended_at, updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $interviewId,
                'ended_at' => date('Y-m-d H:i:s')
            ]
        );

        $this->logEvent($interviewId, 'meeting_ended', [
            'by_role' => $isAdmin ? 'admin' : 'employer'
        ], $request);

        $response->json(['success' => true]);
    }

    public function event(Request $request, Response $response): void
    {
        $jitsi = new JitsiService();
        if (!$this->ensureSecureMediaOrigin($request, $response, $jitsi, true)) {
            return;
        }

        if (!$this->requireAuth($request, $response)) {
            return;
        }

        $interviewId = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT i.id, i.employer_id, a.candidate_user_id
             FROM interviews i
             INNER JOIN applications a ON a.id = i.application_id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $interviewId]
        );
        if (!$row) {
            $response->json(['error' => 'Not found'], 404);
            return;
        }

        $userId = (int)$this->currentUser->id;
        $role = (string)($this->currentUser->role ?? '');
        $isAdmin = $this->currentUser->isAdmin();
        $isEmployerOwner = $role === 'employer' && (int)$row['employer_id'] === (int)($this->currentUser->employer()?->id ?? 0);
        $isCandidate = $role === 'candidate' && (int)$row['candidate_user_id'] === $userId;

        if (!$isAdmin && !$isEmployerOwner && !$isCandidate) {
            $response->json(['error' => 'Forbidden'], 403);
            return;
        }

        $payload = $request->getJsonBody() ?? $request->all();
        $type = (string)($payload['type'] ?? '');
        if ($type === '' || strlen($type) > 64) {
            $response->json(['error' => 'Invalid event'], 422);
            return;
        }

        $data = $payload['data'] ?? null;
        if (is_array($data)) {
            $data = json_encode($data);
        } elseif (!is_string($data) && $data !== null) {
            $data = json_encode(['value' => $data]);
        }

        $evt = new InterviewEvent();
        $evt->fill([
            'interview_id' => $interviewId,
            'actor_user_id' => $userId,
            'actor_role' => $isAdmin ? 'admin' : $role,
            'event_type' => $type,
            'payload' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 512),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $evt->save();

        if (in_array($type, ['recording_started', 'recording_stopped', 'admin_mute_all', 'admin_kick'], true)) {
            $subscriptionId = null;
            if ($isEmployerOwner) {
                $sub = (new JitsiService())->getCurrentSubscriptionForEmployer((int)$row['employer_id']);
                $subscriptionId = $sub ? (int)$sub->attributes['id'] : null;
            }
            if ($subscriptionId) {
                SubscriptionUsageLog::logUsage(
                    $subscriptionId,
                    (int)$row['employer_id'],
                    $type,
                    null,
                    null,
                    null,
                    ['interview_id' => $interviewId]
                );
            }
        }

        $response->json(['success' => true]);
    }

    public function analytics(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) {
            return;
        }

        $interviewId = (int)$request->param('id');
        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT 
                i.*,
                a.candidate_user_id,
                j.title AS job_title,
                COALESCE(c.full_name, u.google_name, u.apple_name, u.email) AS candidate_name
             FROM interviews i
             INNER JOIN applications a ON a.id = i.application_id
             INNER JOIN jobs j ON j.id = a.job_id
             INNER JOIN users u ON u.id = a.candidate_user_id
             LEFT JOIN candidates c ON c.user_id = u.id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $interviewId]
        );

        if (!$row) {
            $response->view('errors/404', ['message' => 'Interview not found'], 404);
            return;
        }

        $role = (string)($this->currentUser->role ?? '');
        $isAdmin = $this->currentUser->isAdmin();
        $isEmployerOwner = $role === 'employer' && (int)$row['employer_id'] === (int)($this->currentUser->employer()?->id ?? 0);
        if (!$isAdmin && !$isEmployerOwner) {
            $response->view('errors/403', [], 403);
            return;
        }

        $jitsi = new JitsiService();
        if (!$isAdmin && !$jitsi->canUseAnalytics((int)$row['employer_id'])) {
            $response->view('errors/403', [], 403);
            return;
        }

        $events = $db->fetchAll(
            "SELECT event_type, COUNT(*) AS cnt
             FROM interview_events
             WHERE interview_id = :id
             GROUP BY event_type
             ORDER BY cnt DESC",
            ['id' => $interviewId]
        );

        $timeline = $db->fetchAll(
            "SELECT actor_role, event_type, created_at
             FROM interview_events
             WHERE interview_id = :id
             ORDER BY created_at ASC
             LIMIT 250",
            ['id' => $interviewId]
        );

        $response->view('interviews/analytics', [
            'title' => 'Interview Analytics',
            'interview' => $row,
            'events' => $events,
            'timeline' => $timeline
        ], 200, 'interviews/layout');
    }

    public function joinWithToken(Request $request, Response $response): void
    {
        $jitsi = new JitsiService();
        if (!$this->ensureSecureMediaOrigin($request, $response, $jitsi, false)) {
            return;
        }

        $token = (string)$request->get('token', '');
        $payload = \App\Services\NotificationService::validateJoinToken($token);
        if (!$payload) {
            $response->view('errors/403', ['message' => 'Invalid or expired interview link'], 403);
            return;
        }

        if (!$this->currentUser) {
            $response->redirect('/login?next=' . urlencode('/interview/join?token=' . $token));
            return;
        }

        $userId = (int)$this->currentUser->id;
        if ($userId !== (int)($payload['user_id'] ?? 0) && !$this->currentUser->isAdmin()) {
            $response->view('errors/403', ['message' => 'This interview link is not assigned to your account'], 403);
            return;
        }

        $interviewId = (int)($payload['interview_id'] ?? 0);
        if ($interviewId <= 0) {
            $response->view('errors/404', ['message' => 'Interview not found'], 404);
            return;
        }

        $response->redirect('/interviews/' . $interviewId . '/room');
    }

    private function logEvent(int $interviewId, string $type, array $data, Request $request): void
    {
        $evt = new InterviewEvent();
        $evt->fill([
            'interview_id' => $interviewId,
            'actor_user_id' => (int)$this->currentUser->id,
            'actor_role' => $this->currentUser->isAdmin() ? 'admin' : (string)($this->currentUser->role ?? ''),
            'event_type' => $type,
            'payload' => json_encode($data),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 512),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $evt->save();
    }

    private function ensureSecureMediaOrigin(Request $request, Response $response, JitsiService $jitsi, bool $json): bool
    {
        if (!$jitsi->shouldForceHttps() || $this->isSecureRequest() || $this->isLocalRequestHost()) {
            return true;
        }

        if ($json) {
            $response->json([
                'success' => false,
                'error' => 'HTTPS is required for video interviews. Please reload this page using https://.'
            ], 426);
            return false;
        }

        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        $uri = $request->getUri();
        if ($host !== '') {
            $response->redirect('https://' . $host . $uri, 301);
            return false;
        }

        $response->view('errors/403', ['message' => 'HTTPS is required for video interviews.'], 403);
        return false;
    }

    private function isSecureRequest(): bool
    {
        $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
        $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $forwardedSsl = strtolower((string)($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));

        return ($https !== '' && $https !== 'off')
            || (string)($_SERVER['SERVER_PORT'] ?? '') === '443'
            || $forwardedProto === 'https'
            || $forwardedSsl === 'on';
    }

    private function isLocalRequestHost(): bool
    {
        $host = preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
        return in_array(strtolower((string)$host), ['localhost', '127.0.0.1', '::1'], true);
    }
}
