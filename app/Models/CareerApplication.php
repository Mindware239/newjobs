<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class CareerApplication extends Model
{
    protected string $table = 'career_applications';
    protected string $primaryKey = 'id';

    public static function createApplication(array $data): int
    {
        Career::ensureSchema();

        $db = Database::getInstance();
        $db->execute(
            'INSERT INTO career_applications
                (career_id, first_name, last_name, email, phone, resume, cover_letter, created_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['career_id'],
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['resume'],
                $data['cover_letter'] ?? null,
            ]
        );

        return (int)$db->lastInsertId();
    }

    public static function forCareer(int $careerId): array
    {
        Career::ensureSchema();

        return Database::getInstance()->fetchAll(
            'SELECT * FROM career_applications WHERE career_id = ? ORDER BY created_at DESC, id DESC',
            [$careerId]
        );
    }
}
