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
        $whereSql = "id > :last_id
            AND COALESCE(profile_status, 'verified') != 'suspended'
            AND COALESCE(visibility, 'searchable') != 'private'";

        if (!empty($titleKeywords)) {
            $likeParts = [];
            foreach ($titleKeywords as $i => $kw) {
                $titleKey = "kw_title_{$i}";
                $skillsKey = "kw_skills_{$i}";
                $experienceKey = "kw_experience_{$i}";
                $introKey = "kw_intro_{$i}";
                $likeParts[] = "(
                    LOWER(COALESCE(professional_title, '')) LIKE :{$titleKey}
                    OR LOWER(COALESCE(skills_data, '')) LIKE :{$skillsKey}
                    OR LOWER(COALESCE(experience_data, '')) LIKE :{$experienceKey}
                    OR LOWER(COALESCE(self_introduction, '')) LIKE :{$introKey}
                )";
                $params[$titleKey] = "%{$kw}%";
                $params[$skillsKey] = "%{$kw}%";
                $params[$experienceKey] = "%{$kw}%";
                $params[$introKey] = "%{$kw}%";
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
        $whereSql = "j.status = 'published' AND j.id > :last_id";

        if (!empty($titleKeywords)) {
            $likeParts = [];
            foreach ($titleKeywords as $i => $kw) {
                $titleKey = "kw_title_{$i}";
                $categoryKey = "kw_category_{$i}";
                $descriptionKey = "kw_description_{$i}";
                $skillKey = "kw_skill_{$i}";
                $likeParts[] = "(
                    LOWER(COALESCE(j.title, '')) LIKE :{$titleKey}
                    OR LOWER(COALESCE(j.category, '')) LIKE :{$categoryKey}
                    OR LOWER(COALESCE(j.description, '')) LIKE :{$descriptionKey}
                    OR EXISTS (
                        SELECT 1 FROM job_skills js
                        INNER JOIN skills s ON s.id = js.skill_id
                        WHERE js.job_id = j.id AND LOWER(s.name) LIKE :{$skillKey}
                    )
                )";
                $params[$titleKey] = "%{$kw}%";
                $params[$categoryKey] = "%{$kw}%";
                $params[$descriptionKey] = "%{$kw}%";
                $params[$skillKey] = "%{$kw}%";
            }
            $whereTitle = implode(' OR ', $likeParts);
            $whereSql .= " AND ({$whereTitle})";
        }

        $sql = "SELECT j.* FROM jobs j
                WHERE {$whereSql}
                ORDER BY j.id ASC
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
