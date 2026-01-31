<?php

declare(strict_types=1);

namespace App\Helpers;

class Sanitizer
{
    public static function cleanBlogHtml(string $html): string
    {
        $allowed = '<h1><h2><h3><h4><h5><h6><p><ul><ol><li><strong><em><u><code><pre><table><thead><tbody><tr><th><td><img><a><blockquote><hr><span><br><iframe>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace_callback('/<img\b[^>]*>/i', function($m) {
            $tag = $m[0];
            if (!preg_match('/\balt=/', $tag)) {
                $tag = preg_replace('/<img/i', '<img alt=""', $tag, 1);
            }
            return $tag;
        }, $clean);
        $clean = preg_replace_callback('/<iframe\b[^>]*>/i', function($m) {
            $tag = $m[0];
            if (!preg_match('/\bsrc="https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//i', $tag)) {
                return '';
            }
            return $tag;
        }, $clean);
        return $clean ?? '';
    }
}
