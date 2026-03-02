<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SocialOrganization
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureOrgSchema();
    }

    private function ensureOrgSchema(): void
    {
        try {
            $this->db->query("SELECT logo_url FROM social_organizations LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $this->db->execute("ALTER TABLE social_organizations ADD COLUMN logo_url VARCHAR(512) NULL AFTER website");
            } catch (\Throwable $ignore) {}
        }
    }

    // =========================
    // CREATE ORGANIZATION
    // =========================
    public function create(array $data): int
    {
        $this->ensureOrgSchema();
        $sql = "INSERT INTO social_organizations (
            employer_id,
            organization_name,
            acronyms,
            organization_type,
            is_agency,
            website,
            logo_url,
            ein,
            staff_count,
            mission_focus,
            mission,
            impact,
            created_at
        ) VALUES (
            :employer_id,
            :organization_name,
            :acronyms,
            :organization_type,
            :is_agency,
            :website,
            :logo_url,
            :ein,
            :staff_count,
            :mission_focus,
            :mission,
            :impact,
            :created_at
        )";

        $params = array_merge([
            'logo_url' => $data['logo_url'] ?? null
        ], $data);
        $this->db->query($sql, $params);

        return (int)$this->db->lastInsertId();
    }

    // =========================
    // FIND BY ID
    // =========================
    public function find(int $id)
    {
        return $this->db->fetchOne(
            "SELECT * FROM social_organizations WHERE id = :id",
            ['id' => $id]
        );
    }

    // =========================
    // GET ALL BY EMPLOYER
    // =========================
    public function getByEmployer(int $employer_id): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM social_organizations 
             WHERE employer_id = :employer_id
             ORDER BY created_at DESC",
            ['employer_id' => $employer_id]
        );
    }

    // =========================
    // SEARCH ORGANIZATION (AJAX)
    // =========================
    public function search(string $keyword): array
    {
        return $this->db->fetchAll(
            "SELECT id, organization_name 
             FROM social_organizations
             WHERE organization_name LIKE :name
             ORDER BY organization_name
             LIMIT 10",
            ['name' => "%$keyword%"]
        );
    }

    // =========================
    // CHECK DUPLICATE NAME
    // =========================
    public function exists(string $name, int $employer_id): bool
    {
        $row = $this->db->fetchOne(
            "SELECT id FROM social_organizations 
             WHERE organization_name = :name 
             AND employer_id = :employer_id",
            [
                'name' => $name,
                'employer_id' => $employer_id
            ]
        );

        return !empty($row);
    }

    // =========================
    // DELETE ORGANIZATION
    // =========================
    public function delete(int $id): void
    {
        $this->db->query(
            "DELETE FROM social_organizations WHERE id = :id",
            ['id' => $id]
        );
    }

    public function updateById(int $id, array $data): void
    {
        $this->ensureOrgSchema();
        $fields = [];
        $params = ['id' => $id];
        foreach ([
            'organization_name','acronyms','organization_type','is_agency',
            'website','logo_url','ein','staff_count','mission_focus','mission','impact'
        ] as $k) {
            if (array_key_exists($k, $data)) {
                $fields[] = "$k = :$k";
                $params[$k] = $data[$k];
            }
        }
        if (!empty($fields)) {
            $sql = "UPDATE social_organizations SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
            $this->db->query($sql, $params);
        }
    }
}
