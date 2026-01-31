<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Job;
use App\Models\Candidate;
use App\Core\Database;

/**
 * Job Match Scorer Service
 * 
 * Uses AI to score candidate-job matches and stores results in candidate_job_scores table.
 */
class JobMatchScorer
{
    private AIResumeParser $aiParser;
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private int $maxRetries;

    public function __construct(
        AIResumeParser $aiParser,
        string $apiKey = null,
        string $model = 'gpt-4o-mini'
    ) {
        $this->aiParser = $aiParser;
        $this->apiKey = $apiKey ?? $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model = $model;
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
        $this->maxRetries = 3;

        if (empty($this->apiKey)) {
            throw new \RuntimeException("OpenAI API key not configured");
        }
    }

    /**
     * Score match between candidate and job using AI
     * 
     * @param int $candidateId Candidate ID
     * @param int $jobId Job ID
     * @return array Match scores and recommendations
     * @throws \RuntimeException If scoring fails
     */
    public function scoreMatch(int $candidateId, int $jobId): array
    {
        $candidate = Candidate::findByUserId($candidateId);
        if (!$candidate) {
            throw new \RuntimeException("Candidate not found: {$candidateId}");
        }

        $job = Job::find($jobId);
        if (!$job) {
            throw new \RuntimeException("Job not found: {$jobId}");
        }

        // Get candidate profile data
        $candidateProfile = $this->buildCandidateProfile($candidate);
        
        // Get job requirements
        $jobRequirements = $this->buildJobRequirements($job);

        // Build matching prompt
        $prompt = $this->buildMatchingPrompt($candidateProfile, $jobRequirements);

        // Call AI
        $response = $this->aiParser->callOpenAI($prompt);

        // Validate and normalize scores
        $scores = $this->validateScores($response);

        // Calculate overall score using weighted formula
        $scores['overall_match_score'] = $this->calculateOverallMatchScore(
            $scores['skill_match_score'],
            $scores['experience_match_score'],
            $scores['education_match_score']
        );

        // Store in database
        $this->storeScores($candidate->attributes['id'], $jobId, $scores);

        return $scores;
    }

