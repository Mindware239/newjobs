<?php

declare(strict_types=1);

namespace App\Models;

class CandidatePremiumPurchase extends Model
{
    protected string $table = 'candidate_premium_purchases';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'candidate_id', 'plan_type', 'amount', 'payment_method', 
        'payment_id', 'status', 'expires_at'
    ];
}

