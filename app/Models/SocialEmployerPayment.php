<?php

declare(strict_types=1);

namespace App\Models;

class SocialEmployerPayment extends Model
{
    protected string $table = 'social_employer_payments';

    protected array $fillable = [
        'employer_id',
        'subscription_id',
        'plan_id',
        'amount',
        'currency',
        'gateway',
        'payment_method',
        'status',
        'txn_id',
        'error_message',
        'meta',
        'paid_at'
    ];
}
