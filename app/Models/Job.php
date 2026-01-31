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
        'is_remote', 'locations', 'status', 'visibility', 'publish_at',
        'expires_at', 'vacancies', 'views', 'job_timings', 'interview_timings', 'job_address',
        'experience_type', 'min_experience', 'max_experience', 'offers_bonus', 'call_availability',
        'company_name', 'contact_person', 'phone', 'email', 'contact_profile', 'company_size', 'hiring_urgency'
    ];

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

    public function locations()
    {
        return JobLocation::where('job_id', '=', $this->attributes['id'])->get();
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

    public function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $baseSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $result = $this->getDb()->fetchOne(
            "SELECT id FROM {$this->table} WHERE slug = :slug",
            ['slug' => $slug]
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
