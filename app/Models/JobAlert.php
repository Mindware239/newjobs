<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAlert extends Model
{
    use HasFactory;

    protected $table = 'job_alerts';

    protected $fillable = [

        // Basic info
        'subject_name',
        'notification_email',

        // Status
        'alert_status',

        // Frequency
        'frequency',

        // Filters
        'role_type',
        'workplace_option',
        'time_commitment',
        'role_category',
        'minimum_education',
        'minimum_experience',

        // Pay
        'pay_term',
        'minimum_hourly_rate',
        'minimum_salary',

        // Impact
        'impact_area'
    ];

    protected $casts = [
        'alert_status' => 'boolean',
        'minimum_experience' => 'integer',
        'minimum_hourly_rate' => 'decimal:2',
        'minimum_salary' => 'decimal:2'
    ];
}
