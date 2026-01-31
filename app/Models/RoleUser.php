<?php

declare(strict_types=1);

namespace App\Models;

class RoleUser extends Model
{
    protected string $table = 'role_user';
    protected string $primaryKey = 'id';
    protected array $fillable = ['user_id','role_id'];
}

