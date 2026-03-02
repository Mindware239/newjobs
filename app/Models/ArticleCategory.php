<?php

declare(strict_types=1);

namespace App\Models;

class ArticleCategory extends Model
{
    protected string $table = 'article_categories';

    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'slug',
        'created_at'
    ];
}