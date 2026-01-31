<?php

declare(strict_types=1);

namespace App\Models;

class PermissionRole extends Model
{
    protected string $table = 'permission_role';
    protected string $primaryKey = 'id';
    protected array $fillable = ['role_id','permission_id'];
}

