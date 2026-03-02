<?php

declare(strict_types=1);

namespace App\Models;

class ResumeFile extends Model
{
    protected string $table = 'resume_files';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'batch_id', 'candidate_id', 'filename', 'filepath', 'hash', 'status',
        'failure_reason', 'parsed_data', 'created_at', 'processed_at'
    ];
}
