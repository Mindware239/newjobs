<?php

declare(strict_types=1);

namespace App\Models;

class CandidateLanguage extends Model
{
    protected string $table = 'candidate_languages';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'language', 'proficiency'
    ];
}

