<?php

declare(strict_types=1);

namespace App\Helpers;

class SeoHelper
{
    public static function forBlogDetail(array $blog): array
    {
        $title = $blog['meta_title'] ?? ($blog['title'] ?? '');
        $desc = $blog['meta_description'] ?? ($blog['excerpt'] ?? '');
        $canon = $blog['canonical_url'] ?? ('/blog/' . ($blog['slug'] ?? ''));
        $defaultImage = ($_ENV['APP_URL'] ?? '') . '/public/assets/images/working-team-office.jpg';
        return [
            'title' => $title,
            'description' => $desc,
            'canonical' => $canon,
            'image' => $blog['featured_image'] ?? $defaultImage
        ];
    }
}
