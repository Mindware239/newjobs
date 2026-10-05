<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class ApiChatRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getEmployerConversations(int $employerId): array
    {
        $sql = "SELECT
                    c.id,
                    c.candidate_user_id,
                    c.updated_at,
                    c.unread_employer AS unread_count,
                    m.body AS last_message_body,
                    m.created_at AS last_message_time,
                    u.email AS other_email,
                    cand.full_name AS other_name
                FROM conversations c
                LEFT JOIN messages m ON c.last_message_id = m.id
                LEFT JOIN users u ON c.candidate_user_id = u.id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                WHERE c.employer_id = :employer_id
                ORDER BY c.updated_at DESC
                LIMIT 100";
        return $this->db->fetchAll($sql, ['employer_id' => $employerId]);
    }

    public function getCandidateConversations(int $candidateUserId): array
    {
        $sql = "SELECT
                    c.id,
                    c.employer_id,
                    c.updated_at,
                    c.unread_candidate AS unread_count,
                    m.body AS last_message_body,
                    m.created_at AS last_message_time,
                    e.company_name AS other_name,
                    u.email AS other_email
                FROM conversations c
                LEFT JOIN messages m ON c.last_message_id = m.id
                LEFT JOIN employers e ON c.employer_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                WHERE c.candidate_user_id = :candidate_user_id
                ORDER BY c.updated_at DESC
                LIMIT 100";
        return $this->db->fetchAll($sql, ['candidate_user_id' => $candidateUserId]);
    }

    public function getEmployerUnreadCount(int $employerId): int
    {
        $row = $this->db->fetchOne(
            "SELECT SUM(unread_employer) AS total FROM conversations WHERE employer_id = :employer_id",
            ['employer_id' => $employerId]
        );
        return (int)($row['total'] ?? 0);
    }

    public function getCandidateUnreadCount(int $candidateUserId): int
    {
        $row = $this->db->fetchOne(
            "SELECT SUM(unread_candidate) AS total FROM conversations WHERE candidate_user_id = :candidate_user_id",
            ['candidate_user_id' => $candidateUserId]
        );
        return (int)($row['total'] ?? 0);
    }
}
