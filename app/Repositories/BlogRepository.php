<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BlogRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Returns sidebar-ready interview blogs from the main blogs source.
     */
    public function getInterviewSidebarBlogs(int $limit = 10): array
    {
        $limit = max(1, $limit);

        $blogs = $this->db->fetchAll(
            "SELECT DISTINCT b.*
             FROM blogs b
             LEFT JOIN blog_category_map bcm ON bcm.blog_id = b.id
             LEFT JOIN blog_categories bc ON bc.id = bcm.category_id
             WHERE b.published_at IS NOT NULL
               AND COALESCE(b.status_id, 1) = 1
               AND COALESCE(NULLIF(TRIM(b.title), ''), '') <> ''
               AND COALESCE(NULLIF(TRIM(b.featured_image), ''), '') <> ''
               AND (
                    LOWER(COALESCE(bc.slug, '')) LIKE '%interview%'
                    OR LOWER(COALESCE(bc.name, '')) LIKE '%interview%'
               )
             ORDER BY COALESCE(b.is_featured, 0) DESC, COALESCE(b.sort_order, 0) DESC, b.published_at DESC
             LIMIT {$limit}"
        );

        if (!empty($blogs)) {
            return $blogs;
        }

        return $this->db->fetchAll(
            "SELECT b.*
             FROM blogs b
             WHERE b.published_at IS NOT NULL
               AND COALESCE(b.status_id, 1) = 1
               AND COALESCE(NULLIF(TRIM(b.title), ''), '') <> ''
               AND COALESCE(NULLIF(TRIM(b.featured_image), ''), '') <> ''
             ORDER BY COALESCE(b.is_featured, 0) DESC, COALESCE(b.sort_order, 0) DESC, b.published_at DESC
             LIMIT {$limit}"
        );
    }

    public function getFeaturedBlogs(int $limit = 6): array
    {
        $limit = max(1, $limit);
        return $this->db->fetchAll(
            "SELECT * FROM blogs
             WHERE published_at IS NOT NULL AND is_featured = 1
             ORDER BY sort_order DESC, published_at DESC
             LIMIT {$limit}"
        );
    }

    public function searchPublishedBlogs(string $search, int $perPage, int $offset): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM blogs
             WHERE published_at IS NOT NULL
               AND (title LIKE :q1 OR excerpt LIKE :q2 OR content LIKE :q3)
             ORDER BY published_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            [
                'q1' => '%' . $search . '%',
                'q2' => '%' . $search . '%',
                'q3' => '%' . $search . '%',
            ]
        );
    }

    public function countSearchPublishedBlogs(string $search): int
    {
        return (int)($this->db->fetchOne(
            "SELECT COUNT(*) as c FROM blogs WHERE published_at IS NOT NULL AND (title LIKE :q1 OR excerpt LIKE :q2 OR content LIKE :q3)",
            [
                'q1' => '%' . $search . '%',
                'q2' => '%' . $search . '%',
                'q3' => '%' . $search . '%',
            ]
        )['c'] ?? 0);
    }

    public function getPublishedBlogs(int $perPage, int $offset): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM blogs
             WHERE published_at IS NOT NULL
             ORDER BY is_featured DESC, sort_order DESC, published_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset
        );
    }

    public function countPublishedBlogs(): int
    {
        return (int)($this->db->fetchOne("SELECT COUNT(*) as c FROM blogs WHERE published_at IS NOT NULL")['c'] ?? 0);
    }

    public function getBlogCategoriesWithCounts(): array
    {
        return $this->db->fetchAll(
            "SELECT bc.*, COUNT(bcm.blog_id) as blog_count
             FROM blog_categories bc
             LEFT JOIN blog_category_map bcm ON bcm.category_id = bc.id
             GROUP BY bc.id
             ORDER BY bc.name ASC"
        );
    }

    public function getBlogTagsWithCounts(): array
    {
        return $this->db->fetchAll(
            "SELECT bt.*, COUNT(btm.blog_id) as blog_count
             FROM blog_tags bt
             LEFT JOIN blog_tag_map btm ON btm.tag_id = bt.id
             GROUP BY bt.id
             ORDER BY bt.name ASC"
        );
    }

    public function getLatestPublishedBlogs(int $limit = 3): array
    {
        $limit = max(1, $limit);
        return $this->db->fetchAll(
            "SELECT * FROM blogs
             WHERE published_at IS NOT NULL
             ORDER BY published_at DESC
             LIMIT {$limit}"
        );
    }

    public function getPublishedBlogsByCategoryId(int $categoryId, int $perPage, int $offset): array
    {
        return $this->db->fetchAll(
            "SELECT b.* FROM blogs b
             INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
             WHERE bcm.category_id = :cid AND b.published_at IS NOT NULL
             ORDER BY b.published_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            ['cid' => $categoryId]
        );
    }

    public function countPublishedBlogsByCategoryId(int $categoryId): int
    {
        return (int)($this->db->fetchOne(
            "SELECT COUNT(*) as c FROM blogs b
             INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
             WHERE bcm.category_id = :cid AND b.published_at IS NOT NULL",
            ['cid' => $categoryId]
        )['c'] ?? 0);
    }

    public function getPublishedBlogsByTagId(int $tagId, int $perPage, int $offset): array
    {
        return $this->db->fetchAll(
            "SELECT b.* FROM blogs b
             INNER JOIN blog_tag_map btm ON btm.blog_id = b.id
             WHERE btm.tag_id = :tid AND b.published_at IS NOT NULL
             ORDER BY b.published_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            ['tid' => $tagId]
        );
    }

    public function countPublishedBlogsByTagId(int $tagId): int
    {
        return (int)($this->db->fetchOne(
            "SELECT COUNT(*) as c FROM blogs b
             INNER JOIN blog_tag_map btm ON btm.blog_id = b.id
             WHERE btm.tag_id = :tid AND b.published_at IS NOT NULL",
            ['tid' => $tagId]
        )['c'] ?? 0);
    }

    public function getRelatedBlogsByCategoryIds(array $categoryIds, int $excludeBlogId, int $limit = 6): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        $ids = implode(',', array_map('intval', $categoryIds));
        $limit = max(1, $limit);
        return $this->db->fetchAll(
            "SELECT DISTINCT b.* FROM blogs b
             INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
             WHERE bcm.category_id IN ({$ids})
               AND b.id != :id
               AND b.published_at IS NOT NULL
             ORDER BY b.published_at DESC
             LIMIT {$limit}",
            ['id' => $excludeBlogId]
        );
    }

    public function getRelatedBlogsByTagIds(array $tagIds, int $excludeBlogId, int $limit = 6): array
    {
        if (empty($tagIds)) {
            return [];
        }
        $ids = implode(',', array_map('intval', $tagIds));
        $limit = max(1, $limit);
        return $this->db->fetchAll(
            "SELECT DISTINCT b.* FROM blogs b
             INNER JOIN blog_tag_map btm ON btm.blog_id = b.id
             WHERE btm.tag_id IN ({$ids})
               AND b.id != :id
               AND b.published_at IS NOT NULL
             ORDER BY b.published_at DESC
             LIMIT {$limit}",
            ['id' => $excludeBlogId]
        );
    }
}
