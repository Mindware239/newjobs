<?php

declare(strict_types=1);

namespace App\Models;

class CandidateExperience extends Model
{
    protected string $table = 'candidate_experience';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'job_title', 'company_name', 'start_date',
        'end_date', 'is_current', 'description', 'location'
    ];
}

