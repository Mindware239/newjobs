<?php
namespace App\Repositories;

use App\Core\Database;

class JobRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch recently published jobs with employer and location info
     * Optimized to avoid N+1 queries by using GROUP_CONCAT for locations
     */
    public function getRecentPublishedJobs(int $limit = 6): array
    {
        $limit = (int) $limit;
        $sql = "
            SELECT j.*, e.company_name, e.logo_url as company_logo, e.industry, j.slug,
                GROUP_CONCAT(DISTINCT CONCAT_WS(', ', c.name, s.name, cnt.name) SEPARATOR ' | ') as location_names
            FROM jobs j
            LEFT JOIN employers e ON j.employer_id = e.id
            LEFT JOIN job_locations jl ON jl.job_id = j.id
            LEFT JOIN cities c ON jl.city_id = c.id
            LEFT JOIN states s ON jl.state_id = s.id
            LEFT JOIN countries cnt ON jl.country_id = cnt.id
            WHERE j.status = 'published'
            GROUP BY j.id
            ORDER BY j.created_at DESC
            LIMIT {$limit}
        ";
        
        return $this->db->fetchAll($sql);
    }

    public function getCategoriesWithJobCount(int $limit = 10): array
    {
        $limit = (int) $limit;
        $sql = "SELECT 
                jc.id, jc.name, jc.slug, jc.image, COUNT(DISTINCT j.id) as count
            FROM job_categories jc
            LEFT JOIN jobs j ON j.category = jc.name AND j.status = 'published'
            WHERE jc.is_active = 1
            GROUP BY jc.id, jc.name, jc.slug, jc.image
            HAVING count > 0
            ORDER BY jc.sort_order ASC, count DESC
            LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }

    public function getJobStats(): array
    {
        try {
            $jobs = (int)($this->db->fetchOne("SELECT COUNT(*) as total FROM jobs WHERE status = 'published'")['total'] ?? 0);
            $candidates = (int)($this->db->fetchOne("SELECT COUNT(*) as total FROM candidates")['total'] ?? 0);
            $companies = (int)($this->db->fetchOne("SELECT COUNT(*) as total FROM employers WHERE verified = 1")['total'] ?? 0);
            
            return [
                'jobs' => $jobs,
                'candidates' => $candidates,
                'companies' => $companies
            ];
        } catch (\Exception $e) {
            error_log("Error fetching stats: " . $e->getMessage());
            return ['jobs' => 25850, 'candidates' => 10250, 'companies' => 18400]; // fallback defaults
        }
    }

    public function getLocationBySlug(string $slug, string $slugCanonical): ?array
    {
        // Try City
        $location = $this->db->fetchOne("
            SELECT c.id, c.name, 'city' as type 
            FROM cities c 
            WHERE c.slug = :slug OR c.slug = :slug_canonical OR c.name LIKE :name_like
        ", ['slug' => $slug, 'slug_canonical' => $slugCanonical, 'name_like' => str_replace('-', ' ', $slug)]);

        if (!$location) {
            $location = $this->db->fetchOne("SELECT id, name, 'state' as type FROM states WHERE slug = :slug OR name LIKE :name_like", ['slug' => $slug, 'name_like' => str_replace('-', ' ', $slug)]);
        }
        if (!$location) {
            $location = $this->db->fetchOne("SELECT id, name, 'country' as type FROM countries WHERE slug = :slug OR name LIKE :name_like", ['slug' => $slug, 'name_like' => str_replace('-', ' ', $slug)]);
        }

        return $location ?: null;
    }

    public function getCityFullDetails(int $cityId): ?array
    {
        return $this->db->fetchOne("
            SELECT c.name as city_name, c.slug as city_slug,
                   s.name as state_name, s.slug as state_slug,
                   cnt.name as country_name, cnt.slug as country_slug
            FROM cities c
            LEFT JOIN states s ON c.state_id = s.id
            LEFT JOIN countries cnt ON s.country_id = cnt.id
            WHERE c.id = :id
        ", ['id' => $cityId]) ?: null;
    }

    public function getStateFullDetails(int $stateId): ?array
    {
        return $this->db->fetchOne("
            SELECT s.name as state_name, s.slug as state_slug,
                   cnt.name as country_name, cnt.slug as country_slug
            FROM states s
            LEFT JOIN countries cnt ON s.country_id = cnt.id
            WHERE s.id = :id
        ", ['id' => $stateId]) ?: null;
    }

    public function countJobsByLocation(string $locationType, int $locationId): int
    {
        $whereClause = $this->getLocationWhereClause($locationType);
        return (int)($this->db->fetchOne(
            "SELECT COUNT(DISTINCT j.id) as cnt FROM job_locations jl 
             JOIN jobs j ON j.id = jl.job_id 
             WHERE {$whereClause} AND j.status = 'published'",
            ['loc_id' => $locationId]
        )['cnt'] ?? 0);
    }

    public function getTopTitlesByLocation(string $locationType, int $locationId, int $limit = 5): array
    {
        $whereClause = $this->getLocationWhereClause($locationType);
        $rows = $this->db->fetchAll(
            "SELECT j.title, COUNT(*) as cnt 
             FROM jobs j 
             JOIN job_locations jl ON j.id = jl.job_id
             WHERE {$whereClause} AND j.status = 'published'
             GROUP BY j.title
             ORDER BY cnt DESC
             LIMIT {$limit}",
            ['loc_id' => $locationId]
        );
        return array_values(array_filter(array_map(fn($r) => $r['title'] ?? '', $rows)));
    }

    public function getJobsByLocation(string $locationType, int $locationId, int $page = 1, int $perPage = 20): array
    {
        $whereClause = $this->getLocationWhereClause($locationType);
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT j.*, e.company_name, e.logo_url as company_logo,
                    GROUP_CONCAT(DISTINCT CONCAT_WS(', ', c.name, s.name, cnt.name) SEPARATOR ' | ') as location_names
                FROM jobs j 
                JOIN job_locations jl ON j.id = jl.job_id 
                JOIN employers e ON j.employer_id = e.id
                LEFT JOIN job_locations jl_all ON jl_all.job_id = j.id
                LEFT JOIN cities c ON jl_all.city_id = c.id
                LEFT JOIN states s ON jl_all.state_id = s.id
                LEFT JOIN countries cnt ON jl_all.country_id = cnt.id
                WHERE {$whereClause} AND j.status = 'published'
                GROUP BY j.id
                ORDER BY j.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return $this->db->fetchAll($sql, ['loc_id' => $locationId]);
    }

    public function getSkillBySlug(string $slug): ?array
    {
        return $this->db->fetchOne("SELECT * FROM skills WHERE slug = :slug", ['slug' => $slug]) ?: null;
    }

    public function countJobsByRoleAndLocation(string $locationType, int $locationId, ?array $skill, string $roleName): int
    {
        $where = ["j.status = 'published'"];
        $params = [];

        $where[] = $this->getLocationWhereClause($locationType);
        $params['loc_id'] = $locationId;

        $join = "JOIN job_locations jl ON j.id = jl.job_id";
        
        if ($skill) {
            $join .= " JOIN job_skills js ON j.id = js.job_id";
            $where[] = "js.skill_id = :skill_id";
            $params['skill_id'] = $skill['id'];
        } else {
            $where[] = "(j.title LIKE :role OR j.description LIKE :role)";
            $params['role'] = '%' . $roleName . '%';
        }

        $sql = "SELECT COUNT(DISTINCT j.id) as cnt FROM jobs j {$join} WHERE " . implode(' AND ', $where);
        return (int)($this->db->fetchOne($sql, $params)['cnt'] ?? 0);
    }

    public function getJobsByRoleAndLocation(string $locationType, int $locationId, ?array $skill, string $roleName, int $page = 1, int $perPage = 20): array
    {
        $where = ["j.status = 'published'"];
        $params = [];

        $where[] = $this->getLocationWhereClause($locationType);
        $params['loc_id'] = $locationId;

        $join = "JOIN job_locations jl ON j.id = jl.job_id JOIN employers e ON j.employer_id = e.id";
        
        if ($skill) {
            $join .= " JOIN job_skills js ON j.id = js.job_id";
            $where[] = "js.skill_id = :skill_id";
            $params['skill_id'] = $skill['id'];
        } else {
            $where[] = "(j.title LIKE :role OR j.description LIKE :role)";
            $params['role'] = '%' . $roleName . '%';
        }

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT j.*, e.company_name, e.logo_url as company_logo,
                    GROUP_CONCAT(DISTINCT CONCAT_WS(', ', c.name, s.name, cnt.name) SEPARATOR ' | ') as location_names
                FROM jobs j 
                {$join}
                LEFT JOIN job_locations jl_all ON jl_all.job_id = j.id
                LEFT JOIN cities c ON jl_all.city_id = c.id
                LEFT JOIN states s ON jl_all.state_id = s.id
                LEFT JOIN countries cnt ON jl_all.country_id = cnt.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY j.id
                ORDER BY j.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return $this->db->fetchAll($sql, $params);
    }

    public function getCategoryBySlugOrName(string $slug): ?array
    {
        $category = $this->db->fetchOne("SELECT name, slug FROM job_categories WHERE slug = :slug", ['slug' => $slug]);
        if (!$category) {
            $category = $this->db->fetchOne("SELECT name, slug FROM job_categories WHERE name LIKE :name_like", ['name_like' => str_replace('-', ' ', $slug)]);
        }
        return $category ?: null;
    }

    public function countJobsByCategory(string $categoryName): int
    {
        return (int)($this->db->fetchOne(
            "SELECT COUNT(DISTINCT j.id) as cnt FROM jobs j WHERE j.status = 'published' AND j.category = :cat", 
            ['cat' => $categoryName]
        )['cnt'] ?? 0);
    }

    public function getJobsByCategory(string $categoryName, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT j.*, e.company_name, e.logo_url as company_logo,
                    GROUP_CONCAT(DISTINCT CONCAT_WS(', ', c.name, s.name, cnt.name) SEPARATOR ' | ') as location_names
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                LEFT JOIN job_locations jl_all ON jl_all.job_id = j.id
                LEFT JOIN cities c ON jl_all.city_id = c.id
                LEFT JOIN states s ON jl_all.state_id = s.id
                LEFT JOIN countries cnt ON jl_all.country_id = cnt.id
                WHERE j.status = 'published' AND j.category = :cat
                GROUP BY j.id
                ORDER BY j.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return $this->db->fetchAll($sql, ['cat' => $categoryName]);
    }

    public function getAllCategoriesWithCounts(): array
    {
        $sql = "SELECT 
                jc.name,
                jc.slug,
                COUNT(DISTINCT j.id) as count
            FROM job_categories jc
            LEFT JOIN jobs j ON j.category = jc.name AND j.status = 'published'
            WHERE jc.is_active = 1
            GROUP BY jc.id, jc.name, jc.slug
            ORDER BY jc.name ASC";
        return $this->db->fetchAll($sql);
    }

    private function getLocationWhereClause(string $type): string
    {
        if ($type === 'city') return "jl.city_id = :loc_id";
        if ($type === 'state') return "jl.state_id = :loc_id";
        return "jl.country_id = :loc_id";
    }
}