<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;

/**
 * Category → subcategory → skill tree built from resources/data/categories/*.php.
 *  - Category = the file's "// Sector: …" header.
 *  - Subcategory = the "// Group" comment above the items; files without group comments are grouped
 *    by the role's last word ("… Driver", "… Promoter"); groups under MIN_GROUP items go to "Other".
 * Cached in storage/cache/skill_taxonomy.json (rebuilt when a category file changes).
 */
class SkillTaxonomy
{
    private const MIN_GROUP = 8;

    /** [['slug','name','subs' => [['slug','name','skills' => [...]]], 'count'], …] */
    public static function tree(): array
    {
        static $tree = null;
        if ($tree !== null) {
            return $tree;
        }
        $files = glob(dirname(__DIR__, 3) . '/resources/data/categories/*.php') ?: [];
        natsort($files);
        $stamp = md5(implode('|', array_map(static fn($f) => $f . filemtime($f), $files)));
        $cache = dirname(__DIR__, 3) . '/storage/cache/skill_taxonomy.json';
        if (is_file($cache) && ($c = json_decode((string)file_get_contents($cache), true)) && ($c['stamp'] ?? '') === $stamp) {
            return $tree = $c['tree'];
        }
        $tree = [];
        foreach ($files as $file) {
            $tree[] = self::parse($file);
        }
        @file_put_contents($cache, json_encode(['stamp' => $stamp, 'tree' => $tree], JSON_UNESCAPED_UNICODE));
        return $tree;
    }

