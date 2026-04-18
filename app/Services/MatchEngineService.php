<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Candidate;
use App\Models\Job;
use App\Repositories\JobFilterRepository;

class MatchEngineService
{
    private Database $db;
    private ScoreCalculatorService $scoreCalc;
    private NotificationQueueService $queueService;
    private JobFilterRepository $filterRepo;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->scoreCalc = new ScoreCalculatorService();
        $this->queueService = new NotificationQueueService();
        $this->filterRepo = new JobFilterRepository();
    }

    /**
     * Main match entry point for a new Job
     */
    public function processNewJob(Job $job): int
    {
        $notifiedCount = 0;
        $threshold = 70;
        $batchSize = 200;
        $lastId = 0;

        $jobTitle = strtolower(trim((string)($job->attributes['title'] ?? '')));
        $keywords = array_filter(explode(' ', preg_replace('/[^a-z0-9 ]/', '', $jobTitle)), fn($w) => strlen($w) > 2);
        
        while (true) {
            $candidatesRow = $this->filterRepo->getPotentialCandidatesForJob($job, $keywords, $batchSize, $lastId);
            
            if (empty($candidatesRow)) break;

            foreach ($candidatesRow as $row) {
                $lastId = (int)$row['id'];
                
                // Skip if processed recently (within 24 hours)
                if ($this->filterRepo->isMatchProcessedRecently($lastId, (int)$job->id)) {
                    continue;
                }

                $candidate = new Candidate();
                $candidate->attributes = $row;
                $candidate->id = $row['id'];

                $match = $this->calculateAndStoreMatch($candidate, $job);
                
                if ($match['overall_match_score'] >= $threshold) {
                    $this->queueService->queueCandidateNotification($candidate, $job, $match['overall_match_score']);
                    $notifiedCount++;
                }
            }
        }
        
        if ($notifiedCount > 0) {
            $this->queueService->queueEmployerNotification($job, $notifiedCount);
        }

        return $notifiedCount;
    }

    /**
     * Main match entry point for a new/updated Candidate
     */
    public function processCandidateProfile(Candidate $candidate): int
    {
        $notified = 0;
        $threshold = 70;
        $batchSize = 200;
        $lastId = 0;

        $candTitle = strtolower(trim((string)($candidate->attributes['professional_title'] ?? '')));
        $keywords = array_filter(explode(' ', preg_replace('/[^a-z0-9 ]/', '', $candTitle)), fn($w) => strlen($w) > 2);

        $user = $candidate->user();
        if (!$user) return 0;

        while (true) {
            $jobsRow = $this->filterRepo->getPotentialJobsForCandidate($keywords, $batchSize, $lastId);
            
            if (empty($jobsRow)) break;

            foreach ($jobsRow as $row) {
                $lastId = (int)$row['id'];

                if ($this->filterRepo->isMatchProcessedRecently((int)$candidate->id, $lastId)) {
                    continue;
                }

                $job = new Job();
                $job->attributes = $row;
                $job->id = $row['id'];

                $match = $this->calculateAndStoreMatch($candidate, $job);
                
                if ($match['overall_match_score'] >= $threshold) {
                    $this->queueService->queueJobMatchNotification($user, $job, $candidate, $match['overall_match_score']);
                    $this->queueService->queueEmployerMatchNotification($job, $candidate);
                    $notified++;
                }
            }
        }

        return $notified;
    }

    /**
     * Calculate all factors, compute overall score, and store in DB
     */
    public function calculateAndStoreMatch(Candidate $candidate, Job $job): array
    {
        $titleMatch = $this->scoreCalc->calculateTitleMatch($candidate, $job);
        
        $matchedSkills = [];
        $missingSkills = [];
        $extraSkills = [];
        $skillMatch = $this->scoreCalc->calculateSkillMatch($candidate, $job, $matchedSkills, $missingSkills, $extraSkills);
        
        $expMatch = $this->scoreCalc->calculateExperienceMatch($candidate, $job);
        $locMatch = $this->scoreCalc->calculateLocationMatch($candidate, $job);
        $eduMatch = $this->scoreCalc->calculateEducationMatch($candidate, $job);
        $salMatch = $this->scoreCalc->calculateSalaryMatch($candidate, $job);
        $prefMatch = $this->scoreCalc->calculatePreferenceMatch($candidate, $job);

        $weights = [
            'title' => 0.30,
            'skills' => 0.25,
            'experience' => 0.15,
            'location' => 0.15,
            'salary' => 0.10,
            'education' => 0.05
        ];

        $overall = ($titleMatch * $weights['title']) +
                   ($skillMatch * $weights['skills']) +
                   ($expMatch * $weights['experience']) +
                   ($locMatch * $weights['location']) +
                   ($salMatch * $weights['salary']) +
                   ($eduMatch * $weights['education']);

        $overall = (int)round(min(100, max(0, $overall)));

        $matchData = [
            'overall_match_score' => $overall,
            'title_match_score' => (int)round($titleMatch),
            'skill_match_score' => (int)round($skillMatch),
            'experience_match_score' => (int)round($expMatch),
            'education_match_score' => (int)round($eduMatch),
            'location_match_score' => (int)round($locMatch),
            'salary_match_score' => (int)round($salMatch),
            'preference_match_score' => (int)round($prefMatch),
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills
        ];

        $this->storeMatchScore((int)$candidate->id, (int)$job->id, $matchData);

        return $matchData;
    }

    private function storeMatchScore(int $candidateId, int $jobId, array $data): void
    {
        try {
            $existing = $this->db->fetchOne(
                "SELECT id FROM candidate_job_scores WHERE candidate_id = :cid AND job_id = :jid",
                ['cid' => $candidateId, 'jid' => $jobId]
            );

            if ($existing) {
                $this->db->query(
                    "UPDATE candidate_job_scores 
                     SET overall_match_score = :overall, 
                         match_details = :details, 
                         updated_at = NOW()
                     WHERE id = :id",
                    [
                        'id' => $existing['id'],
                        'overall' => $data['overall_match_score'],
                        'details' => json_encode($data)
                    ]
                );
            } else {
                $this->db->query(
                    "INSERT INTO candidate_job_scores (candidate_id, job_id, overall_match_score, match_details, created_at, updated_at) 
                     VALUES (:cid, :jid, :overall, :details, NOW(), NOW())",
                    [
                        'cid' => $candidateId,
                        'jid' => $jobId,
                        'overall' => $data['overall_match_score'],
                        'details' => json_encode($data)
                    ]
                );
            }
        } catch (\Throwable $t) {
            error_log("Failed to store match score: " . $t->getMessage());
        }
    }
}
