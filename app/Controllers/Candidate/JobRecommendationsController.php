<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Job Recommendations Controller
 * 
 * Provides AI-powered job recommendations for candidates
 */
class JobRecommendationsController extends BaseController
{
    /**
     * Get recommended jobs for candidate
     * 
     * GET /candidate/jobs/recommended
     */
    public function getRecommendedJobs(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        $candidate = \App\Models\Candidate::findByUserId((int)$userId);
        if (!$candidate) {
            $response->redirect('/candidate/profile/complete');
            return;
        }

        $candidateId = $candidate->attributes['id'];

        // Get recommended jobs based on match scores
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT
                    j.id,
                    j.title,
                    j.slug,
                    j.description,
                    j.short_description,
                    j.salary_min,
                    j.salary_max,
                    j.currency,
                    j.employment_type,
                    j.is_remote,
                    j.company_name,
                    j.location,
                    j.created_at,
                    cjs.overall_match_score,
                    cjs.recommendation,
                    cjs.summary AS match_summary,
                    e.company_name AS employer_company_name,
                    e.logo_url AS employer_logo
                FROM candidate_job_scores cjs
                JOIN jobs j ON cjs.job_id = j.id
                LEFT JOIN employers e ON j.employer_id = e.id
                WHERE cjs.candidate_id = :candidate_id
                  AND j.status = 'published'
                ORDER BY cjs.overall_match_score DESC
                LIMIT 20";

        $recommendedJobs = $db->fetchAll($sql, ['candidate_id' => $candidateId]);

        // Get trending/hot jobs
        $trendingJobs = $this->getTrendingJobs();
        
        // Ensure all jobs have slug field
        foreach ($recommendedJobs as &$job) {
            if (empty($job['slug']) && !empty($job['id'])) {
                // Fallback: if slug is missing, we'll use id (shouldn't happen, but safety)
                $job['slug'] = 'job-' . $job['id'];
            }
        }
        foreach ($trendingJobs as &$job) {
            if (empty($job['slug']) && !empty($job['id'])) {
                $job['slug'] = 'job-' . $job['id'];
            }
        }

        $response->view('candidate/jobs/recommended', [
            'title' => 'Jobs for You',
            'recommendedJobs' => $recommendedJobs,
            'trendingJobs' => $trendingJobs,
            'candidate' => $candidate
        ]);
    }

    /**
     * Get trending jobs (last 7 days)
     * 
     * @return array Trending jobs
     */
    private function getTrendingJobs(): array
    {
        $db = Database::getInstance();
        $sql = "SELECT
                    j.id,
                    j.title,
                    j.slug,
                    j.short_description,
                    j.salary_min,
                    j.salary_max,
                    j.currency,
                    j.employment_type,
                    j.company_name,
                    j.location,
                    COUNT(a.id) AS application_count,
                    e.logo_url AS employer_logo
                FROM jobs j
                JOIN applications a ON a.job_id = j.id
                LEFT JOIN employers e ON j.employer_id = e.id
                WHERE a.created_at >= (NOW() - INTERVAL 7 DAY)
                  AND j.status = 'published'
                GROUP BY j.id
                ORDER BY application_count DESC
                LIMIT 10";

        return $db->fetchAll($sql);
    }
}

