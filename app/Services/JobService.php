<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Job;
use App\Models\Application;
use App\Models\JobView;

class JobService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function searchJobs(array $filters, ?int $userId = null): array
    {
        $page = (int)($filters['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // This is a simplified search. The complex logic from Candidate\JobController should be migrated here.
        $sql = "SELECT j.*, e.company_name, e.logo_url as company_logo 
                FROM jobs j 
                LEFT JOIN employers e ON j.employer_id = e.id 
                WHERE j.status = 'published'";
        
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (j.title LIKE :keyword OR j.description LIKE :keyword)";
            $params['keyword'] = "%{$filters['keyword']}%";
        }

        if (!empty($filters['location'])) {
            $sql .= " AND j.locations LIKE :location";
            $params['location'] = "%{$filters['location']}%";
        }

        $total = $this->db->fetchOne("SELECT COUNT(*) as count FROM ({$sql}) as sub", $params)['count'] ?? 0;

        $sql .= " ORDER BY j.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $jobs = $this->db->fetchAll($sql, $params);

        return [
            'jobs' => $jobs,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    public function getJobBySlug(string $slug, ?int $userId = null): ?array
    {
        $sql = "SELECT j.*, e.company_name, e.description as company_description, e.logo_url as company_logo
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                WHERE j.slug = :slug AND j.status = 'published'";
        
        $job = $this->db->fetchOne($sql, ['slug' => $slug]);

        if (!$job) {
            return null;
        }

        if ($userId) {
            $candidate = \App\Models\Candidate::findByUserId($userId);
            if ($candidate) {
                $this->trackJobView($candidate->id, $job['id']);
            }
        }

        return $job;
    }

    public function applyForJob(int $jobId, int $candidateId, int $userId): array
    {
        $existing = Application::where('job_id', '=', $jobId)
                                ->where('candidate_id', '=', $candidateId)
                                ->first();
        
        if ($existing) {
            return ['success' => false, 'message' => 'You have already applied for this job', 'code' => 409];
        }

        $application = new Application();
        $application->fill([
            'job_id' => $jobId,
            'candidate_id' => $candidateId,
            'candidate_user_id' => $userId,
            'status' => 'applied',
            'applied_at' => date('Y-m-d H:i:s')
        ]);

        if ($application->save()) {
            return ['success' => true, 'application_id' => $application->id];
        } else {
            return ['success' => false, 'message' => 'Failed to submit application', 'code' => 500];
        }
    }

    private function trackJobView(int $candidateId, int $jobId): void
    {
        $today = date('Y-m-d');
        $existing = JobView::where('candidate_id', '=', $candidateId)
            ->where('job_id', '=', $jobId)
            ->where('viewed_at', '>=', $today)
            ->first();
        
        if (!$existing) {
            $view = new JobView();
            $view->fill([
                'candidate_id' => $candidateId,
                'job_id' => $jobId
            ]);
            $view->save();
        }
    }
}
