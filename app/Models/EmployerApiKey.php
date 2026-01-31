<?php

declare(strict_types=1);

namespace App\Models;

class EmployerApiKey extends Model
{
    protected string $table = 'employer_api_keys';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'name', 'secret_hash', 'allowed_ips', 'scopes', 'revoked'
    ];

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public static function generateSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function setSecret(string $secret): void
    {
        $this->attributes['secret_hash'] = hash('sha256', $secret);
    }

    public function verifySecret(string $secret): bool
    {
        return hash_equals($this->attributes['secret_hash'] ?? '', hash('sha256', $secret));
    }
}

