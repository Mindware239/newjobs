<?php

declare(strict_types=1);

namespace App\Models;

class VerificationResponse extends Model
{
    protected string $table = 'verification_responses';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'request_id',
        'status',
        'confirmed_working',
        'duration_text',
        'designation',
        'rehire_eligibility',
        'misconduct',
        'remarks',
        'ip'
    ];
    protected array $casts = [
        'confirmed_working' => 'boolean',
        'misconduct' => 'boolean'
    ];
}
