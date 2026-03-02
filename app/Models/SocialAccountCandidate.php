<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SocialAccountCandidate
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch SINGLE candidate profile by candidate ID
     * (admin view)
     */
    public function findById(int $id): ?array
    {
        $result = $this->db->query(
            "SELECT *
             FROM social_account_candidate
             WHERE id = ?",
            [$id]
        )->fetch();

        return $result ?: null;
    }

    /**
     * Fetch candidate profile by user_id (login-based)
     */
    public function findByUserId(int $userId): ?array
    {
        $result = $this->db->query(
            "SELECT *
             FROM social_account_candidate
             WHERE user_id = ?",
            [$userId]
        )->fetch();

        return $result ?: null;
    }

    /**
     * Verify candidate (admin action)
     */
    public function verify(int $id): bool
    {
        return (bool) $this->db->query(
            "UPDATE social_account_candidate
             SET is_verified = 1, updated_at = NOW()
             WHERE id = ?",
            [$id]
        );
    }

    /**
     * Deactivate candidate profile
     */
    public function deactivate(int $id): bool
    {
        return (bool) $this->db->query(
            "UPDATE social_account_candidate
             SET profile_status = 'inactive', updated_at = NOW()
             WHERE id = ?",
            [$id]
        );
    }
    /**
 * Create or Update candidate profile (by user_id)
 */
public function saveOrUpdate(array $data): bool
{
    // Check if profile already exists
    $existing = $this->db->query(
        "SELECT id FROM social_account_candidate WHERE user_id = ?",
        [$data['user_id']]
    )->fetch();

    if ($existing) {
        // UPDATE
        return (bool) $this->db->query(
            "UPDATE social_account_candidate SET
                email = ?,
                phone = ?,
                first_name = ?,
                last_name = ?,
                preferred_name = ?,
                pronouns = ?,
                headline = ?,
                summary = ?,
                role_type = ?,
                work_category = ?,
                workplace_option = ?,
                time_commitment = ?,
                education_level = ?,
                experience_years = ?,
                expected_salary_min = ?,
                expected_salary_max = ?,
                pay_type = ?,
                country = ?,
                state = ?,
                city = ?,
                updated_at = NOW()
             WHERE user_id = ?",
            [
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['first_name'],
                $data['last_name'],
                $data['preferred_name'] ?? null,
                $data['pronouns'] ?? null,
                $data['headline'] ?? null,
                $data['summary'] ?? null,
                $data['role_type'] ?? null,
                $data['work_category'] ?? null,
                $data['workplace_option'] ?? null,
                $data['time_commitment'] ?? null,
                $data['education_level'] ?? null,
                $data['experience_years'] ?? null,
                $data['expected_salary_min'] ?? null,
                $data['expected_salary_max'] ?? null,
                $data['pay_type'] ?? null,
                $data['country'] ?? null,
                $data['state'] ?? null,
                $data['city'] ?? null,
                $data['user_id']
            ]
        );
    }

    // INSERT
    return (bool) $this->db->query(
        "INSERT INTO social_account_candidate (
            user_id, email, phone, first_name, last_name,
            preferred_name, pronouns, headline, summary,
            role_type, work_category, workplace_option,
            time_commitment, education_level, experience_years,
            expected_salary_min, expected_salary_max, pay_type,
            country, state, city, profile_status, created_at
        ) VALUES (
            ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'draft', NOW()
        )",
        [
            $data['user_id'],
            $data['email'],
            $data['phone'] ?? null,
            $data['first_name'],
            $data['last_name'],
            $data['preferred_name'] ?? null,
            $data['pronouns'] ?? null,
            $data['headline'] ?? null,
            $data['summary'] ?? null,
            $data['role_type'] ?? null,
            $data['work_category'] ?? null,
            $data['workplace_option'] ?? null,
            $data['time_commitment'] ?? null,
            $data['education_level'] ?? null,
            $data['experience_years'] ?? null,
            $data['expected_salary_min'] ?? null,
            $data['expected_salary_max'] ?? null,
            $data['pay_type'] ?? null,
            $data['country'] ?? null,
            $data['state'] ?? null,
            $data['city'] ?? null
        ]
    );
}

    
}
