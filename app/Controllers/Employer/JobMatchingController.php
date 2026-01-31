<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\ResumeTextExtractor;
use App\Services\AIResumeParser;
use App\Services\JobMatchScorer;

/**
 * Job Matching Controller
 * 
 * Handles AI-powered job matching and candidate scoring
 */
class JobMatchingController extends BaseController
{
    /**
     * Get candidates for a job sorted by match score
     * 
     * GET /employer/jobs/:slug/candidates
     */
    public function getCandidatesForJob(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = $request->param('slug');
        $employer = $this->currentUser->employer();

        $job = \App\Models\Job::findBySlug($slug);
        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }

        $jobId = $job->attributes['id'];

        $db = \App\Core\Database::getInstance();
        $sql = "SELECT
                    c.id,
                    c.user_id,
                    c.full_name,
                    c.city,
                    c.state,
                    c.profile_picture,
                    c.resume_url,
                    c.profile_strength,
                    (CASE WHEN c.is_premium = 1 AND c.premium_expires_at > NOW() THEN 1 ELSE 0 END) AS premium_active,
                    u.last_login,
                    cjs.overall_match_score,
                    cjs.skill_score,
                    cjs.experience_score,
                    cjs.education_score,
                    cjs.recommendation,
                    cjs.summary,
                    cjs.matched_skills,
                    cjs.missing_skills,
                    cjs.created_at AS score_created_at
                FROM candidate_job_scores cjs
                JOIN candidates c ON cjs.candidate_id = c.id
                JOIN users u ON u.id = c.user_id
                WHERE cjs.job_id = :job_id
                ORDER BY premium_active DESC, c.profile_strength DESC, u.last_login DESC, cjs.overall_match_score DESC, cjs.created_at DESC";

        $candidates = $db->fetchAll($sql, ['job_id' => $jobId]);

        // Decode JSON fields
        foreach ($candidates as &$candidate) {
            $candidate['matched_skills'] = json_decode($candidate['matched_skills'] ?? '[]', true);
            $candidate['missing_skills'] = json_decode($candidate['missing_skills'] ?? '[]', true);
        }

        $response->json([
            'success' => true,
            'job_id' => $jobId,
            'candidates' => $candidates
        ]);
    }

    /**
     * Generate AI scores for all candidates for a job
     * 
     * POST /employer/jobs/:slug/generate-scores
     */
    public function generateScores(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = $request->param('slug');
        $employer = $this->currentUser->employer();

        $job = \App\Models\Job::findBySlug($slug);
        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }

        $jobId = $job->attributes['id'];

        // Get all candidates who have applied or have resumes
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT DISTINCT c.id, c.user_id
                FROM candidates c
                LEFT JOIN applications a ON a.candidate_id = c.id AND a.job_id = :job_id
                WHERE (a.id IS NOT NULL OR c.resume_url IS NOT NULL)
                  AND c.resume_url IS NOT NULL
                LIMIT 50"; // Limit to prevent timeout

        $candidates = $db->fetchAll($sql, ['job_id' => $jobId]);

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            $extractor = new ResumeTextExtractor();
            $aiParser = new AIResumeParser($extractor);
            $scorer = new JobMatchScorer($aiParser);

            foreach ($candidates as $candidate) {
                try {
                    $scorer->scoreMatch((int)$candidate['user_id'], $jobId);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'candidate_id' => $candidate['id'],
                        'error' => $e->getMessage()
                    ];
                    error_log("Failed to score candidate {$candidate['id']} for job {$jobId}: " . $e->getMessage());
                }
            }

            $response->json([
                'success' => true,
                'message' => "Generated scores for {$results['success']} candidates",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            error_log("Generate scores error: " . $e->getMessage());
            $response->json(['error' => 'Failed to generate scores'], 500);
        }
    }

    /**
     * Score a specific candidate for a job
     * 
     * POST /employer/jobs/:slug/candidates/:candidate_id/score
     */
    public function scoreCandidate(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $slug = $request->param('slug');
        $candidateId = (int)$request->param('candidate_id');
        $employer = $this->currentUser->employer();

        $job = \App\Models\Job::findBySlug($slug);
        if (!$job || $job->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }

        $jobId = $job->attributes['id'];

        $candidate = \App\Models\Candidate::find($candidateId);
        if (!$candidate) {
            $response->json(['error' => 'Candidate not found'], 404);
            return;
        }

        try {
            $extractor = new ResumeTextExtractor();
            $aiParser = new AIResumeParser($extractor);
            $scorer = new JobMatchScorer($aiParser);

            $scores = $scorer->scoreMatch($candidate->attributes['user_id'], $jobId);

            $response->json([
                'success' => true,
                'scores' => $scores
            ]);

        } catch (\Exception $e) {
            error_log("Score candidate error: " . $e->getMessage());
            $response->json([
                'error' => 'Failed to score candidate',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

