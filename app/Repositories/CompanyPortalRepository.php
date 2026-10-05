<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class CompanyPortalRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findEmployerByCompanySlug(string $slug): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM employers WHERE company_slug = ? LIMIT 1",
            [$slug]
        );
        return is_array($row) ? $row : null;
    }

    public function createCompanyFromEmployer(array $employer, string $slug): void
    {
        $sql = "INSERT INTO companies (employer_id, short_name, name, slug, logo_url, website, description, industry, company_size, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $this->db->execute($sql, [
            (int)($employer['id'] ?? 0),
            $employer['company_name'] ?? 'Company',
            $employer['company_name'] ?? 'Company',
            $slug,
            $employer['logo_url'] ?? null,
            $employer['website'] ?? null,
            $employer['description'] ?? null,
            $employer['industry'] ?? null,
            $employer['size'] ?? null
        ]);
    }

    public function getPublishedJobsByEmployerId(int $employerId): array
    {
        return $this->db->fetchAll(
            "SELECT j.*,
             GROUP_CONCAT(
                DISTINCT TRIM(CONCAT_WS(', ',
                    NULLIF(TRIM(COALESCE(c.name, jl.city)), ''),
                    NULLIF(TRIM(COALESCE(s.name, jl.state)), ''),
                    NULLIF(TRIM(COALESCE(cnt.name, jl.country)), '')
                ))
                SEPARATOR ' | '
             ) AS location_display
             FROM jobs j
             LEFT JOIN job_locations jl ON jl.job_id = j.id
             LEFT JOIN cities c ON jl.city_id = c.id
             LEFT JOIN states s ON jl.state_id = s.id
             LEFT JOIN countries cnt ON jl.country_id = cnt.id
             WHERE j.employer_id = :employer_id
             AND j.status = 'published'
             GROUP BY j.id
             ORDER BY j.created_at DESC",
            ['employer_id' => $employerId]
        );
    }

    public function getApprovedReviewsByCompanyId(int $companyId, int $limit = 10): array
    {
        $limit = max(1, $limit);
        return $this->db->fetchAll(
            "SELECT reviewer_name, rating, title, review_text, created_at
             FROM reviews
             WHERE company_id = :cid
             AND (status = 'approved' OR status IS NULL)
             ORDER BY created_at DESC
             LIMIT {$limit}",
            ['cid' => $companyId]
        );
    }

    public function insertCompanyReview(int $companyId, int $userId, ?int $candidateId, string $reviewerName, int $rating, string $title, string $text): void
    {
        $sql = "INSERT INTO reviews (company_id, user_id, candidate_id, reviewer_name, rating, title, review_text, status, created_at)
                VALUES (:cid, :uid, :candidate_id, :name, :rating, :title, :text, 'approved', NOW())";
        $this->db->execute($sql, [
            'cid' => $companyId,
            'uid' => $userId,
            'candidate_id' => $candidateId,
            'name' => $reviewerName,
            'rating' => $rating,
            'title' => $title,
            'text' => $text
        ]);
    }
}
