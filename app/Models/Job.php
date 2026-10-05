<?php

declare(strict_types=1);

namespace App\Models;

class Job extends Model
{
    protected string $table = 'jobs';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'title', 'slug', 'description', 'short_description',
        'employment_type', 'seniority', 'salary_min', 'salary_max', 'currency',
        'pay_type', 'pay_frequency', 'pay_fixed_amount', 'hours_per_week', 'shift',
        'contract_length', 'contract_period', 'commission_percent', 'incentive_rules',
        'stipend', 'internship_length', 'season_duration', 'flexible_hours',
        'remote_policy', 'remote_tools', 'language', 'category',
        'is_remote', 'locations', 'qualifications', 'status', 'visibility', 'publish_at',
        'expires_at', 'vacancies', 'views', 'job_timings', 'interview_timings', 'job_address',
        'experience_type', 'min_experience', 'max_experience', 'offers_bonus', 'call_availability',
        'company_name', 'contact_person', 'phone', 'email', 'contact_profile', 'company_size', 'hiring_urgency',
        'job_type', 'apply_link', 'company_logo'
    ];

    public static function getAllBenefits(): array
    {
        try {
            $instance = new self();
            return $instance->getDb()->fetchAll("SELECT * FROM benefits ORDER BY name ASC");
        } catch (\Exception $e) {
            error_log("Job::getAllBenefits - Error: " . $e->getMessage());
            return [];
        }
    }

    public static function getAllCategories(): array
    {
        try {
            $instance = new self();
            return $instance->getDb()->fetchAll("SELECT name as label, name as value FROM job_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        } catch (\Exception $e) {
            error_log("Job::getAllCategories - Error: " . $e->getMessage());
            return [];
        }
    }

    public function syncSkills(array $skillNames): void
    {
        $jobId = (int)($this->attributes['id'] ?? $this->id ?? 0);
        if (!$jobId) return;

        $db = $this->getDb();
        $db->query("DELETE FROM job_skills WHERE job_id = :job_id", ['job_id' => $jobId]);

        foreach ($skillNames as $name) {
            $name = trim($name);
            if ($name === '') continue;

            $skill = Skill::where('name', '=', $name)->first();
            if (!$skill) {
                $skill = new Skill();
                $skill->fill(['name' => $name, 'slug' => $skill->generateSlug($name)]);
                if (!$skill->save()) continue;
            }

            $db->query(
                "INSERT INTO job_skills (job_id, skill_id, importance) VALUES (:job_id, :skill_id, :importance) 
                 ON DUPLICATE KEY UPDATE importance = VALUES(importance)",
                ['job_id' => $jobId, 'skill_id' => $skill->id, 'importance' => 5]
            );
        }
    }

    public function syncBenefits(array $benefitIds): void
    {
        $jobId = (int)($this->attributes['id'] ?? $this->id ?? 0);
        if (!$jobId) return;

        $db = $this->getDb();
        $db->query("DELETE FROM job_benefits WHERE job_id = :job_id", ['job_id' => $jobId]);

        foreach ($benefitIds as $id) {
            $id = (int)$id;
            if ($id <= 0) continue;

            $db->query(
                "INSERT INTO job_benefits (job_id, benefit_id) VALUES (:job_id, :benefit_id)
                 ON DUPLICATE KEY UPDATE job_id = VALUES(job_id), benefit_id = VALUES(benefit_id)",
                ['job_id' => $jobId, 'benefit_id' => $id]
            );
        }
    }

    public function syncLocations(array $locations): void
    {
        $jobId = (int)($this->attributes['id'] ?? $this->id ?? 0);
        if (!$jobId) return;

        $db = $this->getDb();
        $db->query("DELETE FROM job_locations WHERE job_id = :job_id", ['job_id' => $jobId]);

        foreach ($locations as $locData) {
            if (!empty($locData['city']) || !empty($locData['state']) || !empty($locData['country'])) {
                try {
                    $location = new JobLocation();
                    $location->fill([
                        'job_id' => $jobId,
                        'city' => $locData['city'] ?? null,
                        'state' => $locData['state'] ?? null,
                        'country' => $locData['country'] ?? 'India',
                        'city_id' => null,
                        'state_id' => null,
                        'country_id' => null,
                        'latitude' => $locData['latitude'] ?? null,
                        'longitude' => $locData['longitude'] ?? null,
                    ]);
                    $location->save();
                } catch (\Exception $e) {
                    error_log("Job::syncLocations - Failed to save location: " . $e->getMessage());
                }
            }
        }
    }

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function skills()
    {
        $jobId = $this->attributes['id'] ?? $this->id ?? null;
        if (!$jobId) {
            error_log("Job::skills() - No job ID available");
            return [];
        }
        
        $sql = "SELECT s.*, js.importance 
                FROM skills s 
                INNER JOIN job_skills js ON s.id = js.skill_id 
                WHERE js.job_id = :job_id";
        $results = $this->getDb()->fetchAll($sql, ['job_id' => $jobId]);
        
        error_log("Job::skills() - Job ID: {$jobId}, Results count: " . count($results));
        if (!empty($results)) {
            error_log("Job::skills() - First result: " . json_encode($results[0]));
        }
        
        return $results;
    }

    public function qualifications()
    {
        $jsonQualifications = $this->attributes['qualifications'] ?? $this->qualifications ?? null;
        if ($jsonQualifications) {
            $decoded = json_decode($jsonQualifications, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    public function locations()
    {
        // Try getting from job_locations table first
        $locations = JobLocation::where('job_id', '=', $this->attributes['id'] ?? $this->id ?? 0)->get();
        if (!empty($locations)) {
            return $locations;
        }

        // Fallback to locations JSON column in jobs table
        $jsonLocations = $this->attributes['locations'] ?? $this->locations ?? null;
        if ($jsonLocations) {
            $decoded = json_decode($jsonLocations, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    public function applications()
    {
        return Application::where('job_id', '=', $this->attributes['id'])->get();
    }

    /**
     * Get job benefits
     * Returns array of benefit arrays with id and name
     */
    public function benefits(): array
    {
        $jobId = $this->attributes['id'] ?? $this->id ?? null;
        if (!$jobId) {
            return [];
        }
        
        try {
            $sql = "SELECT b.* 
                    FROM benefits b 
                    INNER JOIN job_benefits jb ON b.id = jb.benefit_id 
                    WHERE jb.job_id = :job_id";
            $results = $this->getDb()->fetchAll($sql, ['job_id' => $jobId]);
            return $results ?? [];
        } catch (\Exception $e) {
            error_log("Job::benefits() - Error: " . $e->getMessage());
            return [];
        }
    }

    public function attachSkills(array $skillIds, array $importances = []): void
    {
        $sql = "INSERT INTO job_skills (job_id, skill_id, importance) VALUES (:job_id, :skill_id, :importance)";
        foreach ($skillIds as $index => $skillId) {
            $importance = $importances[$index] ?? 5;
            $this->getDb()->query($sql, [
                'job_id' => $this->attributes['id'],
                'skill_id' => $skillId,
                'importance' => $importance
            ]);
        }
    }

    public function detachSkills(): void
    {
        $this->getDb()->query(
            "DELETE FROM job_skills WHERE job_id = :job_id",
            ['job_id' => $this->attributes['id']]
        );
    }

    /** URL slug from the title, unique among jobs (pass $excludeId when renaming an existing job). */
    public function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = strtolower(trim((string)preg_replace('/[^A-Za-z0-9]+/', '-', $title), '-'));
        $slug = mb_substr($slug, 0, 180) ?: 'job';
        $baseSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $result = $this->getDb()->fetchOne(
            "SELECT id FROM {$this->table} WHERE slug = :slug AND id <> :id LIMIT 1",
            ['slug' => $slug, 'id' => (int)$excludeId]
        );
        return $result !== null;
    }

    /**
     * Find job by slug
     * 
     * @param string $slug Job slug
     * @return self|null
     */
    public static function findBySlug(string $slug): ?self
    {
        $instance = new self();
        $result = $instance->getDb()->fetchOne(
            "SELECT * FROM {$instance->getTable()} WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );

        if ($result) {
            return new self($result);
        }

        return null;
    }

    public function isPublished(): bool
    {
        return ($this->attributes['status'] ?? '') === 'published';
    }

    public function incrementViews(): void
    {
        $this->attributes['views'] = ($this->attributes['views'] ?? 0) + 1;
        $this->getDb()->query(
            "UPDATE {$this->table} SET views = views + 1 WHERE id = :id",
            ['id' => $this->attributes['id']]
        );
    }
    public function getJobsByCompanyId(int $companyId): array
{
    $sql = "SELECT j.*
            FROM jobs j
            INNER JOIN companies c ON c.employer_id = j.employer_id
            WHERE c.id = :company_id";

    try {
        $rows = $this->getDb()->fetchAll($sql, ['company_id' => $companyId]);
        return is_array($rows) ? $rows : [];
    } catch (\Exception $e) {
        error_log("Job::getJobsByCompanyId error: " . $e->getMessage());
        return [];
    }
}




}
