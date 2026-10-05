<?php

declare(strict_types=1);

namespace App\Models;

class Review extends Model
{
    protected string $table = 'reviews';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'company_id',
        'user_id',
        'candidate_id',
        'reviewer_name',
        'rating',
        'title',
        'review_text',
        'status',
        'created_at',
        'updated_at',
    ];
}