    /**
     * Build candidate profile JSON for AI matching
     * 
     * @param Candidate $candidate Candidate model
     * @return string JSON string
     */
    private function buildCandidateProfile(Candidate $candidate): string
    {
        $profile = [
            'skills' => $candidate->skills(),
            'education' => $candidate->education(),
            'experience' => $candidate->experience(),
            'languages' => $candidate->languages(),
            'expected_salary_min' => $candidate->attributes['expected_salary_min'] ?? null,
            'expected_salary_max' => $candidate->attributes['expected_salary_max'] ?? null,
            'current_salary' => $candidate->attributes['current_salary'] ?? null,
            'preferred_location' => $candidate->attributes['preferred_job_location'] ?? null,
            'notice_period' => $candidate->attributes['notice_period'] ?? null
        ];

        return json_encode($profile, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Build job requirements for AI matching
     * 
     * @param Job $job Job model
     * @return array Job requirements data
     */
    private function buildJobRequirements(Job $job): array
    {
        $skills = $job->skills();
        $skillNames = array_map(fn($s) => $s['name'] ?? '', $skills);
        
        return [
            'title' => $job->attributes['title'] ?? '',
            'description' => $job->attributes['description'] ?? '',
            'required_skills' => array_filter($skillNames),
            'salary_min' => $job->attributes['salary_min'] ?? null,
            'salary_max' => $job->attributes['salary_max'] ?? null,
            'employment_type' => $job->attributes['employment_type'] ?? '',
            'location' => $job->attributes['locations'] ?? '',
            'min_experience' => $job->attributes['min_experience'] ?? null,
            'max_experience' => $job->attributes['max_experience'] ?? null
        ];
    }

    /**
     * Build prompt for job matching
     * 
     * @param string $candidateProfile JSON string of candidate profile
     * @param array $jobRequirements Job requirements array
     * @return string Complete prompt
     */
    private function buildMatchingPrompt(string $candidateProfile, array $jobRequirements): string
    {
        $jobSkillsText = implode(', ', $jobRequirements['required_skills']);
        
        return <<<PROMPT
You are an expert recruiter. Compare a candidate profile against a job requirement and provide match scores.

CANDIDATE PROFILE (JSON):
{$candidateProfile}

JOB REQUIREMENTS:
Title: {$jobRequirements['title']}
Description: {$jobRequirements['description']}
Required Skills: {$jobSkillsText}
Salary Range: ₹{$jobRequirements['salary_min']} - ₹{$jobRequirements['salary_max']}
Employment Type: {$jobRequirements['employment_type']}
Location: {$jobRequirements['location']}
Experience Required: {$jobRequirements['min_experience']}-{$jobRequirements['max_experience']} years

SCORING CRITERIA:
- skill_match_score (0-100): Percentage of required skills the candidate has
- experience_match_score (0-100): How well candidate's experience matches job requirements
- education_match_score (0-100): How well candidate's education matches job requirements
- overall_match_score: Will be calculated separately using weighted formula

INSTRUCTIONS:
1. Identify which skills from job requirements the candidate has (matched_skills).
2. Identify which required skills the candidate is missing (missing_skills).
3. Identify any extra relevant skills the candidate has (extra_relevant_skills).
4. Score each category (skills, experience, education) from 0-100.
5. Write a brief 1-3 line summary for recruiters.
6. Provide recommendation: "Reject", "Review", "Shortlist", or "Strong Hire".

CRITICAL: Return ONLY valid JSON. No markdown, no code blocks, no explanations.

REQUIRED JSON FORMAT:
{
  "skill_match_score": 85,
  "experience_match_score": 75,
  "education_match_score": 90,
  "matched_skills": ["PHP", "MySQL", "Laravel"],
  "missing_skills": ["React", "AWS"],
  "extra_relevant_skills": ["Docker", "Git"],
  "summary": "Strong technical background with 5+ years PHP experience. Missing React but has transferable skills.",
  "recommendation": "Shortlist"
}

Return the JSON now:
PROMPT;
    }

    /**
     * Validate and normalize AI response scores
     * 
     * @param array $response Raw AI response
     * @return array Validated scores
     */
    private function validateScores(array $response): array
    {
        $scores = [
            'skill_match_score' => 0,
            'experience_match_score' => 0,
            'education_match_score' => 0,
            'matched_skills' => [],
            'missing_skills' => [],
            'extra_relevant_skills' => [],
            'summary' => '',
            'recommendation' => 'Review'
        ];

        // Validate scores (0-100)
        if (isset($response['skill_match_score'])) {
            $scores['skill_match_score'] = max(0, min(100, (int)$response['skill_match_score']));
        }
        if (isset($response['experience_match_score'])) {
            $scores['experience_match_score'] = max(0, min(100, (int)$response['experience_match_score']));
        }
        if (isset($response['education_match_score'])) {
            $scores['education_match_score'] = max(0, min(100, (int)$response['education_match_score']));
        }

        // Validate arrays
        if (isset($response['matched_skills']) && is_array($response['matched_skills'])) {
            $scores['matched_skills'] = array_filter(
                array_map('trim', $response['matched_skills']),
                fn($s) => !empty($s)
            );
        }
        if (isset($response['missing_skills']) && is_array($response['missing_skills'])) {
            $scores['missing_skills'] = array_filter(
                array_map('trim', $response['missing_skills']),
                fn($s) => !empty($s)
            );
        }
        if (isset($response['extra_relevant_skills']) && is_array($response['extra_relevant_skills'])) {
            $scores['extra_relevant_skills'] = array_filter(
                array_map('trim', $response['extra_relevant_skills']),
                fn($s) => !empty($s)
            );
        }

        // Validate summary
        if (isset($response['summary']) && is_string($response['summary'])) {
            $scores['summary'] = trim($response['summary']);
        }

        // Validate recommendation
        $validRecommendations = ['Reject', 'Review', 'Shortlist', 'Strong Hire'];
        if (isset($response['recommendation']) && in_array($response['recommendation'], $validRecommendations)) {
            $scores['recommendation'] = $response['recommendation'];
        }

        return $scores;
    }

    /**
     * Calculate overall match score using weighted formula
     * 
     * Formula: skill_score * 0.6 + experience_score * 0.3 + education_score * 0.1
     * 
     * @param int $skillScore Skill match score (0-100)
     * @param int $experienceScore Experience match score (0-100)
     * @param int $educationScore Education match score (0-100)
     * @return int Overall match score (0-100)
     */
    public function calculateOverallMatchScore(
        int $skillScore,
        int $experienceScore,
        int $educationScore
    ): int {
        $overall = round(
            ($skillScore * 0.6) +
            ($experienceScore * 0.3) +
            ($educationScore * 0.1)
        );

        return max(0, min(100, $overall));
    }

    /**
     * Store scores in candidate_job_scores table
     * 
     * @param int $candidateId Candidate ID
     * @param int $jobId Job ID
     * @param array $scores Score data
     * @return bool Success
     */
    private function storeScores(int $candidateId, int $jobId, array $scores): bool
    {
        $db = Database::getInstance();

        $sql = "INSERT INTO candidate_job_scores (
            candidate_id, job_id, overall_match_score,
            skill_score, experience_score, education_score,
            matched_skills, missing_skills, extra_relevant_skills,
            summary, recommendation, ai_parsed_at
        ) VALUES (
            :candidate_id, :job_id, :overall_match_score,
            :skill_score, :experience_score, :education_score,
            :matched_skills, :missing_skills, :extra_relevant_skills,
            :summary, :recommendation, NOW()
        ) ON DUPLICATE KEY UPDATE
            overall_match_score = VALUES(overall_match_score),
            skill_score = VALUES(skill_score),
            experience_score = VALUES(experience_score),
            education_score = VALUES(education_score),
            matched_skills = VALUES(matched_skills),
            missing_skills = VALUES(missing_skills),
            extra_relevant_skills = VALUES(extra_relevant_skills),
            summary = VALUES(summary),
            recommendation = VALUES(recommendation),
            ai_parsed_at = NOW(),
            updated_at = NOW()";

        try {
            $db->query($sql, [
                'candidate_id' => $candidateId,
                'job_id' => $jobId,
                'overall_match_score' => $scores['overall_match_score'],
                'skill_score' => $scores['skill_match_score'],
                'experience_score' => $scores['experience_match_score'],
                'education_score' => $scores['education_match_score'],
                'matched_skills' => json_encode($scores['matched_skills'], JSON_UNESCAPED_UNICODE),
                'missing_skills' => json_encode($scores['missing_skills'], JSON_UNESCAPED_UNICODE),
                'extra_relevant_skills' => json_encode($scores['extra_relevant_skills'], JSON_UNESCAPED_UNICODE),
                'summary' => $scores['summary'],
                'recommendation' => $scores['recommendation']
            ]);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to store job match scores: " . $e->getMessage());
            return false;
        }
    }
}

