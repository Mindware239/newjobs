<?php

declare(strict_types=1);

namespace App\Models;

class EmployerKycDocument extends Model
{
    protected string $table = 'employer_kyc_documents';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'doc_type', 'file_url', 'file_name',
        'uploaded_by', 'review_status', 'review_notes', 'reviewed_by', 'reviewed_at'
    ];

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function approve(int $reviewedBy, string $notes = ''): bool
    {
        $this->attributes['review_status'] = 'approved';
        $this->attributes['reviewed_by'] = $reviewedBy;
        $this->attributes['reviewed_at'] = date('Y-m-d H:i:s');
        $this->attributes['review_notes'] = $notes;
        return $this->save();
    }

    public function reject(int $reviewedBy, string $notes): bool
    {
        $this->attributes['review_status'] = 'rejected';
        $this->attributes['reviewed_by'] = $reviewedBy;
        $this->attributes['reviewed_at'] = date('Y-m-d H:i:s');
        $this->attributes['review_notes'] = $notes;
        return $this->save();
    }
}

