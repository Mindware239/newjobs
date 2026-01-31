<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Job;
use App\Models\Application;
use App\Models\Interview;

class InterviewsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        $db = \App\Core\Database::getInstance();
        $employerId = (int)$employer->id;

        // Get filter parameters
        $statusFilter = $request->get('status', 'all'); // all, upcoming, today, week, completed, cancelled
        $search = $request->get('search', '');
        $typeFilter = $request->get('type', 'all'); // all, phone, video, onsite
        $sortBy = $request->get('sort_by', 'date'); // date, candidate, job

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employerId)
            ->where('status', '=', 'published')->count();
        
        $totalApplicationsSql = "SELECT COUNT(*) as count 
                               FROM applications a 
                               INNER JOIN jobs j ON a.job_id = j.id 
                               WHERE j.employer_id = :employer_id";
        $totalApplicationsResult = $db->fetchOne($totalApplicationsSql, ['employer_id' => $employerId]);
        $totalApplications = (int)($totalApplicationsResult['count'] ?? 0);

        // Build WHERE conditions for interviews
        $whereConditions = ["i.employer_id = :employer_id"];
        $params = ['employer_id' => $employerId];

        // Status filter
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));

        switch ($statusFilter) {
            case 'upcoming':
                $whereConditions[] = "(i.status = 'live' OR (i.status IN ('scheduled', 'rescheduled') AND i.scheduled_start >= NOW()))";
                break;
            case 'today':
                $whereConditions[] = "DATE(i.scheduled_start) = :today_date AND i.status IN ('scheduled', 'rescheduled', 'live')";
                $params['today_date'] = $today;
                break;
            case 'week':
                $whereConditions[] = "DATE(i.scheduled_start) BETWEEN :week_start AND :week_end AND i.status IN ('scheduled', 'rescheduled', 'live')";
                $params['week_start'] = $weekStart;
                $params['week_end'] = $weekEnd;
                break;
            case 'completed':
                $whereConditions[] = "i.status = 'completed'";
                break;
            case 'cancelled':
                $whereConditions[] = "i.status = 'cancelled'";
                break;
            default:
                // 'all' - no additional filter
                break;
        }

        // Type filter
        if ($typeFilter !== 'all') {
            $whereConditions[] = "i.interview_type = :interview_type";
            $params['interview_type'] = $typeFilter;
        }

        // Search filter
        if (!empty($search)) {
            $whereConditions[] = "(c.full_name LIKE :search OR j.title LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        // Exclude interviews where application status is 'hired' (unless filtering for completed)
        if ($statusFilter !== 'completed') {
            $whereConditions[] = "a.status != 'hired'";
        }
        
        $whereClause = implode(' AND ', $whereConditions);

        // Get statistics (exclude hired applications)
        $statsSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN (i.status = 'live' OR (i.status IN ('scheduled', 'rescheduled') AND i.scheduled_start >= NOW())) THEN 1 ELSE 0 END) as upcoming,
                    SUM(CASE WHEN DATE(i.scheduled_start) = CURDATE() AND i.status IN ('scheduled', 'rescheduled', 'live') THEN 1 ELSE 0 END) as today,
                    SUM(CASE WHEN DATE(i.scheduled_start) BETWEEN :week_start AND :week_end AND i.status IN ('scheduled', 'rescheduled', 'live') THEN 1 ELSE 0 END) as this_week,
                    SUM(CASE WHEN i.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN i.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM interviews i
                INNER JOIN applications a ON i.application_id = a.id
                WHERE i.employer_id = :employer_id AND a.status != 'hired'";

        $stats = $db->fetchOne($statsSql, [
            'employer_id' => $employerId,
            'week_start' => $weekStart,
            'week_end' => $weekEnd
        ]);
        
        $interviewsSql = "SELECT 
                    i.*,
                    a.id as application_id,
                    a.status as application_status,
                    j.id as job_id,
                    j.title as job_title,
                    j.slug as job_slug,
                    u.id as candidate_user_id,
                    u.email as candidate_email,
                    u.phone as candidate_phone,
                    c.id as candidate_id,
                    c.full_name as candidate_name,
                    c.profile_picture as candidate_picture,
                    c.resume_url
                FROM interviews i
                INNER JOIN applications a ON i.application_id = a.id
                INNER JOIN jobs j ON a.job_id = j.id
                INNER JOIN users u ON a.candidate_user_id = u.id
                LEFT JOIN candidates c ON c.user_id = u.id
                WHERE $whereClause";

        // Sorting
        switch ($sortBy) {
            case 'candidate':
                $interviewsSql .= " ORDER BY c.full_name ASC, i.scheduled_start ASC";
                break;
            case 'job':
                $interviewsSql .= " ORDER BY j.title ASC, i.scheduled_start ASC";
                break;
            default: // date
                $interviewsSql .= " ORDER BY i.scheduled_start ASC";
                break;
        }

        $interviews = $db->fetchAll($interviewsSql, $params);

        // Format interview data
        foreach ($interviews as &$interview) {
            $interview['formatted_date'] = date('M d, Y', strtotime($interview['scheduled_start']));
            $interview['formatted_time'] = date('h:i A', strtotime($interview['scheduled_start']));
            $interview['formatted_end_time'] = date('h:i A', strtotime($interview['scheduled_end']));
            $interview['is_past'] = strtotime($interview['scheduled_start']) < time();
            $interview['is_today'] = date('Y-m-d', strtotime($interview['scheduled_start'])) === $today;
            // Platform detection
            $type = (string)($interview['interview_type'] ?? '');
            $link = (string)($interview['meeting_link'] ?? '');
            $platformLabel = 'Video';
            if ($type === 'phone') {
                $platformLabel = 'Phone';
            } elseif ($type === 'onsite') {
                $platformLabel = 'On-site';
            } elseif ($type === 'telephonic') {
                $platformLabel = 'Telephonic';
            } elseif ($type === 'video') {
                if ($link === '') {
                    $platformLabel = 'Jitsi (auto)';
                } else {
                    $host = strtolower((string)(parse_url($link, PHP_URL_HOST) ?: ''));
                    if ($host === '' || strpos($host, 'jit.si') !== false) $platformLabel = 'Jitsi';
                    elseif (strpos($host, 'zoom.us') !== false) $platformLabel = 'Zoom';
                    elseif (strpos($host, 'meet.google.com') !== false) $platformLabel = 'Google Meet';
                    elseif (strpos($host, 'teams.microsoft.com') !== false || strpos($host, 'office.com') !== false) $platformLabel = 'Microsoft Teams';
                }
            }
            $interview['platform_label'] = $platformLabel;
        }

        $response->view('employer/interviews', [
            'title' => 'Interviews',
            'employer' => $employer,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'interviews' => $interviews,
            'stats' => $stats ?: [
                'total' => 0,
                'upcoming' => 0,
                'today' => 0,
                'this_week' => 0,
                'completed' => 0,
                'cancelled' => 0
            ],
            'filters' => [
                'status' => $statusFilter,
                'type' => $typeFilter,
                'search' => $search,
                'sort_by' => $sortBy
            ]
        ], 200, 'employer/layout');
    }

    public function schedule(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $applicationId = (int)$request->post('application_id');
        $interviewType = $request->post('interview_type', 'phone');
        $scheduledStart = $request->post('scheduled_start');
        $scheduledEnd = $request->post('scheduled_end');
        $timezone = $request->post('timezone', 'Asia/Kolkata');
        $location = $request->post('location', '');
        $meetingLink = $request->post('meeting_link', '');

        // Validate required fields
        if (!$applicationId || !$scheduledStart || !$scheduledEnd) {
            $response->json(['error' => 'Missing required fields'], 400);
            return;
        }

        // Validate application belongs to employer
        $db = \App\Core\Database::getInstance();
        $applicationSql = "SELECT a.*, j.employer_id 
                         FROM applications a 
                         INNER JOIN jobs j ON a.job_id = j.id 
                         WHERE a.id = :application_id AND j.employer_id = :employer_id";
        $application = $db->fetchOne($applicationSql, [
            'application_id' => $applicationId,
            'employer_id' => $employer->id
        ]);

        if (!$application) {
            $response->json(['error' => 'Application not found or access denied'], 404);
            return;
        }

        // Validate interview type
        if (!in_array($interviewType, ['phone', 'video', 'onsite'])) {
            $response->json(['error' => 'Invalid interview type'], 400);
            return;
        }

        // Validate dates
        $startTime = strtotime($scheduledStart);
        $endTime = strtotime($scheduledEnd);
        if ($startTime === false || $endTime === false || $endTime <= $startTime) {
            $response->json(['error' => 'Invalid date/time range'], 400);
            return;
        }

        try {
            $existing = $db->fetchOne(
                "SELECT id, meeting_link, status
                 FROM interviews
                 WHERE application_id = :application_id
                   AND employer_id = :employer_id
                   AND status IN ('scheduled', 'rescheduled', 'live')
                   AND scheduled_end > NOW()
                 ORDER BY scheduled_start DESC
                 LIMIT 1",
                [
                    'application_id' => $applicationId,
                    'employer_id' => $employer->id
                ]
            );

            $interviewId = (int)($existing['id'] ?? 0);
            if ($interviewId > 0) {
                $db->query(
                    "UPDATE interviews
                     SET scheduled_by = :scheduled_by,
                         interview_type = :interview_type,
                         scheduled_start = :scheduled_start,
                         scheduled_end = :scheduled_end,
                         timezone = :timezone,
                         location = :location,
                         meeting_link = :meeting_link,
                         status = 'rescheduled',
                         updated_at = NOW()
                     WHERE id = :id AND employer_id = :employer_id",
                    [
                        'id' => $interviewId,
                        'employer_id' => $employer->id,
                        'scheduled_by' => $this->currentUser->id,
                        'interview_type' => $interviewType,
                        'scheduled_start' => date('Y-m-d H:i:s', $startTime),
                        'scheduled_end' => date('Y-m-d H:i:s', $endTime),
                        'timezone' => $timezone,
                        'location' => $location,
                        'meeting_link' => $meetingLink !== '' ? $meetingLink : ($existing['meeting_link'] ?? null)
                    ]
                );
            } else {
                $sql = "INSERT INTO interviews 
                        (application_id, employer_id, scheduled_by, interview_type, scheduled_start, scheduled_end, 
                         timezone, location, meeting_link, status, created_at, updated_at)
                        VALUES 
                        (:application_id, :employer_id, :scheduled_by, :interview_type, :scheduled_start, :scheduled_end,
                         :timezone, :location, :meeting_link, 'scheduled', NOW(), NOW())";

                $db->query($sql, [
                    'application_id' => $applicationId,
                    'employer_id' => $employer->id,
                    'scheduled_by' => $this->currentUser->id,
                    'interview_type' => $interviewType,
                    'scheduled_start' => date('Y-m-d H:i:s', $startTime),
                    'scheduled_end' => date('Y-m-d H:i:s', $endTime),
                    'timezone' => $timezone,
                    'location' => $location,
                    'meeting_link' => $meetingLink !== '' ? $meetingLink : null
                ]);

                $interviewId = (int)$db->lastInsertId();
            }

            if ($interviewType === 'video' && ($meetingLink === '' || $meetingLink === null)) {
                $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
                $meetingLink = ($base !== '' ? $base : '') . '/interviews/' . (int)$interviewId . '/room';
                $db->query(
                    "UPDATE interviews SET meeting_link = :meeting_link, updated_at = NOW() WHERE id = :id",
                    ['meeting_link' => $meetingLink, 'id' => (int)$interviewId]
                );
            }

            // Update application status to 'interview'
            $updateAppSql = "UPDATE applications SET status = 'interview', updated_at = NOW() WHERE id = :application_id";
            $db->query($updateAppSql, ['application_id' => $applicationId]);
            
            $infoSql = "SELECT 
                               j.title as job_title, 
                               u.id as candidate_user_id, 
                               u.email as candidate_email, 
                               COALESCE(c.full_name, u.google_name, u.apple_name, u.email) as candidate_name,
                               e.company_name, e.logo_url as company_logo, e.website as company_website
                        FROM applications a 
                        INNER JOIN jobs j ON a.job_id = j.id 
                        INNER JOIN users u ON a.candidate_user_id = u.id
                        INNER JOIN employers e ON j.employer_id = e.id
                        LEFT JOIN candidates c ON c.user_id = u.id
                        WHERE a.id = :application_id";
            $info = $db->fetchOne($infoSql, ['application_id' => $applicationId]);
            if ($info && !empty($info['candidate_email'])) {
                \App\Services\NotificationService::queueEmail(
                    $info['candidate_email'],
                    'interview_scheduled',
                    [
                        'job_title' => (string)($info['job_title'] ?? ''),
                        'scheduled_time' => date('M d, Y h:i A', $startTime),
                        'employer_id' => (int)$employer->id,
                        'candidate_user_id' => (int)$info['candidate_user_id'],
                        'interview_id' => (int)$interviewId,
                        'company_name' => (string)($info['company_name'] ?? 'MindInfotech'),
                        'company_logo' => (string)($info['company_logo'] ?? ''),
                        'company_website' => (string)($info['company_website'] ?? ''),
                        'candidate_name' => (string)($info['candidate_name'] ?? 'Candidate'),
                        'location' => (string)$location,
                        'meeting_link' => (string)$meetingLink
                ]
            );

            // Send chat notification
            $chatMessage = "Hi " . ($info['candidate_name'] ?? 'Candidate') . ",\n\n" .
                           "We have scheduled an interview for the " . ($info['job_title'] ?? '') . " position at " . ($info['company_name'] ?? 'our company') . ".\n\n" .
                           "Date & Time: " . date('M d, Y h:i A', $startTime) . "\n" .
                           "Location: " . $location . "\n" .
                           (!empty($meetingLink) ? "Meeting Link: " . $meetingLink . "\n" : "") .
                           "\nPlease check your email for full details.";
            \App\Services\NotificationService::queueChatNotification(
                (int)$employer->id,
                (int)$info['candidate_user_id'],
                $chatMessage
            );

            \App\Services\NotificationService::notifyInterviewScheduled(
                (int)$info['candidate_user_id'],
                (string)($info['job_title'] ?? ''),
                date('M d, Y h:i A', $startTime)
            );

            // WhatsApp notifications (candidate + employer)
            try {
                $candidateUser = \App\Models\User::find((int)($info['candidate_user_id'] ?? 0));
                $candidatePhone = (string)($candidateUser->attributes['phone'] ?? '');
                $empUser = \App\Models\User::find((int)$employer->attributes['user_id'] ?? (int)$employer->id);
                $employerPhone = (string)($empUser->attributes['phone'] ?? '');

                $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
                $candToken = \App\Services\NotificationService::generateJoinToken((int)$interviewId, 'candidate', (int)($info['candidate_user_id'] ?? 0), 7200);
                $empToken = \App\Services\NotificationService::generateJoinToken((int)$interviewId, 'employer', (int)($empUser->attributes['id'] ?? 0), 7200);
                $secureLinkCandidate = ($base !== '' ? $base : '') . '/interview/join?token=' . urlencode($candToken) . '&type=candidate';
                $secureLinkEmployer = ($base !== '' ? $base : '') . '/interview/join?token=' . urlencode($empToken) . '&type=employer';

                $common = [
                    'company_name' => (string)($info['company_name'] ?? ''),
                    'job_title' => (string)($info['job_title'] ?? ''),
                    'scheduled_start' => date('Y-m-d H:i:s', $startTime),
                    'scheduled_end' => date('Y-m-d H:i:s', $endTime),
                    'timezone' => (string)$timezone,
                    'interview_type' => (string)$interviewType,
                    'candidate_name' => (string)($info['candidate_name'] ?? 'Candidate'),
                    'candidate_phone' => $candidatePhone,
                    'employer_id' => (int)$employer->id,
                    'candidate_user_id' => (int)($info['candidate_user_id'] ?? 0),
                ];
                if ($candidatePhone !== '') {
                    $dataCand = $common + ['secure_link' => $secureLinkCandidate];
                    \App\Services\NotificationService::queueWhatsApp($candidatePhone, 'interview_scheduled_candidate', $dataCand);
                }
                if ($employerPhone !== '') {
                    $dataEmp = $common + ['secure_link' => $secureLinkEmployer];
                    \App\Services\NotificationService::queueWhatsApp($employerPhone, 'interview_scheduled_employer', $dataEmp);
                }
            } catch (\Throwable $t) {
                error_log('WhatsApp schedule error: ' . $t->getMessage());
            }
        }

        $response->json([
                'success' => true,
                'message' => 'Interview scheduled successfully',
                'interview_id' => $interviewId
            ]);
        } catch (\Exception $e) {
            error_log("Schedule interview error: " . $e->getMessage());
            $response->json(['error' => 'Failed to schedule interview', 'message' => $e->getMessage()], 500);
        }
    }

    public function reschedule(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $interviewId = (int)$request->param('id');
        $scheduledStart = $request->post('scheduled_start');
        $scheduledEnd = $request->post('scheduled_end');
        $timezone = $request->post('timezone', 'Asia/Kolkata');
        $location = $request->post('location', '');
        $meetingLink = $request->post('meeting_link', '');

        if (!$scheduledStart || !$scheduledEnd) {
            $response->json(['error' => 'Missing required fields'], 400);
            return;
        }

        $db = \App\Core\Database::getInstance();

        // Verify interview belongs to employer
        $interviewSql = "SELECT * FROM interviews WHERE id = :id AND employer_id = :employer_id";
        $interview = $db->fetchOne($interviewSql, [
            'id' => $interviewId,
            'employer_id' => $employer->id
        ]);

        if (!$interview) {
            $response->json(['error' => 'Interview not found or access denied'], 404);
            return;
        }

        if (($interview['interview_type'] ?? '') === 'video' && $meetingLink === '' && ($interview['meeting_link'] ?? '') === '') {
            $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
            $meetingLink = ($base !== '' ? $base : '') . '/interviews/' . (int)$interviewId . '/room';
        }

        $startTime = strtotime($scheduledStart);
        $endTime = strtotime($scheduledEnd);
        if ($startTime === false || $endTime === false || $endTime <= $startTime) {
            $response->json(['error' => 'Invalid date/time range'], 400);
            return;
        }

        try {
            $updateSql = "UPDATE interviews 
                         SET scheduled_start = :scheduled_start, 
                             scheduled_end = :scheduled_end,
                             timezone = :timezone,
                             location = :location,
                             meeting_link = :meeting_link,
                             status = 'rescheduled',
                             updated_at = NOW()
                         WHERE id = :id AND employer_id = :employer_id";

            $db->query($updateSql, [
                'id' => $interviewId,
                'employer_id' => $employer->id,
                'scheduled_start' => date('Y-m-d H:i:s', $startTime),
                'scheduled_end' => date('Y-m-d H:i:s', $endTime),
                'timezone' => $timezone,
                'location' => $location ?: $interview['location'],
                'meeting_link' => $meetingLink !== '' ? $meetingLink : ($interview['meeting_link'] ?? null)
            ]);
            
            $infoSql = "SELECT j.title as job_title, u.id as candidate_user_id, u.email as candidate_email 
                        FROM interviews i
                        INNER JOIN applications a ON i.application_id = a.id
                        INNER JOIN jobs j ON a.job_id = j.id 
                        INNER JOIN users u ON a.candidate_user_id = u.id
                        WHERE i.id = :id";
            $info = $db->fetchOne($infoSql, ['id' => $interviewId]);
            if ($info && !empty($info['candidate_email'])) {
                \App\Services\NotificationService::queueEmail(
                    $info['candidate_email'],
                    'interview_rescheduled',
                    [
                        'job_title' => (string)($info['job_title'] ?? ''),
                        'scheduled_time' => date('M d, Y h:i A', $startTime),
                        'employer_id' => (int)$employer->id,
                        'candidate_user_id' => (int)$info['candidate_user_id'],
                        'interview_id' => (int)$interviewId
                    ]
                );
            }

            try {
                $candidateUser = \App\Models\User::find((int)($info['candidate_user_id'] ?? 0));
                $candidatePhone = (string)($candidateUser->attributes['phone'] ?? '');
                $empUser = \App\Models\User::find((int)$employer->attributes['user_id'] ?? (int)$employer->id);
                $employerPhone = (string)($empUser->attributes['phone'] ?? '');
                $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
                $candToken = \App\Services\NotificationService::generateJoinToken((int)$interviewId, 'candidate', (int)($info['candidate_user_id'] ?? 0), 7200);
                $empToken = \App\Services\NotificationService::generateJoinToken((int)$interviewId, 'employer', (int)($empUser->attributes['id'] ?? 0), 7200);
                $secureLinkCandidate = ($base !== '' ? $base : '') . '/interview/join?token=' . urlencode($candToken) . '&type=candidate';
                $secureLinkEmployer = ($base !== '' ? $base : '') . '/interview/join?token=' . urlencode($empToken) . '&type=employer';
                $common = [
                    'company_name' => (string)($info['company_name'] ?? ''),
                    'job_title' => (string)($info['job_title'] ?? ''),
                    'scheduled_start' => date('Y-m-d H:i:s', $startTime),
                    'scheduled_end' => date('Y-m-d H:i:s', $endTime),
                    'timezone' => (string)$timezone,
                    'interview_type' => (string)$interviewType,
                    'candidate_name' => (string)($info['candidate_name'] ?? 'Candidate'),
                    'candidate_phone' => $candidatePhone,
                    'employer_id' => (int)$employer->id,
                    'candidate_user_id' => (int)($info['candidate_user_id'] ?? 0),
                ];
                if ($candidatePhone !== '') {
                    \App\Services\NotificationService::queueWhatsApp($candidatePhone, 'interview_rescheduled', $common + ['secure_link' => $secureLinkCandidate]);
                }
                if ($employerPhone !== '') {
                    \App\Services\NotificationService::queueWhatsApp($employerPhone, 'interview_rescheduled', $common + ['secure_link' => $secureLinkEmployer]);
                }
            } catch (\Throwable $t) {
                error_log('WhatsApp reschedule error: ' . $t->getMessage());
            }

            $response->json([
                'success' => true,
                'message' => 'Interview rescheduled successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Reschedule interview error: " . $e->getMessage());
            $response->json(['error' => 'Failed to reschedule interview', 'message' => $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $interviewId = (int)$request->param('id');
        $db = \App\Core\Database::getInstance();

        // Verify interview belongs to employer
        $interviewSql = "SELECT * FROM interviews WHERE id = :id AND employer_id = :employer_id";
        $interview = $db->fetchOne($interviewSql, [
            'id' => $interviewId,
            'employer_id' => $employer->id
        ]);

        if (!$interview) {
            $response->json(['error' => 'Interview not found or access denied'], 404);
            return;
        }

        try {
            $updateSql = "UPDATE interviews 
                         SET status = 'cancelled', updated_at = NOW()
                         WHERE id = :id AND employer_id = :employer_id";

            $db->query($updateSql, [
                'id' => $interviewId,
                'employer_id' => $employer->id
            ]);
            
            $infoSql = "SELECT j.title as job_title, u.id as candidate_user_id, u.email as candidate_email 
                        FROM interviews i
                        INNER JOIN applications a ON i.application_id = a.id
                        INNER JOIN jobs j ON a.job_id = j.id 
                        INNER JOIN users u ON a.candidate_user_id = u.id
                        WHERE i.id = :id";
            $info = $db->fetchOne($infoSql, ['id' => $interviewId]);
            if ($info && !empty($info['candidate_email'])) {
                \App\Services\NotificationService::queueEmail(
                    $info['candidate_email'],
                    'interview_cancelled',
                    [
                        'job_title' => (string)($info['job_title'] ?? ''),
                        'employer_id' => (int)$employer->id,
                        'candidate_user_id' => (int)$info['candidate_user_id'],
                        'interview_id' => (int)$interviewId
                    ]
                );
            }

            try {
                $candidateUser = \App\Models\User::find((int)($info['candidate_user_id'] ?? 0));
                $candidatePhone = (string)($candidateUser->attributes['phone'] ?? '');
                $empUser = \App\Models\User::find((int)$employer->attributes['user_id'] ?? (int)$employer->id);
                $employerPhone = (string)($empUser->attributes['phone'] ?? '');
                $common = [
                    'company_name' => (string)($info['company_name'] ?? ''),
                    'job_title' => (string)($info['job_title'] ?? ''),
                    'candidate_name' => (string)($info['candidate_name'] ?? 'Candidate'),
                    'employer_id' => (int)$employer->id,
                    'candidate_user_id' => (int)($info['candidate_user_id'] ?? 0),
                ];
                if ($candidatePhone !== '') {
                    \App\Services\NotificationService::queueWhatsApp($candidatePhone, 'interview_cancelled', $common);
                }
                if ($employerPhone !== '') {
                    \App\Services\NotificationService::queueWhatsApp($employerPhone, 'interview_cancelled', $common);
                }
            } catch (\Throwable $t) {
                error_log('WhatsApp cancel error: ' . $t->getMessage());
            }

            $response->json([
                'success' => true,
                'message' => 'Interview cancelled successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Cancel interview error: " . $e->getMessage());
            $response->json(['error' => 'Failed to cancel interview', 'message' => $e->getMessage()], 500);
        }
    }

    public function complete(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $interviewId = (int)$request->param('id');
        $db = \App\Core\Database::getInstance();

        // Verify interview belongs to employer
        $interviewSql = "SELECT * FROM interviews WHERE id = :id AND employer_id = :employer_id";
        $interview = $db->fetchOne($interviewSql, [
            'id' => $interviewId,
            'employer_id' => $employer->id
        ]);

        if (!$interview) {
            $response->json(['error' => 'Interview not found or access denied'], 404);
            return;
        }

        try {
            $updateSql = "UPDATE interviews 
                         SET status = 'completed', updated_at = NOW()
                         WHERE id = :id AND employer_id = :employer_id";

            $db->query($updateSql, [
                'id' => $interviewId,
                'employer_id' => $employer->id
            ]);

            $response->json([
                'success' => true,
                'message' => 'Interview marked as completed successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Complete interview error: " . $e->getMessage());
            $response->json(['error' => 'Failed to complete interview', 'message' => $e->getMessage()], 500);
        }
    }
}

