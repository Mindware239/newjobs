<?php

declare(strict_types=1);

namespace App\Models;

class ResumeBatch extends Model
{
    protected string $table = 'resume_batches';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'bulk_account_id', 'total_files', 'processed_files', 'failed_files',
        'status', 'created_at', 'completed_at'
    ];
}
