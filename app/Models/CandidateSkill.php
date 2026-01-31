<?php

declare(strict_types=1);

namespace App\Models;

class CandidateSkill extends Model
{
    protected string $table = 'candidate_skills';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'skill_id', 'proficiency_level', 'years_of_experience'
    ];
}

