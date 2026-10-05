<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Interview;
use App\Models\Application;
use App\Models\Employer;
use App\Models\User;
use App\Services\NotificationService;
use App\Core\Database;

class InterviewController extends ApiController
{
    /**
     * GET /api/v1/employer/interviews/stats
     * Get interview statistics for the dashboard
     */
    public function stats(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $db = Database::getInstance();
        $employerId = (int)$employer->id;

        $statsSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN (i.status = 'live' OR (i.status IN ('scheduled', 'rescheduled') AND i.scheduled_start >= NOW())) THEN 1 ELSE 0 END) as upcoming,
                    SUM(CASE WHEN DATE(i.scheduled_start) = CURDATE() AND i.status IN ('scheduled', 'rescheduled', 'live') THEN 1 ELSE 0 END) as today,
                    SUM(CASE WHEN DATE(i.scheduled_start) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND i.status IN ('scheduled', 'rescheduled', 'live') THEN 1 ELSE 0 END) as this_week,
                    SUM(CASE WHEN i.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN i.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM interviews i
                WHERE i.employer_id = :employer_id";

        $stats = $db->fetchOne($statsSql, ['employer_id' => $employerId]);

        $this->success($response, $stats ?: [
            'total' => 0,
            'upcoming' => 0,
            'today' => 0,
            'this_week' => 0,
            'completed' => 0,
            'cancelled' => 0
        ]);
    }

    /**
     * GET /api/v1/employer/interviews
     * List interviews for the authenticated employer
     */
    public function index(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $db = Database::getInstance();
        $employerId = (int)$employer->id;

        // Get filter parameters
        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search', '');
        $typeFilter = $request->query('type', 'all');
        $sortBy = $request->query('sort_by', 'date');
        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);
        $offset = ($page - 1) * $perPage;

        // Build WHERE conditions
        $whereConditions = ["i.employer_id = :employer_id"];
        $params = ['employer_id' => $employerId];

        // Status filter
        switch ($statusFilter) {
            case 'upcoming':
                $whereConditions[] = "(i.status = 'live' OR (i.status IN ('scheduled', 'rescheduled') AND i.scheduled_start >= NOW()))";
                break;
            case 'today':
                $whereConditions[] = "DATE(i.scheduled_start) = CURDATE() AND i.status IN ('scheduled', 'rescheduled', 'live')";
                break;
            case 'week':
                $whereConditions[] = "DATE(i.scheduled_start) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND i.status IN ('scheduled', 'rescheduled', 'live')";
                break;
            case 'completed':
                $whereConditions[] = "i.status = 'completed'";
                break;
            case 'cancelled':
                $whereConditions[] = "i.status = 'cancelled'";
                break;
        }

        if ($typeFilter !== 'all') {
            $whereConditions[] = "i.interview_type = :interview_type";
            $params['interview_type'] = $typeFilter;
        }

