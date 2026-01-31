<?php

declare(strict_types=1);

namespace App\Models;

class Role extends Model
{
    protected string $table = 'roles';
    protected string $primaryKey = 'id';
    protected array $fillable = ['name','slug','description','created_at'];
}

