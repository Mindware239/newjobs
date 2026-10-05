<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class BulkUploadRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function incrementAccountLimitUsed(int $accountId, int $count): void
    {
        $this->db->execute(
            "UPDATE bulk_upload_accounts SET limit_used = COALESCE(limit_used,0) + :c WHERE id = :id",
            ['c' => $count, 'id' => $accountId]
        );
    }

    public function getFilesForAccount(int $accountId, int $limit = 200): array
    {
        $limit = max(1, $limit);
        return $this->db->fetchAll(
            "SELECT rf.* FROM resume_files rf
             INNER JOIN resume_batches rb ON rf.batch_id = rb.id
             WHERE rb.bulk_account_id = :id
             ORDER BY rf.id DESC
             LIMIT {$limit}",
            ['id' => $accountId]
        );
    }

    public function getFileStatusAggregateForAccount(int $accountId): array
    {
        return $this->db->fetchAll(
            "SELECT rf.status AS status, COUNT(*) c
             FROM resume_files rf
             INNER JOIN resume_batches rb ON rf.batch_id = rb.id
             WHERE rb.bulk_account_id = :id
             GROUP BY rf.status",
            ['id' => $accountId]
        );
    }

    public function findFileForAccount(int $fileId, int $accountId): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT rf.* FROM resume_files rf
             INNER JOIN resume_batches rb ON rf.batch_id = rb.id
             WHERE rf.id = :id AND rb.bulk_account_id = :bid",
            ['id' => $fileId, 'bid' => $accountId]
        );
        return is_array($row) ? $row : null;
    }
}
