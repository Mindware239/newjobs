<?php

declare(strict_types=1);

namespace App\Models;

class EmploymentDocument extends Model
{
    protected string $table = 'employment_documents';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employment_id',
        'doc_type',
        'file_path',
        'file_hash',
        'mime_type',
        'size_bytes',
        'metadata'
    ];
    protected array $casts = [
        'metadata' => 'json'
    ];
}
