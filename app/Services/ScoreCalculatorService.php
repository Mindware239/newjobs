<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Candidate;
use App\Models\Job;

class ScoreCalculatorService
{
    /**
     * Calculate Title Match Score
     */
    public function calculateTitleMatch(Candidate $candidate, Job $job): float
    {
        $jobTitle = strtolower(trim((string)($job->attributes['title'] ?? '')));
        $candTitle = strtolower(trim((string)($candidate->attributes['professional_title'] ?? '')));
        
        if (empty($jobTitle)) return 50;
        if (empty($candTitle)) {
            // Try to extract from current experience if missing
            $experiences = $candidate->experience();
            foreach ($experiences as $exp) {
                if (!empty($exp['is_current']) && !empty($exp['job_title'])) {
                    $candTitle = strtolower(trim($exp['job_title']));
                    break;
                }
            }
            if (empty($candTitle)) return 50; // Neutral if completely missing
        }

        // Exact match
        if ($jobTitle === $candTitle) return 100;

        // Substring match
        if (strpos($jobTitle, $candTitle) !== false || strpos($candTitle, $jobTitle) !== false) {
            return 85;
        }

        // Keyword overlap match
        $jobWords = explode(' ', preg_replace('/[^a-z0-9 ]/', '', $jobTitle));
        $candWords = explode(' ', preg_replace('/[^a-z0-9 ]/', '', $candTitle));
        
        $jobWords = array_filter($jobWords, fn($w) => strlen($w) > 2);
        $candWords = array_filter($candWords, fn($w) => strlen($w) > 2);

        $overlap = array_intersect($jobWords, $candWords);
        
        if (!empty($overlap)) {
            $matchRatio = count($overlap) / max(count($jobWords), 1);
            return 40 + ($matchRatio * 40); // 40 to 80 range
        }

        return 10; // Very poor match
    }

    /**
     * Calculate Skill Match Score
     */
    public function calculateSkillMatch(Candidate $candidate, Job $job, array &$matchedSkills, array &$missingSkills, array &$extraRelevantSkills): float
    {
        // Get job requirements
        $jobSkills = $job->skills();
        $jobSkillNames = array_map(fn($s) => strtolower(trim($s['name'] ?? '')), $jobSkills);
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

        if (!empty($jobSkillNames)) {
            return (count($matchedSkills) / count($jobSkillNames)) * 100;
        }

        return 0;
    }

    /**
     * Calculate Experience Match Score
     */
    public function calculateExperienceMatch(Candidate $candidate, Job $job): float
    {
        $jobMinExp = (int)($job->attributes['min_experience'] ?? 0);
        $jobMaxExp = (int)($job->attributes['max_experience'] ?? 0);

        if ($jobMinExp === 0 && $jobMaxExp === 0) return 50;

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

        if ($jobMinExp > 0 && $totalYears < $jobMinExp) {
            $ratio = $totalYears / max($jobMinExp, 1);
            return max(0, $ratio * 50);
        } elseif ($jobMaxExp > 0 && $totalYears > $jobMaxExp) {
            return 85;
        }
        
        return 100;
    }

    /**
     * Calculate Location Match Score
     */
    public function calculateLocationMatch(Candidate $candidate, Job $job): float
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
            $preferredLocations = array_map(fn($v) => strtolower(trim((string)$v)), $prefs['preferred_locations']);
            $preferredLocations = array_filter($preferredLocations);
        }
        $preferredWorkMode = strtolower(trim((string)($prefs['preferred_work_mode'] ?? '')));

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

        $isRemote = (int)($job->attributes['is_remote'] ?? 0);
        if ($isRemote) {
            if ($preferredWorkMode === 'office') return 70;
            return 100;
        }

        if (empty($jobLocationStrings)) return 50;

        foreach ($jobLocationStrings as $jobLoc) {
            foreach ($preferredLocations as $prefLoc) {
                if ($prefLoc !== '' && (strpos($jobLoc, $prefLoc) !== false || strpos($prefLoc, $jobLoc) !== false)) {
                    return 100;
                }
            }
            if (!empty($candidateLocation) && (strpos($jobLoc, $candidateLocation) !== false || strpos($candidateLocation, $jobLoc) !== false)) {
                return 95;
            }
            if (!empty($candidateCity) && strpos($jobLoc, $candidateCity) !== false) {
                return 90;
            }
            if (!empty($candidateState) && strpos($jobLoc, $candidateState) !== false) {
                return 75;
            }
        }

        return 30;
    }

    /**
     * Calculate Education Match Score
     */
    public function calculateEducationMatch(Candidate $candidate, Job $job): float
    {
        $candidateEducation = $candidate->education();
        if (empty($candidateEducation)) return 50;
        return 80;
    }

    /**
     * Calculate Salary Match Score
     */
    public function calculateSalaryMatch(Candidate $candidate, Job $job): float
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

        if ($candidateMin === 0 || $jobMax === 0) return 50;
        if ($candidateMin <= $jobMax && $candidateMax >= $jobMin) return 100;
        if ($candidateMin <= $jobMax * 1.2) return 75;
        if ($candidateMin <= $jobMax * 1.5) return 50;

        return 25;
    }

    /**
     * Calculate Preference Match Score
     */
    public function calculatePreferenceMatch(Candidate $candidate, Job $job): float
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
             $score += 20;
        }

        if (is_array($jobTypePref) && !empty($jobTypePref) && $jobType !== '') {
            $normalized = array_map(fn($v) => strtolower(trim((string)$v)), $jobTypePref);
            if (in_array($jobType, $normalized)) {
                $score += 30;
            }
        } else {
             $score += 15;
        }

        if ($relocate === 1) {
            $score += 30;
        } else {
            $score += 10;
        }
        return min(100, $score);
    }
}
