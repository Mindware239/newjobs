<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Job;
use App\Services\NotificationService;
use App\Services\MailService;

class JobsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $page = (int)($request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $sort = $request->get('sort', 'created_at');

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(j.title LIKE :search OR e.company_name LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== 'all') {
            $where[] = "j.status = :status";
            $params['status'] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $total = (int)($db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM jobs j
             LEFT JOIN employers e ON e.id = j.employer_id
             {$whereClause}",
            $params
        )['count'] ?? 0);

        // Get jobs
        $jobs = $db->fetchAll(
            "SELECT j.*, e.company_name, e.kyc_status,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as applications_count
             FROM jobs j
             LEFT JOIN employers e ON e.id = j.employer_id
             {$whereClause}
             ORDER BY j.{$sort} DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        $response->view('admin/jobs/index', [
            'title' => 'Manage Jobs',
            'jobs' => $jobs,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sort
            ],
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $db = Database::getInstance();

        $job = $db->fetchOne(
            "SELECT j.*, e.company_name, e.kyc_status, u.email as employer_email
             FROM jobs j
             LEFT JOIN employers e ON e.id = j.employer_id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE j.slug = :slug",
            ['slug' => $slug]
        );

        if (!$job) {
            $response->redirect('/admin/jobs');
            return;
        }

        // Get applications
        $applications = $db->fetchAll(
            "SELECT a.*, cand.id as candidate_id, cand.full_name, u.email
             FROM applications a
             INNER JOIN users u ON u.id = a.candidate_user_id
             LEFT JOIN candidates cand ON cand.user_id = u.id
             WHERE a.job_id = :job_id
             ORDER BY a.applied_at DESC",
            ['job_id' => $job['id']]
        );

        // Get locations
        $locations = $db->fetchAll(
            "SELECT * FROM job_locations WHERE job_id = :job_id",
            ['job_id' => $job['id']]
        );

        // Get skills
        $skills = $db->fetchAll(
            "SELECT s.name FROM job_skills js
             INNER JOIN skills s ON s.id = js.skill_id
             WHERE js.job_id = :job_id",
            ['job_id' => $job['id']]
        );

        $response->view('admin/jobs/show', [
            'title' => 'Job Details - ' . ($job['title'] ?? 'Unknown'),
            'job' => $job,
            'applications' => $applications,
            'locations' => $locations,
            'skills' => $skills,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function approve(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $job = Job::where('slug', '=', $slug)->first();

        if ($job) {
            $id = $job->id ?? $job->attributes['id'] ?? null;
            $oldStatus = $job->status ?? 'pending_review';
            $job->status = 'published';
            $job->approved_at = date('Y-m-d H:i:s');
            $job->save();

            $this->logAction('approve_job', ['job_id' => $id]);
            
            // Send notification to employer when job is published
            if ($oldStatus !== 'published') {
                $employer = \App\Models\Employer::find((int)($job->attributes['employer_id'] ?? 0));
                /** @var \App\Models\Employer|null $employer */
                if ($employer) {
                    $user = $employer->user();
                    if ($user) {
                        // Create in-app notification
                        \App\Services\NotificationService::notify(
                            $user->id,
                            'job_published',
                            'Job Published Successfully!',
                            "Your job posting '{$job->title}' has been approved and published. It's now live and visible to candidates.",
                            "/employer/jobs/{$job->slug}"
                        );
                        
                        NotificationService::queueEmail(
                            $user->email,
                            'job_published_employer',
                            [
                                'job_title' => (string)($job->title ?? ''),
                                'job_slug' => (string)($job->slug ?? ''),
                                'employer_id' => (int)$employer->id
                            ]
                        );
                        error_log("✓ Job published notification queued for employer: " . $user->email);
                    }
                }
            }
        }

        $response->redirect('/admin/jobs/' . $slug);
    }

    public function reject(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $reason = $request->post('reason', '');
        $job = Job::where('slug', '=', $slug)->first();

        if ($job) {
            $id = $job->id ?? $job->attributes['id'] ?? null;
            $job->status = 'rejected';
            $job->save();

            $this->logAction('reject_job', ['job_id' => $id, 'reason' => $reason]);
            
            $employer = \App\Models\Employer::find((int)($job->attributes['employer_id'] ?? 0));
            /** @var \App\Models\Employer|null $employer */
            if ($employer) {
                $user = $employer->user();
                if ($user) {
                    NotificationService::queueEmail(
                        $user->email,
                        'job_rejected_employer',
                        [
                            'job_title' => (string)($job->title ?? ''),
                            'reason' => (string)$reason,
                            'employer_id' => (int)$employer->id
                        ]
                    );
                    error_log("✓ Job rejection notification queued for employer: " . $user->email);
                }
            }
        }

        $response->redirect('/admin/jobs/' . $slug);
    }

    public function takeDown(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $reason = $request->post('reason', '');
        $job = Job::where('slug', '=', $slug)->first();

        if ($job) {
            $id = $job->id ?? $job->attributes['id'] ?? null;
            $job->status = 'taken_down';
            $job->save();

            $this->logAction('take_down_job', ['job_id' => $id, 'reason' => $reason]);
        }

        $response->redirect('/admin/jobs/' . $slug);
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }

    private function logAction(string $action, array $data = []): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address, created_at)
                 VALUES (:user_id, :action, :entity_type, :entity_id, :old_value, :new_value, :ip_address, NOW())",
                [
                    'user_id' => $this->currentUser->id,
                    'action' => $action,
                    'entity_type' => 'job',
                    'entity_id' => $data['job_id'] ?? null,
                    'old_value' => json_encode($data),
                    'new_value' => json_encode(['status' => 'changed']),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}

