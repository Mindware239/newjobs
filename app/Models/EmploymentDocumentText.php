<?php

declare(strict_types=1);

namespace App\Models;

class EmploymentDocumentText extends Model
{
    protected string $table = 'employment_document_texts';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'document_id',
        'extracted_text',
        'language'
    ];
}
