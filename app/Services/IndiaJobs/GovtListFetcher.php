<?php

declare(strict_types=1);

namespace App\Services\IndiaJobs;

use App\Core\Database;

/**
 * State recruitment boards that publish a plain "title + date + PDF" list (no feed): one reader, one profile
 * per official page (keyed by its URL = the source's feed_url, feed_type 'govtlist'). Profiles live in
 * PROFILES below and in resources/data/govt-boards/*.php; a row may also capture the closing date as `last`.
 *
 * A profile gives the row pattern (named groups title / date / pdf / link / section), the date format,
 * keywords that keep only recruitment items (results, admit cards, answer keys are dropped) and, where the
 * list has no dates, a detail page pattern. Pages without a closing date never get an invented one:
 * only items posted in the last MAX_AGE_DAYS are imported, and they are hidden MAX_AGE_DAYS after posting.
 */
class GovtListFetcher
{
    public const MAX_AGE_DAYS = 120;
    private const DETAIL_PAGES_PER_RUN = 6;

    /** feed_url => profile. Sources are seeded from these (see ExternalJob::ensureSchema). */
    public const PROFILES = [
        'https://dtc.delhi.gov.in/recruitment' => [
            'name' => 'Delhi Transport Corporation (DTC)', 'org_type' => 'state_govt', 'state' => 'Delhi', 'location' => 'Delhi', 'website' => 'https://dtc.delhi.gov.in',
            'row' => '#<li>\s*<div class="notification-view">\s*<div class="tab-title">(?<title>.*?)</div>\s*<div class="tab-date">\s*Date:\s*(?<date>\d{1,2}-\d{1,2}-\d{4}).*?href="(?<pdf>[^"]+\.pdf)"#is',
            'date' => 'd-m-Y',
            'apply' => null,
            'note' => 'DTC posts are mostly on contract or deputation – read the advertisement for eligibility, last date and how to apply.',
        ],
        'http://sssb.punjab.gov.in/vacancy-group-b/' => [
            'name' => 'Punjab Subordinate Services Selection Board (PSSSB) – Group B', 'org_type' => 'state_govt', 'state' => 'Punjab', 'website' => 'https://sssb.punjab.gov.in',
            'row' => '#<tr>\s*<td>\d+</td>\s*<td>\s*<a href="(?<pdf>[^"]+\.pdf)"[^>]*>(?<title>.*?)</a>.*?href="(?<link>[^"]*vacancy/\?key=[^"]+)"#is',
            'detail' => 'psssb', 'apply' => 'https://sssb.punjab.gov.in', 'force_http' => true,
        ],
        'http://sssb.punjab.gov.in/vacancy-group-c/' => [
            'name' => 'Punjab Subordinate Services Selection Board (PSSSB) – Group C', 'org_type' => 'state_govt', 'state' => 'Punjab', 'website' => 'https://sssb.punjab.gov.in',
            'row' => '#<tr>\s*<td>\d+</td>\s*<td>\s*<a href="(?<pdf>[^"]+\.pdf)"[^>]*>(?<title>.*?)</a>.*?href="(?<link>[^"]*vacancy/\?key=[^"]+)"#is',
            'detail' => 'psssb', 'apply' => 'https://sssb.punjab.gov.in', 'force_http' => true,
        ],
        'http://sssb.punjab.gov.in/vacancy-group-d/' => [
            'name' => 'Punjab Subordinate Services Selection Board (PSSSB) – Group D', 'org_type' => 'state_govt', 'state' => 'Punjab', 'website' => 'https://sssb.punjab.gov.in',
            'row' => '#<tr>\s*<td>\d+</td>\s*<td>\s*<a href="(?<pdf>[^"]+\.pdf)"[^>]*>(?<title>.*?)</a>.*?href="(?<link>[^"]*vacancy/\?key=[^"]+)"#is',
            'detail' => 'psssb', 'apply' => 'https://sssb.punjab.gov.in', 'force_http' => true,
        ],
        'https://hssc.gov.in/advertisement' => [
            'name' => 'Haryana Staff Selection Commission (HSSC)', 'org_type' => 'state_govt', 'state' => 'Haryana', 'website' => 'https://hssc.gov.in',
            'row' => '#<td class="content-text p-2">(?<title>.*?)</td>\s*<td class="content-text p-2 text-center">(?<date>\d{4}-\d{2}-\d{2})</td>.*?href="(?<pdf>https://hssc\.gov\.in/file/[^"]+)"#is',
            'date' => 'Y-m-d', 'apply' => 'https://hssc.gov.in', 'title_prefix' => 'HSSC ',
        ],
        'https://sssc.uk.gov.in/recruitment-notification/' => [
            'name' => 'Uttarakhand Subordinate Service Selection Commission (UKSSSC)', 'org_type' => 'state_govt', 'state' => 'Uttarakhand', 'website' => 'https://sssc.uk.gov.in',
            'row' => '#<td role="rowheader"[^>]*>(?<title>.*?)</td>\s*<td>(?<date>\d{2}/\d{2}/\d{4})</td>.*?href="(?<pdf>[^"]+\.pdf)"#is',
            'date' => 'd/m/Y',
            'keep' => '/विज्ञापन|विज्ञप्ति|सीधी भर्ती|advertisement|recruitment/iu', 'drop' => '/प्रवेश पत्र|admit|परिणाम|result|उत्तर कुंजी|answer key|चयन सूची|अभिलेख सत्यापन|साक्षात्कार|परीक्षा कार्यक्रम/iu',
            'apply' => 'https://sssc.uk.gov.in',
            'clean' => ['/^\s*पदनाम\s*[-–:]\s*/u', '/\s*(?:हेतु|के लिए)?\s*क्लिक करें\s*$/u'],
        ],
        'https://psc.uk.gov.in/' => [
            'name' => 'Uttarakhand Public Service Commission (UKPSC)', 'org_type' => 'state_govt', 'state' => 'Uttarakhand', 'website' => 'https://psc.uk.gov.in',
            'row' => '#<li>\s*<a href="(?<link>[^"]+)">.*?<span class="announcement-date">(?<date>\d{2}-\d{2}-\d{4})</span>(?<title>.*?)<span class="under-section">\(\s*(?<section>[^)]*)\)</span>#is',
            'date' => 'd-m-Y',
            'keep' => '/notification|advertisement/i', 'drop' => '/result|answer key|admit|interview|marks|cut.?off/i', 'section' => '/Recruitment Notification/i',
            'apply' => 'https://psc.uk.gov.in/candidate-corner/recruitment',
        ],
        'https://ukmssb.org/' => [
            'name' => 'Uttarakhand Medical Service Selection Board (UKMSSB)', 'org_type' => 'state_govt', 'state' => 'Uttarakhand', 'website' => 'https://ukmssb.org',
            'row' => '#<tr>\s*<td[^>]*>(?<date>\d{2}-\d{2}-\d{4})</td>\s*<td>\s*<a href="(?<pdf>https://ukmssb\.org/[^"]+\.pdf)"[^>]*>(?<title>.*?)</a>#is',
            'date' => 'd-m-Y',
            'keep' => '/विज्ञप्ति|विज्ञापन|advertisement|advt|recruitment/iu', 'drop' => '/परिणाम|result|उत्तर कुंजी|answer key|प्रवेश पत्र|admit|चयन सूची|साक्षात्कार|interview/iu',
            'apply' => 'https://ukmssb.org',
        ],
    ];

