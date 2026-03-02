<?php

declare(strict_types=1);

namespace App\Models;

class EmploymentRecord extends Model
{
    protected string $table = 'employment_records';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id',
        'company_name',
        'designation',
        'employee_id',
        'start_date',
        'end_date',
        'consent_given',
        'consent_at',
        'status_level1',
        'status_level2',
        'status_level3',
        'status_overall',
        'risk_score',
        'risk_category',
        'verification_date',
        'admin_override'
    ];
    protected array $casts = [
        'consent_given' => 'boolean',
        'verification_date' => 'datetime'
    ];
}
