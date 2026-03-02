<?php

declare(strict_types=1);

namespace App\Models;

class EmployerUnlock extends Model
{
    protected string $table = 'employer_unlocks';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employment_id',
        'employer_id',
        'price',
        'status',
        'invoice_number',
        'invoice_url'
    ];
}
