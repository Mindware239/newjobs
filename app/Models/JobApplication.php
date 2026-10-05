<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class JobApplication extends Model
{
    protected string $table = 'job_applications';
    protected string $primaryKey = 'id';

    public static function createApplication(array $data): int
    {
        Career::ensureSchema();

        $db = Database::getInstance();
        $db->execute(
            'INSERT INTO job_applications (job_id, first_name, last_name, email, resume, cover_letter, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['job_id'],
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['resume'],
                $data['cover_letter'] ?? null,
            ]
        );

        return (int)$db->lastInsertId();
    }

    public static function forJob(int $jobId): array
    {
        Career::ensureSchema();

        return Database::getInstance()->fetchAll(
            'SELECT * FROM job_applications WHERE job_id = ? ORDER BY created_at DESC',
            [$jobId]
        );
    }
}
