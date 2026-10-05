<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Job;
use App\Services\NotificationService;

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
            $where[] = "(j.title LIKE :s1 OR e.company_name LIKE :s2)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        \App\Services\JobApprovalService::ensureStatusEnum();
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
            \App\Services\JobApprovalService::ensureStatusEnum();
            $job->status = 'published';
            $job->save();
            \App\Services\JobApprovalService::resolveQueue((int)$id, 'approved', (int)($this->currentUser->id ?? 0) ?: null);

            $this->logAction('approve_job', ['job_id' => $id]);
            
            // Send notification to employer when job is published
            if ($oldStatus !== 'published') {
                $employer = \App\Models\Employer::find((int)($job->attributes['employer_id'] ?? 0));
                /** @var \App\Models\Employer|null $employer */
                if ($employer) {
                    $user = $employer->user();
                    if ($user) {
                        // Use centralized NotificationService
                        \App\Services\NotificationService::send(
                            (int)$user->id,
                            'job_published',
                            'Job Published Successfully!',
                            "Your job posting '{$job->title}' has been approved and published. It's now live and visible to candidates.",
                            [
                                'job_title' => (string)($job->title ?? ''),
                                'job_slug' => (string)($job->slug ?? ''),
                                'employer_id' => (int)$employer->id,
                                'email_template' => 'job_published_employer',
                                'reference_id' => (string)($job->id ?? $id)
                            ],
                            "/employer/jobs/{$job->slug}"
                        );
                        error_log("✓ Job published notification sent via service for employer: " . $user->email);
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
            \App\Services\JobApprovalService::ensureStatusEnum();
            $job->status = 'rejected';
            $job->save();
            \App\Services\JobApprovalService::resolveQueue((int)$id, 'rejected', (int)($this->currentUser->id ?? 0) ?: null, (string)$reason);

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
            \App\Services\JobApprovalService::ensureStatusEnum();
            $job->status = 'taken_down';
            $job->save();

            $this->logAction('take_down_job', ['job_id' => $id, 'reason' => $reason]);
        }

        $response->redirect('/admin/jobs/' . $slug);
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT name FROM job_categories WHERE is_active = 1 ORDER BY name ASC");

        $response->view('admin/jobs/create', [
            'title' => 'Create Job',
            'categories' => $categories,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function store(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $data = $request->all();
        $job = new Job();
        
        // Find or create Admin Employer ID to satisfy foreign key constraint
        $adminUser = $db->fetchOne("SELECT id FROM users WHERE role IN ('admin', 'super_admin') LIMIT 1");
        $adminEmployerId = 0;
        if ($adminUser) {
            $adminEmployer = $db->fetchOne("SELECT id FROM employers WHERE user_id = :user_id LIMIT 1", ['user_id' => $adminUser['id']]);
            if ($adminEmployer) {
                $adminEmployerId = $adminEmployer['id'];
            } else {
                // Create one if not exists (fallback)
                $db->query("INSERT INTO employers (user_id, company_name, company_slug, verified, kyc_status) 
                            VALUES (:user_id, 'Jobsence', 'jobsence-admin', 1, 'approved')", 
                            ['user_id' => $adminUser['id']]);
                $adminEmployerId = $db->fetchOne("SELECT LAST_INSERT_ID() as id")['id'];
            }
        }

        // Handle logo upload
        $logoPath = null;
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/company/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('logo_') . '.' . $extension;
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadDir . $fileName)) {
                $logoPath = '/' . $uploadDir . $fileName;
            }
        }

        $title = $data['title'] ?? '';
        $slug = $job->generateSlug($title);

        $job->fill([
            'employer_id' => $adminEmployerId, // Admin posted job linked to admin employer account
            'title' => $title,
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'company_name' => $data['company_name'] ?? '',
            'company_logo' => $logoPath,
            'locations' => json_encode([['city' => $data['location'] ?? '', 'state' => '', 'country' => 'India']]),
            'experience_type' => 'any',
            'min_experience' => $data['min_experience'] ?? null,
            'max_experience' => $data['max_experience'] ?? null,
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'category' => $data['category'] ?? '',
            'job_type' => $data['job_type'] ?? 'internal',
            'apply_link' => $data['job_type'] === 'external' ? ($data['apply_link'] ?? '') : null,
            'status' => 'published',
            'visibility' => 'public',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($job->save()) {
            $this->logAction('create_job', ['job_id' => $job->id]);
            $response->redirect('/admin/jobs');
        } else {
            $response->redirect('/admin/jobs/create?error=1');
        }
    }

    public function edit(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $job = Job::where('slug', '=', $slug)->first();

        if (!$job) {
            $response->redirect('/admin/jobs');
            return;
        }

        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT name FROM job_categories WHERE is_active = 1 ORDER BY name ASC");

        $response->view('admin/jobs/edit', [
            'title' => 'Edit Job',
            'job' => $job->toArray(),
            'categories' => $categories,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $job = Job::where('slug', '=', $slug)->first();

        if (!$job) {
            $response->redirect('/admin/jobs');
            return;
        }

        $data = $request->all();
        
        // Handle logo upload
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/company/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('logo_') . '.' . $extension;
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadDir . $fileName)) {
                $job->company_logo = '/' . $uploadDir . $fileName;
            }
        }

        $job->title = $data['title'] ?? $job->title;
        $job->description = $data['description'] ?? $job->description;
        $job->company_name = $data['company_name'] ?? $job->company_name;
        $job->min_experience = $data['min_experience'] ?? $job->min_experience;
        $job->max_experience = $data['max_experience'] ?? $job->max_experience;
        $job->salary_min = $data['salary_min'] ?? $job->salary_min;
        $job->salary_max = $data['salary_max'] ?? $job->salary_max;
        $job->category = $data['category'] ?? $job->category;
        $job->job_type = $data['job_type'] ?? $job->job_type;
        $job->apply_link = $data['job_type'] === 'external' ? ($data['apply_link'] ?? '') : null;
        $job->updated_at = date('Y-m-d H:i:s');

        // Handle location update
        if (isset($data['location'])) {
            $job->locations = json_encode([['city' => $data['location'], 'state' => '', 'country' => 'India']]);
        }

        if ($job->save()) {
            $this->logAction('update_job', ['job_id' => $job->id]);
            $response->redirect('/admin/jobs');
        } else {
            $response->redirect('/admin/jobs/edit/' . $slug . '?error=1');
        }
    }

    public function delete(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $slug = (string)$request->param('slug');
        $job = Job::where('slug', '=', $slug)->first();

        if ($job) {
            $id = $job->id;
            $job->delete();
            $this->logAction('delete_job', ['job_id' => $id]);
        }

        $response->redirect('/admin/jobs');
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

