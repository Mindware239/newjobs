<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Publishes blog posts written as files (content/blogs/*.html) – used by the daily Claude routine, which can
 * change the repository but not the database. Runs with the hourly india_jobs_fetch cron (and as task blog_import).
 *
 * File format (front matter between --- lines, then the HTML body):
 *   ---
 *   title: How to apply for SSC CGL 2026
 *   slug: how-to-apply-ssc-cgl-2026
 *   excerpt: One or two sentences for the blog list.
 *   meta_title: SSC CGL 2026 – How to Apply | Jobsence
 *   meta_description: Up to 160 characters for Google.
 *   meta_keywords: ssc cgl 2026, govt jobs, how to apply
 *   publish_date: 2026-10-08
 *   ---
 *   <p>Body HTML…</p>
 *
 * A post is inserted once (matched by slug) and never overwritten, so edits made in Admin → Blog stay.
 * Posts with a publish_date in the future wait until that day.
 */
class BlogFileImporter
{
    private const ALLOWED_TAGS = '<p><h2><h3><h4><ul><ol><li><strong><b><em><i><a><blockquote><table><thead><tbody><tr><th><td><br><hr>';

    /** @return string[] log lines */
    public static function run(?string $dir = null): array
    {
        $dir ??= dirname(__DIR__, 2) . '/content/blogs';
        $files = glob($dir . '/*.html') ?: [];
        if (!$files) {
            return [];
        }
        $db = Database::getInstance();
        $out = [];
        foreach ($files as $file) {
            $post = self::parse((string)file_get_contents($file));
            if ($post === null) {
                $out[] = 'blog_import: skipped ' . basename($file) . ' (bad front matter)';
                continue;
            }
            if (strtotime($post['publish_date']) > time()) {
                continue; // scheduled for later
            }
            if ($db->fetchOne('SELECT id FROM blogs WHERE slug = ?', [$post['slug']])) {
                continue; // already imported
            }
            $db->execute(
                'INSERT INTO blogs (author_id, title, slug, excerpt, content, status_id, published_at, meta_title, meta_description, meta_keywords,
                    canonical_url, view_count, is_featured, sort_order, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, 0, 0, 0, NOW(), NOW())',
                [
                    (int)($_ENV['BLOG_AUTHOR_ID'] ?? 1), $post['title'], $post['slug'], $post['excerpt'], $post['content'],
                    $post['publish_date'] . ' 06:00:00', $post['meta_title'], $post['meta_description'], $post['meta_keywords'],
                    rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/') . '/blog/' . $post['slug'],
                ]
            );
            $out[] = 'blog_import: published ' . $post['slug'];
        }
        if ($out) {
            try {
                \App\Core\RedisClient::getInstance()->delete('blog:index:page:1:search:' . md5('')); // fresh list
            } catch (\Throwable $e) {
            }
        }
        return $out;
    }

    /** Front matter + body → post fields, or null when title / slug / body are missing. */
    public static function parse(string $raw): ?array
    {
        $raw = str_replace("\r\n", "\n", ltrim($raw, "\xEF\xBB\xBF"));
        if (!preg_match('/^---\n(.*?)\n---\n(.*)$/s', $raw, $m)) {
            return null;
        }
        $meta = [];
        foreach (explode("\n", $m[1]) as $line) {
            if (preg_match('/^([a-z_]+):\s*(.*)$/', trim($line), $kv)) {
                $meta[$kv[1]] = trim($kv[2], " \"'");
            }
        }
        $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $meta['slug'] ?? ''), '-'));
        $title = mb_substr(trim($meta['title'] ?? ''), 0, 255);
        // Only simple formatting tags; no scripts, styles, iframes or event handlers.
        $body = (string)preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $m[2]);
        $content = trim(strip_tags($body, self::ALLOWED_TAGS));
        $content = (string)preg_replace('/\s(on\w+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content);
        $content = (string)preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/i', 'href="#"', $content);
        if ($slug === '' || $title === '' || $content === '') {
            return null;
        }
        $date = $meta['publish_date'] ?? '';
        return [
            'title' => $title,
            'slug' => substr($slug, 0, 200),
            'excerpt' => mb_substr($meta['excerpt'] ?? '', 0, 500),
            'content' => $content,
            'publish_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d'),
            'meta_title' => mb_substr($meta['meta_title'] ?? $title, 0, 255),
            'meta_description' => mb_substr($meta['meta_description'] ?? ($meta['excerpt'] ?? ''), 0, 255),
            'meta_keywords' => mb_substr($meta['meta_keywords'] ?? '', 0, 1000),
        ];
    }
}
