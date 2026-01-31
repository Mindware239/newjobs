<?php

declare(strict_types=1);

namespace App\Models;

class Tag extends Model
{
    protected string $table = 'blog_tags';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name', 'slug'];

    public static function findBySlug(string $slug): ?self
    {
        $instance = new self();
        $row = $instance->getDb()->fetchOne(
            "SELECT * FROM {$instance->getTable()} WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );
        return $row ? new self($row) : null;
    }

    public function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $base = $slug;
        $i = 1;
        while ($this->slugExists($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $row = $this->getDb()->fetchOne(
            "SELECT id FROM {$this->table} WHERE slug = :slug",
            ['slug' => $slug]
        );
        return $row !== null;
    }
}

