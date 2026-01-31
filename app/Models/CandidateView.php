<?php

declare(strict_types=1);

namespace App\Models;

class CandidateView extends Model
{
    protected string $table = 'candidate_views';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'candidate_id', 'viewed_at'
    ];
}
