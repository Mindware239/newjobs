<?php

declare(strict_types=1);

namespace App\Models;

class Blog extends Model
{
    protected string $table = 'blogs';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status_id',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'view_count'
    ];

    public static function findBySlug(string $slug): ?self
    {
        $instance = new self();
        $row = $instance->getDb()->fetchOne(
            "SELECT * FROM {$instance->getTable()} WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );
        return $row ? new self($row) : null;
    }

    public function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
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

    public function getCategories(): array
    {
        $rows = $this->getDb()->fetchAll(
            "SELECT c.* FROM blog_category_map bcm 
             INNER JOIN blog_categories c ON c.id = bcm.category_id
             WHERE bcm.blog_id = :blog_id",
            ['blog_id' => $this->attributes['id']]
        );
        return array_map(fn($r) => new Category($r), $rows);
    }

    public function getTags(): array
    {
        $rows = $this->getDb()->fetchAll(
            "SELECT t.* FROM blog_tag_map btm 
             INNER JOIN blog_tags t ON t.id = btm.tag_id
             WHERE btm.blog_id = :blog_id",
            ['blog_id' => $this->attributes['id']]
        );
        return array_map(fn($r) => new Tag($r), $rows);
    }

    public function attachCategoriesByIds(array $categoryIds): void
    {
        if (empty($this->attributes['id'])) {
            return;
        }
        foreach ($categoryIds as $cid) {
            $this->getDb()->query(
                "INSERT IGNORE INTO blog_category_map (blog_id, category_id) VALUES (:bid, :cid)",
                ['bid' => $this->attributes['id'], 'cid' => $cid]
            );
        }
    }

    public function detachCategories(): void
    {
        if (empty($this->attributes['id'])) {
            return;
        }
        $this->getDb()->query(
            "DELETE FROM blog_category_map WHERE blog_id = :bid",
            ['bid' => $this->attributes['id']]
        );
    }

    public function attachTagsByIds(array $tagIds): void
    {
        if (empty($this->attributes['id'])) {
            return;
        }
        foreach ($tagIds as $tid) {
            $this->getDb()->query(
                "INSERT IGNORE INTO blog_tag_map (blog_id, tag_id) VALUES (:bid, :tid)",
                ['bid' => $this->attributes['id'], 'tid' => $tid]
            );
        }
    }

    public function detachTags(): void
    {
        if (empty($this->attributes['id'])) {
            return;
        }
        $this->getDb()->query(
            "DELETE FROM blog_tag_map WHERE blog_id = :bid",
            ['bid' => $this->attributes['id']]
        );
    }
}

