<?php

declare(strict_types=1);

namespace App\Services\IndiaJobs;

/**
 * ICSIL – Intelligent Communication Systems India Ltd (JV of DSIIDC, Govt of NCT of Delhi, and TCIL) –
 * "Current Jobs" table at icsil.in/requirement-careers, read hourly with the other Jobs in India sources.
 * One listing per Job ID: title, advertisement PDF, start / end date-time; qualification, minimum marks,
 * experience and age come from each job's detail page (read once, cached). Applications go to
 * icsil.in/app only; ICSIL itself charges applicants a One Time Registration fee (stated on its page).
 */
class IcsilFetcher
{
    private const BASE = 'https://www.icsil.in';
    private const APPLY = 'https://icsil.in/app/';
    private const ORG = 'Intelligent Communication Systems India Ltd (ICSIL)';
    /** Detail pages fetched per run (only for jobs not seen before). */
    private const NEW_PAGES_PER_RUN = 10;

    /** Listings from the Current Jobs page. */
    public static function listings(string $html, string $pageUrl): array
    {
        // Columns ICSIL has hidden are left as HTML comments – drop them first.
        $html = (string)preg_replace('/<!--.*?-->/s', '', $html);
        $otr = preg_match('/One Time Registration Fee of Rs\.?\s*([\d,]+)/i', $html, $m) ? (int)str_replace(',', '', $m[1]) : null;
        if (!preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $html, $rows)) {
            return [];
        }
        $items = [];
        $budget = self::NEW_PAGES_PER_RUN;
        foreach ($rows[1] as $row) {
            if (!preg_match_all('#<td[^>]*>(.*?)</td>#is', $row, $c) || count($c[1]) < 8) {
                continue; // header or layout rows
            }
            $cells = $c[1];
            $jobId = trim(strip_tags($cells[1]));
            $title = self::text($cells[2]);
            if (!ctype_digit($jobId) || $title === '') {
                continue;
            }
            // [0] Sr, [1] Job ID, [2] title, [3] advertisement, [4] corrigendum, [5] start, [6] end, [7] eligibility, [8] apply
            $advt = preg_match('#href="([^"]+\.pdf)"#i', $cells[3], $a) ? self::abs(html_entity_decode($a[1])) : null;
            $corr = preg_match('#href="([^"]+\.pdf)"#i', $cells[4], $a) ? self::abs(html_entity_decode($a[1])) : null;
            [$startDate, $startTime] = self::dateTime(self::text($cells[5]));
            [$endDate, $endTime] = self::dateTime(self::text($cells[6]));
            $detailUrl = preg_match('#href="([^"]*requirement-careers-detail/\d+)"#i', $cells[7], $d) ? self::abs(html_entity_decode($d[1])) : self::BASE . '/requirement-careers-detail/' . $jobId;

            $detail = self::detail($jobId, $detailUrl, $budget);
            $contract = stripos($title, 'contract') !== false || stripos($title, 'outsourc') !== false;
            $lines = array_filter([
                'ICSIL Job ID ' . $jobId . ': ' . $title,
                $detail['qualification'] ? 'Qualification: ' . $detail['qualification'] : '',
                $detail['experience'] ? 'Experience required: ' . $detail['experience'] : '',
                $detail['age'] ? 'Age: ' . $detail['age'] : '',
                'Location: Delhi',
                $startDate ? 'Apply from: ' . date('d M Y', strtotime($startDate)) . ($startTime ? ', ' . $startTime : '') : '',
                $endDate ? 'Last date: ' . date('d M Y', strtotime($endDate)) . ($endTime ? ', ' . $endTime : '') : '',
                $corr ? 'A corrigendum has been issued – read it with the advertisement.' : '',
                $contract ? 'Engagement: purely contractual / outsourced (not a regular Government post).' : '',
                $otr ? 'ICSIL charges applicants a One Time Registration (OTR) fee of ₹' . number_format($otr) . ' on its own portal – pay it at least 2 days before the closing date.' : '',
                'Read the advertisement PDF before applying. Apply only at icsil.in/app.',
            ]);
            $items[] = [
                'title' => mb_substr($title, 0, 255),
                'org_name' => self::ORG,
                'org_type' => 'psu',
                'location' => 'Delhi',
                'state' => 'Delhi',
                'qualification' => $detail['qualification'] ? mb_substr($detail['qualification'], 0, 250) : null,
                'last_date' => $endDate,
                'published_at' => $startDate ? $startDate . ' ' . ($startTime ?: '09:00') . ':00' : null,
                'summary' => mb_substr($title . ' at ICSIL, Delhi' . ($detail['qualification'] ? ' – ' . $detail['qualification'] : '')
                    . ($endDate ? '. Apply online at icsil.in/app by ' . date('d M Y', strtotime($endDate)) . ($endTime ? ' (' . $endTime . ')' : '') : '') . '.', 0, 1000),
                'details' => implode("\n", $lines),
                'source_url' => $advt ?? $pageUrl,
                'apply_url' => self::APPLY,
                'guid_hash' => sha1('icsil|job|' . $jobId),
            ];
        }
        return $items;
    }

    /** Qualification / experience / age from the job's detail page (cached; new pages within the run budget). */
    private static function detail(string $jobId, string $url, int &$budget): array
    {
        $empty = ['qualification' => '', 'experience' => '', 'age' => ''];
        $cache = self::root() . '/storage/cache/icsil/job_' . $jobId . '.json';
        if (is_file($cache) && is_array($c = json_decode((string)file_get_contents($cache), true))) {
            return $c + $empty;
        }
        if ($budget-- <= 0) {
            return $empty; // read next hour; the listing is completed then
        }
        [$code, $html] = FeedFetcher::get($url, 1_000_000);
        if ($code !== 200) {
            return $empty;
        }
        $i = strpos($html, 'id="main-content"');
        $html = (string)preg_replace('/<!--.*?-->/s', '', $i !== false ? substr($html, $i) : $html);
        $quals = [];
        if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $html, $rows)) {
            foreach ($rows[1] as $row) {
                if (preg_match_all('#<td[^>]*>(.*?)</td>#is', $row, $c) && count($c[1]) >= 2) {
                    $q = self::text($c[1][1]);
                    $pct = isset($c[1][2]) ? self::text($c[1][2]) : '';
                    if ($q !== '') {
                        $quals[] = $q . ($pct !== '' && strcasecmp($pct, 'Pass Marks') !== 0 ? ' (min ' . $pct . ')' : '');
                    }
                }
            }
        }
        $text = self::text($html);
        $out = [
            'qualification' => implode(' / ', array_unique($quals)),
            'experience' => preg_match('/Experience Required\s*:\s*([^|]{1,60}?)(?=\s+Age|\s*$)/i', $text, $m) ? trim($m[1]) : '',
            'age' => trim((preg_match('/Age\s*\(Min\)\s*:\s*(\d+)/i', $text, $a) ? $a[1] : '')
                . (preg_match('/Age\s*\(Max\)\s*:\s*(\d+)/i', $text, $b) ? '–' . $b[1] : ''), '–') . ((isset($a[1]) || isset($b[1])) ? ' years' : ''),
        ];
        @mkdir(dirname($cache), 0775, true);
        @file_put_contents($cache, json_encode($out, JSON_UNESCAPED_UNICODE));
        return $out;
    }

    /** "05-10-2026 13:00:00" → ['2026-10-05', '1:00 PM']. */
    private static function dateTime(string $s): array
    {
        if (!preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})(?:\s+(\d{1,2}):(\d{2}))?/', $s, $m) || !checkdate((int)$m[2], (int)$m[1], (int)$m[3])) {
            return [null, null];
        }
        $date = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        return [$date, isset($m[4]) ? date('g:i A', strtotime($date . ' ' . $m[4] . ':' . $m[5])) : null];
    }

    private static function text(string $html): string
    {
        return trim((string)preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function abs(string $href): string
    {
        $href = trim($href);
        if (!preg_match('#^https?://#i', $href)) {
            $href = self::BASE . '/' . ltrim($href, '/');
        }
        return str_replace(' ', '%20', $href);
    }

    private static function root(): string
    {
        return dirname(__DIR__, 3);
    }
}
