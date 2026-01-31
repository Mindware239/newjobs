<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Core\Database;

class Company
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getFeaturedCountsByIndustries(array $industries): array
    {
        $this->ensureFeaturedSchema();
        $counts = [];
        foreach ($industries as $key => $label) {
            try {
                $row = $this->db->fetchOne(
                    "SELECT COUNT(*) AS c FROM companies WHERE is_featured = 1 AND industry LIKE :ind",
                    ['ind' => '%' . $label . '%']
                );
                $counts[$key] = (int)($row['c'] ?? 0);
            } catch (\Exception $e) {
                $counts[$key] = 0;
            }
        }
        return $counts;
    }

    // ---------------- FEATURED ----------------
    public function ensureFeaturedSchema(): void
    {
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM companies");
            $names = array_map(fn($c) => strtolower((string)($c['Field'] ?? '')), $cols ?? []);
            $hasFeatured = in_array('is_featured', $names, true);
            $hasOrder = in_array('featured_order', $names, true);
            if (!$hasFeatured) {
                $this->db->execute("ALTER TABLE companies ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!$hasOrder) {
                $this->db->execute("ALTER TABLE companies ADD COLUMN featured_order INT NOT NULL DEFAULT 0");
            }
        } catch (\Exception $e) {
            error_log('ensureFeaturedSchema failed: ' . $e->getMessage());
        }
    }

    public function setFeatured(int $companyId, bool $isFeatured, int $order = 0): bool
    {
        $this->ensureFeaturedSchema();
        try {
            $this->db->execute(
                "UPDATE companies SET is_featured = :f, featured_order = :o WHERE id = :id",
                ['f' => $isFeatured ? 1 : 0, 'o' => $order, 'id' => $companyId]
            );
            return true;
        } catch (\Exception $e) {
            error_log('setFeatured failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getFeaturedCompanies(int $limit = 12): array
    {
        $this->ensureFeaturedSchema();
        try {
            return $this->db->fetchAll(
                "SELECT id, name, slug, logo_url, employer_id, industry, founded_year, headquarters, company_size 
                 FROM companies 
                 WHERE is_featured = 1 
                 ORDER BY featured_order ASC, name ASC 
                 LIMIT " . (int)$limit
            ) ?: [];
        } catch (\Exception $e) {
            error_log('getFeaturedCompanies failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getFeaturedCompaniesFiltered(array $filters = [], int $limit = 48, int $offset = 0): array
    {
        $this->ensureFeaturedSchema();
        $where = ["is_featured = 1"];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = "name LIKE :q";
            $params['q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['industry'])) {
            $where[] = "industry LIKE :industry";
            $params['industry'] = '%' . $filters['industry'] . '%';
        }
        if (!empty($filters['year_from'])) {
            $where[] = "founded_year >= :yf";
            $params['yf'] = (int)$filters['year_from'];
        }
        if (!empty($filters['year_to'])) {
            $where[] = "founded_year <= :yt";
            $params['yt'] = (int)$filters['year_to'];
        }
        if (!empty($filters['location'])) {
            $where[] = "EXISTS (
                SELECT 1 
                FROM jobs j 
                WHERE j.employer_id = companies.employer_id 
                  AND j.status = 'published'
                  AND (j.locations LIKE :loc)
            )";
            $params['loc'] = '%' . $filters['location'] . '%';
        }
        if (!empty($filters['department'])) {
            $where[] = "EXISTS (
                SELECT 1 
                FROM jobs j
                WHERE j.employer_id = companies.employer_id 
                  AND j.status = 'published'
                  AND (j.category LIKE :dept OR j.title LIKE :dept)
            )";
            $params['dept'] = '%' . $filters['department'] . '%';
        }
        if (!empty($filters['experience'])) {
            if ($filters['experience'] === 'entry') {
                $where[] = "EXISTS (
                    SELECT 1 FROM jobs j
                    WHERE j.employer_id = companies.employer_id 
                      AND j.status = 'published'
                      AND (j.min_experience IS NULL OR j.min_experience <= 1)
                )";
            } elseif ($filters['experience'] === 'experienced') {
                $where[] = "EXISTS (
                    SELECT 1 FROM jobs j
                    WHERE j.employer_id = companies.employer_id 
                      AND j.status = 'published'
                      AND (j.min_experience >= 2 OR j.max_experience >= 2)
                )";
            }
        }
        $sql = "SELECT id, name, slug, logo_url, employer_id, industry, founded_year, headquarters, company_size
                FROM companies
                WHERE " . implode(' AND ', $where) . "
                ORDER BY featured_order ASC, name ASC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        try {
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (\Exception $e) {
            error_log('getFeaturedCompaniesFiltered failed: ' . $e->getMessage());
            return [];
        }
    }

    // ---------------- PUBLIC PROFILE ----------------

    public function findBySlug($slug)
    {
        return $this->db->fetchOne("SELECT * FROM companies WHERE slug = ?", [$slug]);
    }

    public function getStats($companyId)
    {
        try {
            $stats = $this->db->fetchOne("SELECT * FROM company_stats WHERE company_id = ?", [$companyId]) ?: [];
            if (empty($stats) || (!isset($stats['rating']) && !isset($stats['reviews_count']))) {
                $fallback = $this->db->fetchOne(
                    "SELECT COALESCE(AVG(rating), 0) AS rating, COUNT(*) AS reviews_count 
                     FROM reviews 
                     WHERE company_id = ? AND (status = 'approved' OR status IS NULL)",
                    [$companyId]
                ) ?: [];
                if (isset($fallback['rating'])) {
                    $fallback['rating'] = round((float)$fallback['rating'], 1);
                }
                $stats = array_merge(['rating' => 0, 'reviews_count' => 0], $stats, $fallback);
            }
            return $stats;
        } catch (\Exception $e) {
            // Table doesn't exist or query failed - return empty array
            error_log('Company stats table not found or query failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getOpenJobs($companyId, $limit = 10, $offset = 0)
    {
        $sql = "SELECT * FROM jobs WHERE company_id = ? LIMIT ?, ?";
        return $this->db->fetchAll($sql, [$companyId, $offset, $limit]);
    }

    // ---------------- DASHBOARD ----------------

    public function findByEmployerId($employerId)
    {
        return $this->db->fetchOne("SELECT * FROM companies WHERE employer_id = ?", [$employerId]);
    }

    public function updateCompany($companyId, $data)
    {
        $fields = [
            'short_name', 'name', 'website', 'headquarters', 'founded_year',
            'company_size', 'revenue', 'logo_url', 'banner_url', 'description',
            'ceo_name', 'ceo_photo'
        ];

        $setClauses = [];
        $values = [];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $setClauses[] = "`$field` = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        $values[] = $companyId;
        $sql = "UPDATE companies SET " . implode(', ', $setClauses) . " WHERE id = ?";
        
        try {
            $this->db->execute($sql, $values);
            return true;
        } catch (\Exception $e) {
            error_log('Company update failed: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------- STATS ----------------

    public function updateCompanyStats($companyId, $data)
    {
        try {
            $fields = [
                'rating', 'reviews_count', 'salaries_count', 'interviews_count'
            ];

            $existingStats = $this->getStats($companyId);

            if ($existingStats && !empty($existingStats)) {
                // UPDATE
                $setClauses = [];
                $values = [];

                foreach ($fields as $field) {
                    if (isset($data[$field])) {
                        $setClauses[] = "`$field` = ?";
                        $values[] = $data[$field];
                    }
                }

                if (empty($setClauses)) {
                    return true;
                }

                $values[] = $companyId;
                $sql = "UPDATE company_stats SET " . implode(', ', $setClauses) . " WHERE company_id = ?";
                
                try {
                    $this->db->execute($sql, $values);
                    return true;
                } catch (\Exception $e) {
                    error_log('Company stats update failed: ' . $e->getMessage());
                    return false;
                }

            } else {
                // INSERT
                $insertFields = [];
                $placeholders = [];
                $values = [];

                foreach ($fields as $field) {
                    if (isset($data[$field])) {
                        $insertFields[] = "`$field`";
                        $placeholders[] = "?";
                        $values[] = $data[$field];
                    }
                }

                $insertFields[] = "`company_id`";
                $placeholders[] = "?";
                $values[] = $companyId;

                $sql = "INSERT INTO company_stats (" . implode(', ', $insertFields) . ")
                        VALUES (" . implode(', ', $placeholders) . ")";

                try {
                    $this->db->execute($sql, $values);
                    return true;
                } catch (\Exception $e) {
                    error_log('Company stats insert failed: ' . $e->getMessage());
                    return false;
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist - silently fail
            error_log('Company stats table not available: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------- REVIEW AGGREGATION ----------------

    public function recalculateCompanyStats($companyId)
    {
        try {
            $sql = "
                SELECT COUNT(id) as total_reviews,
                       COALESCE(AVG(rating), 0) as average_rating
                FROM reviews
                WHERE company_id = ?
            ";

            $results = $this->db->fetchOne($sql, [$companyId]);

            if (!$results) {
                return false;
            }

            $statsData = [
                'reviews_count' => (int)($results['total_reviews'] ?? 0),
                'rating' => round((float)($results['average_rating'] ?? 0), 1)
            ];

            return $this->updateCompanyStats($companyId, $statsData);
        } catch (\Exception $e) {
            // Reviews table doesn't exist or other error - just log and return false
            error_log('Recalculate company stats failed: ' . $e->getMessage());
            return false;
        }
    }
}
