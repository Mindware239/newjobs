<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Candidate;
use App\Core\Database;

/**
 * Candidate Profile Service
 * 
 * Updates candidate profile with parsed resume data and calculates profile strength.
 */
class CandidateProfileService
{
    /**
     * Update candidate profile with parsed resume data
     * 
     * @param int $userId User ID
     * @param array $parsedData Parsed resume data from AI
     * @return bool Success
     */
    public function updateProfileFromParsedData(int $userId, array $parsedData): bool
    {
        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            throw new \RuntimeException("Candidate not found for user ID: {$userId}");
        }

        // Update JSON columns
        $updates = [];

        if (!empty($parsedData['skills'])) {
            // Convert to format matching existing structure
            $skillsData = array_map(function($skillName) {
                return [
                    'skill_id' => null,
                    'name' => $skillName,
                    'proficiency_level' => 'intermediate', // Default, can be enhanced
                    'years_of_experience' => null
                ];
            }, $parsedData['skills']);
            $updates['skills_data'] = json_encode($skillsData, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($parsedData['education'])) {
            // Convert to format matching existing structure
            $educationData = array_map(function($edu) {
                return [
                    'degree' => $edu['degree'] ?? '',
                    'field_of_study' => $edu['field_of_study'] ?? '',
                    'institution' => $edu['institution'] ?? '',
                    'start_date' => $this->formatYearToDate($edu['start_year'] ?? 0),
                    'end_date' => $edu['is_current'] ? null : $this->formatYearToDate($edu['end_year'] ?? 0),
                    'is_current' => $edu['is_current'] ?? false,
                    'grade' => null,
                    'description' => null
                ];
            }, $parsedData['education']);
            $updates['education_data'] = json_encode($educationData, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($parsedData['experience'])) {
            // Convert to format matching existing structure
            $experienceData = array_map(function($exp) {
                return [
                    'job_title' => $exp['job_title'] ?? '',
                    'company_name' => $exp['company_name'] ?? '',
                    'location' => $exp['location'] ?? '',
                    'start_date' => $exp['start_date'] ?? '',
                    'end_date' => $exp['is_current'] ? null : ($exp['end_date'] ?? null),
                    'is_current' => $exp['is_current'] ?? false,
                    'description' => $exp['summary'] ?? ''
                ];
            }, $parsedData['experience']);
            $updates['experience_data'] = json_encode($experienceData, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($parsedData['languages'])) {
            // Convert to format matching existing structure
            $languagesData = array_map(function($lang) {
                return [
                    'language' => $lang['name'] ?? '',
                    'proficiency' => $lang['proficiency'] ?? 'intermediate'
                ];
            }, $parsedData['languages']);
            $updates['languages_data'] = json_encode($languagesData, JSON_UNESCAPED_UNICODE);
        }

        // Update self_introduction if summary_profile is provided
        if (!empty($parsedData['summary_profile'])) {
            $updates['self_introduction'] = $parsedData['summary_profile'];
        }

        // Calculate profile strength
        $updates['profile_strength'] = $this->calculateProfileStrength($candidate, $updates);

        // Build UPDATE SQL
        $setParts = [];
        $params = ['user_id' => $userId];

        foreach ($updates as $field => $value) {
            $setParts[] = "{$field} = :{$field}";
            $params[$field] = $value;
        }

        if (empty($setParts)) {
            return false; // Nothing to update
        }

        $setParts[] = "updated_at = NOW()";
        $sql = "UPDATE candidates SET " . implode(', ', $setParts) . " WHERE user_id = :user_id";

        try {
            Database::getInstance()->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to update candidate profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate profile strength percentage (0-100)
     * 
     * @param Candidate $candidate Candidate model
     * @param array $updates Pending updates
     * @return int Profile strength (0-100)
     */
    private function calculateProfileStrength(Candidate $candidate, array $updates): int
    {
        $score = 0;
        $maxScore = 100;

        // Resume URL (10 points)
        if (!empty($candidate->attributes['resume_url']) || !empty($updates['resume_url'])) {
            $score += 10;
        }

        // Full name (5 points)
        if (!empty($candidate->attributes['full_name'])) {
            $score += 5;
        }

        // Contact info (10 points)
        if (!empty($candidate->attributes['mobile'])) {
            $score += 5;
        }
        if (!empty($candidate->attributes['city']) && !empty($candidate->attributes['state'])) {
            $score += 5;
        }

        // Skills (25 points)
        $skillsData = !empty($updates['skills_data']) 
            ? json_decode($updates['skills_data'], true) 
            : $candidate->skills();
        if (!empty($skillsData) && count($skillsData) > 0) {
            $score += min(25, count($skillsData) * 2); // 2 points per skill, max 25
        }

        // Experience (25 points)
        $experienceData = !empty($updates['experience_data'])
            ? json_decode($updates['experience_data'], true)
            : $candidate->experience();
        if (!empty($experienceData) && count($experienceData) > 0) {
            $score += min(25, count($experienceData) * 8); // 8 points per experience, max 25
        }

        // Education (15 points)
        $educationData = !empty($updates['education_data'])
            ? json_decode($updates['education_data'], true)
            : $candidate->education();
        if (!empty($educationData) && count($educationData) > 0) {
            $score += min(15, count($educationData) * 7); // 7 points per education, max 15
        }

        // Languages (5 points)
        $languagesData = !empty($updates['languages_data'])
            ? json_decode($updates['languages_data'], true)
            : $candidate->languages();
        if (!empty($languagesData) && count($languagesData) > 0) {
            $score += min(5, count($languagesData) * 2); // 2 points per language, max 5
        }

        // Self introduction (5 points)
        if (!empty($candidate->attributes['self_introduction']) || !empty($updates['self_introduction'])) {
            $score += 5;
        }

        return min($maxScore, $score);
    }

    /**
     * Format year to date string (YYYY-01-01)
     * 
     * @param int $year Year
     * @return string|null Date string or null if invalid
     */
    private function formatYearToDate(int $year): ?string
    {
        if ($year < 1900 || $year > 2100) {
            return null;
        }
        return sprintf('%d-01-01', $year);
    }
}

