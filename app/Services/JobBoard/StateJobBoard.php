<?php

declare(strict_types=1);

namespace App\Services\JobBoard;

use App\Controllers\Front\NearMeController;
use App\Core\Database;
use App\Models\ExternalJob;
use App\Models\FreeJobPost;

/**
 * All jobs by state and city, A–Z (/jobs-by-state): free posts by companies, jobs posted by Jobsence
 * employers and Jobs in India listings (Govt / PSU). Each job: title, company, state, city, posted date
 * & time, link and source. Jobs without a known state are grouped under ALL_INDIA.
 */
class StateJobBoard
{
    public const ALL_INDIA = 'All India / Central Govt';

    /** @return array<int, array{title:string, company:string, state:string, city:string, posted:?string, url:string, source:string, last_date:?string}> */
    public static function all(): array
    {
        static $jobs = null;
        if ($jobs !== null) {
            return $jobs;
        }
        $jobs = [];
        foreach (FreeJobPost::live() as $p) {
            $jobs[] = ['title' => $p['title'], 'company' => $p['company_name'], 'state' => self::stateName((string)$p['state']) ?? self::ALL_INDIA,
                'city' => (string)$p['city'], 'posted' => $p['published_at'], 'url' => FreeJobPost::url($p), 'source' => 'free', 'last_date' => null];
        }
        try {
            $rows = Database::getInstance()->fetchAll(
                "SELECT title, slug, company_name, locations, COALESCE(publish_at, created_at) AS posted FROM jobs
                 WHERE status = 'published' AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY posted DESC LIMIT 2000"
            );
        } catch (\Throwable $e) {
            $rows = [];
        }
        foreach ($rows as $r) {
            $loc = json_decode((string)$r['locations'], true);
            $loc = is_array($loc) && isset($loc[0]) && is_array($loc[0]) ? $loc[0] : [];
            [$state, $city] = self::place((string)($loc['state'] ?? ''), (string)($loc['city'] ?? ''));
            $jobs[] = ['title' => $r['title'], 'company' => (string)($r['company_name'] ?: 'Company'), 'state' => $state, 'city' => $city,
                'posted' => $r['posted'], 'url' => '/job/' . $r['slug'], 'source' => 'jobsence', 'last_date' => null];
        }
        try {
            $rows = Database::getInstance()->fetchAll(
                "SELECT id, slug, title, org_name, state, location, published_at, last_date FROM external_jobs
                 WHERE is_active = 1 AND (last_date IS NULL OR last_date >= CURDATE()) ORDER BY published_at DESC LIMIT 3000"
            );
        } catch (\Throwable $e) {
            $rows = [];
        }
        foreach ($rows as $r) {
            [$state, $city] = self::place((string)$r['state'], (string)$r['location']);
            $jobs[] = ['title' => $r['title'], 'company' => $r['org_name'], 'state' => $state, 'city' => $city,
                'posted' => $r['published_at'], 'url' => ExternalJob::url($r), 'source' => 'govt', 'last_date' => $r['last_date']];
        }
        return $jobs;
    }

    /** state => ['count' => n, 'cities' => [city => [jobs…]]], states A–Z (All India first), cities A–Z, jobs newest first. */
    public static function grouped(?string $onlyState = null): array
    {
        $out = [];
        foreach (self::all() as $j) {
            if ($onlyState !== null && $j['state'] !== $onlyState) {
                continue;
            }
            $city = $j['city'] !== '' ? $j['city'] : ($j['state'] === self::ALL_INDIA ? 'Anywhere in India' : 'Whole state');
            $out[$j['state']]['cities'][$city][] = $j;
            $out[$j['state']]['count'] = ($out[$j['state']]['count'] ?? 0) + 1;
        }
        uksort($out, static fn($a, $b) => $a === self::ALL_INDIA ? -1 : ($b === self::ALL_INDIA ? 1 : strcasecmp($a, $b)));
        foreach ($out as &$s) {
            ksort($s['cities'], SORT_NATURAL | SORT_FLAG_CASE);
            foreach ($s['cities'] as &$list) {
                usort($list, static fn($x, $y) => strcmp((string)$y['posted'], (string)$x['posted']));
            }
        }
        return $out;
    }

    /** Newest jobs across India. */
    public static function latest(int $n = 30): array
    {
        $all = self::all();
        usort($all, static fn($x, $y) => strcmp((string)$y['posted'], (string)$x['posted']));
        return array_slice($all, 0, $n);
    }

