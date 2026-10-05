<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class AuthRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findUserByAnyEmail(string $email): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM users
             WHERE LOWER(email) = LOWER(:email)
                OR LOWER(google_email) = LOWER(:google_email)
                OR LOWER(apple_email) = LOWER(:apple_email)
             LIMIT 1",
            ['email' => $email, 'google_email' => $email, 'apple_email' => $email]
        );
        return is_array($row) ? $row : null;
    }

    public function getRoleSlugsByUserId(int $userId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT r.slug FROM roles r INNER JOIN role_user ru ON ru.role_id = r.id WHERE ru.user_id = :uid',
            ['uid' => $userId]
        );
        return array_values(array_filter(array_map(static fn($r) => (string)($r['slug'] ?? ''), $rows)));
    }

    public function upsertPasswordResetToken(int $userId, string $email, string $token, string $expiresAt): void
    {
        $this->db->query(
            "DELETE FROM password_resets WHERE user_id = :user_id OR expires_at < UTC_TIMESTAMP()",
            ['user_id' => $userId]
        );

        $this->db->query(
            "INSERT INTO password_resets (email, token, user_id, expires_at) VALUES (:email, :token, :user_id, :expires_at)",
            ['email' => $email, 'token' => $token, 'user_id' => $userId, 'expires_at' => $expiresAt]
        );
    }

    public function getValidPasswordResetTokenData(string $token): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT user_id, email, expires_at FROM password_resets WHERE token = :token AND expires_at > UTC_TIMESTAMP()",
            ['token' => $token]
        );
        return is_array($row) ? $row : null;
    }

    public function getPasswordResetTokenData(string $token): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT token, expires_at FROM password_resets WHERE token = :token",
            ['token' => $token]
        );
        return is_array($row) ? $row : null;
    }

    public function deletePasswordResetToken(string $token): void
    {
        $this->db->query("DELETE FROM password_resets WHERE token = :token", ['token' => $token]);
    }

    public function activateCandidateProfileByUserId(int $userId): void
    {
        $this->db->query(
            "UPDATE candidates SET profile_status = :status WHERE user_id = :user_id",
            ['status' => 'active', 'user_id' => $userId]
        );
    }
}
