<?php

declare(strict_types=1);

namespace App\Models;

class JobBookmark extends Model
{
    protected string $table = 'job_bookmarks';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'job_id'
    ];
}

