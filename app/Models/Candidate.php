<?php

declare(strict_types=1);

namespace App\Models;

class Candidate extends Model
{
    protected string $table = 'candidates';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id', 'full_name', 'dob', 'gender', 'mobile', 'city', 'state', 'country',
        'profile_picture', 'resume_url', 'video_intro_url', 'video_intro_type',
        'self_introduction', 'expected_salary_min', 'expected_salary_max', 'current_salary',
        'notice_period', 'preferred_job_location', 'portfolio_url', 'linkedin_url',
        'github_url', 'website_url', 'profile_strength', 'is_profile_complete',
        'is_verified', 'is_premium', 'premium_expires_at',
        'education_data', 'experience_data', 'skills_data', 'languages_data', 'preferences_data',
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
        // Check if candidate already exists
        $existing = self::findByUserId($userId);
        if ($existing) {
            // Update with initial data if provided
            if (!empty($initialData)) {
                $updateData = [];
                foreach ($initialData as $key => $value) {
                    if (in_array($key, $existing->fillable) && empty($existing->attributes[$key])) {
                        $updateData[$key] = $value;
                    }
                }
                if (!empty($updateData)) {
                    $existing->fill($updateData);
                    $existing->save();
                    $existing->updateProfileStrength();
                }
            }
            return $existing;
        }
        
        $candidate = new self();
        $candidateData = [
            'user_id' => $userId,
            'profile_strength' => 0,
            'is_profile_complete' => 0,
            'source' => 'website'
        ];
        
        // Merge initial data (e.g., from Google OAuth)
        if (!empty($initialData)) {
            foreach ($initialData as $key => $value) {
                if (in_array($key, $candidate->fillable) && !empty($value)) {
                    $candidateData[$key] = $value;
                }
            }
        }
        
        $candidate->fill($candidateData);
        if ($candidate->save()) {
            // Reload candidate to get the ID that was just inserted
            $insertedId = $candidate->attributes[$candidate->primaryKey] ?? null;
            if ($insertedId) {
                // Reload from database to ensure all fields are populated
                // Cast to int to fix type error
                $candidate = self::find((int)$insertedId);
                if (!$candidate) {
                    // Fallback: reload by user_id
                    $candidate = self::findByUserId($userId);
                }
            }
        }
        
        // Calculate initial profile strength
        if (!empty($initialData) && $candidate) {
            /** @var Candidate $candidate */
            $candidate->updateProfileStrength();
        }
        
        return $candidate;
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

        // Additional Info (10 points)
        $totalPoints += 10;
        if (!empty($this->attributes['expected_salary_min'])) $points += 5;
        if (!empty($this->attributes['notice_period'])) $points += 5;

        return (int) round(($points / $totalPoints) * 100);
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
}
