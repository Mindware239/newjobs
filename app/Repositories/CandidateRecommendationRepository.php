<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class CandidateRecommendationRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getRecommendedJobs(int $candidateId, int $limit = 20): array
    {
        $limit = max(1, $limit);
        $sql = "SELECT
                    j.id, j.title, j.slug, j.short_description,
                    j.salary_min, j.salary_max, j.currency,
                    j.employment_type, j.is_remote, j.company_name, j.locations,
                    cjs.overall_match_score, cjs.recommendation
                FROM candidate_job_scores cjs
                JOIN jobs j ON cjs.job_id = j.id
                WHERE cjs.candidate_id = :candidate_id AND j.status = 'published'
                ORDER BY cjs.overall_match_score DESC
                LIMIT {$limit}";
        return $this->db->fetchAll($sql, ['candidate_id' => $candidateId]);
    }
}
