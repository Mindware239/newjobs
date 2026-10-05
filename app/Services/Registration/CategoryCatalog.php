<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;

/**
 * Searchable list of job roles / skills / trades for the registration forms:
 * curated lists in resources/data/categories/*.php merged with the portal's own
 * job_titles, job_categories and skills tables. Cached as JSON for a day.
 */
class CategoryCatalog
{
    private const TTL = 86400;

    public static function all(): array
    {
        $cache = self::cachePath();
        if (is_file($cache) && filemtime($cache) > time() - self::TTL) {
            $list = json_decode((string)file_get_contents($cache), true);
            if (is_array($list) && $list) {
                return $list;
            }
        }

        $list = self::build();
        if (!is_dir(dirname($cache))) {
            @mkdir(dirname($cache), 0775, true);
        }
        @file_put_contents($cache, json_encode($list, JSON_UNESCAPED_UNICODE));

        return $list;
    }

    public static function json(): string
    {
        $cache = self::cachePath();
        self::all();
        return is_file($cache) ? (string)file_get_contents($cache) : json_encode(self::build(), JSON_UNESCAPED_UNICODE);
    }

    private static function build(): array
    {
        $byKey = [];
        $add = static function ($name) use (&$byKey): void {
            $name = trim(preg_replace('/\s+/', ' ', (string)$name));
            if ($name === '' || mb_strlen($name) > 80) {
                return;
            }
            $key = mb_strtolower($name);
            $byKey[$key] ??= ucwords($name) === $name || preg_match('/[A-Z]/', $name) ? $name : ucwords($name);
        };

        foreach (glob(__DIR__ . '/../../../resources/data/categories/*.php') ?: [] as $file) {
            foreach ((array)require $file as $name) {
                $add($name);
            }
        }

        try {
            $db = Database::getInstance();
            foreach ($db->fetchAll('SELECT title AS n FROM job_titles WHERE is_active = 1') as $r) {
                $add($r['n']);
            }
            foreach ($db->fetchAll('SELECT name AS n FROM job_categories WHERE is_active = 1') as $r) {
                $add($r['n']);
            }
            foreach ($db->fetchAll('SELECT name AS n FROM skills') as $r) {
                $add($r['n']);
            }
        } catch (\Throwable $e) {
            // DB optional – curated lists alone are enough.
        }

        $list = array_values($byKey);
        natcasesort($list);
        return array_values($list);
    }

    private static function cachePath(): string
    {
        return __DIR__ . '/../../../storage/cache/registration_categories.json';
    }
}
