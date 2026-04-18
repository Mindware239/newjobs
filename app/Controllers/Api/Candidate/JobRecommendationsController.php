<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;

class JobRecommendationsController extends ApiController
{
    public function getRecommendedJobs(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate profile not found', 404);
            return;
        }

        $candidateId = $candidate->attributes['id'];
        $db = \App\Core\Database::getInstance();
        
        $sql = "SELECT
                    j.id, j.title, j.slug, j.short_description,
                    j.salary_min, j.salary_max, j.currency,
                    j.employment_type, j.is_remote, j.company_name, j.location,
                    cjs.overall_match_score, cjs.recommendation
                FROM candidate_job_scores cjs
                JOIN jobs j ON cjs.job_id = j.id
                WHERE cjs.candidate_id = :candidate_id AND j.status = 'published'
                ORDER BY cjs.overall_match_score DESC
                LIMIT 20";

        $recommendedJobs = $db->fetchAll($sql, ['candidate_id' => $candidateId]);
        
        $this->success($response, [
            'recommended_jobs' => $recommendedJobs ?: []
        ], 'Recommendations fetched successfully');
    }
}
