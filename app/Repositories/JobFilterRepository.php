<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Job;
use App\Models\Candidate;

class JobFilterRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get potential candidates for a job using cursor-based pagination
     */
    public function getPotentialCandidatesForJob(Job $job, array $titleKeywords, int $limit, int $lastId = 0): array
    {
        $params = ['last_id' => $lastId];
        $whereSql = "status = 'active' AND id > :last_id";

        if (!empty($titleKeywords)) {
            $likeParts = [];
            foreach ($titleKeywords as $i => $kw) {
                $key = "kw_{$i}";
                $likeParts[] = "LOWER(professional_title) LIKE :{$key}";
                $params[$key] = "%{$kw}%";
            }
            $whereTitle = implode(' OR ', $likeParts);
            $whereSql .= " AND ({$whereTitle})";
        }

        $sql = "SELECT * FROM candidates 
                WHERE {$whereSql}
                ORDER BY id ASC
                LIMIT {$limit}";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get potential jobs for a candidate using cursor-based pagination
     */
    public function getPotentialJobsForCandidate(array $titleKeywords, int $limit, int $lastId = 0): array
    {
        $params = ['last_id' => $lastId];
        $whereSql = "status = 'published' AND id > :last_id";

        if (!empty($titleKeywords)) {
            $likeParts = [];
            foreach ($titleKeywords as $i => $kw) {
                $key = "kw_{$i}";
                $likeParts[] = "LOWER(title) LIKE :{$key}";
                $params[$key] = "%{$kw}%";
            }
            $whereTitle = implode(' OR ', $likeParts);
            $whereSql .= " AND ({$whereTitle})";
        }

        $sql = "SELECT * FROM jobs 
                WHERE {$whereSql}
                ORDER BY id ASC
                LIMIT {$limit}";

        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Check if match was recently processed
     */
    public function isMatchProcessedRecently(int $candidateId, int $jobId, int $hours = 24): bool
    {
        $sql = "SELECT id FROM candidate_job_scores 
                WHERE candidate_id = :cid AND job_id = :jid 
                AND updated_at > DATE_SUB(NOW(), INTERVAL :hours HOUR)";
        
        return !empty($this->db->fetchOne($sql, [
            'cid' => $candidateId, 
            'jid' => $jobId,
            'hours' => $hours
        ]));
    }
}
