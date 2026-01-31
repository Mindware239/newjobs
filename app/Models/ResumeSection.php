<?php

declare(strict_types=1);

namespace App\Models;

class ResumeSection extends Model
{
    protected string $table = 'resume_sections';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'resume_id',
        'section_type',
        'section_data',
        'sort_order',
        'is_visible'
    ];

    /**
     * Get resume that owns this section
     */
    public function resume()
    {
        $resumeId = $this->attributes['resume_id'] ?? null;
        if (!$resumeId) {
            return null;
        }
        return Resume::find((int)$resumeId);
    }

    /**
     * Get section data as array
     */
    public function getData(): array
    {
        $dataJson = $this->attributes['section_data'] ?? '{}';
        return json_decode($dataJson, true) ?? [];
    }

    /**
     * Update section data
     */
    public function updateData(array $data): bool
    {
        $this->setAttribute('section_data', json_encode($data, JSON_UNESCAPED_UNICODE));
        return $this->save();
    }

    /**
     * Move section to new sort order
     */
    public function move(int $newOrder): bool
    {
        $this->setAttribute('sort_order', $newOrder);
        return $this->save();
    }

    /**
     * Toggle visibility
     */
    public function toggleVisibility(): bool
    {
        $current = (bool)($this->attributes['is_visible'] ?? true);
        $this->setAttribute('is_visible', $current ? 0 : 1);
        return $this->save();
    }

    /**
     * Delete section
     */
    public function delete(): bool
    {
        $id = $this->attributes['id'] ?? $this->id ?? null;
        if (!$id) {
            return false;
        }

        return $this->getDb()->query(
            "DELETE FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        ) !== false;
    }
}

