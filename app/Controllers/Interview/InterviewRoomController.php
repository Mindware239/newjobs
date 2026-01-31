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

        $jitsi = new JitsiService();

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
        // CRITICAL FIX: Allow candidates to load Jitsi if room exists (not just when status is 'live')
        // This fixes the issue where candidate can't join even when employer has started the meeting
        $roomExists = !empty($row['room_name']);
        
        if ($capabilities['can_start'] || $isAdmin) {
            // Employer/admin can always load Jitsi (they can start meetings)
            $canLoadJitsi = true;
        } elseif ($isCandidate) {
            // Candidate can load Jitsi if:
            // 1. Status is 'live' (normal case), OR
            // 2. Room exists (allows late joiners even if status shows as completed)
            $canLoadJitsi = ($status === 'live') || ($roomExists && $status !== 'cancelled');
        } else {
            $canLoadJitsi = ($status === 'live');
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
            'display_name' => (string)$displayName,
            'can_load_jitsi' => $canLoadJitsi,
            'consent_required' => true
        ], 200, 'interviews/layout');
    }

    public function state(Request $request, Response $response): void
    {
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
        $roomName = null;
        $roomPassword = null;
        
        // CRITICAL: Allow candidates to join if room exists (even if status shows completed)
        // This fixes the issue where candidate can't join even when employer is waiting
        $roomNameVal = (string)($row['room_name'] ?? '');
        if ($roomNameVal !== '') {
            $roomName = $roomNameVal;
            // If room exists, always allow password to be retrieved for candidates
            $enc = (string)($row['room_password_enc'] ?? '');
            if ($enc !== '') {
                $roomPassword = (new JitsiService())->decrypt($enc);
            }
        }
        
        // CRITICAL FIX: Allow candidate to join if room exists (status might be completed but room still active)
        // Only prevent joining if explicitly ended and room doesn't exist
        $canJoin = false;
        if ($isAdmin || $isEmployerOwner) {
            $canJoin = true; // Admin/employer can always join
        } elseif ($isCandidate) {
            // Candidate can join if:
            // 1. Status is 'live' (normal case), OR
            // 2. Room exists and hasn't been explicitly ended (allows late joiners)
            if ($status === 'live') {
                $canJoin = true;
            } elseif ($status !== 'cancelled' && $roomName !== '') {
                // Allow candidate to join if room exists (might be showing as completed but room is still active)
                $canJoin = true;
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

        $jitsi = new JitsiService();

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

        try {
            // Load info for WhatsApp notifications
            $info = $db->fetchOne(
                "SELECT 
                    i.scheduled_start, i.scheduled_end, i.timezone, i.interview_type,
                    j.title AS job_title, e.company_name,
                    u.id AS candidate_user_id, u.phone AS candidate_phone,
                    eu.phone AS employer_phone
                 FROM interviews i
                 INNER JOIN applications a ON a.id = i.application_id
                 INNER JOIN jobs j ON j.id = a.job_id
                 INNER JOIN employers e ON e.id = i.employer_id
                 INNER JOIN users u ON u.id = a.candidate_user_id
                 INNER JOIN users eu ON eu.id = e.user_id
                 WHERE i.id = :id
                 LIMIT 1",
                ['id' => $interviewId]
            );
            if ($info) {
                $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
                $candidateToken = \App\Services\NotificationService::generateJoinToken($interviewId, 'candidate', (int)($info['candidate_user_id'] ?? 0), 7200);
                $secureLinkCandidate = ($base !== '' ? $base : '') . '/interview/join?token=' . urlencode($candidateToken) . '&type=candidate';

                $dataCommon = [
                    'company_name' => (string)($info['company_name'] ?? ''),
                    'job_title' => (string)($info['job_title'] ?? ''),
                    'scheduled_start' => (string)($info['scheduled_start'] ?? ''),
                    'scheduled_end' => (string)($info['scheduled_end'] ?? ''),
                    'timezone' => (string)($info['timezone'] ?? 'Asia/Kolkata'),
                    'interview_type' => (string)($info['interview_type'] ?? 'video'),
                    'candidate_user_id' => (int)($info['candidate_user_id'] ?? 0),
                    'employer_id' => (int)($row['employer_id'] ?? 0),
                    'secure_link' => $secureLinkCandidate
                ];
                $candPhone = (string)($info['candidate_phone'] ?? '');
                if ($candPhone !== '') {
                    \App\Services\NotificationService::queueWhatsApp($candPhone, 'interview_live', $dataCommon);
                }
                $empPhone = (string)($info['employer_phone'] ?? '');
                if ($empPhone !== '') {
                    $empToken = \App\Services\NotificationService::generateJoinToken($interviewId, 'employer', (int)($row['employer_id'] ?? 0), 7200);
                    $dataEmp = $dataCommon;
                    $dataEmp['secure_link'] = ($base !== '' ? $base : '') . '/interview/join?token=' . urlencode($empToken) . '&type=employer';
                    \App\Services\NotificationService::queueWhatsApp($empPhone, 'interview_live', $dataEmp);
                }
            }
        } catch (\Throwable $t) {
            error_log('WhatsApp live notify error: ' . $t->getMessage());
        }

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

        try {
            $info = $db->fetchOne(
                "SELECT 
                    j.title AS job_title, e.company_name,
                    u.id AS candidate_user_id, u.phone AS candidate_phone,
                    eu.phone AS employer_phone
                 FROM interviews i
                 INNER JOIN applications a ON a.id = i.application_id
                 INNER JOIN jobs j ON j.id = a.job_id
                 INNER JOIN employers e ON e.id = i.employer_id
                 INNER JOIN users u ON u.id = a.candidate_user_id
                 INNER JOIN users eu ON eu.id = e.user_id
                 WHERE i.id = :id
                 LIMIT 1",
                ['id' => $interviewId]
            );
            if ($info) {
                $data = [
                    'company_name' => (string)($info['company_name'] ?? ''),
                    'job_title' => (string)($info['job_title'] ?? ''),
                    'candidate_user_id' => (int)($info['candidate_user_id'] ?? 0),
                    'employer_id' => (int)($row['employer_id'] ?? 0)
                ];
                $candPhone = (string)($info['candidate_phone'] ?? '');
                if ($candPhone !== '') {
                    \App\Services\NotificationService::queueWhatsApp($candPhone, 'interview_ended', $data);
                }
                $empPhone = (string)($info['employer_phone'] ?? '');
                if ($empPhone !== '') {
                    \App\Services\NotificationService::queueWhatsApp($empPhone, 'interview_ended', $data);
                }
            }
        } catch (\Throwable $t) {
            error_log('WhatsApp end notify error: ' . $t->getMessage());
        }

        $response->json(['success' => true]);
    }

    public function event(Request $request, Response $response): void
    {
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

    public function joinWithToken(Request $request, Response $response): void
    {
        $token = (string)$request->get('token', '');
        $payload = \App\Services\NotificationService::validateJoinToken($token);
        if (!$payload) {
            $response->view('errors/403', ['message' => 'Invalid or expired link'], 403);
            return;
        }
        if (!$this->currentUser) {
            $response->redirect('/login?next=' . urlencode('/interview/join?token=' . $token));
            return;
        }
        $userId = (int)$this->currentUser->id;
        if ($userId !== (int)($payload['user_id'] ?? 0)) {
            $response->view('errors/403', ['message' => 'Link not for this user'], 403);
            return;
        }
        $interviewId = (int)($payload['interview_id'] ?? 0);
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
}