    /** Built-in profiles + every resources/data/govt-boards/*.php file (one per region, same keys). */
    public static function profiles(): array
    {
        static $all = null;
        if ($all === null) {
            $all = self::PROFILES;
            foreach (glob(dirname(__DIR__, 3) . '/resources/data/govt-boards/*.php') ?: [] as $file) {
                $more = require $file;
                if (is_array($more)) {
                    $all += $more;
                }
            }
        }
        return $all;
    }

    public static function profile(string $url): ?array
    {
        return self::profiles()[$url] ?? null;
    }

    /** Listings from one official page. */
    public static function listings(string $html, string $pageUrl): array
    {
        $p = self::profile($pageUrl);
        if (!$p || !preg_match_all($p['row'], $html, $rows, PREG_SET_ORDER)) {
            return [];
        }
        $items = [];
        $budget = self::DETAIL_PAGES_PER_RUN;
        $cutoff = strtotime('-' . self::MAX_AGE_DAYS . ' days');
        foreach ($rows as $r) {
            $title = self::text($r['title'] ?? '');
            foreach ($p['clean'] ?? [] as $re) {
                $title = trim((string)preg_replace($re, '', $title));
            }
            // Tidy titles taken from file names or repeated labels: "MICRO_BIOLOGIST" → "MICRO BIOLOGIST",
            // "Goa Rehabilitation Board Goa Rehabilitation Board …" → "Goa Rehabilitation Board …".
            $title = trim((string)preg_replace(['/_+/', '/\s{2,}/u'], ' ', $title));
            $title = (string)preg_replace('/^(.{6,}?)\s+(?=\s|$)/u', '$1', $title);
            if ($title === '' || mb_strlen($title) < 6) {
                continue;
            }
            if (!empty($p['section']) && !preg_match($p['section'], (string)($r['section'] ?? ''))) {
                continue;
            }
            if ((!empty($p['keep']) && !preg_match($p['keep'], $title)) || (!empty($p['drop']) && preg_match($p['drop'], $title))) {
                continue; // results, admit cards, answer keys … are not openings
            }
            $pdf = !empty($r['pdf']) ? self::abs(html_entity_decode($r['pdf']), $pageUrl) : null;
            $link = !empty($r['link']) ? self::abs(html_entity_decode($r['link']), $pageUrl) : null;
            $fetchLink = $link && !empty($p['force_http']) ? (string)preg_replace('#^https://#i', 'http://', $link) : $link;
            $posted = !empty($r['date']) ? self::date((string)$r['date'], (string)($p['date'] ?? 'd-m-Y')) : null;
            $start = $end = $applyUrl = null;
            if (($p['detail'] ?? '') === 'psssb' && $link) {
                $d = self::psssbDetail((string)$fetchLink, $budget);
                $posted = $d['published'] ?? $posted ?? self::monthFromPath((string)$pdf);
                [$start, $end] = [$d['start'] ?? null, $d['end'] ?? null];
                $applyUrl = $d['apply'] ?? null;
            }
            if ($end === null && !empty($r['last'])) {
                $end = self::date((string)$r['last'], (string)($p['last_format'] ?? $p['last_date'] ?? $p['date'] ?? 'd-m-Y')); // closing date in the row
            }
            if ($posted === null && $pdf) {
                $posted = self::monthFromPath($pdf); // only a closing date shown: posting month from the upload folder
            }
            $last = $end;
            if ($last !== null ? strtotime($last) < strtotime('today') : ($posted === null || strtotime($posted) < $cutoff)) {
                continue; // closed, or too old to still be open
            }
            $source = !empty($p['link_to_home']) ? $pageUrl : ($pdf ?? $link ?? $pageUrl);
            $lines = array_filter([
                $title,
                'Organisation: ' . $p['name'],
                !empty($p['state']) ? 'State: ' . $p['state'] : 'Location: All India',
                $posted ? 'Posted on: ' . date('d M Y', strtotime($posted)) : '',
                $start ? 'Apply from: ' . date('d M Y', strtotime($start)) : '',
                $end ? 'Last date: ' . date('d M Y', strtotime($end)) : 'Last date: see the official notification',
                $p['note'] ?? '',
                'Read the official notification before applying. Apply only on the official website' . (!empty($p['apply']) ? ' (' . parse_url((string)$p['apply'], PHP_URL_HOST) . ')' : '') . '.',
            ]);
            $items[] = [
                'title' => mb_substr((string)($p['title_prefix'] ?? '') . $title, 0, 255),
                'org_name' => $p['name'],
                'org_type' => $p['org_type'],
                'state' => $p['state'] ?? null, // none = All India (e.g. PSUs hiring across the country)
                'location' => $p['location'] ?? null,
                'last_date' => $end,
                'published_at' => $posted ? $posted . ' 09:00:00' : null,
                'summary' => mb_substr($title . ' – ' . $p['name'] . '.' . ($end ? ' Last date ' . date('d M Y', strtotime($end)) . '.' : ''), 0, 1000),
                'details' => implode("\n", $lines),
                'source_url' => $source,
                'apply_url' => $applyUrl ?? $p['apply'] ?? $source,
                'guid_hash' => sha1('govtlist|' . $pageUrl . '|' . ($pdf ?? $link ?? '') . '|' . mb_strtolower($title)),
            ];
        }
        return $items;
    }

