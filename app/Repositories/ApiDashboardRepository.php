<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class ApiDashboardRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCandidateIdByUserId(int $userId): int
    {
        return (int)($this->db->fetchOne("SELECT id FROM candidates WHERE user_id = :uid", ['uid' => $userId])['id'] ?? 0);
    }

    public function getEmployerIdByUserId(int $userId): int
    {
        return (int)($this->db->fetchOne("SELECT id FROM employers WHERE user_id = :uid", ['uid' => $userId])['id'] ?? 0);
    }

    public function getCandidateDashboardData(int $candidateId, int $userId): array
    {
        return [
            'total_applications' => (int)($this->db->fetchOne("SELECT COUNT(*) as count FROM applications WHERE candidate_id = :cid", ['cid' => $candidateId])['count'] ?? 0),
            'shortlisted_applications' => (int)($this->db->fetchOne("SELECT COUNT(*) as count FROM applications WHERE candidate_id = :cid AND status = 'shortlisted'", ['cid' => $candidateId])['count'] ?? 0),
            'rejected_applications' => (int)($this->db->fetchOne("SELECT COUNT(*) as count FROM applications WHERE candidate_id = :cid AND status = 'rejected'", ['cid' => $candidateId])['count'] ?? 0),
            'saved_jobs' => (int)($this->db->fetchOne("SELECT COUNT(*) as count FROM job_bookmarks WHERE user_id = :uid", ['uid' => $userId])['count'] ?? 0),
            'recent_applications' => $this->db->fetchAll(
                "SELECT a.*, j.title, e.company_name
                 FROM applications a
                 JOIN jobs j ON a.job_id = j.id
                 JOIN employers e ON j.employer_id = e.id
                 WHERE a.candidate_id = :cid
                 ORDER BY a.applied_at DESC LIMIT 5",
                ['cid' => $candidateId]
            )
        ];
    }

    public function getEmployerDashboardData(int $employerId): array
    {
        return [
            'total_jobs' => (int)($this->db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE employer_id = :eid", ['eid' => $employerId])['count'] ?? 0),
            'active_jobs' => (int)($this->db->fetchOne("SELECT COUNT(*) as count FROM jobs WHERE employer_id = :eid AND status = 'published'", ['eid' => $employerId])['count'] ?? 0),
            'total_applications' => (int)($this->db->fetchOne(
                "SELECT COUNT(*) as count FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = :eid",
                ['eid' => $employerId]
            )['count'] ?? 0),
            'recent_jobs' => $this->db->fetchAll(
                "SELECT * FROM jobs WHERE employer_id = :eid ORDER BY created_at DESC LIMIT 5",
                ['eid' => $employerId]
            )
        ];
    }
}
