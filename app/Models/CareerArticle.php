<?php

declare(strict_types=1);

namespace App\Models;

class CareerArticle extends Model
{
    protected string $table = 'career_articles';

    protected string $primaryKey = 'id';

    protected array $fillable = [
        'category_id',
        'title',
        'slug',
        'short_description',
        'content',
        'image',
        'author',
        'status',
        'published_at',
        'created_at',
        'updated_at'
    ];
}