<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SocialSubscriptionEmploy
{
    protected Database $db;
    protected array $data = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function fill(array $data): void
    {
        $this->data = $data;
    }

    public function save(): void
    {
        if (empty($this->data)) {
            return;
        }

        $columns = array_keys($this->data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO social_subscription_employ (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, $this->data);
    }
}