    private static function parse(string $file): array
    {
        $sector = 'Other';
        $groups = [];
        $current = null;
        $hasGroups = false;
        foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $t = trim($line);
            if (preg_match('#^//\s*Sector:\s*(.+)$#i', $t, $m)) {
                $sector = trim($m[1]);
            } elseif (preg_match('#^//\s*(.+)$#', $t, $m)) {
                $current = trim($m[1], " -–:");
                $hasGroups = true;
            } elseif (preg_match("#^'((?:[^'\\\\]|\\\\.)+)',?#", $t, $m)) {
                $skill = stripcslashes($m[1]);
                $groups[$current ?? '_'][] = $skill;
            }
        }
        if (!$hasGroups) {
            // Group generated lists by the head noun ("Hospital Security Guard" → Guard).
            $byHead = [];
            foreach ($groups['_'] ?? [] as $skill) {
                $byHead[self::head($skill)][] = $skill;
            }
            $groups = [];
            foreach ($byHead as $head => $skills) {
                $groups[count($skills) >= self::MIN_GROUP ? $head : 'Other'] = array_merge($groups[count($skills) >= self::MIN_GROUP ? $head : 'Other'] ?? [], $skills);
            }
            uksort($groups, static fn($a, $b) => [$a === 'Other', -count($groups[$a])] <=> [$b === 'Other', -count($groups[$b])]);
        }
        $subs = [];
        $total = 0;
        foreach ($groups as $name => $skills) {
            $skills = array_values(array_unique($skills));
            $total += count($skills);
            $name = $name === '_' ? 'General' : $name;
            $subs[] = ['slug' => self::slug($name), 'name' => $name, 'skills' => $skills];
        }
        return ['slug' => self::slug(self::shortName($sector)), 'name' => $sector, 'short' => self::shortName($sector), 'subs' => $subs, 'count' => $total];
    }

    /** Role family of a title: "Hospital Security Guard (Night Shift)" → "Guard", "Driver Part Time" → "Driver". */
    private static function head(string $title): string
    {
        $t = trim((string)preg_replace('/\s*[\(\[].*$/', '', $title));
        // "Truck Driver 1 Ton - Long Route" → before the dash; "Assistant Manager - Civil Engineering" → after it.
        if (preg_match('/^(.+?)\s+[-–]\s+(.+)$/u', $t, $m)) {
            if (preg_match('/\b(manager|head|director|officer|lead|executive|associate|vp|president)$/i', trim($m[1]))) {
                return ucwords(trim($m[2]));
            }
            $t = trim($m[1]);
        }
        $t = trim((string)preg_replace(['/\s+for\s+.+$/i', '/\s+\d+(?:\.\d+)?\s*(?:tons?|tonnes?|wheelers?|seaters?|ft|feet|kl|mt)$/i'], '', $t));
        $variant ='/[\s\-–]+(?:on\s+)?(?:full[\s-]?time|part[\s-]?time|per\s+\w+|night\s+shift|day\s+shift|\w+\s+shift|shift|contract(?:ual)?|daily|hourly|weekly|monthly|commission|basis|freelance|remote|wfh|work\s+from\s+home|temporary|permanent|seasonal|route|time|trip|local|outstation|weekend)$/i';
        for ($i = 0; $i < 4; $i++) {
            $n = trim((string)preg_replace($variant, '', $t));
            if ($n === $t || $n === '') {
                break;
            }
            $t = $n;
        }
        $words = preg_split('/\s+/', $t) ?: [$t];
        $head = ucfirst(strtolower((string)end($words)));
        return in_array($head, ['Intern', 'Trainee', 'Apprentice', 'Fresher'], true) && count($words) > 1 ? 'Interns & Trainees' : $head;
    }

    /** "Construction, Civil Works, Trades, …" → "Construction, Civil Works & Trades" (first three). */
    private static function shortName(string $sector): string
    {
        $parts = array_map('trim', preg_split('/,|&| and /', $sector) ?: [$sector]);
        $parts = array_values(array_filter($parts));
        return count($parts) > 3 ? implode(', ', array_slice($parts, 0, 2)) . ' & ' . $parts[2] : $sector;
    }

    public static function slug(string $s): string
    {
        return trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-') ?: 'other';
    }

    public static function find(string $sectorSlug, ?string $subSlug = null): ?array
    {
        foreach (self::tree() as $sector) {
            if ($sector['slug'] === $sectorSlug) {
                if ($subSlug === null) {
                    return $sector;
                }
                foreach ($sector['subs'] as $sub) {
                    if ($sub['slug'] === $subSlug) {
                        return ['sector' => $sector, 'sub' => $sub];
                    }
                }
                return null;
            }
        }
        return null;
    }

    public static function totalSkills(): int
    {
        return array_sum(array_column(self::tree(), 'count'));
    }

    /** Near Me professions grouped for the catalogue: group => [profession keys]. */
    public const NEAR_ME_GROUPS = [
        'Home Repair & Construction' => ['plumber', 'electrician', 'carpenter', 'welder', 'painter', 'mason', 'tile_fitter', 'pop_ceiling', 'waterproofing', 'fabricator', 'aluminium_glass', 'interior', 'furniture_repair', 'curtain_blinds', 'locksmith', 'borewell', 'water_tank', 'motor_pump'],
        'Appliance & Electronics Repair' => ['ac_mechanic', 'fridge_mechanic', 'washing_machine', 'ro_technician', 'geyser_repair', 'tv_repair', 'mobile_repair', 'computer_repair', 'cctv', 'inverter_battery', 'solar'],
        'Cleaning & Household Help' => ['pest_control', 'deep_cleaning', 'sofa_carpet', 'laundry', 'ironing', 'cook', 'maid', 'baby_sitter', 'gardener', 'security_guard', 'packers_movers'],
        'Tailoring, Shoes & Jewellery' => ['tailor', 'gents_tailor', 'ladies_tailor', 'cobbler', 'goldsmith'],
        'Vehicles & Transport' => ['driver', 'driver_on_call', 'car_mechanic', 'bike_mechanic', 'car_wash', 'courier'],
        'Beauty, Salon & Wellness' => ['hair_dresser', 'salon', 'beautician', 'beauty_parlour', 'makeup_artist', 'mehendi', 'massage_spa', 'yoga_fitness'],
        'Health & Care at Home' => ['elder_care', 'home_nurse', 'physiotherapist', 'lab_test_home', 'pet_care', 'vet'],
        'Teaching, Events & Professional' => ['home_tutor', 'music_dance', 'photographer', 'caterer', 'tent_decorator', 'dj_sound', 'event_helper', 'pandit', 'ca_tax', 'advocate', 'property_dealer'],
    ];

    /** Near Me catalogue in the same shape as tree(): one category, groups as subcategories. */
    public static function nearMeTree(): array
    {
        $subs = [];
        $total = 0;
        foreach (self::NEAR_ME_GROUPS as $group => $keys) {
            $skills = array_values(array_filter(array_map(static fn($k) => FormRegistry::NEAR_ME_PROFESSIONS[$k][1] ?? null, $keys)));
            $total += count($skills);
            $subs[] = ['slug' => self::slug($group), 'name' => $group, 'skills' => $skills];
        }
        return [['slug' => 'near-me-services', 'name' => 'Near Me Services', 'short' => 'Near Me Services', 'subs' => $subs, 'count' => $total]];
    }

    /**
     * Live numbers per category name (lower-case) for a catalogue mode:
     * 'want' = paid, open seekers choosing it; 'offer' = verified providers / open jobs offering it.
     */
    public static function live(string $mode = 'skills'): array
    {
        static $cache = [];
        if (isset($cache[$mode])) {
            return $cache[$mode];
        }
        $live = [];
        $open = "r.payment_status = 'paid' AND r.status NOT IN ('rejected','completed','expired') AND (r.valid_until IS NULL OR r.valid_until > NOW())";
        $sql = match ($mode) {
            'internships' => [
                'want' => "SELECT r.categories AS c FROM portal_registrations r WHERE r.type = 'internship' AND $open
                           AND NOT EXISTS (SELECT 1 FROM mentor_assignments a WHERE a.candidate_reg_id = r.id AND a.status = 'accepted')",
                'offer' => "SELECT categories AS c FROM portal_registrations WHERE type = 'internpro' AND payment_status = 'paid' AND status = 'selected'",
            ],
            'jobs' => [
                'want' => "SELECT r.categories AS c FROM portal_registrations r WHERE r.type IN ('fulltime','parttime','wfh','hospitality','healthcare') AND $open",
                'offer' => "SELECT title AS c FROM jobs WHERE status = 'published'
                            UNION ALL SELECT categories AS c FROM portal_registrations WHERE type = 'jobpro' AND payment_status = 'paid' AND status = 'selected'",
            ],
            'near-me' => [
                'want' => "SELECT '' AS c FROM DUAL WHERE 0",
                'offer' => "SELECT r.categories AS c FROM portal_registrations r WHERE r.type = 'nearpro' AND r.status = 'selected' AND $open",
            ],
            default => [
                'want' => "SELECT r.categories AS c FROM portal_registrations r WHERE r.type = 'skill' AND $open
                           AND NOT EXISTS (SELECT 1 FROM mentor_assignments a WHERE a.candidate_reg_id = r.id AND a.status = 'accepted')",
                'offer' => "SELECT categories AS c FROM portal_registrations WHERE type = 'provider' AND payment_status = 'paid' AND status = 'selected'",
            ],
        };
        try {
            Mentoring::ensureSchema();
            $db = Database::getInstance();
            foreach ($sql as $k => $q) {
                foreach ($db->fetchAll($q) as $r) {
                    foreach (array_filter(array_map('trim', explode(',', (string)$r['c']))) as $c) {
                        $key = mb_strtolower($c);
                        $live[$key][$k] = ($live[$key][$k] ?? 0) + 1;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('SkillTaxonomy::live: ' . $e->getMessage());
        }
        return $cache[$mode] = $live;
    }

    /** Sum of live numbers over a list of category names. */
    public static function sum(array $skills, string $mode = 'skills'): array
    {
        $live = self::live($mode);
        $out = ['want' => 0, 'offer' => 0];
        foreach ($skills as $s) {
            $l = $live[mb_strtolower($s)] ?? null;
            if ($l) {
                $out['want'] += $l['want'] ?? 0;
                $out['offer'] += $l['offer'] ?? 0;
            }
        }
        return $out;
    }
}