        if (!empty($search)) {
            $whereConditions[] = "(c.full_name LIKE :search OR j.title LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as count 
                    FROM interviews i
                    INNER JOIN applications a ON i.application_id = a.id
                    INNER JOIN jobs j ON a.job_id = j.id
                    LEFT JOIN candidates c ON a.candidate_user_id = c.user_id
                    WHERE $whereClause";
        $totalCount = (int)$db->fetchOne($countSql, $params)['count'];

        // Get interviews
        $interviewsSql = "SELECT 
                    i.*,
                    a.status as application_status,
                    j.title as job_title,
                    j.slug as job_slug,
                    c.full_name as candidate_name,
                    c.profile_picture as candidate_picture
                FROM interviews i
                INNER JOIN applications a ON i.application_id = a.id
                INNER JOIN jobs j ON a.job_id = j.id
                LEFT JOIN candidates c ON a.candidate_user_id = c.user_id
                WHERE $whereClause";

        // Sorting
        switch ($sortBy) {
            case 'candidate':
                $interviewsSql .= " ORDER BY c.full_name ASC";
                break;
            case 'job':
                $interviewsSql .= " ORDER BY j.title ASC";
                break;
            default:
                $interviewsSql .= " ORDER BY i.scheduled_start DESC";
                break;
        }

        $interviewsSql .= " LIMIT $perPage OFFSET $offset";

        $interviews = $db->fetchAll($interviewsSql, $params);

        // Format data
        foreach ($interviews as &$interview) {
            $interview['formatted_date'] = date('M d, Y', strtotime($interview['scheduled_start']));
            $interview['formatted_time'] = date('h:i A', strtotime($interview['scheduled_start']));
            $interview['is_past'] = strtotime($interview['scheduled_start']) < time();
        }

        $this->success($response, [
            'interviews' => $interviews,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => ceil($totalCount / $perPage)
            ]
        ]);
    }

    /**
     * POST /api/v1/employer/interviews
     * Schedule a new interview
     */
    public function schedule(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'application_id' => 'required',
            'interview_type' => 'required|in:phone,video,onsite',
            'scheduled_start' => 'required',
            'scheduled_end' => 'required',
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $applicationId = (int)$data['application_id'];
        $db = Database::getInstance();

        // Verify application belongs to employer
        $application = $db->fetchOne(
            "SELECT a.* FROM applications a INNER JOIN jobs j ON a.job_id = j.id WHERE a.id = :id AND j.employer_id = :employer_id",
            ['id' => $applicationId, 'employer_id' => $employer->id]
        );

        if (!$application) {
            $this->error($response, 'Application not found or access denied', 404);
            return;
        }

        try {
            $db->query(
                "INSERT INTO interviews (application_id, employer_id, scheduled_by, interview_type, scheduled_start, scheduled_end, timezone, location, meeting_link, status, created_at, updated_at) 
                 VALUES (:application_id, :employer_id, :scheduled_by, :interview_type, :scheduled_start, :scheduled_end, :timezone, :location, :meeting_link, 'scheduled', NOW(), NOW())",
                [
                    'application_id' => $applicationId,
                    'employer_id' => $employer->id,
                    'scheduled_by' => $this->user($request)->id,
                    'interview_type' => $data['interview_type'],
                    'scheduled_start' => $data['scheduled_start'],
                    'scheduled_end' => $data['scheduled_end'],
                    'timezone' => $data['timezone'] ?? 'Asia/Kolkata',
                    'location' => $data['location'] ?? '',
                    'meeting_link' => $data['meeting_link'] ?? ''
                ]
            );

            $interviewId = (int)$db->lastInsertId();

            // Auto-generate meeting link if video and empty
            if ($data['interview_type'] === 'video' && empty($data['meeting_link'])) {
                $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
                $meetingLink = $base . '/interviews/' . $interviewId . '/room';
                $db->query("UPDATE interviews SET meeting_link = :link WHERE id = :id", ['link' => $meetingLink, 'id' => $interviewId]);
            }

            // Update application status
            $db->query("UPDATE applications SET status = 'interview' WHERE id = :id", ['id' => $applicationId]);

            // Notify candidate
            NotificationService::notifyInterviewScheduled(
                (int)$application['candidate_user_id'],
                'Hiring Manager', // Or fetch job title
                $data['scheduled_start']
            );

            $this->success($response, ['interview_id' => $interviewId], 'Interview scheduled successfully', 201);
        } catch (\Exception $e) {
            $this->error($response, 'Failed to schedule interview: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/employer/interviews/{id}
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance();

        $interview = $db->fetchOne(
            "SELECT i.*, j.title as job_title, c.full_name as candidate_name 
             FROM interviews i
             INNER JOIN applications a ON i.application_id = a.id
             INNER JOIN jobs j ON a.job_id = j.id
             LEFT JOIN candidates c ON a.candidate_user_id = c.user_id
             WHERE i.id = :id AND i.employer_id = :employer_id",
            ['id' => $id, 'employer_id' => $employer->id]
        );

        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        $this->success($response, ['interview' => $interview]);
    }

    /**
     * POST /api/v1/employer/interviews/{id}/reschedule
     */
    public function reschedule(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $data = $request->getJsonBody();
        
        $db = Database::getInstance();
        $interview = $db->fetchOne("SELECT * FROM interviews WHERE id = :id AND employer_id = :employer_id", ['id' => $id, 'employer_id' => $employer->id]);

        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        try {
            $db->query(
                "UPDATE interviews SET scheduled_start = :start, scheduled_end = :end, status = 'rescheduled', updated_at = NOW() WHERE id = :id",
                [
                    'start' => $data['scheduled_start'],
                    'end' => $data['scheduled_end'],
                    'id' => $id
                ]
            );

            $this->success($response, [], 'Interview rescheduled successfully');
        } catch (\Exception $e) {
            $this->error($response, 'Failed to reschedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/employer/interviews/{id}/cancel
     */
    public function cancel(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance();

        $interview = $db->fetchOne("SELECT * FROM interviews WHERE id = :id AND employer_id = :employer_id", ['id' => $id, 'employer_id' => $employer->id]);

        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        try {
            $db->query("UPDATE interviews SET status = 'cancelled', updated_at = NOW() WHERE id = :id", ['id' => $id]);
            $this->success($response, [], 'Interview cancelled successfully');
        } catch (\Exception $e) {
            $this->error($response, 'Failed to cancel: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/employer/interviews/{id}/complete
     */
    public function complete(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance();

        $interview = $db->fetchOne("SELECT * FROM interviews WHERE id = :id AND employer_id = :employer_id", ['id' => $id, 'employer_id' => $employer->id]);

        if (!$interview) {
            $this->error($response, 'Interview not found', 404);
            return;
        }

        try {
            $db->query("UPDATE interviews SET status = 'completed', updated_at = NOW() WHERE id = :id", ['id' => $id]);
            $this->success($response, [], 'Interview marked as completed');
        } catch (\Exception $e) {
            $this->error($response, 'Failed to complete: ' . $e->getMessage(), 500);
        }
    }

    private function employer(Request $request, Response $response): ?Employer
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Employer authentication is required', 403);
            return null;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile is incomplete', 409);
            return null;
        }

        return $employer;
    }
}
