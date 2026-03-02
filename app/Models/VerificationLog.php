<?php

declare(strict_types=1);

namespace App\Models;

class VerificationLog extends Model
{
    protected string $table = 'verification_logs';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employment_id',
        'event',
        'metadata'
    ];
    protected array $casts = [
        'metadata' => 'json'
    ];
}
