<?php

declare(strict_types=1);

namespace App\Models;

class JobView extends Model
{
    protected string $table = 'job_views';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'job_id', 'viewed_at'
    ];
}

