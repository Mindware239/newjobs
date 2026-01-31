<?php

declare(strict_types=1);

namespace App\Models;

class Resume extends Model
{
    protected string $table = 'resumes';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'candidate_id',
        'template_id',
        'title',
        'job_category',
        'status',
        'strength_score',
        'ats_score',
        'is_primary',
        'pdf_url',
        'preview_image',
        'version'
    ];

    /**
     * Get candidate that owns this resume
     */
    public function candidate()
    {
        $candidateId = $this->attributes['candidate_id'] ?? null;
        if (!$candidateId) {
            return null;
        }
        return Candidate::find((int)$candidateId);
    }

    /**
     * Get template for this resume
     */
    public function template()
    {
        $templateId = $this->attributes['template_id'] ?? null;
        if (!$templateId) {
            return null;
        }
        return ResumeTemplate::find((int)$templateId);
    }

    /**
     * Get all sections for this resume
     */
    public function sections(): array
    {
        $resumeId = $this->attributes['id'] ?? $this->id ?? null;
        if (!$resumeId) {
            return [];
        }

        $results = $this->getDb()->fetchAll(
            "SELECT * FROM resume_sections WHERE resume_id = :resume_id ORDER BY sort_order ASC, id ASC",
            ['resume_id' => $resumeId]
        );

        return array_map(function ($row) {
            return new ResumeSection($row);
        }, $results);
    }

    /**
     * Get sections as array (for JSON responses)
     */
    public function getSectionsArray(): array
    {
        $sections = $this->sections();
        return array_map(function ($section) {
            $attrs = $section->attributes ?? [];
            return [
                'id' => $attrs['id'] ?? null,
                'section_type' => $attrs['section_type'] ?? null,
                'section_data' => json_decode($attrs['section_data'] ?? '{}', true),
                'sort_order' => (int)($attrs['sort_order'] ?? 0),
                'is_visible' => (bool)($attrs['is_visible'] ?? true),
            ];
        }, $sections);
    }

    /**
     * Get id property
     */
    public function getId(): ?int
    {
        return isset($this->attributes['id']) ? (int)$this->attributes['id'] : null;
    }

    /**
     * Calculate resume strength score (0-100)
     */
    public function calculateStrengthScore(): int
    {
        $score = 0;
        $sections = $this->sections();
        
        if (empty($sections)) {
            return 0;
        }

        // Check for required sections
        $requiredSections = ['header', 'summary', 'experience', 'education'];
        $presentSections = array_map(function ($section) {
            return $section->attributes['section_type'] ?? null;
        }, $sections);

        foreach ($requiredSections as $reqSection) {
            if (in_array($reqSection, $presentSections)) {
                $score += 25; // Each required section worth 25 points
            }
        }

        // Bonus points for additional sections
        $optionalSections = ['skills', 'languages', 'certifications', 'projects'];
        $optionalCount = 0;
        foreach ($optionalSections as $optSection) {
            if (in_array($optSection, $presentSections)) {
                $optionalCount++;
            }
        }
        $score += min($optionalCount * 5, 20); // Max 20 bonus points

        // Check if sections have content
        foreach ($sections as $section) {
            $data = json_decode($section->attributes['section_data'] ?? '{}', true);
            if (!empty($data) && isset($data['content'])) {
                $content = $data['content'];
                // Handle both string and array content
                if (is_string($content) && !empty(trim($content))) {
                    $score += 2; // Max 20 points for content
                } elseif (is_array($content) && !empty($content)) {
                    // For array content (like header with full_name, email, etc.), check if any field has value
                    $hasContent = false;
                    foreach ($content as $value) {
                        if (is_string($value) && !empty(trim($value))) {
                            $hasContent = true;
                            break;
                        } elseif (!empty($value)) {
                            $hasContent = true;
                            break;
                        }
                    }
                    if ($hasContent) {
                        $score += 2; // Max 20 points for content
                    }
                }
            }
        }

        return min($score, 100);
    }

    /**
     * Set this resume as primary for the candidate
     */
    public function setPrimary(): bool
    {
        $candidateId = $this->attributes['candidate_id'] ?? null;
        if (!$candidateId) {
            return false;
        }

        $resumeId = $this->attributes['id'] ?? $this->id ?? null;
        if (!$resumeId) {
            return false;
        }

        $db = $this->getDb();

        // Unset all other primary resumes for this candidate
        $db->query(
            "UPDATE resumes SET is_primary = 0 WHERE candidate_id = :candidate_id AND id != :resume_id",
            ['candidate_id' => $candidateId, 'resume_id' => $resumeId]
        );

        // Set this resume as primary
        $this->setAttribute('is_primary', 1);
        return $this->save();
    }

    /**
     * Check if candidate can create more resumes (premium check)
     */
    public static function canCreateMore(int $candidateId): bool
    {
        $candidate = Candidate::find($candidateId);
        if (!$candidate) {
            return false;
        }

        // Premium candidates can create unlimited resumes
        if ($candidate->attributes['is_premium'] ?? false) {
            return true;
        }

        // Free candidates limited to 1 resume
        $instance = new self();
        $count = $instance->getDb()->fetchOne(
            "SELECT COUNT(*) as count FROM resumes WHERE candidate_id = :candidate_id AND status != 'archived'",
            ['candidate_id' => $candidateId]
        );

        return (int)($count['count'] ?? 0) < 1;
    }

    /**
     * Get primary resume for candidate
     */
    public static function getPrimary(int $candidateId): ?self
    {
        $instance = new self();
        $result = $instance->getDb()->fetchOne(
            "SELECT * FROM {$instance->table} WHERE candidate_id = :candidate_id AND is_primary = 1 LIMIT 1",
            ['candidate_id' => $candidateId]
        );

        if ($result) {
            return new self($result);
        }

        return null;
    }

    /**
     * Get all resumes for candidate
     */
    public static function getByCandidateId(int $candidateId, string $status = null): array
    {
        $instance = new self();
        $sql = "SELECT * FROM {$instance->table} WHERE candidate_id = :candidate_id";
        $params = ['candidate_id' => $candidateId];

        if ($status !== null) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY is_primary DESC, updated_at DESC";

        $results = $instance->getDb()->fetchAll($sql, $params);

        return array_map(function ($row) {
            return new self($row);
        }, $results);
    }
}

