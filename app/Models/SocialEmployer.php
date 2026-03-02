<?php

namespace App\Models;

class SocialEmployer extends Model
{
    protected string $table = 'social_employers';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'user_id',
        'full_name',
        'preferred_name',
        'pronouns',
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'suffix'
    ];
}
