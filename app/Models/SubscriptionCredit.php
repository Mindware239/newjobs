<?php

declare(strict_types=1);

namespace App\Models;

class SubscriptionCredit extends Model
{
    protected string $table = 'subscription_credits';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'subscription_id', 'employer_id', 'type', 'amount', 'note', 'created_by_user_id', 'applied'
    ];
}