    /** Hide this source's listings that have no closing date once they are MAX_AGE_DAYS old. */
    public static function retire(int $sourceId): void
    {
        Database::getInstance()->execute(
            'UPDATE external_jobs SET is_active = 0 WHERE source_id = ? AND is_active = 1 AND last_date IS NULL AND published_at < NOW() - INTERVAL ' . self::MAX_AGE_DAYS . ' DAY',
            [$sourceId]
        );
    }

    /** PSSSB vacancy detail page → published / start / end dates (cached; new pages within the run budget). */
    private static function psssbDetail(string $url, int &$budget): array
    {
        $cache = dirname(__DIR__, 3) . '/storage/cache/govtlist/psssb_' . sha1($url) . '.json';
        if (is_file($cache) && is_array($c = json_decode((string)file_get_contents($cache), true))) {
            return $c;
        }
        if ($budget-- <= 0) {
            return [];
        }
        [$code, $html] = FeedFetcher::get($url, 1_000_000);
        if ($code !== 200) {
            return [];
        }
        $out = [];
        if (preg_match_all('#<td class="opening-date">\s*([^<]+?)\s*</td>#i', $html, $m)) {
            $cells = $m[1];
            $out['published'] = isset($cells[0]) && strtotime($cells[0]) ? date('Y-m-d', strtotime($cells[0])) : null;
            $out['start'] = isset($cells[1]) ? self::date($cells[1], 'd-m-Y') : null;
            $out['end'] = isset($cells[2]) ? self::date($cells[2], 'd-m-Y') : null;
        }
        if (preg_match('#href="(https://eservices\.punjab\.gov\.in/[^"]+)"[^>]*>\s*Apply Online#i', $html, $a)) {
            $out['apply'] = html_entity_decode($a[1]);
        }
        @mkdir(dirname($cache), 0775, true);
        @file_put_contents($cache, json_encode($out));
        return $out;
    }

