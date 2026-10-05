<?php
declare(strict_types=1);

namespace App\Services;

class BlogPresentationService
{
    public function buildIndexPayload(
        array $blogs,
        array $featured,
        array $categories,
        array $tags,
        array $latestArticles,
        array $byCategory,
        string $search,
        int $page,
        int $perPage,
        int $total
    ): array {
        return [
            'title' => 'Blog',
            'blogs' => $blogs,
            'featured' => $featured,
            'categories' => $categories,
            'tags' => $tags,
            'latestArticles' => $latestArticles,
            'byCategory' => $byCategory,
            'search' => $search,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total
            ]
        ];
    }

    public function uniqueRelatedBlogs(array $related, int $limit = 6): array
    {
        $seen = [];
        $filtered = array_values(array_filter($related, static function ($item) use (&$seen) {
            $id = (int)($item['id'] ?? 0);
            if ($id <= 0 || isset($seen[$id])) {
                return false;
            }
            $seen[$id] = true;
            return true;
        }));

        return array_slice($filtered, 0, max(1, $limit));
    }

    public function buildPagedPayload(string $title, array $entity, string $entityKey, array $blogs, array $latestArticles, int $page, int $perPage, int $total): array
    {
        return [
            'title' => $title,
            $entityKey => $entity,
            'blogs' => $blogs,
            'latestArticles' => $latestArticles,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total
            ]
        ];
    }
}
