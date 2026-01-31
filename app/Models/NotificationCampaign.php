<?php

declare(strict_types=1);

namespace App\Models;

class NotificationCampaign extends Model
{
    protected string $table = 'notification_campaigns';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'title',
        'subject',
        'message',
        'filters', // JSON: {role, location, skills, experience, etc.}
        'channel', // email, app, etc.
        'status', // draft, scheduled, sent, failed
        'scheduled_at',
        'sent_at',
        'recipient_count',
        'success_count',
        'failure_count',
        'created_by'
    ];

    protected array $casts = [
        'filters' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime'
    ];
}
