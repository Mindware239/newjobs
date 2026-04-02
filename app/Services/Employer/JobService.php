<?php

declare(strict_types=1);

namespace App\Services\Employer;

use App\Core\Database;
use App\Models\Job;

class JobService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getJobsByEmployer(int $employerId): array
    {
        return Job::where('employer_id', '=', $employerId)->get();
    }

    public function createJob(int $employerId, array $data): array
    {
        // Basic validation
        if (empty($data['title']) || empty($data['description'])) {
            return ['success' => false, 'message' => 'Title and description are required'];
        }

        $job = new Job();
        $job->fill(array_merge($data, ['employer_id' => $employerId]));
        
        if ($job->save()) {
            return ['success' => true, 'job' => $job->attributes];
        } else {
            return ['success' => false, 'message' => 'Failed to create job'];
        }
    }

    public function updateJob(int $employerId, int $jobId, array $data): array
    {
        $job = Job::find($jobId);

        if (!$job || (int)$job->employer_id !== $employerId) {
            return ['success' => false, 'message' => 'Job not found or you do not have permission to edit it', 'code' => 404];
        }

        $job->fill($data);

        if ($job->save()) {
            return ['success' => true, 'job' => $job->attributes];
        } else {
            return ['success' => false, 'message' => 'Failed to update job', 'code' => 500];
        }
    }

    public function deleteJob(int $employerId, int $jobId): array
    {
        $job = Job::find($jobId);

        if (!$job || (int)$job->employer_id !== $employerId) {
            return ['success' => false, 'message' => 'Job not found or you do not have permission to delete it', 'code' => 404];
        }

        if ($job->delete()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to delete job', 'code' => 500];
        }
    }
}
