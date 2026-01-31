<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Job;
use App\Models\Candidate;
use App\Core\Database;
use App\Services\NotificationService;

/**
 * Job Match Service - Database-based matching (no AI required)
 * Can be enhanced with AI later
 */
class JobMatchService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find matching candidates for a job and notify them
     */
    public function findAndNotifyCandidates(Job $job): int
    {
        // 1. Get potential candidates (active, and ideally matching some criteria)
        // For now, we'll fetch all active candidates to be safe, but in production, we should filter by skills/location
        $candidates = Candidate::where('status', '=', 'active')->get(); // Assuming 'status' column exists
        if (empty($candidates)) {
            // Fallback if status column doesn't exist or is different
             $candidates = Candidate::all();
        }

        $notifiedCount = 0;
        $threshold = 70; // 70% match score required

        foreach ($candidates as $candidate) {
            // Calculate match
            $match = $this->calculateMatch((int)$candidate->id, (int)$job->id, true);
            
            if ($match['overall_match_score'] >= $threshold) {
                // Send Notification
                $this->notifyCandidate($candidate, $job, $match['overall_match_score']);
                $notifiedCount++;
            }
        }
        
        // Also notify employer about the matches
        if ($notifiedCount > 0) {
             $this->notifyEmployer($job, $notifiedCount);
        }

        return $notifiedCount;
    }

    /**
     * Find published jobs that match a candidate and notify respective employers
     */
    public function findMatchingJobsForCandidateAndNotifyEmployers(Candidate $candidate): int
    {
        $jobs = \App\Models\Job::where('status', '=', 'published')->get();
        if (empty($jobs)) {
            return 0;
        }

        $threshold = 70;
        $matchJobs = 0;

        foreach ($jobs as $job) {
            $match = $this->calculateMatch((int)$candidate->id, (int)$job->id, true);
            if ($match['overall_match_score'] >= $threshold) {
                $employer = $job->employer();
                if ($employer) {
                    $user = $employer->user();
                    if ($user) {
                        $link = "/employer/jobs/{$job->slug}/candidates";
                        $candidateName = $candidate->attributes['full_name'] ?? 'A candidate';
                        NotificationService::send(
                            (int)$user->id,
                            'candidate_match_employer',
                            'New Candidate Match',
                            "{$candidateName} matches your job '{$job->title}'.",
                            [
                                'job_title' => $job->title,
                                'match_count' => 1,
                                'dashboard_link' => $link,
                                'email_template' => 'generic_notification'
                            ],
                            $link
                        );
                        $matchJobs++;
                    }
                }
            }
        }

        return $matchJobs;
    }

    /**
     * Notify a candidate about published jobs that match their profile
     */
    public function findMatchingJobsForCandidateAndNotifyCandidate(Candidate $candidate): int
    {
        $jobs = \App\Models\Job::where('status', '=', 'published')->get();
        if (empty($jobs)) {
            return 0;
        }

        $threshold = 70;
        $notified = 0;
        $user = $candidate->user();
        if (!$user) {
            return 0;
        }

        foreach ($jobs as $job) {
            $match = $this->calculateMatch((int)$candidate->id, (int)$job->id, true);
            if ($match['overall_match_score'] >= $threshold) {
                $link = "/candidate/jobs/{$job->slug}";
                NotificationService::send(
                    (int)$user->id,
                    'job_match',
                    'New Job Match Found!',
                    "A new job '{$job->title}' matches your profile ({$match['overall_match_score']}% match).",
                    [
                        'job_title' => $job->title,
                        'match_score' => (int)$match['overall_match_score'],
                        'link' => $link,
                        'email_template' => 'generic_notification'
                    ],
                    $link
                );
                $notified++;
            }
        }

        return $notified;
    }

    private function notifyCandidate(Candidate $candidate, Job $job, int $score): void
    {
        $user = $candidate->user();
        if (!$user) return;

        $isHighSalary = false;
        $jobMinSalary = (int)($job->salary_min ?? 0);
        $candExpMax = (int)($candidate->expected_salary_max ?? 0);
        
        // High salary definition: > 1.5x candidate expectation OR > 100k globally
        if (($candExpMax > 0 && $jobMinSalary > ($candExpMax * 1.5)) || $jobMinSalary >= 100000) {
            $isHighSalary = true;
        }

        $title = $isHighSalary ? '🚀 High Salary Job Match!' : 'New Job Match Found!';
        $message = "We found a new job that matches {$score}% of your profile: {$job->title} at " . ($job->company_name ?? 'Mindware Infotech');
        
        if ($isHighSalary) {
             $message = "Priority Alert: High paying job found! {$job->title} matches {$score}% of your profile.";
        }

        $link = "/candidate/jobs/{$job->slug}";

        NotificationService::send(
            (int)$user->id,
            'job_match',
            $title,
            $message,
            [
                'job_title' => $job->title,
                'company_name' => $job->company_name ?? 'Mindware Infotech',
                'match_score' => $score,
                'link' => $link,
                'is_high_salary' => $isHighSalary,
                'email_template' => 'generic_notification'
            ],
            $link
        );
    }

    private function notifyEmployer(Job $job, int $count): void
    {
        $employer = $job->employer();
        if (!$employer) return;
        
        $user = $employer->user();
        if (!$user) return;

        $message = "Great news! We found {$count} candidates matching your new job post: {$job->title}";
        $link = "/employer/jobs/{$job->slug}/candidates"; // Employer link to matching candidates

        NotificationService::send(
            (int)$user->id,
            'employer_job_match',
            'Candidates Found!',
            $message,
             [
                'job_title' => $job->title,
                'count' => $count,
                'link' => $link,
                'email_template' => 'generic_notification'
            ],
            $link
        );
    }

    /**
     * Calculate match between candidate and job using database logic
     * 
     * @param int $candidateId Candidate ID
     * @param int $jobId Job ID
     * @param bool $storeResults Whether to store results in candidate_job_scores table
     * @return array Match analysis with scores and details
     */
    public function calculateMatch(int $candidateId, int $jobId, bool $storeResults = true): array
    {
        $candidate = Candidate::find($candidateId);
        $job = Job::find($jobId);

        if (!$candidate || !$job) {
            return $this->getEmptyMatch();
        }

        // Get job requirements
        $jobSkills = $job->skills();
        $jobSkillNames = array_map(function($s) {
            return strtolower(trim($s['name'] ?? ''));
        }, $jobSkills);
        $jobSkillNames = array_filter($jobSkillNames);

        // Get candidate skills
        $candidateSkills = $candidate->skills();
        $candidateSkillNames = [];
        foreach ($candidateSkills as $skill) {
            $skillName = strtolower(trim($skill['name'] ?? ''));
            if (!empty($skillName)) {
                $candidateSkillNames[] = $skillName;
            }
        }

        // Calculate skill match
        $matchedSkills = [];
        $missingSkills = [];
        $extraRelevantSkills = [];

        // Create a map of lowercase skill names to original skill names
        $jobSkillMap = [];
        foreach ($jobSkills as $js) {
            $originalName = trim($js['name'] ?? '');
            if (!empty($originalName)) {
                $jobSkillMap[strtolower($originalName)] = $originalName;
            }
        }

        foreach ($jobSkillNames as $jobSkill) {
            $found = false;
            $originalJobSkill = $jobSkillMap[$jobSkill] ?? ucfirst($jobSkill);
            
            foreach ($candidateSkillNames as $candidateSkill) {
                // Exact match or partial match (e.g., "php" matches "PHP", "php developer")
                if ($jobSkill === $candidateSkill || 
                    strpos($candidateSkill, $jobSkill) !== false || 
                    strpos($jobSkill, $candidateSkill) !== false) {
                    $matchedSkills[] = $originalJobSkill;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingSkills[] = $originalJobSkill;
            }
        }

        // Find extra relevant skills (candidate has but job doesn't require)
        foreach ($candidateSkillNames as $candidateSkill) {
            $isRelevant = false;
            foreach ($jobSkillNames as $jobSkill) {
                if (strpos($candidateSkill, $jobSkill) !== false || 
                    strpos($jobSkill, $candidateSkill) !== false) {
                    $isRelevant = true;
                    break;
                }
            }
            if (!$isRelevant && !in_array(ucfirst($candidateSkill), $matchedSkills)) {
                $extraRelevantSkills[] = ucfirst($candidateSkill);
            }
        }

        // Calculate skill match score (0-100)
        $skillMatchScore = 0;
        if (!empty($jobSkillNames)) {
            $skillMatchScore = (count($matchedSkills) / count($jobSkillNames)) * 100;
        }

        // Calculate experience match
        $experienceMatchScore = $this->calculateExperienceMatch($candidate, $job);

        // Calculate location match
        $locationMatchScore = $this->calculateLocationMatch($candidate, $job);

        // Calculate education match (simplified)
        $educationMatchScore = $this->calculateEducationMatch($candidate, $job);

        // Calculate salary match
        $salaryMatchScore = $this->calculateSalaryMatch($candidate, $job);

        // Calculate preference match
        $preferenceMatchScore = $this->calculatePreferenceMatch($candidate, $job);

        // Calculate overall match score (weighted)
        $overallMatchScore = $this->calculateOverallScore([
            'skills' => $skillMatchScore,
            'experience' => $experienceMatchScore,
            'location' => $locationMatchScore,
            'education' => $educationMatchScore,
            'salary' => $salaryMatchScore,
            'preferences' => $preferenceMatchScore
        ]);

        // Generate recommendation
        $recommendation = $this->getRecommendation($overallMatchScore, count($matchedSkills), count($missingSkills));

        // Generate summary
        $summary = $this->generateSummary($matchedSkills, $missingSkills, $overallMatchScore);

        $matchData = [
            'overall_match_score' => (int)round($overallMatchScore),
            'skill_match_score' => (int)round($skillMatchScore),
            'experience_match_score' => (int)round($experienceMatchScore),
            'education_match_score' => (int)round($educationMatchScore),
            'location_match_score' => (int)round($locationMatchScore),
            'salary_match_score' => (int)round($salaryMatchScore),
            'preferences_match_score' => (int)round($preferenceMatchScore),
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills,
            'extra_relevant_skills' => array_slice($extraRelevantSkills, 0, 5), // Limit to 5
            'recommendation' => $recommendation,
            'summary' => $summary,
            'match_method' => 'database' // Can be 'ai' when AI is used
        ];

        // Store results if requested
        if ($storeResults) {
            $this->storeMatchResults($candidateId, $jobId, $matchData);
        }

        return $matchData;
    }

    /**
     * Calculate experience match score
     */
    private function calculateExperienceMatch(Candidate $candidate, Job $job): float
    {
        $jobMinExp = (int)($job->attributes['min_experience'] ?? 0);
        $jobMaxExp = (int)($job->attributes['max_experience'] ?? 0);

        if ($jobMinExp === 0 && $jobMaxExp === 0) {
            return 50; // Neutral score if no experience requirement
        }

        // Get candidate total experience
        $candidateExperience = $candidate->experience();
        $totalYears = 0;

        foreach ($candidateExperience as $exp) {
            $startDate = $exp['start_date'] ?? '';
            $endDate = $exp['end_date'] ?? 'Present';
            
            if (empty($startDate)) continue;

            $start = strtotime($startDate);
            $end = ($endDate === 'Present' || empty($endDate)) ? time() : strtotime($endDate);
            
            if ($start && $end) {
                $years = ($end - $start) / (365.25 * 24 * 60 * 60);
                $totalYears += max(0, $years);
            }
        }

        // Calculate match
        if ($jobMinExp > 0 && $totalYears < $jobMinExp) {
            // Less than minimum - lower score
            $ratio = $totalYears / max($jobMinExp, 1);
            return max(0, $ratio * 50); // 0-50 points
        } elseif ($jobMaxExp > 0 && $totalYears > $jobMaxExp) {
            // More than maximum - still good but slightly reduced
            return 85;
        } else {
            // Within range - perfect match
            return 100;
        }
    }

    /**
     * Calculate location match score
     */
    private function calculateLocationMatch(Candidate $candidate, Job $job): float
    {
        $candidateLocation = strtolower(trim($candidate->attributes['preferred_job_location'] ?? ''));
        $candidateCity = strtolower(trim($candidate->attributes['city'] ?? ''));
        $candidateState = strtolower(trim($candidate->attributes['state'] ?? ''));
        $prefs = [];
        if (!empty($candidate->attributes['preferences_data'])) {
            $prefs = json_decode($candidate->attributes['preferences_data'], true) ?? [];
        }
        $preferredLocations = [];
        if (!empty($prefs['preferred_locations']) && is_array($prefs['preferred_locations'])) {
            $preferredLocations = array_map(function($v){ return strtolower(trim((string)$v)); }, $prefs['preferred_locations']);
            $preferredLocations = array_filter($preferredLocations);
        }
        $preferredWorkMode = strtolower(trim((string)($prefs['preferred_work_mode'] ?? '')));

        // Get job locations
        $jobLocations = $job->locations();
        $jobLocationStrings = [];
        
        foreach ($jobLocations as $loc) {
            if ($loc && isset($loc->attributes)) {
                $locParts = array_filter([
                    strtolower(trim($loc->attributes['city'] ?? '')),
                    strtolower(trim($loc->attributes['state'] ?? '')),
                    strtolower(trim($loc->attributes['country'] ?? ''))
                ]);
                if (!empty($locParts)) {
                    $jobLocationStrings[] = implode(', ', $locParts);
                }
            }
        }

        // Check if remote job
        $isRemote = (int)($job->attributes['is_remote'] ?? 0);
        if ($isRemote) {
            if ($preferredWorkMode === 'office') {
                return 70; // remote but candidate prefers office
            }
            return 100; // remote fits remote/hybrid or unspecified
        }

        if (empty($jobLocationStrings)) {
            return 50; // Neutral if no location specified
        }

        // Check for matches
        foreach ($jobLocationStrings as $jobLoc) {
            // preferences list first
            foreach ($preferredLocations as $prefLoc) {
                if ($prefLoc !== '' && (strpos($jobLoc, $prefLoc) !== false || strpos($prefLoc, $jobLoc) !== false)) {
                    return 100;
                }
            }
            if (!empty($candidateLocation) && 
                (strpos($jobLoc, $candidateLocation) !== false || 
                 strpos($candidateLocation, $jobLoc) !== false)) {
                return 95; // Strong match
            }
            if (!empty($candidateCity) && strpos($jobLoc, $candidateCity) !== false) {
                return 90; // City match
            }
            if (!empty($candidateState) && strpos($jobLoc, $candidateState) !== false) {
                return 75; // State match
            }
        }

        return 30; // Low match
    }

    /**
     * Calculate education match score
     */
    private function calculateEducationMatch(Candidate $candidate, Job $job): float
    {
        $candidateEducation = $candidate->education();
        
        if (empty($candidateEducation)) {
            return 50; // Neutral if no education data
        }

        // For now, just check if candidate has education
        // Can be enhanced to match specific degrees/qualifications
        return 80; // Good score if has education
    }

    /**
     * Calculate salary match score
     */
    private function calculateSalaryMatch(Candidate $candidate, Job $job): float
    {
        $candidateMin = (int)($candidate->attributes['expected_salary_min'] ?? 0);
        $candidateMax = (int)($candidate->attributes['expected_salary_max'] ?? 0);
        $jobMin = (int)($job->attributes['salary_min'] ?? 0);
        $jobMax = (int)($job->attributes['salary_max'] ?? 0);
        $prefs = [];
        if (!empty($candidate->attributes['preferences_data'])) {
            $prefs = json_decode($candidate->attributes['preferences_data'], true) ?? [];
        }
        $minAcceptable = (int)($prefs['minimum_acceptable_salary'] ?? 0);
        if ($minAcceptable > 0) {
            $candidateMin = $minAcceptable;
        }

        if ($candidateMin === 0 || $jobMax === 0) {
            return 50; // Neutral if no salary data
        }

        // Perfect match - candidate range overlaps with job range
        if ($candidateMin <= $jobMax && $candidateMax >= $jobMin) {
            return 100;
        }

        // Close match - within 20%
        if ($candidateMin <= $jobMax * 1.2) {
            return 75;
        }

        // Somewhat close - within 50%
        if ($candidateMin <= $jobMax * 1.5) {
            return 50;
        }

        return 25; // Low match
    }

    /**
     * Calculate overall weighted score
     */
    private function calculateOverallScore(array $scores): float
    {
        $weights = [
            'skills' => 0.35,
            'experience' => 0.25,
            'location' => 0.15,
            'education' => 0.10,
            'salary' => 0.10,
            'preferences' => 0.05
        ];

        $overall = 0;
        foreach ($weights as $key => $weight) {
            $overall += ($scores[$key] ?? 0) * $weight;
        }

        return min(100, max(0, $overall));
    }

    private function calculatePreferenceMatch(Candidate $candidate, Job $job): float
    {
        $prefs = [];
        if (!empty($candidate->attributes['preferences_data'])) {
            $prefs = json_decode($candidate->attributes['preferences_data'], true) ?? [];
        }
        $score = 0;
        $workMode = strtolower(trim((string)($prefs['preferred_work_mode'] ?? '')));
        $jobTypePref = $prefs['preferred_job_types'] ?? [];
        $relocate = (int)($prefs['open_to_relocation'] ?? 0);
        $jobType = strtolower(trim((string)($job->attributes['employment_type'] ?? '')));
        $isRemote = (int)($job->attributes['is_remote'] ?? 0);
        
        if ($workMode !== '') {
            if ($isRemote && in_array($workMode, ['remote','hybrid'])) {
                $score += 40;
            } elseif (!$isRemote && $workMode === 'office') {
                $score += 40;
            } elseif ($workMode === 'hybrid') {
                $score += 30;
            }
        } else {
             $score += 20; // Default
        }

        if (is_array($jobTypePref) && !empty($jobTypePref) && $jobType !== '') {
            $normalized = array_map(function($v){ return strtolower(trim((string)$v)); }, $jobTypePref);
            if (in_array($jobType, $normalized)) {
                $score += 30;
            }
        } else {
             $score += 15; // Default
        }

        if ($relocate === 1) {
            $score += 30;
        } else {
            $score += 10;
        }
        return min(100, $score);
    }

    /**
     * Get recommendation based on scores
     */
    private function getRecommendation(float $overallScore, int $matchedCount, int $missingCount): string
    {
        if ($overallScore >= 80 && $missingCount === 0) {
            return 'Strong Hire';
        } elseif ($overallScore >= 70) {
            return 'Shortlist';
        } elseif ($overallScore >= 50) {
            return 'Review';
        } else {
            return 'Reject';
        }
    }

    /**
     * Generate summary text
     */
    private function generateSummary(array $matchedSkills, array $missingSkills, float $overallScore): string
    {
        $matchedCount = count($matchedSkills);
        $missingCount = count($missingSkills);
        $total = $matchedCount + $missingCount;

        if ($total === 0) {
            return "No skill requirements specified for this job.";
        }

        $matchPercent = round(($matchedCount / $total) * 100);
        
        if ($matchPercent >= 80) {
            return "Excellent match! Candidate has {$matchedCount} of {$total} required skills.";
        } elseif ($matchPercent >= 60) {
            return "Good match. Candidate has {$matchedCount} of {$total} required skills. Missing: " . implode(', ', array_slice($missingSkills, 0, 3));
        } elseif ($matchPercent >= 40) {
            return "Moderate match. Candidate has {$matchedCount} of {$total} required skills. Missing: " . implode(', ', array_slice($missingSkills, 0, 3));
        } else {
            return "Low match. Candidate has {$matchedCount} of {$total} required skills. Missing: " . implode(', ', array_slice($missingSkills, 0, 3));
        }
    }

    /**
     * Store match results in database
     */
    private function storeMatchResults(int $candidateId, int $jobId, array $matchData): void
    {
        try {
            $sql = "INSERT INTO candidate_job_scores 
                    (candidate_id, job_id, overall_match_score, skill_score, experience_score, 
                     education_score, matched_skills, missing_skills, extra_relevant_skills, 
                     recommendation, summary, created_at, updated_at)
                    VALUES 
                    (:candidate_id, :job_id, :overall_match_score, :skill_score, :experience_score,
                     :education_score, :matched_skills, :missing_skills, :extra_relevant_skills,
                     :recommendation, :summary, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                    overall_match_score = VALUES(overall_match_score),
                    skill_score = VALUES(skill_score),
                    experience_score = VALUES(experience_score),
                    education_score = VALUES(education_score),
                    matched_skills = VALUES(matched_skills),
                    missing_skills = VALUES(missing_skills),
                    extra_relevant_skills = VALUES(extra_relevant_skills),
                    recommendation = VALUES(recommendation),
                    summary = VALUES(summary),
                    updated_at = NOW()";

            $this->db->query($sql, [
                'candidate_id' => $candidateId,
                'job_id' => $jobId,
                'overall_match_score' => $matchData['overall_match_score'],
                'skill_score' => $matchData['skill_match_score'],
                'experience_score' => $matchData['experience_match_score'],
                'education_score' => $matchData['education_match_score'],
                'matched_skills' => json_encode($matchData['matched_skills']),
                'missing_skills' => json_encode($matchData['missing_skills']),
                'extra_relevant_skills' => json_encode($matchData['extra_relevant_skills']),
                'recommendation' => $matchData['recommendation'],
                'summary' => $matchData['summary']
            ]);
        } catch (\Exception $e) {
            error_log("Failed to store match results: " . $e->getMessage());
        }
    }

    private function getEmptyMatch(): array
    {
        return [
            'overall_match_score' => 0,
            'skill_match_score' => 0,
            'experience_match_score' => 0,
            'education_match_score' => 0,
            'location_match_score' => 0,
            'salary_match_score' => 0,
            'preferences_match_score' => 0,
            'matched_skills' => [],
            'missing_skills' => [],
            'extra_relevant_skills' => [],
            'recommendation' => 'Reject',
            'summary' => 'Data missing for matching.',
            'match_method' => 'database'
        ];
    }
}
