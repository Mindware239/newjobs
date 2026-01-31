<?php

declare(strict_types=1);

namespace App\Models;

class Permission extends Model
{
    protected string $table = 'permissions';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name','slug','module','created_at'];
}

