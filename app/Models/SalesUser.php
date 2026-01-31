<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SalesUser extends Model
{
    protected string $table = 'sales_users';
    protected string $primaryKey = 'id';

    public static function findById(int $id): ?self
    {
        return self::find($id);
    }

    public static function findAll(): array
    {
        return self::all();
    }

    public static function executives(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll('SELECT * FROM sales_users WHERE role = "executive" ORDER BY name');
        return array_map(fn($r) => new self($r), $rows);
    }
}

