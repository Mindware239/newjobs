<?php

declare(strict_types=1);

namespace App\Helpers;

class SlugHelper
{
    public static function slugify(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}

