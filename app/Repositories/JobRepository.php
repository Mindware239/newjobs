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

    private function getExternalJobsFilter(): string
    {
        $hideExternal = $this->db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'hide_external_jobs'")['setting_value'] ?? '0';
        return $hideExternal === '1' ? " AND j.job_type != 'external' " : "";
    }

    /**
     * Fetch recently published jobs with employer and location info
     * Optimized to avoid N+1 queries by using GROUP_CONCAT for locations
     */
    public function getRecentPublishedJobs(int $limit = 6): array
    {
        $limit = (int) $limit;
        $hideExternal = $this->db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'hide_external_jobs'")['setting_value'] ?? '0';
        $externalFilter = $hideExternal === '1' ? "AND j.job_type != 'external'" : "";

        $sql = "
            SELECT j.*, COALESCE(j.company_name, e.company_name) as company_name, 
                COALESCE(NULLIF(j.company_logo, ''), c.logo_url, e.logo_url) as company_logo, 
                c.banner_url as company_banner,
                e.industry, j.slug, jc.image as category_image,
                GROUP_CONCAT(
                    DISTINCT CONCAT_WS(
                        ', ',
                        COALESCE(ct.name, NULLIF(jl.city, '')),
                        COALESCE(st.name, NULLIF(jl.state, '')),
                        COALESCE(cnt.name, NULLIF(jl.country, ''))
                    ) SEPARATOR ' | '
                ) as location_names
            FROM jobs j
            LEFT JOIN employers e ON j.employer_id = e.id
            LEFT JOIN companies c ON e.id = c.employer_id
            LEFT JOIN job_categories jc ON j.category = jc.name
            LEFT JOIN job_locations jl ON jl.job_id = j.id
            LEFT JOIN cities ct ON jl.city_id = ct.id
            LEFT JOIN states st ON jl.state_id = st.id
            LEFT JOIN countries cnt ON jl.country_id = cnt.id
            WHERE j.status = 'published' {$externalFilter}
            GROUP BY j.id
            ORDER BY j.created_at DESC
            LIMIT {$limit}
        ";
        
        $results = $this->db->fetchAll($sql);
        return array_map([$this, 'formatJobLocation'], $results);
    }

    private function formatJobLocation(array $job): array
    {
        $locationDisplay = $job['location_names'] ?? '';
        if (empty($locationDisplay) && !empty($job['locations'])) {
            $locs = json_decode($job['locations'], true);
            if (is_array($locs) && !empty($locs)) {
                $parts = array_filter([
                    $locs[0]['city'] ?? '',
                    $locs[0]['state'] ?? '',
                    $locs[0]['country'] ?? ''
                ]);
                $locationDisplay = implode(', ', $parts);
            }
        }
        $job['location_display'] = $locationDisplay ?: 'Location not specified';
        return $job;
    }

    public function getCategoriesWithJobCount(int $limit = 10): array
    {
        $limit = (int) $limit;
        $sql = "SELECT 
                jc.id, jc.name, jc.slug, 
                COALESCE(
                    (SELECT c2.banner_url FROM jobs j2 
                     JOIN employers e2 ON j2.employer_id = e2.id 
                     JOIN companies c2 ON e2.id = c2.employer_id 
                     WHERE j2.category = jc.name AND j2.status = 'published' AND c2.banner_url IS NOT NULL AND c2.banner_url != ''
                     ORDER BY c2.is_featured DESC, j2.created_at DESC LIMIT 1),
                    jc.image
                ) as image, 
                COUNT(DISTINCT j.id) as count
            FROM job_categories jc
            LEFT JOIN jobs j ON j.category = jc.name AND j.status = 'published'
            WHERE jc.is_active = 1
            GROUP BY jc.id, jc.name, jc.slug, jc.image
            HAVING count > 0
            ORDER BY jc.sort_order ASC, count DESC
            LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get all active category names for the typewriter effect
     */
    public function getAllActiveCategoryNames(): array
    {
        $sql = "SELECT name FROM job_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        $results = $this->db->fetchAll($sql);
        return array_map(fn($r) => $r['name'], $results);
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
        $externalFilter = $this->getExternalJobsFilter();
        return (int)($this->db->fetchOne(
            "SELECT COUNT(DISTINCT j.id) as cnt FROM job_locations jl 
             JOIN jobs j ON j.id = jl.job_id 
             WHERE {$whereClause} AND j.status = 'published' {$externalFilter}",
            ['loc_id' => $locationId]
        )['cnt'] ?? 0);
    }

    public function getTopTitlesByLocation(string $locationType, int $locationId, int $limit = 5): array
    {
        $whereClause = $this->getLocationWhereClause($locationType);
        $externalFilter = $this->getExternalJobsFilter();
        $rows = $this->db->fetchAll(
            "SELECT j.title, COUNT(*) as cnt 
             FROM jobs j 
             JOIN job_locations jl ON j.id = jl.job_id
             WHERE {$whereClause} AND j.status = 'published' {$externalFilter}
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
        $externalFilter = $this->getExternalJobsFilter();
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT j.*, COALESCE(j.company_name, e.company_name) as company_name, 
                    COALESCE(NULLIF(j.company_logo, ''), e.logo_url) as company_logo,
                    GROUP_CONCAT(
                    DISTINCT CONCAT_WS(
                        ', ',
                        COALESCE(c.name, NULLIF(jl_all.city, '')),
                        COALESCE(s.name, NULLIF(jl_all.state, '')),
                        COALESCE(cnt.name, NULLIF(jl_all.country, ''))
                    ) SEPARATOR ' | '
                ) as location_names
                FROM jobs j 
                JOIN job_locations jl ON j.id = jl.job_id 
                LEFT JOIN employers e ON j.employer_id = e.id
                LEFT JOIN job_locations jl_all ON jl_all.job_id = j.id
                LEFT JOIN cities c ON jl_all.city_id = c.id
                LEFT JOIN states s ON jl_all.state_id = s.id
                LEFT JOIN countries cnt ON jl_all.country_id = cnt.id
                WHERE {$whereClause} AND j.status = 'published' {$externalFilter}
                GROUP BY j.id
                ORDER BY j.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return array_map([$this, 'formatJobLocation'], $this->db->fetchAll($sql, ['loc_id' => $locationId]));
    }

    public function getSkillBySlug(string $slug): ?array
    {
        return $this->db->fetchOne("SELECT * FROM skills WHERE slug = :slug", ['slug' => $slug]) ?: null;
    }

    public function countJobsByRoleAndLocation(string $locationType, int $locationId, ?array $skill, string $roleName): int
    {
        $where = ["j.status = 'published'"];
        $where[] = $this->getExternalJobsFilter();
        $params = [];

        $where[] = $this->getLocationWhereClause($locationType);
        $params['loc_id'] = $locationId;

        $join = "JOIN job_locations jl ON j.id = jl.job_id";
        
        if ($skill) {
            $join .= " JOIN job_skills js ON j.id = js.job_id";
            $where[] = "js.skill_id = :skill_id";
            $params['skill_id'] = $skill['id'];
        } else {
            $where[] = "(j.title LIKE :role_title OR j.description LIKE :role_description)";
            $roleLike = '%' . $roleName . '%';
            $params['role_title'] = $roleLike;
            $params['role_description'] = $roleLike;
        }

        $sql = "SELECT COUNT(DISTINCT j.id) as cnt FROM jobs j {$join} WHERE " . implode(' AND ', array_filter($where));
        return (int)($this->db->fetchOne($sql, $params)['cnt'] ?? 0);
    }

    public function getJobsByRoleAndLocation(string $locationType, int $locationId, ?array $skill, string $roleName, int $page = 1, int $perPage = 20): array
    {
        $where = ["j.status = 'published'"];
        $where[] = $this->getExternalJobsFilter();
        $params = [];

        $where[] = $this->getLocationWhereClause($locationType);
        $params['loc_id'] = $locationId;

        $join = "JOIN job_locations jl ON j.id = jl.job_id LEFT JOIN employers e ON j.employer_id = e.id";
        
        if ($skill) {
            $join .= " JOIN job_skills js ON j.id = js.job_id";
            $where[] = "js.skill_id = :skill_id";
            $params['skill_id'] = $skill['id'];
        } else {
            $where[] = "(j.title LIKE :role_title OR j.description LIKE :role_description)";
            $roleLike = '%' . $roleName . '%';
            $params['role_title'] = $roleLike;
            $params['role_description'] = $roleLike;
        }

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT j.*, COALESCE(j.company_name, e.company_name) as company_name, 
                    COALESCE(NULLIF(j.company_logo, ''), e.logo_url) as company_logo,
                    GROUP_CONCAT(
                    DISTINCT CONCAT_WS(
                        ', ',
                        COALESCE(c.name, NULLIF(jl_all.city, '')),
                        COALESCE(s.name, NULLIF(jl_all.state, '')),
                        COALESCE(cnt.name, NULLIF(jl_all.country, ''))
                    ) SEPARATOR ' | '
                ) as location_names
                FROM jobs j 
                {$join}
                LEFT JOIN job_locations jl_all ON jl_all.job_id = j.id
                LEFT JOIN cities c ON jl_all.city_id = c.id
                LEFT JOIN states s ON jl_all.state_id = s.id
                LEFT JOIN countries cnt ON jl_all.country_id = cnt.id
                WHERE " . implode(' AND ', array_filter($where)) . "
                GROUP BY j.id
                ORDER BY j.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return array_map([$this, 'formatJobLocation'], $this->db->fetchAll($sql, $params));
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
        $externalFilter = $this->getExternalJobsFilter();
        return (int)($this->db->fetchOne(
            "SELECT COUNT(DISTINCT j.id) as cnt FROM jobs j WHERE j.status = 'published' AND j.category = :cat {$externalFilter}", 
            ['cat' => $categoryName]
        )['cnt'] ?? 0);
    }

    public function getJobsByCategory(string $categoryName, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $externalFilter = $this->getExternalJobsFilter();
        
        $sql = "SELECT j.*, COALESCE(j.company_name, e.company_name) as company_name, 
                    COALESCE(NULLIF(j.company_logo, ''), e.logo_url) as company_logo,
                    GROUP_CONCAT(
                    DISTINCT CONCAT_WS(
                        ', ',
                        COALESCE(c.name, NULLIF(jl_all.city, '')),
                        COALESCE(s.name, NULLIF(jl_all.state, '')),
                        COALESCE(cnt.name, NULLIF(jl_all.country, ''))
                    ) SEPARATOR ' | '
                ) as location_names
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                LEFT JOIN job_locations jl_all ON jl_all.job_id = j.id
                LEFT JOIN cities c ON jl_all.city_id = c.id
                LEFT JOIN states s ON jl_all.state_id = s.id
                LEFT JOIN countries cnt ON jl_all.country_id = cnt.id
                WHERE j.status = 'published' AND j.category = :cat {$externalFilter}
                GROUP BY j.id
                ORDER BY j.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return array_map([$this, 'formatJobLocation'], $this->db->fetchAll($sql, ['cat' => $categoryName]));
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

    public function searchJobs(string $keyword, string $location, int $page = 1, int $perPage = 20): array
    {
        $keyword = trim($keyword);
        $location = trim($location);
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = ["j.status = 'published'"];

        if ($keyword !== '') {
            $keywordLike = '%' . $keyword . '%';
            $keywordTerms = $this->getSearchTerms($keyword);
            $keywordParts = [
                "j.title LIKE :keyword_title",
                "j.description LIKE :keyword_description",
                "j.short_description LIKE :keyword_short_description",
                "j.category LIKE :keyword_category",
                "e.company_name LIKE :keyword_company",
                "j.company_name LIKE :keyword_company_alt",
                "EXISTS (
                    SELECT 1 FROM job_skills js
                    INNER JOIN skills sk ON sk.id = js.skill_id
                    WHERE js.job_id = j.id AND sk.name LIKE :keyword_skill
                )"
            ];
            $params['keyword_title'] = $keywordLike;
            $params['keyword_description'] = $keywordLike;
            $params['keyword_short_description'] = $keywordLike;
            $params['keyword_category'] = $keywordLike;
            $params['keyword_company'] = $keywordLike;
            $params['keyword_company_alt'] = $keywordLike;
            $params['keyword_skill'] = $keywordLike;

            foreach ($keywordTerms as $index => $term) {
                $param = "keyword_term_{$index}";
                $keywordParts[] = "(j.title LIKE :{$param} OR j.category LIKE :{$param}_category)";
                $params[$param] = '%' . $term . '%';
                $params["{$param}_category"] = '%' . $term . '%';
            }

            $where[] = '(' . implode(' OR ', $keywordParts) . ')';
        }

        if ($location !== '') {
            $where[] = "(EXISTS (
                SELECT 1 FROM job_locations jl
                LEFT JOIN cities c ON jl.city_id = c.id
                LEFT JOIN states s ON jl.state_id = s.id
                LEFT JOIN countries cnt ON jl.country_id = cnt.id
                WHERE jl.job_id = j.id
                  AND (
                      c.name LIKE :loc_city_name
                      OR s.name LIKE :loc_state_name
                      OR cnt.name LIKE :loc_country_name
                      OR jl.city LIKE :loc_city
                      OR jl.state LIKE :loc_state
                      OR jl.country LIKE :loc_country
                  )
            ) OR j.job_address LIKE :loc_address OR j.locations LIKE :loc_json)";
            $locationLike = '%' . $location . '%';
            $params['loc_city_name'] = $locationLike;
            $params['loc_state_name'] = $locationLike;
            $params['loc_country_name'] = $locationLike;
            $params['loc_city'] = $locationLike;
            $params['loc_state'] = $locationLike;
            $params['loc_country'] = $locationLike;
            $params['loc_address'] = $locationLike;
            $params['loc_json'] = $locationLike;
        }

        $orderBy = "j.created_at DESC";
        if ($keyword !== '') {
            $params['rank_title_exact'] = $keyword;
            $params['rank_title_starts'] = $keyword . '%';
            $params['rank_title_contains'] = '%' . $keyword . '%';
            $params['rank_skill_exact'] = $keyword;
            $orderBy = "(CASE
                    WHEN LOWER(j.title) = LOWER(:rank_title_exact) THEN 0
                    WHEN j.title LIKE :rank_title_starts THEN 1
                    WHEN j.title LIKE :rank_title_contains THEN 2
                    WHEN EXISTS (
                        SELECT 1 FROM job_skills jsr
                        INNER JOIN skills skr ON skr.id = jsr.skill_id
                        WHERE jsr.job_id = j.id AND LOWER(skr.name) = LOWER(:rank_skill_exact)
                    ) THEN 3
                    ELSE 4
                END) ASC, j.created_at DESC";
        }

        $externalFilter = $this->getExternalJobsFilter();
        $sql = "SELECT j.*, COALESCE(j.company_name, e.company_name) as company_name, 
                    COALESCE(j.company_logo, e.logo_url) as company_logo,
                    GROUP_CONCAT(
                        DISTINCT CONCAT_WS(
                            ', ',
                            COALESCE(NULLIF(c.name, ''), NULLIF(jl_all.city, '')),
                            COALESCE(NULLIF(s.name, ''), NULLIF(jl_all.state, '')),
                            COALESCE(NULLIF(cnt.name, ''), NULLIF(jl_all.country, ''))
                        ) SEPARATOR ' | '
                    ) as location_names
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                LEFT JOIN job_locations jl_all ON jl_all.job_id = j.id
                LEFT JOIN cities c ON jl_all.city_id = c.id
                LEFT JOIN states s ON jl_all.state_id = s.id
                LEFT JOIN countries cnt ON jl_all.country_id = cnt.id
                WHERE " . implode(' AND ', $where) . " {$externalFilter}
                GROUP BY j.id
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";

        return array_map([$this, 'formatJobLocation'], $this->db->fetchAll($sql, $params));
    }

    public function countSearchJobs(string $keyword, string $location): int
    {
        $keyword = trim($keyword);
        $location = trim($location);
        $params = [];
        $where = ["j.status = 'published'"];

        if ($keyword !== '') {
            $keywordLike = '%' . $keyword . '%';
            $keywordTerms = $this->getSearchTerms($keyword);
            $keywordParts = [
                "j.title LIKE :keyword_title",
                "j.description LIKE :keyword_description",
                "j.short_description LIKE :keyword_short_description",
                "j.category LIKE :keyword_category",
                "e.company_name LIKE :keyword_company",
                "j.company_name LIKE :keyword_company_alt",
                "EXISTS (
                    SELECT 1 FROM job_skills js
                    INNER JOIN skills sk ON sk.id = js.skill_id
                    WHERE js.job_id = j.id AND sk.name LIKE :keyword_skill
                )"
            ];
            $params['keyword_title'] = $keywordLike;
            $params['keyword_description'] = $keywordLike;
            $params['keyword_short_description'] = $keywordLike;
            $params['keyword_category'] = $keywordLike;
            $params['keyword_company'] = $keywordLike;
            $params['keyword_company_alt'] = $keywordLike;
            $params['keyword_skill'] = $keywordLike;

            foreach ($keywordTerms as $index => $term) {
                $param = "keyword_term_{$index}";
                $keywordParts[] = "(j.title LIKE :{$param} OR j.category LIKE :{$param}_category)";
                $params[$param] = '%' . $term . '%';
                $params["{$param}_category"] = '%' . $term . '%';
            }

            $where[] = '(' . implode(' OR ', $keywordParts) . ')';
        }

        if ($location !== '') {
            $where[] = "(EXISTS (
                SELECT 1 FROM job_locations jl
                LEFT JOIN cities c ON jl.city_id = c.id
                LEFT JOIN states s ON jl.state_id = s.id
                LEFT JOIN countries cnt ON jl.country_id = cnt.id
                WHERE jl.job_id = j.id
                  AND (
                      c.name LIKE :loc_city_name
                      OR s.name LIKE :loc_state_name
                      OR cnt.name LIKE :loc_country_name
                      OR jl.city LIKE :loc_city
                      OR jl.state LIKE :loc_state
                      OR jl.country LIKE :loc_country
                  )
            ) OR j.job_address LIKE :loc_address OR j.locations LIKE :loc_json)";
            $locationLike = '%' . $location . '%';
            $params['loc_city_name'] = $locationLike;
            $params['loc_state_name'] = $locationLike;
            $params['loc_country_name'] = $locationLike;
            $params['loc_city'] = $locationLike;
            $params['loc_state'] = $locationLike;
            $params['loc_country'] = $locationLike;
            $params['loc_address'] = $locationLike;
            $params['loc_json'] = $locationLike;
        }

        $externalFilter = $this->getExternalJobsFilter();
        $sql = "SELECT COUNT(DISTINCT j.id) as cnt
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                WHERE " . implode(' AND ', $where) . " {$externalFilter}";
        return (int)($this->db->fetchOne($sql, $params)['cnt'] ?? 0);
    }

    public function getJobDetailRowBySlug(string $slug): ?array
    {
        $sql = "SELECT j.*,
                       COALESCE(j.company_name, e.company_name) as company_name,
                       e.description as company_description,
                       COALESCE(NULLIF(j.company_logo, ''), e.logo_url) as company_logo,
                       e.website as company_website,
                       e.company_slug, e.id as employer_id,
                       c.id as company_id, c.name as company_full_name, c.slug as company_slug_from_companies,
                       c.banner_url, c.logo_url as company_logo_from_companies,
                       c.description as company_about, c.ceo_name, c.ceo_photo,
                       c.founded_year, c.headquarters,
                       GROUP_CONCAT(
                    DISTINCT CONCAT_WS(
                        ', ',
                        COALESCE(ct.name, NULLIF(jl.city, '')),
                        COALESCE(st.name, NULLIF(jl.state, '')),
                        COALESCE(cnt.name, NULLIF(jl.country, ''))
                    ) SEPARATOR ' | '
                ) as location_names
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                LEFT JOIN companies c ON e.id = c.employer_id
                LEFT JOIN job_locations jl ON jl.job_id = j.id
                LEFT JOIN cities ct ON jl.city_id = ct.id
                LEFT JOIN states st ON jl.state_id = st.id
                LEFT JOIN countries cnt ON jl.country_id = cnt.id
                WHERE j.slug = :slug
                GROUP BY j.id";

        $row = $this->db->fetchOne($sql, ['slug' => $slug]);
        return is_array($row) ? $row : null;
    }

    public function isHideExternalJobsEnabled(): bool
    {
        $value = $this->db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'hide_external_jobs'")['setting_value'] ?? '0';
        return $value === '1';
    }

    public function getJobLocations(int $jobId): array
    {
        return $this->db->fetchAll(
            "SELECT
                COALESCE(c.name, jl.city) as city,
                c.slug as city_slug,
                COALESCE(s.name, jl.state) as state,
                s.slug as state_slug,
                COALESCE(cnt.name, jl.country) as country,
                cnt.slug as country_slug,
                jl.latitude, jl.longitude
             FROM job_locations jl
             LEFT JOIN cities c ON jl.city_id = c.id
             LEFT JOIN states s ON jl.state_id = s.id
             LEFT JOIN countries cnt ON jl.country_id = cnt.id
             WHERE jl.job_id = :job_id",
            ['job_id' => $jobId]
        );
    }

    public function getJobSkillNames(int $jobId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT s.name FROM job_skills js
             INNER JOIN skills s ON js.skill_id = s.id
             WHERE js.job_id = :job_id",
            ['job_id' => $jobId]
        );
        return array_values(array_filter(array_column($rows, 'name')));
    }

    public function getCompanyById(int $companyId): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM companies WHERE id = :id", ['id' => $companyId]);
        return is_array($row) ? $row : null;
    }

    public function getOtherPublishedJobsByEmployer(int $employerId, int $currentJobId, int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT j.*,
                COALESCE(
                    GROUP_CONCAT(DISTINCT TRIM(CONCAT_WS(', ',
                        NULLIF(TRIM(COALESCE(c.name, jl.city)), ''),
                        NULLIF(TRIM(COALESCE(s.name, jl.state)), ''),
                        NULLIF(TRIM(COALESCE(cnt.name, jl.country)), '')
                    )) SEPARATOR ' | '),
                    j.locations,
                    'Location not specified'
                ) as location_display
             FROM jobs j
             LEFT JOIN job_locations jl ON jl.job_id = j.id
             LEFT JOIN cities c ON jl.city_id = c.id
             LEFT JOIN states s ON jl.state_id = s.id
             LEFT JOIN countries cnt ON jl.country_id = cnt.id
             WHERE j.employer_id = :employer_id
               AND j.status = 'published'
               AND j.id != :current_job_id
             GROUP BY j.id
             ORDER BY j.created_at DESC
             LIMIT {$limit}",
            ['employer_id' => $employerId, 'current_job_id' => $currentJobId]
        );
    }

    public function logPublicJobView(int $jobId, ?int $userId, ?string $ipAddress, string $userAgent): void
    {
        $this->db->execute(
            "INSERT INTO job_views_log (job_id, user_id, ip_address, user_agent, viewed_at)
             VALUES (:job_id, :user_id, :ip_address, :user_agent, NOW())",
            [
                'job_id' => $jobId,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => substr($userAgent, 0, 1000),
            ]
        );
    }

    public function getApplicationMetrics(int $jobId): array
    {
        $row = $this->db->fetchOne(
            "SELECT
                COUNT(*) AS applications_count,
                SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) AS shortlisted_count
             FROM applications
             WHERE job_id = :job_id",
            ['job_id' => $jobId]
        ) ?? [];

        return [
            'applications_count' => (int)($row['applications_count'] ?? 0),
            'shortlisted_count' => (int)($row['shortlisted_count'] ?? 0),
        ];
    }

    public function getJobViewsLogCount(int $jobId): int
    {
        return (int)(($this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM job_views_log WHERE job_id = :job_id",
            ['job_id' => $jobId]
        )['total'] ?? 0));
    }

    private function getSearchTerms(string $keyword): array
    {
        $terms = preg_split('/\s+/', strtolower(trim($keyword))) ?: [];
        $stopWords = ['job', 'jobs', 'opening', 'openings', 'for', 'and', 'or', 'the', 'in'];

        return array_values(array_unique(array_filter($terms, function(string $term) use ($stopWords): bool {
            return strlen($term) >= 2 && !in_array($term, $stopWords, true);
        })));
    }

    private function getLocationWhereClause(string $type): string
    {
        if ($type === 'city') return "jl.city_id = :loc_id";
        if ($type === 'state') return "jl.state_id = :loc_id";
        return "jl.country_id = :loc_id";
    }
}
