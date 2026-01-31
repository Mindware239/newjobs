<?php

declare(strict_types=1);

namespace App\Models;

class CandidateEducation extends Model
{
    protected string $table = 'candidate_education';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'degree', 'field_of_study', 'institution',
        'start_date', 'end_date', 'is_current', 'grade', 'description'
    ];
}

