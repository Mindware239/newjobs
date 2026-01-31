<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class SalesStage extends Model
{
    protected string $table = 'sales_stages';
    protected string $primaryKey = 'id';

    public static function findById(int $id): ?self
    {
        return self::find($id);
    }

    public static function findAll(): array
    {
        return self::all();
    }
}

