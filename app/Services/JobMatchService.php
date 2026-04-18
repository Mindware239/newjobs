<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Job;
use App\Models\Candidate;

/**
 * JobMatchService (Facade/Coordinator)
 * Refactored to Enterprise Architecture:
 * - MatchEngineService (Core Loop & Caching)
 * - ScoreCalculatorService (Scoring Logic)
 * - NotificationQueueService (Async Notifications)
 * - JobFilterRepository (Cursor-based SQL pre-filtering)
 */
class JobMatchService
{
    private MatchEngineService $engine;

    public function __construct()
    {
        $this->engine = new MatchEngineService();
    }

    /**
     * Find matching candidates for a job and notify them using batching and DB pre-filtering
     */
    public function findAndNotifyCandidates(Job $job): int
    {
        return $this->engine->processNewJob($job);
    }

    /**
     * Find published jobs that match a candidate and notify respective employers
     */
    public function findMatchingJobsForCandidateAndNotifyEmployers(Candidate $candidate): int
    {
        // Handled completely by the unified processCandidateProfile loop
        // To maintain interface parity, we return the total notified.
        // In real event-driven systems, this would just fire an Event.
        return $this->engine->processCandidateProfile($candidate);
    }

    /**
     * Notify a candidate about published jobs that match their profile
     */
    public function findMatchingJobsForCandidateAndNotifyCandidate(Candidate $candidate): int
    {
        // Since processCandidateProfile notifies both employer and candidate in one unified loop
        // to save CPU cycles, we just return 0 here to prevent running the loop twice.
        return 0; 
    }

    /**
     * Public calculation method (used by Candidate dashboard for real-time scores)
     */
    public function calculateMatch(int $candidateId, int $jobId, bool $skipDb = false): array
    {
        // For real-time one-off checks (e.g. Dashboard view)
        $candidate = \App\Models\Candidate::find($candidateId);
        $job = \App\Models\Job::find($jobId);
        
        if (!$candidate || !$job) {
            return $this->getEmptyMatch();
        }
        
        return $this->engine->calculateAndStoreMatch($candidate, $job);
    }

    private function getEmptyMatch(): array
    {
        return [
            'overall_match_score' => 0,
            'title_match_score' => 0,
            'skill_match_score' => 0,
            'experience_match_score' => 0,
            'education_match_score' => 0,
            'location_match_score' => 0,
            'salary_match_score' => 0,
            'preference_match_score' => 0,
            'matched_skills' => [],
            'missing_skills' => []
        ];
    }
}
