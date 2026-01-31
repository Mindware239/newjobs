<?php

declare(strict_types=1);

namespace App\Models;

class CallLog extends Model
{
    protected string $table = 'call_logs';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'candidate_user_id', 'initiated_by',
        'call_start', 'call_end', 'call_status', 'provider', 'recording_url'
    ];
}

