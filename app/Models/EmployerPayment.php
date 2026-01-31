<?php

declare(strict_types=1);

namespace App\Models;

class EmployerPayment extends Model
{
    protected string $table = 'employer_payments';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'amount', 'currency', 'gateway', 'payment_method', 'status', 'txn_id', 'meta', 'description', 'created_at', 'invoice_id'
    ];

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }
}

