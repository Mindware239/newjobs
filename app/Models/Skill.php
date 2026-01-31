<?php

declare(strict_types=1);

namespace App\Models;

class Skill extends Model
{
    protected string $table = 'skills';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name', 'slug'];

    public function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
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
}

