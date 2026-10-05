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

        $keywords = $this->extractJobKeywords($job);
        
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

        $keywords = $this->extractCandidateKeywords($candidate);

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
            'missing_skills' => $missingSkills,
            'extra_relevant_skills' => $extraSkills
        ];

        $this->storeMatchScore((int)$candidate->id, (int)$job->id, $matchData);

        return $matchData;
    }

    private function extractJobKeywords(Job $job): array
    {
        $parts = [
            (string)($job->attributes['title'] ?? ''),
            (string)($job->attributes['category'] ?? '')
        ];

        foreach ($job->skills() as $skill) {
            $parts[] = (string)($skill['name'] ?? '');
        }

        return $this->extractKeywords(implode(' ', $parts));
    }

    private function extractCandidateKeywords(Candidate $candidate): array
    {
        $parts = [
            (string)($candidate->attributes['professional_title'] ?? ''),
            (string)($candidate->attributes['self_introduction'] ?? '')
        ];

        foreach ($candidate->skills() as $skill) {
            $parts[] = (string)($skill['name'] ?? '');
        }

        foreach ($candidate->experience() as $experience) {
            $parts[] = (string)($experience['job_title'] ?? '');
        }

        return $this->extractKeywords(implode(' ', $parts));
    }

    private function extractKeywords(string $text): array
    {
        $words = preg_split('/\s+/', strtolower(preg_replace('/[^a-z0-9+#. ]/i', ' ', $text) ?? '')) ?: [];
        $stopWords = ['job', 'jobs', 'developer', 'engineer', 'executive', 'manager', 'senior', 'junior', 'and', 'or', 'the', 'for', 'with'];

        return array_values(array_unique(array_filter($words, function(string $word) use ($stopWords): bool {
            return strlen($word) > 2 && !in_array($word, $stopWords, true);
        })));
    }

    private function storeMatchScore(int $candidateId, int $jobId, array $data): void
    {
        try {
            $existing = $this->db->fetchOne(
                "SELECT id FROM candidate_job_scores WHERE candidate_id = :cid AND job_id = :jid",
                ['cid' => $candidateId, 'jid' => $jobId]
            );

            $params = [
                'cid' => $candidateId,
                'jid' => $jobId,
                'overall' => $data['overall_match_score'],
                'skill' => $data['skill_match_score'],
                'exp' => $data['experience_match_score'],
                'edu' => $data['education_match_score'],
                'matched' => json_encode($data['matched_skills']),
                'missing' => json_encode($data['missing_skills'])
            ];

            if ($existing) {
                $this->db->query(
                    "UPDATE candidate_job_scores 
                     SET overall_match_score = :overall, 
                         skill_score = :skill,
                         experience_score = :exp,
                         education_score = :edu,
                         matched_skills = :matched,
                         missing_skills = :missing,
                         updated_at = NOW()
                     WHERE id = :id",
                    [
                        'overall' => $data['overall_match_score'],
                        'skill' => $data['skill_match_score'],
                        'exp' => $data['experience_match_score'],
                        'edu' => $data['education_match_score'],
                        'matched' => json_encode($data['matched_skills']),
                        'missing' => json_encode($data['missing_skills']),
                        'id' => $existing['id']
                    ]
                );
            } else {
                $this->db->query(
                    "INSERT INTO candidate_job_scores (candidate_id, job_id, overall_match_score, skill_score, experience_score, education_score, matched_skills, missing_skills, created_at, updated_at) 
                     VALUES (:cid, :jid, :overall, :skill, :exp, :edu, :matched, :missing, NOW(), NOW())",
                    $params
                );
            }
        } catch (\Throwable $t) {
            error_log("Failed to store match score: " . $t->getMessage());
        }
    }
}
