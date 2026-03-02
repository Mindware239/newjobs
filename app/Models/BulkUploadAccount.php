<?php

declare(strict_types=1);

namespace App\Models;

class BulkUploadAccount extends Model
{
    protected string $table = 'bulk_upload_accounts';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'name', 'username', 'password_hash', 'type', 'limit_total', 'limit_used',
        'expires_at', 'status'
    ];

    public function setPassword(string $password): void
    {
        $this->attributes['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password_hash'] ?? '');
    }
}
