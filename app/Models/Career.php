<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Career extends Model
{
    protected string $table = 'careers';
    protected string $primaryKey = 'id';

    public static function allActive(?string $department = null, ?string $location = null): array
    {
        self::ensureSchema();

        $where = ["status = 'active'"];
        $params = [];

        if ($department !== null && $department !== '') {
            $where[] = 'department = ?';
            $params[] = $department;
        }

        if ($location !== null && $location !== '') {
            $where[] = 'location = ?';
            $params[] = $location;
        }

        return Database::getInstance()->fetchAll(
            'SELECT * FROM careers WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC, id DESC',
            $params
        );
    }

    public static function findCareer(int $id, bool $activeOnly = true): ?array
    {
        self::ensureSchema();

        $where = 'id = ?';
        $params = [$id];

        if ($activeOnly) {
            $where .= " AND status = 'active'";
        }

        return Database::getInstance()->fetchOne("SELECT * FROM careers WHERE {$where} LIMIT 1", $params);
    }

    public static function departments(bool $activeOnly = true, int $limit = 0): array
    {
        self::ensureSchema();

        $where = 'department IS NOT NULL AND department != ""';
        if ($activeOnly) {
            $where .= " AND status = 'active'";
        }

        $limitSql = $limit > 0 ? ' LIMIT ' . (int)$limit : '';

        return Database::getInstance()->fetchAll(
            "SELECT department, COUNT(*) AS total FROM careers WHERE {$where} GROUP BY department ORDER BY department ASC{$limitSql}"
        );
    }

    public static function locations(bool $activeOnly = true, int $limit = 0): array
    {
        self::ensureSchema();

        $where = 'location IS NOT NULL AND location != ""';
        if ($activeOnly) {
            $where .= " AND status = 'active'";
        }

        $limitSql = $limit > 0 ? ' LIMIT ' . (int)$limit : '';

        return Database::getInstance()->fetchAll(
            "SELECT location, COUNT(*) AS total FROM careers WHERE {$where} GROUP BY location ORDER BY total DESC, location ASC{$limitSql}"
        );
    }

    public static function allForAdmin(): array
    {
        self::ensureSchema();

        return Database::getInstance()->fetchAll(
            'SELECT c.*, COUNT(a.id) AS application_count
             FROM careers c
             LEFT JOIN career_applications a ON a.career_id = c.id
             GROUP BY c.id
             ORDER BY c.created_at DESC, c.id DESC'
        );
    }

    public static function createCareer(array $data): int
    {
        self::ensureSchema();

        $status = in_array($data['status'] ?? 'active', ['active', 'inactive'], true) ? $data['status'] : 'active';
        $db = Database::getInstance();

        $db->execute(
            'INSERT INTO careers
                (title, department, location, job_type, work_type, description, responsibilities, requirements, status, created_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['title'],
                $data['department'],
                $data['location'],
                $data['job_type'] ?: 'Full Time',
                $data['work_type'] ?: 'On-site',
                $data['description'],
                $data['responsibilities'],
                $data['requirements'],
                $status,
            ]
        );

        return (int)$db->lastInsertId();
    }

    public static function updateCareer(int $id, array $data): void
    {
        self::ensureSchema();

        $status = in_array($data['status'] ?? 'active', ['active', 'inactive'], true) ? $data['status'] : 'active';

        Database::getInstance()->execute(
            'UPDATE careers
             SET title = ?, department = ?, location = ?, job_type = ?, work_type = ?, description = ?,
                 responsibilities = ?, requirements = ?, status = ?
             WHERE id = ?',
            [
                $data['title'],
                $data['department'],
                $data['location'],
                $data['job_type'] ?: 'Full Time',
                $data['work_type'] ?: 'On-site',
                $data['description'],
                $data['responsibilities'],
                $data['requirements'],
                $status,
                $id,
            ]
        );
    }

    public static function deleteCareer(int $id): void
    {
        self::ensureSchema();

        Database::getInstance()->execute('DELETE FROM careers WHERE id = ?', [$id]);
    }

    public static function toggleStatus(int $id): void
    {
        self::ensureSchema();

        $job = self::findCareer($id, false);
        if (!$job) {
            return;
        }

        $next = ($job['status'] ?? 'active') === 'active' ? 'inactive' : 'active';
        Database::getInstance()->execute('UPDATE careers SET status = ? WHERE id = ?', [$next, $id]);
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }

        $pdo = Database::getInstance()->getConnection();
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS careers (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(180) NOT NULL,
                department VARCHAR(150) NOT NULL,
                location VARCHAR(150) NOT NULL,
                job_type VARCHAR(80) NOT NULL,
                work_type VARCHAR(80) NOT NULL,
                description TEXT NOT NULL,
                responsibilities TEXT NOT NULL,
                requirements TEXT NOT NULL,
                status ENUM('active','inactive') NOT NULL DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_careers_status (status),
                KEY idx_careers_department (department),
                KEY idx_careers_location (location)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS career_applications (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                career_id BIGINT UNSIGNED NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                resume VARCHAR(255) NOT NULL,
                cover_letter VARCHAR(255) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_career_applications_career_id (career_id),
                CONSTRAINT fk_career_applications_career_id
                    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $done = true;
    }
}
