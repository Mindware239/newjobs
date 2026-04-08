<?php

declare(strict_types=1);

namespace App\Models;

class PaymentMethod extends Model
{
    protected string $table = 'payment_methods';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'gateway', 'token', 'method_type', 
        'last4', 'brand', 'exp_month', 'exp_year', 'is_default'
    ];

    public static function getForEmployer(int $employerId): array
    {
        return self::where('employer_id', '=', $employerId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
