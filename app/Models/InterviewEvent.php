<?php

declare(strict_types=1);

namespace App\Models;

class InterviewEvent extends Model
{
    protected string $table = 'interview_events';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'interview_id',
        'actor_user_id',
        'actor_role',
        'event_type',
        'payload',
        'ip_address',
        'user_agent',
        'created_at'
    ];
}

