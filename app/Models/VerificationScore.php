<?php

declare(strict_types=1);

namespace App\Models;

class VerificationScore extends Model
{
    protected string $table = 'verification_scores';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employment_id',
        'score',
        'category',
        'breakdown'
    ];
    protected array $casts = [
        'breakdown' => 'json'
    ];
}
