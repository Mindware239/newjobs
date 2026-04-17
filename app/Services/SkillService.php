<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class SkillService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Specialized skills suggestion logic migrated from api.php
     */
    public function getSuggestions(string $q = '', string $title = '', string $category = '', int $limit = 10): array
    {
        $limit = max(1, min(30, $limit));
        $suggestions = [];
        
        try {
            $baseRows = [];
            if ($q !== '') {
                $like = '%' . strtolower($q) . '%';
                $start = strtolower($q) . '%';
                $baseRows = $this->db->fetchAll(
                    "SELECT id, name, slug 
                     FROM skills 
                     WHERE LOWER(name) LIKE :like1 OR LOWER(slug) LIKE :like2
                     ORDER BY 
                        CASE 
                            WHEN LOWER(name) = :exact THEN 1
                            WHEN LOWER(name) LIKE :start THEN 2
                            ELSE 3
                        END,
                        name ASC
                     LIMIT " . (int)($limit * 2),
                    ['like1' => $like, 'like2' => $like, 'exact' => strtolower($q), 'start' => $start]
                );
            }
            
            $ctxRows = [];
            if ($title !== '' || $category !== '') {
                $params = [];
                $conds = ["j.status = 'published'"];
                if ($title !== '') {
                    $conds[] = "LOWER(j.title) LIKE :title_like";
                    $params['title_like'] = '%' . strtolower($title) . '%';
                }
                if ($category !== '') {
                    $conds[] = "j.category = :category_exact";
                    $params['category_exact'] = $category;
                }
                $sql = "SELECT s.id, s.name, s.slug, COUNT(js.skill_id) AS usage_count
                        FROM job_skills js
                        INNER JOIN skills s ON s.id = js.skill_id
                        INNER JOIN jobs j ON j.id = js.job_id
                        WHERE " . implode(' AND ', $conds) . "
                        GROUP BY s.id, s.name, s.slug
                        ORDER BY usage_count DESC, s.name ASC
                        LIMIT " . (int)($limit * 2);
                $ctxRows = $this->db->fetchAll($sql, $params);
            }
            
            $map = [];
            foreach ($baseRows as $row) {
                $key = (string)$row['id'];
                if (!isset($map[$key])) {
                    $map[$key] = ['id' => (int)$row['id'], 'name' => $row['name'], 'slug' => $row['slug'], 'score' => 0, 'usage_count' => 0];
                }
                $name = strtolower($row['name']);
                if ($q !== '') {
                    if ($name === strtolower($q)) { $map[$key]['score'] += 50; }
                    elseif (strpos($name, strtolower($q)) === 0) { $map[$key]['score'] += 25; }
                    elseif (strpos($name, strtolower($q)) !== false) { $map[$key]['score'] += 10; }
                }
            }
            foreach ($ctxRows as $row) {
                $key = (string)$row['id'];
                if (!isset($map[$key])) {
                    $map[$key] = ['id' => (int)$row['id'], 'name' => $row['name'], 'slug' => $row['slug'], 'score' => 0, 'usage_count' => (int)($row['usage_count'] ?? 0)];
                } else {
                    $map[$key]['usage_count'] = (int)($row['usage_count'] ?? 0);
                }
                $map[$key]['score'] += (int)($row['usage_count'] ?? 0);
            }
            
            if (empty($map) && $q === '' && $category === '' && $title === '') {
                $popular = $this->db->fetchAll(
                    "SELECT s.id, s.name, s.slug, COUNT(js.skill_id) AS usage_count
                     FROM job_skills js
                     INNER JOIN skills s ON s.id = js.skill_id
                     INNER JOIN jobs j ON j.id = js.job_id
                     WHERE j.status = 'published'
                     GROUP BY s.id, s.name, s.slug
                     ORDER BY usage_count DESC, s.name ASC
                     LIMIT " . (int)$limit
                );
                foreach ($popular as $row) {
                    $suggestions[] = ['id' => (int)$row['id'], 'name' => $row['name'], 'slug' => $row['slug'], 'usage_count' => (int)$row['usage_count']];
                }
            } else {
                usort($map, function($a, $b) {
                    if ($a['score'] !== $b['score']) return $b['score'] - $a['score'];
                    if ($a['usage_count'] !== $b['usage_count']) return $b['usage_count'] - $a['usage_count'];
                    return strcmp($a['name'], $b['name']);
                });
                $map = array_slice($map, 0, $limit);
                foreach ($map as $row) {
                    $suggestions[] = ['id' => (int)$row['id'], 'name' => $row['name'], 'slug' => $row['slug'], 'usage_count' => (int)$row['usage_count']];
                }
            }
            
            return ['suggestions' => $suggestions];
        } catch (\Throwable $t) {
            error_log("SkillService suggest error: " . $t->getMessage());
            return ['suggestions' => []];
        }
    }
}
