<?php

declare(strict_types=1);

namespace App\Models;

class SubscriptionHistory extends Model
{
    protected string $table = 'subscription_history';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'subscription_id', 'employer_id', 'plan_id', 'from_plan_id',
        'status', 'started_at', 'ends_at', 'changed_by_user_id', 'change_reason'
    ];
}
