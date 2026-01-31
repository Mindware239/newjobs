<?php

declare(strict_types=1);

namespace App\Models;

class ResumeTemplate extends Model
{
    protected string $table = 'resume_templates';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'job_category',
        'is_premium',
        'preview_image',
        'template_schema',
        'css_styles',
        'is_active',
        'sort_order',
        'has_photo',
        'layout_type',
        'color_scheme',
        'tags'
    ];

    /**
     * Get template schema as array
     */
    public function getSchema(): array
    {
        $schemaJson = $this->attributes['template_schema'] ?? '{}';
        return json_decode($schemaJson, true) ?? [];
    }

    /**
     * Get tags as array
     */
    public function getTags(): array
    {
        $tagsJson = $this->attributes['tags'] ?? '[]';
        return json_decode($tagsJson, true) ?? [];
    }

    /**
     * Check if template is accessible by candidate
     */
    public function isAccessible(Candidate $candidate): bool
    {
        // Free templates are always accessible
        if (!($this->attributes['is_premium'] ?? false)) {
            return true;
        }

        // Premium templates require premium account
        return (bool)($candidate->attributes['is_premium'] ?? false);
    }

    /**
     * Get all active templates with filters
     */
    public static function getActive(array $filters = []): array
    {
        $instance = new self();
        $sql = "SELECT * FROM {$instance->table} WHERE is_active = 1";
        $params = [];

        // Premium filter
        if (isset($filters['premium_only'])) {
            $sql .= " AND is_premium = :is_premium";
            $params['is_premium'] = $filters['premium_only'] ? 1 : 0;
        } elseif (isset($filters['include_premium']) && !$filters['include_premium']) {
            $sql .= " AND is_premium = 0";
        }

        // Category filter
        if (!empty($filters['category'])) {
            $sql .= " AND category = :category";
            $params['category'] = $filters['category'];
        }

        // Job category filter
        if (!empty($filters['job_category'])) {
            $sql .= " AND job_category = :job_category";
            $params['job_category'] = $filters['job_category'];
        }

        // Photo filter
        if (isset($filters['has_photo'])) {
            $sql .= " AND has_photo = :has_photo";
            $params['has_photo'] = $filters['has_photo'] ? 1 : 0;
        }

        // Layout type filter
        if (!empty($filters['layout_type'])) {
            $sql .= " AND layout_type = :layout_type";
            $params['layout_type'] = $filters['layout_type'];
        }

        // Color scheme filter
        if (!empty($filters['color_scheme'])) {
            $sql .= " AND color_scheme = :color_scheme";
            $params['color_scheme'] = $filters['color_scheme'];
        }

        $sql .= " ORDER BY sort_order ASC, name ASC";

        $results = $instance->getDb()->fetchAll($sql, $params);

        return array_map(function ($row) {
            return new self($row);
        }, $results);
    }

    /**
     * Get all active templates (backward compatibility)
     */
    public static function getActiveLegacy(bool $includePremium = true, ?string $category = null): array
    {
        return self::getActive([
            'include_premium' => $includePremium,
            'category' => $category
        ]);
    }

    /**
     * Find template by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        $instance = new self();
        $result = $instance->getDb()->fetchOne(
            "SELECT * FROM {$instance->table} WHERE slug = :slug AND is_active = 1 LIMIT 1",
            ['slug' => $slug]
        );

        if ($result) {
            return new self($result);
        }

        return null;
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        $instance = new self();
        $results = $instance->getDb()->fetchAll(
            "SELECT DISTINCT category FROM {$instance->table} WHERE is_active = 1 AND category IS NOT NULL ORDER BY category ASC"
        );
        return array_column($results, 'category');
    }

    /**
     * Get available job categories
     */
    public static function getJobCategories(): array
    {
        $instance = new self();
        $results = $instance->getDb()->fetchAll(
            "SELECT DISTINCT job_category FROM {$instance->table} WHERE is_active = 1 AND job_category IS NOT NULL ORDER BY job_category ASC"
        );
        return array_column($results, 'job_category');
    }

    /**
     * Get available layout types
     */
    public static function getLayoutTypes(): array
    {
        return ['single-column', 'two-column', 'three-column'];
    }

    /**
     * Get available color schemes
     */
    public static function getColorSchemes(): array
    {
        $instance = new self();
        $results = $instance->getDb()->fetchAll(
            "SELECT DISTINCT color_scheme FROM {$instance->table} WHERE is_active = 1 AND color_scheme IS NOT NULL ORDER BY color_scheme ASC"
        );
        return array_column($results, 'color_scheme');
    }
}