    /** All states / UTs with their districts + towns (for the post form). */
    public static function states(): array
    {
        $out = [];
        foreach (NearMeController::places() as $state => $p) {
            $cities = array_values(array_unique(array_merge($p['districts'] ?? [], $p['towns'] ?? [])));
            sort($cities, SORT_NATURAL | SORT_FLAG_CASE);
            $out[$state] = $cities;
        }
        ksort($out);
        return $out;
    }

    public static function slug(string $state): string
    {
        return strtolower(trim((string)preg_replace('/[^A-Za-z0-9]+/', '-', $state), '-'));
    }

    public static function stateFromSlug(string $slug): ?string
    {
        if ($slug === self::slug(self::ALL_INDIA)) {
            return self::ALL_INDIA;
        }
        foreach (array_keys(self::states()) as $s) {
            if (self::slug($s) === $slug) {
                return $s;
            }
        }
        return null;
    }

    /** Canonical state name for free text ("delhi", "UP", "Uttar Pradesh"), or null. */
    public static function stateName(string $s): ?string
    {
        $s = trim($s);
        if ($s === '') {
            return null;
        }
        static $alias = ['up' => 'Uttar Pradesh', 'mp' => 'Madhya Pradesh', 'hp' => 'Himachal Pradesh', 'j&k' => 'Jammu and Kashmir', 'jk' => 'Jammu and Kashmir',
            'new delhi' => 'Delhi', 'nct of delhi' => 'Delhi', 'orissa' => 'Odisha', 'uttaranchal' => 'Uttarakhand', 'tn' => 'Tamil Nadu', 'wb' => 'West Bengal'];
        $low = strtolower($s);
        foreach (array_keys(self::states()) as $state) {
            if (strtolower($state) === $low) {
                return $state;
            }
        }
        if (isset($alias[$low]) && isset(self::states()[$alias[$low]])) {
            return $alias[$low];
        }
        return self::stateIn($s);
    }

    /** [state, city] from a state field and free location text (e.g. "Kanpur, UP", "HQ is in Nagpur (Maharashtra)"). */
    private static function place(string $state, string $location): array
    {
        $st = self::stateName($state) ?? self::stateIn($location);
        $city = self::cityIn($location, $st);
        if ($st === null && $city !== null) {
            $st = self::cityState()[strtolower($city)] ?? null;
        }
        if ($st === null) {
            return [self::ALL_INDIA, ''];
        }
        return [$st, $city ?? ($st === 'Delhi' ? 'Delhi' : '')];
    }

    /** First state / UT name (or common short form) mentioned in a text. */
    private static function stateIn(string $text): ?string
    {
        if ($text === '') {
            return null;
        }
        foreach (array_keys(self::states()) as $state) {
            if (preg_match('/\b' . preg_quote($state, '/') . '\b/i', $text)) {
                return $state;
            }
        }
        if (preg_match('/\b(?:New Delhi|Delhi)\b/i', $text)) {
            return 'Delhi';
        }
        if (preg_match('/,\s*(UP|MP|HP)\b/', $text, $m)) {
            return ['UP' => 'Uttar Pradesh', 'MP' => 'Madhya Pradesh', 'HP' => 'Himachal Pradesh'][$m[1]];
        }
        return null;
    }

    /** A known district / town named in the text (within $state when known). */
    private static function cityIn(string $text, ?string $state): ?string
    {
        if ($text === '') {
            return null;
        }
        $first = trim((string)preg_split('/[,(]/', $text)[0]);
        $candidates = $state !== null ? (self::states()[$state] ?? []) : array_keys(self::cityState());
        foreach ($candidates as $c) {
            if (strcasecmp($c, $first) === 0) {
                return $state !== null ? $c : (self::cityProper()[strtolower($c)] ?? $c);
            }
        }
        foreach ($candidates as $c) {
            if (mb_strlen($c) >= 4 && preg_match('/\b' . preg_quote($c, '/') . '\b/i', $text)) {
                return $state !== null ? $c : (self::cityProper()[strtolower($c)] ?? $c);
            }
        }
        return null;
    }

    /** lower-case city => state (cities that exist in only one state). */
    private static function cityState(): array
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            $dupe = [];
            foreach (self::states() as $state => $cities) {
                foreach ($cities as $c) {
                    $k = strtolower($c);
                    if (isset($map[$k]) && $map[$k] !== $state) {
                        $dupe[$k] = true;
                    }
                    $map[$k] = $state;
                }
            }
            $map = array_diff_key($map, $dupe);
        }
        return $map;
    }

    private static function cityProper(): array
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            foreach (self::states() as $cities) {
                foreach ($cities as $c) {
                    $map[strtolower($c)] = $c;
                }
            }
        }
        return $map;
    }
}
