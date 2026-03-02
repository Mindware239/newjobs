<?php

declare(strict_types=1);

namespace App\Models;

class VerificationRequest extends Model
{
    protected string $table = 'verification_requests';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employment_id',
        'hr_email',
        'hr_phone',
        'manager_email',
        'company_website',
        'cin',
        'gst',
        'token',
        'expires_at',
        'status'
    ];
    protected array $casts = [
        'expires_at' => 'datetime'
    ];
}
