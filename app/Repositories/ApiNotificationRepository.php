<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class ApiNotificationRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function markAllAsRead(int $userId): void
    {
        $this->db->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0",
            ['uid' => $userId]
        );
    }
}
