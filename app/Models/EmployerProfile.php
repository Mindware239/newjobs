<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class EmployerProfile
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    protected function ensureSchema(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS social_employer_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            full_name VARCHAR(255) DEFAULT '',
            preferred_name VARCHAR(255) DEFAULT '',
            pronouns VARCHAR(50) DEFAULT '',
            prefix VARCHAR(50) DEFAULT '',
            first_name VARCHAR(100) DEFAULT '',
            middle_name VARCHAR(100) DEFAULT '',
            last_name VARCHAR(100) DEFAULT '',
            suffix VARCHAR(50) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            UNIQUE KEY uniq_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        try { $this->db->query($sql); } catch (\Throwable $t) {}
    }

    // ============================
    // CREATE PROFILE
    // ============================

    public function create(array $data): int
    {
        $this->ensureSchema();
        $sql = "INSERT INTO social_employer_profiles (
            user_id,
            full_name,
            preferred_name,
            pronouns,
            prefix,
            first_name,
            middle_name,
            last_name,
            suffix
        ) VALUES (
            :user_id,
            :full_name,
            :preferred_name,
            :pronouns,
            :prefix,
            :first_name,
            :middle_name,
            :last_name,
            :suffix
        )";

        $this->db->query($sql, $data);

        return (int)$this->db->lastInsertId();
    }

    // ============================
    // GET PROFILE BY USER
    // ============================

    public function findByUser(int $userId): ?array
    {
        $this->ensureSchema();
        return $this->db->fetchOne(
            "SELECT * FROM social_employer_profiles WHERE user_id = :id LIMIT 1",
            ['id' => $userId]
        );
    }

    // ============================
    // UPDATE PROFILE
    // ============================

    public function update(int $id, array $data): bool
    {
        $this->ensureSchema();
        $data['id'] = $id;

        $sql = "UPDATE social_employer_profiles SET
            full_name = :full_name,
            preferred_name = :preferred_name,
            pronouns = :pronouns,
            prefix = :prefix,
            first_name = :first_name,
            middle_name = :middle_name,
            last_name = :last_name,
            suffix = :suffix,
            updated_at = NOW()
        WHERE id = :id";

        $stmt = $this->db->query($sql, $data);

        return $stmt->rowCount() > 0;
    }
}