    private static function date(string $s, string $format): ?string
    {
        $s = trim((string)preg_replace('/\s+/', ' ', strip_tags($s)));
        $d = \DateTime::createFromFormat('!' . $format, $s);
        if (!$d) {
            // tolerate a trailing time or text after the date ("19-10-2026 05:00 PM")
            foreach ([$format . ' H:i', $format . ' H:i:s', $format . ' h:i A'] as $f) {
                if ($d = \DateTime::createFromFormat('!' . $f, $s)) {
                    break;
                }
            }
        }
        return $d && (int)$d->format('Y') >= 2000 ? $d->format('Y-m-d') : null;
    }

    /** ".../uploads/2026/09/x.pdf" → 2026-09-01 (approximate posting month). */
    private static function monthFromPath(string $url): ?string
    {
        return preg_match('#/uploads/(20\d\d)/(\d\d)/#', $url, $m) ? $m[1] . '-' . $m[2] . '-01' : null;
    }

    private static function text(string $html): string
    {
        return trim((string)preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function abs(string $href, string $pageUrl): string
    {
        $href = trim($href);
        if (!preg_match('#^https?://#i', $href)) {
            $base = parse_url($pageUrl, PHP_URL_SCHEME) . '://' . parse_url($pageUrl, PHP_URL_HOST);
            $href = $base . '/' . ltrim($href, '/');
        }
        return str_replace(' ', '%20', $href);
    }
}
