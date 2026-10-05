<?php

declare(strict_types=1);

namespace App\Models;

class Candidate extends Model
{
    protected string $table = 'candidates';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id', 'full_name', 'professional_title', 'dob', 'gender', 'mobile', 'city', 'state', 'country',
        'profile_picture', 'resume_url', 'video_intro_url', 'video_intro_type',
        'self_introduction', 'expected_salary_min', 'expected_salary_max', 'current_salary',
        'notice_period', 'preferred_job_location', 'portfolio_url', 'linkedin_url',
        'github_url', 'website_url', 'profile_strength', 'is_profile_complete',
        'is_verified', 'is_premium', 'premium_expires_at',
        'education_data', 'experience_data', 'skills_data', 'languages_data', 'preferences_data',
        'certificates_data', 'verification_data',
        'auto_apply_enabled', 'auto_apply_threshold', 'auto_apply_cooldown_minutes', 'auto_apply_last_run_at',
        'created_by', 'source', 'profile_status', 'visibility'
    ];

    /**
     * Get candidate by user ID
     */
    public static function findByUserId(int $userId): ?self
    {
        $instance = new self();
        // Use direct SQL query to ensure data is fetched correctly
        $sql = "SELECT * FROM {$instance->getTable()} WHERE user_id = :user_id LIMIT 1";
        $result = $instance->getDb()->fetchOne($sql, ['user_id' => $userId]);
        
        error_log("findByUserId: user_id={$userId}, result=" . ($result ? "FOUND(id=" . ($result['id'] ?? 'none') . ")" : "NOT FOUND"));
        
        if ($result) {
            // Ensure 'id' key exists (might be numeric key 0)
            if (!isset($result['id']) && isset($result[0])) {
                $result['id'] = $result[0];
            }
            
            // CRITICAL FIX: Use constructor first, then verify and force-set if needed
            $candidate = new self($result);
            
            // IMMEDIATELY check and fix if attributes are not set
            if (!isset($candidate->attributes) || !is_array($candidate->attributes) || empty($candidate->attributes)) {
                // Force set attributes directly
                if (is_array($result) && !empty($result)) {
                    // Use reflection or direct property access
                    $candidate->attributes = $result;
                } else {
                    error_log("FATAL: Cannot set attributes - invalid result");
                    return null;
                }
            }
            
            // Final verification - attributes MUST be array with data
            if (!is_array($candidate->attributes) || empty($candidate->attributes['id'])) {
                error_log("FATAL ERROR: Attributes still invalid!");
                return null;
            }
            
            return $candidate;
        }
        return null;
    }

    /**
     * Create candidate profile for user
     * Optionally populate with initial data (e.g., from OAuth)
     */
    public static function createForUser(int $userId, array $initialData = []): self
    {
        $service = new \App\Services\CandidateCreationService();
        return $service->ensureCandidateForUser($userId, $initialData);
    }

    /**
     * Calculate profile strength percentage
     */
    public function calculateProfileStrength(): int
    {
        $points = 0;
        $totalPoints = 0;

        // Basic Details (30 points)
        $totalPoints += 30;
        if (!empty($this->attributes['full_name'])) $points += 5;
        if (!empty($this->attributes['dob'])) $points += 5;
        if (!empty($this->attributes['gender'])) $points += 5;
        if (!empty($this->attributes['mobile'])) $points += 5;
        if (!empty($this->attributes['city']) && !empty($this->attributes['state'])) $points += 10;

        // Profile Media (20 points)
        $totalPoints += 20;
        if (!empty($this->attributes['profile_picture'])) $points += 10;
        if (!empty($this->attributes['resume_url'])) $points += 10;

        // Introduction (10 points)
        $totalPoints += 10;
        if (!empty($this->attributes['self_introduction'])) $points += 10;

        // Education (15 points) - from JSON column
        $totalPoints += 15;
        $education = [];
        if (!empty($this->attributes['education_data'])) {
            $education = json_decode($this->attributes['education_data'], true) ?? [];
        }
        if (count($education) > 0) $points += 15;

        // Experience (15 points) - from JSON column
            $totalPoints += 15;
        $experience = [];
        if (!empty($this->attributes['experience_data'])) {
            $experience = json_decode($this->attributes['experience_data'], true) ?? [];
        }
        if (count($experience) > 0) $points += 15;

        // Skills (10 points) - from JSON column
        $totalPoints += 10;
        $skills = [];
        if (!empty($this->attributes['skills_data'])) {
            $skills = json_decode($this->attributes['skills_data'], true) ?? [];
        }
        $skillsCount = count($skills);
        if ($skillsCount >= 3) $points += 10;
        elseif ($skillsCount > 0) $points += 5;

        // Certificates (5 points)
        $totalPoints += 5;
        $certificates = [];
        if (!empty($this->attributes['certificates_data'])) {
            $certificates = json_decode($this->attributes['certificates_data'], true) ?? [];
        }
        if (count($certificates) > 0) $points += 5;

        // Verification (5 points)
        $totalPoints += 5;
        $verification = [];
        if (!empty($this->attributes['verification_data'])) {
            $verification = json_decode($this->attributes['verification_data'], true) ?? [];
        }
        if (!empty($verification['need_verification']) && $verification['need_verification']) {
             if (!empty($verification['relieving_letter']) || !empty($verification['offer_letter']) || !empty($verification['salary_slips'])) {
                 $points += 5;
             }
        }

        // Additional Info (10 points)
        $totalPoints += 10;
        if (!empty($this->attributes['expected_salary_min'])) $points += 5;
        if (!empty($this->attributes['notice_period'])) $points += 5;

        return (int) round(($points / max($totalPoints, 1)) * 100);
    }

    /**
     * Update profile strength
     */
    public function updateProfileStrength(): void
    {
        $this->attributes['profile_strength'] = $this->calculateProfileStrength();
        $this->attributes['is_profile_complete'] = ($this->attributes['profile_strength'] >= 80) ? 1 : 0;
        $this->save();
    }

    /**
     * Get missing mandatory fields for profile completion
     */
    public function getMissingFields(): array
    {
        $missing = [];
        $labels = [
            'full_name' => 'Full Name',
            'dob' => 'Date of Birth',
            'gender' => 'Gender',
            'mobile' => 'Mobile Number',
            'location' => 'City & State',
            'profile_picture' => 'Profile Photo',
            'resume' => 'Resume (CV)',
            'skills' => 'Skills (at least 3)',
            'experience' => 'Work Experience',
            'education' => 'Education Details',
            'languages' => 'Languages Known',
            'self_introduction' => 'Self Introduction'
        ];

        if (empty($this->attributes['full_name'])) $missing[] = $labels['full_name'];
        if (empty($this->attributes['dob'])) $missing[] = $labels['dob'];
        if (empty($this->attributes['gender'])) $missing[] = $labels['gender'];
        if (empty($this->attributes['mobile'])) $missing[] = $labels['mobile'];
        if (empty($this->attributes['city']) || empty($this->attributes['state'])) $missing[] = $labels['location'];
        if (empty($this->attributes['profile_picture'])) $missing[] = $labels['profile_picture'];
        if (empty($this->attributes['resume_url'])) $missing[] = $labels['resume'];
        if (empty($this->attributes['self_introduction'])) $missing[] = $labels['self_introduction'];
        
        $skills = $this->skills();
        if (count($skills) < 3) $missing[] = $labels['skills'];
        
        $experience = $this->experience();
        if (empty($experience)) $missing[] = $labels['experience'];
        
        $education = $this->education();
        if (empty($education)) $missing[] = $labels['education'];

        $languages = $this->languages();
        if (empty($languages)) $missing[] = $labels['languages'];
        
        return $missing;
    }

    /**
     * Get completion percentage
     */
    public function calculateCompletionPercentage(): int
    {
        return $this->calculateProfileStrength();
    }

    /**
     * Get user relationship
     */
    public function user(): ?User
    {
        if (empty($this->attributes['user_id'])) {
            return null;
        }
        return User::find($this->attributes['user_id']);
    }

    /**
     * Check if profile is complete
     */
    public function isProfileComplete(): bool
    {
        return ($this->attributes['is_profile_complete'] ?? 0) == 1;
    }

    /**
     * Check if premium
     */
    public function isPremium(): bool
    {
        $isPremium = (int)($this->attributes['is_premium'] ?? 0) === 1;
        $expiresAt = $this->attributes['premium_expires_at'] ?? null;
        if (!$isPremium || empty($expiresAt)) {
            return false;
        }
        return strtotime((string)$expiresAt) > time();
    }

    /**
     * Get education records from JSON column
     */
    public function education(): array
    {
        if (empty($this->attributes['education_data'])) {
            return [];
        }
        $education = json_decode($this->attributes['education_data'], true) ?? [];
        // Sort by start_date DESC
        usort($education, function($a, $b) {
            $dateA = $a['start_date'] ?? '';
            $dateB = $b['start_date'] ?? '';
            return strcmp($dateB, $dateA);
        });
        return $education;
    }

    /**
     * Get experience records from JSON column
     */
    public function experience(): array
    {
        if (empty($this->attributes['experience_data'])) {
            return [];
        }
        $experience = json_decode($this->attributes['experience_data'], true) ?? [];
        // Sort by start_date DESC
        usort($experience, function($a, $b) {
            $dateA = $a['start_date'] ?? '';
            $dateB = $b['start_date'] ?? '';
            return strcmp($dateB, $dateA);
        });
        return $experience;
    }

    /**
     * Get skills from JSON column
     */
    public function skills(): array
    {
        if (empty($this->attributes['skills_data'])) {
            return [];
        }
        return json_decode($this->attributes['skills_data'], true) ?? [];
    }

    /**
     * Get languages from JSON column
     */
    public function languages(): array
    {
        if (empty($this->attributes['languages_data'])) {
            return [];
        }
        return json_decode($this->attributes['languages_data'], true) ?? [];
    }

    /**
     * Get certificates from JSON column
     */
    public function certificates(): array
    {
        if (empty($this->attributes['certificates_data'])) {
            return [];
        }
        return json_decode($this->attributes['certificates_data'], true) ?? [];
    }

    /**
     * Get verification data from JSON column
     */
    public function verification(): array
    {
        if (empty($this->attributes['verification_data'])) {
            return [];
        }
        return json_decode($this->attributes['verification_data'], true) ?? [];
    }

    /**
     * Get suggested candidates for a job
     */
    public static function getSuggestedCandidates(int $jobId, array $excludeCandidateIds = [], int $limit = 45): array
    {
        $db = \App\Core\Database::getInstance();
        $job = Job::find($jobId);
        if (!$job) return [];

        $jobCategory = $job->attributes['category'] ?? '';
        $excludeIds = !empty($excludeCandidateIds) ? implode(',', array_map('intval', $excludeCandidateIds)) : '0';

        $sql = "SELECT 
                    c.id as candidate_id, u.id as candidate_user_id,
                    c.full_name, c.city, c.state, c.country, c.profile_picture, c.resume_url,
                    u.email as candidate_email, u.phone,
                    c.current_salary, c.expected_salary_min, c.expected_salary_max,
                    c.education_data, c.experience_data, c.skills_data, c.languages_data,
                    rs_cert.section_data as certifications_data,
                    cjs.overall_match_score, cjs.skill_score, cjs.experience_score, cjs.education_score,
                    cjs.recommendation, cjs.summary as match_summary,
                    cjs.matched_skills, cjs.missing_skills, cjs.extra_relevant_skills,
                    'suggested' as status,
                    :job_id as job_id, :job_title as job_title,
                    :job_currency as job_currency, :job_slug as job_slug
                FROM candidates c
                INNER JOIN users u ON u.id = c.user_id
                LEFT JOIN resumes r ON r.candidate_id = c.id AND r.is_primary = 1
                LEFT JOIN resume_sections rs_cert ON rs_cert.resume_id = r.id AND rs_cert.section_type = 'certifications'
                LEFT JOIN candidate_job_scores cjs ON cjs.candidate_id = c.id AND cjs.job_id = :job_id_join
                WHERE c.id NOT IN ({$excludeIds})
                AND (
                    r.job_category = :category 
                    OR c.skills_data LIKE :category_like
                    OR :category_empty = 1
                )
                ORDER BY cjs.overall_match_score DESC, c.profile_strength DESC, c.created_at DESC
                LIMIT :limit";

        return $db->fetchAll($sql, [
            'job_id' => $job->attributes['id'],
            'job_title' => $job->attributes['title'],
            'job_currency' => $job->attributes['currency'],
            'job_slug' => $job->attributes['slug'],
            'job_id_join' => $job->attributes['id'],
            'category' => $jobCategory,
            'category_like' => "%{$jobCategory}%",
            'category_empty' => empty($jobCategory) ? 1 : 0,
            'limit' => $limit
        ]);
    }
}
