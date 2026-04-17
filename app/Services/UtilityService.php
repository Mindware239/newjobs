<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class UtilityService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getFcmWebConfig(): array
    {
        return [
            'apiKey' => $_ENV['FCM_WEB_API_KEY'] ?? '',
            'projectId' => $_ENV['FCM_WEB_PROJECT_ID'] ?? '',
            'messagingSenderId' => $_ENV['FCM_WEB_MESSAGING_SENDER_ID'] ?? '',
            'appId' => $_ENV['FCM_WEB_APP_ID'] ?? '',
            'vapidKey' => $_ENV['FCM_VAPID_KEY'] ?? ($_ENV['FCM_WEB_VAPID_KEY'] ?? '')
        ];
    }

    public function searchJobTitles(string $query = '', int $limit = 10): array
    {
        if (empty($query) || strlen($query) < 2) {
            return ['suggestions' => []];
        }

        $limit = max(1, min(50, $limit));
        
        try {
            $sql = "SELECT id, title, slug, category FROM job_titles 
                    WHERE is_active = 1 AND LOWER(title) LIKE LOWER(:query)
                    ORDER BY 
                        CASE 
                            WHEN LOWER(title) = LOWER(:exact) THEN 1
                            WHEN LOWER(title) LIKE LOWER(:start) THEN 2
                            ELSE 3
                        END,
                        usage_count DESC,
                        title ASC
                    LIMIT " . (int)$limit;
            
            $results = $this->db->fetchAll($sql, [
                'query' => '%' . $query . '%',
                'exact' => $query,
                'start' => $query . '%'
            ]);

            $suggestions = array_map(function($row) {
                return [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'category' => $row['category'] ?? ''
                ];
            }, $results);

            return ['suggestions' => $suggestions];
        } catch (\Throwable $t) {
            error_log("UtilityService searchJobTitles error: " . $t->getMessage());
            return ['suggestions' => []];
        }
    }

    public function searchLocations(string $query = '', int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return ['suggestions' => []];
        }

        $limit = max(1, min(20, $limit));

        try {
            $sql = "
                SELECT 
                    c.id            AS city_id,
                    c.name          AS city,
                    s.name          AS state,
                    co.name         AS country,
                    COUNT(DISTINCT jl.job_id) AS job_count
                FROM job_locations jl
                INNER JOIN cities c     ON c.id = jl.city_id
                LEFT JOIN states s      ON s.id = c.state_id
                LEFT JOIN countries co  ON co.id = s.country_id
                WHERE LOWER(c.name) LIKE :search
                GROUP BY c.id, c.name, s.name, co.name
                ORDER BY
                    CASE
                        WHEN LOWER(c.name) = :exact THEN 1
                        WHEN LOWER(c.name) LIKE :starts THEN 2
                        ELSE 3
                    END,
                    job_count DESC,
                    c.name ASC
                LIMIT {$limit}
            ";

            $params = [
                'search' => '%' . strtolower($query) . '%',
                'exact'  => strtolower($query),
                'starts' => strtolower($query) . '%'
            ];

            $rows = $this->db->fetchAll($sql, $params);

            $suggestions = array_map(function ($row) {
                $display = $row['city'];

                if (!empty($row['state'])) {
                    $display .= ', ' . $row['state'];
                }
                if (!empty($row['country']) && $row['country'] !== 'India') {
                    $display .= ', ' . $row['country'];
                }

                return [
                    'city_id'   => (int)$row['city_id'],
                    'city'      => $row['city'],
                    'state'     => $row['state'] ?? '',
                    'country'   => $row['country'] ?? 'India',
                    'display'   => $display,
                    'job_count' => (int)$row['job_count'],
                    'slug'      => strtolower(str_replace(' ', '-', $row['city']))
                ];
            }, $rows);

            return ['suggestions' => $suggestions];
        } catch (\Throwable $e) {
            error_log('[UtilityService searchLocations ERROR] ' . $e->getMessage());
            return ['suggestions' => []];
        }
    }

    public function getAllLocations(): array
    {
        try {
            $sql = "SELECT 
                        COALESCE(c.name, jl.city) AS city, 
                        COALESCE(s.name, jl.state) AS state, 
                        COALESCE(cnt.name, jl.country) AS country,
                        COUNT(*) AS job_count
                    FROM job_locations jl
                    LEFT JOIN cities c ON jl.city_id = c.id
                    LEFT JOIN states s ON jl.state_id = s.id
                    LEFT JOIN countries cnt ON jl.country_id = cnt.id
                    WHERE (COALESCE(c.name, jl.city) IS NOT NULL AND COALESCE(c.name, jl.city) != '')
                       OR (COALESCE(s.name, jl.state) IS NOT NULL AND COALESCE(s.name, jl.state) != '')
                       OR (COALESCE(cnt.name, jl.country) IS NOT NULL AND COALESCE(cnt.name, jl.country) != '')
                    GROUP BY COALESCE(c.name, jl.city), COALESCE(s.name, jl.state), COALESCE(cnt.name, jl.country)
                    ORDER BY job_count DESC, city ASC, state ASC";
            
            $results = $this->db->fetchAll($sql, []);
            
            $locations = array_map(function($row) {
                $display = trim($row['city'] ?? '');
                if (!empty($row['state'])) {
                    $display .= ', ' . trim($row['state']);
                }
                if (!empty($row['country'])) {
                    $display .= ', ' . trim($row['country']);
                }
                $value = trim($row['city'] ?? '');
                if ($value === '') {
                    $value = trim($row['state'] ?? '');
                }
                if ($value === '') {
                    $value = trim($row['country'] ?? '');
                }
                return [
                    'label' => $display,
                    'value' => $value,
                    'count' => (int)($row['job_count'] ?? 0)
                ];
            }, $results);

            return ['locations' => $locations];
        } catch (\Throwable $e) {
            error_log("UtilityService getAllLocations error: " . $e->getMessage());
            return ['locations' => []];
        }
    }

    public function getIndustries(int $limit = 0): array
    {
        try {
            $categoriesSql = "SELECT id, name, slug, sort_order FROM job_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
            $categories = $this->db->fetchAll($categoriesSql, []);
            
            $data = [];
            foreach ($categories as $category) {
                $countSql = "SELECT COUNT(DISTINCT j.id) as job_count FROM jobs j WHERE j.category = ? AND j.status = 'published'";
                $countResult = $this->db->fetchOne($countSql, [$category['name']]);
                
                $data[] = [
                    'id' => (int)$category['id'],
                    'label' => $category['name'],
                    'value' => $category['name'],
                    'slug' => $category['slug'],
                    'count' => (int)($countResult['job_count'] ?? 0)
                ];
            }
            
            usort($data, fn($a, $b) => $b['count'] - $a['count']);
            if ($limit > 0) {
                $data = array_slice($data, 0, $limit);
            }
            
            return ['industries' => $data];
        } catch (\Throwable $t) {
            error_log("UtilityService getIndustries error: " . $t->getMessage());
            return ['industries' => []];
        }
    }
}
