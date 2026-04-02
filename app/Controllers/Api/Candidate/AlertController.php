<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\JobAlert;
use App\Models\Job;
use App\Services\NotificationService;

class AlertController extends ApiController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * POST /api/v1/candidate/job-alerts
     * Create a job alert
     */
    public function create(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'title' => 'required',
            'keywords' => 'sometimes',
            'location' => 'sometimes',
            'job_title' => 'sometimes',
            'company' => 'sometimes',
            'min_salary' => 'sometimes|numeric',
            'max_salary' => 'sometimes|numeric',
            'job_type' => 'sometimes',
            'experience_level' => 'sometimes',
            'notify_frequency' => 'sometimes|in:daily,weekly,monthly'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $alert = new JobAlert();
        $alert->fill([
            'candidate_id' => $user->id,
            'title' => $data['title'],
            'keywords' => $data['keywords'] ?? null,
            'location' => $data['location'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'company' => $data['company'] ?? null,
            'min_salary' => $data['min_salary'] ?? null,
            'max_salary' => $data['max_salary'] ?? null,
            'job_type' => $data['job_type'] ?? null,
            'experience_level' => $data['experience_level'] ?? null,
            'notify_frequency' => $data['notify_frequency'] ?? 'weekly',
            'is_active' => true
        ]);
        $alert->save();

        $this->success($response, ['alert_id' => $alert->id], 'Job alert created');
    }

    /**
     * GET /api/v1/candidate/job-alerts
     * List all job alerts
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->input('page', 1);
        $limit = (int)$request->input('limit', 20);
        $offset = ($page - 1) * $limit;

        $total = JobAlert::where('candidate_id', '=', $user->id)->count();
        $alerts = JobAlert::where('candidate_id', '=', $user->id)
            ->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($alerts as $alert) {
            $matchingCount = $this->countMatchingJobs($alert);
            $data[] = [
                'id' => $alert->id,
                'title' => $alert->title,
                'keywords' => $alert->keywords,
                'location' => $alert->location,
                'job_title' => $alert->job_title,
                'notify_frequency' => $alert->notify_frequency,
                'is_active' => (bool)$alert->is_active,
                'matching_jobs_count' => $matchingCount,
                'created_at' => $alert->created_at,
                'last_notified_at' => $alert->last_notified_at
            ];
        }

        $this->success($response, [
            'alerts' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * PUT /api/v1/candidate/job-alerts/{id}
     * Update a job alert
     */
    public function update(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $alert = JobAlert::find((int)$params['id']);
        if (!$alert || $alert->candidate_id !== $user->id) {
            $this->error($response, 'Alert not found', 404);
            return;
        }

        $data = $request->getJsonBody();
        
        if (isset($data['title'])) {
            $alert->title = $data['title'];
        }
        if (isset($data['keywords'])) {
            $alert->keywords = $data['keywords'];
        }
        if (isset($data['location'])) {
            $alert->location = $data['location'];
        }
        if (isset($data['job_title'])) {
            $alert->job_title = $data['job_title'];
        }
        if (isset($data['min_salary'])) {
            $alert->min_salary = $data['min_salary'];
        }
        if (isset($data['max_salary'])) {
            $alert->max_salary = $data['max_salary'];
        }
        if (isset($data['notify_frequency'])) {
            $alert->notify_frequency = $data['notify_frequency'];
        }
        if (isset($data['is_active'])) {
            $alert->is_active = (bool)$data['is_active'];
        }

        $alert->save();

        $this->success($response, null, 'Alert updated');
    }

    /**
     * DELETE /api/v1/candidate/job-alerts/{id}
     * Delete a job alert
     */
    public function delete(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $alert = JobAlert::find((int)$params['id']);
        if (!$alert || $alert->candidate_id !== $user->id) {
            $this->error($response, 'Alert not found', 404);
            return;
        }

        $alert->delete();

        $this->success($response, null, 'Alert deleted');
    }

    /**
     * GET /api/v1/candidate/job-alerts/{id}/count
     * Get count of matching jobs for an alert
     */
    public function matchingCount(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $alert = JobAlert::find((int)$params['id']);
        if (!$alert || $alert->candidate_id !== $user->id) {
            $this->error($response, 'Alert not found', 404);
            return;
        }

        $count = $this->countMatchingJobs($alert);

        $this->success($response, [
            'alert_id' => $alert->id,
            'matching_jobs_count' => $count
        ]);
    }

    /**
     * Count matching jobs for an alert
     */
    private function countMatchingJobs(JobAlert $alert): int
    {
        $query = Job::where('status', '=', 'published')
            ->whereNull('deleted_at');

        if ($alert->location) {
            $query->where('location', 'like', '%' . $alert->location . '%');
        }

        if ($alert->job_title) {
            $query->where('title', 'like', '%' . $alert->job_title . '%');
            $query->orWhere('position', 'like', '%' . $alert->job_title . '%');
        }

        if ($alert->keywords) {
            $keywords = explode(',', $alert->keywords);
            foreach ($keywords as $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('title', 'like', '%' . trim($keyword) . '%')
                      ->orWhere('description', 'like', '%' . trim($keyword) . '%');
                });
            }
        }

        if ($alert->company) {
            $query->where('company_name', 'like', '%' . $alert->company . '%');
        }

        if ($alert->min_salary) {
            $query->where('salary_min', '>=', (int)$alert->min_salary);
        }

        if ($alert->max_salary) {
            $query->where('salary_max', '<=', (int)$alert->max_salary);
        }

        if ($alert->job_type) {
            $query->where('job_type', '=', $alert->job_type);
        }

        if ($alert->experience_level) {
            $query->where('experience_level', '=', $alert->experience_level);
        }

        return $query->count();
    }
}
