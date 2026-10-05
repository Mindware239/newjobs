<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class CandidateVerificationRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function candidateOwnsEmploymentRecord(int $employmentId, int $candidateId): bool
    {
        $row = $this->db->fetchOne(
            "SELECT id FROM employment_records WHERE id = :id AND candidate_id = :cid",
            ['id' => $employmentId, 'cid' => $candidateId]
        );
        return !empty($row);
    }

    public function getEmploymentDocumentById(int $id): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM employment_documents WHERE id = :id", ['id' => $id]);
        return is_array($row) ? $row : null;
    }

    public function getEmploymentStatus(int $employmentId, int $candidateId): ?string
    {
        $row = $this->db->fetchOne(
            "SELECT status FROM employment_records WHERE id = :id AND candidate_id = :cid",
            ['id' => $employmentId, 'cid' => $candidateId]
        );
        return isset($row['status']) ? (string)$row['status'] : null;
    }

    public function getEmploymentDocuments(int $employmentId): array
    {
        return $this->db->fetchAll(
            "SELECT doc_type, status, file_url FROM employment_documents WHERE employment_id = :id",
            ['id' => $employmentId]
        );
    }
}
