<?php

declare(strict_types=1);

namespace App\Services\IndiaJobs;

use App\Models\ExternalJob;

/**
 * UPSC (upsc.gov.in) reader, run hourly with the other Jobs in India sources:
 *  - Recruitment Advertisements: each advertisement PDF is downloaded once, kept as a copy and
 *    read; every "Vacancy No." in it becomes one listing (post, department, vacancies, pay, age,
 *    qualifications, headquarters, closing date);
 *  - Active Examinations: each examination with its notification PDF and last date is a listing;
 *  - What's New: results, recruitment tests and interview notices are stored as notices with their PDFs.
 * Official links are always kept; applications go to upsconline.nic.in only.
 */
class UpscFetcher
{
    private const BASE = 'https://www.upsc.gov.in';
    private const ORA = 'https://upsconline.nic.in/ora/';
    private const ORG = 'Union Public Service Commission (UPSC)';
    private const PDF_MAX_BYTES = 20_000_000;
    /** Detail pages fetched per run (only for notices / exams not seen before). */
    private const NEW_PAGES_PER_RUN = 8;

    /** Public folder for the PDF copies (relative to the project root and to the site root). */
    public const PDF_DIR = 'uploads/govt-notices/upsc';

    /** Listings from the advertisement page ($html) plus the active examinations. */
    public static function listings(string $html, string $pageUrl): array
    {
        $items = [];
        foreach (self::advertisements($html) as $ad) {
            array_push($items, ...self::advertisementItems($ad['no'], $ad['pdf'], $pageUrl));
        }
        array_push($items, ...self::examinations());
        return $items;
    }

    /** "Advertisement No.12 - 2026" + PDF link rows of the advertisement page. */
    public static function advertisements(string $html): array
    {
        $out = [];
        if (preg_match_all('#<li>\s*(Advertisement\s+No\.?\s*[^<]{1,40}?)\s*<a\s[^>]*href="([^"]+\.pdf)"#i', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $r) {
                $out[] = ['no' => trim((string)preg_replace('/\s+/', ' ', html_entity_decode($r[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 'pdf' => self::abs(html_entity_decode($r[2]))];
            }
        }
        return $out;
    }

    /** One listing per vacancy of an advertisement PDF (cached per PDF, so it is read only once). */
    private static function advertisementItems(string $advtNo, string $pdfUrl, string $pageUrl): array
    {
        $cache = self::cacheFile('advt', $pdfUrl);
        if (is_file($cache) && ($cached = json_decode((string)file_get_contents($cache), true)) && isset($cached['items'])) {
            return $cached['items'];
        }
        $local = self::savePdf($pdfUrl);
        $text = $local ? self::pdfText(self::root() . '/' . $local) : '';
        $items = $text !== '' ? self::parseAdvertisement($text, $advtNo, $pdfUrl, $local) : [];
        if (!$items) {
            // Unreadable or new format: one listing for the whole advertisement, PDF attached.
            $items[] = [
                'title' => 'UPSC ' . $advtNo . ' – Recruitment by Selection',
                'org_name' => self::ORG, 'org_type' => 'central_govt',
                'summary' => "UPSC {$advtNo}: recruitment by selection to various posts in the Government of India. Read the advertisement PDF for the posts, eligibility and closing date, and apply online at upsconline.nic.in.",
                'source_url' => $pdfUrl, 'apply_url' => self::ORA, 'pdf_path' => $local,
                'guid_hash' => sha1('upsc|advt|' . $pdfUrl),
            ];
        }
        if ($local) { // cache only once the PDF was really read, so a failed download is retried next hour
            @file_put_contents($cache, json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $items;
    }

    /**
     * Advertisement text → listings. Each vacancy starts "1. (Vacancy No. 26091201626) One vacancy for the
     * post of <post> in <department>." and is followed by RESERVATION POSITION, PAY SCALE, AGE,
     * ESSENTIAL QUALIFICATIONS … HEADQUARTERS.
     */
    public static function parseAdvertisement(string $text, string $advtNo, string $pdfUrl, ?string $local): array
    {
        $flat = self::flat($text);
        if (preg_match('#ADVERTISEMENT\s+NO\.?\s*(\d{1,3})\s*/\s*(\d{4})#i', $flat, $m)) {
            $advtNo = 'Advertisement No. ' . (int)$m[1] . '/' . $m[2];
        }
        $closing = preg_match('/CLOSING\s+DATE[^.]{0,200}?(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})/i', $flat, $m) ? self::ymd($m) : null;
        $opening = preg_match('/POSTS\s+FROM\s+(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})/i', $flat, $m) ? self::ymd($m) : null;

        if (!preg_match_all('/(?:^|\s)\d{1,3}\.\s*\(\s*Vacancy\s+No\.?\s*(\d{6,})\s*\)\s*/i', $flat, $starts, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return [];
        }
        $items = [];
        foreach ($starts as $i => $s) {
            $from = $s[0][1] + strlen($s[0][0]);
            $to = isset($starts[$i + 1]) ? $starts[$i + 1][0][1] : min(strlen($flat), $from + 8000);
            $block = substr($flat, $from, max(0, $to - $from));
            if (!preg_match('/^(.{1,40}?)\s+vacanc(?:y|ies)\s+for\s+the\s+posts?\s+of\s+(.+?)\s+in\s+(?:the\s+)?(.+?)\.\s+(?=RESERVATION|PAY\s+SCALE|AGE|ESSENTIAL)/is', $block, $h)) {
                continue;
            }
            $vacancyNo = $s[1][0];
            $post = trim($h[2]);
            $dept = trim($h[3]);
            // Headings in the PDF are upper case, so labels and stop words match case-sensitively.
            $field = static function (string $label, string $until) use ($block): string {
                return preg_match('/' . $label . '\s*:?\s*(.+?)\s*(?=' . $until . '|$)/s', $block, $m) ? trim((string)preg_replace('/(?<=[.)])\s+\d{1,2}$/', '', trim($m[1]))) : ''; // drop a PDF page number
            };
            $reservation = $field('RESERVATION\s+POSITION', 'The post is|Category-wise|PAY\s+SCALE|\.\s+\*|AGE\s*:');
            $pay = $field('PAY\s+SCALE', 'AGE\s*:|ESSENTIAL');
            $age = $field('AGE', 'ESSENTIAL|\(A\)|EDUCATIONAL');
            $quals = $field('ESSENTIAL\s+QUALIFICATIONS?', 'DESIRABLE|NOTE\s*:|DUTIES|OTHER\s+DETAILS');
            $hq = $field('HEADQUARTERS', '\(But|\s[A-Z]{3,}(?: [A-Z]+)*\s*:|\d+\.\s');
            $count = self::count(trim($h[1]));
            $deptShort = trim(explode(',', $dept)[0]);

            $details = array_filter([
                "{$advtNo} – Vacancy No. {$vacancyNo}",
                'Post: ' . $post,
                'Department / Ministry: ' . $dept,
                $count ? 'Vacancies: ' . $count : '',
                $reservation !== '' ? 'Reservation: ' . mb_substr($reservation, 0, 300) : '',
                $pay !== '' ? 'Pay scale: ' . mb_substr($pay, 0, 200) : '',
                $age !== '' ? 'Age: ' . mb_substr($age, 0, 400) : '',
                $quals !== '' ? 'Essential qualifications: ' . mb_substr($quals, 0, 1500) : '',
                $hq !== '' ? 'Headquarters: ' . mb_substr($hq, 0, 150) : '',
                $opening ? 'Apply online from: ' . date('d M Y', strtotime($opening)) : '',
                $closing ? 'Closing date: 6:00 PM, ' . date('d M Y', strtotime($closing)) : '',
                'Read the full advertisement PDF before applying. Apply only at upsconline.nic.in.',
            ]);
            $items[] = [
                'title' => mb_substr($post . ' – ' . $deptShort, 0, 255),
                'org_name' => self::ORG,
                'org_type' => 'central_govt',
                'location' => $hq !== '' ? mb_substr($hq, 0, 120) : null,
                'vacancies' => $count,
                'salary' => $pay !== '' ? mb_substr($pay, 0, 120) : null,
                'qualification' => $quals !== '' ? mb_substr($quals, 0, 250) : null,
                'last_date' => $closing,
                'published_at' => $opening ? $opening . ' 09:00:00' : null,
                'summary' => mb_substr(($count ? $count . ' vacanc' . ($count === 1 ? 'y' : 'ies') : 'Vacancy') . " for the post of {$post} in {$dept}. UPSC {$advtNo}"
                    . ($closing ? '; apply online at upsconline.nic.in by 6 PM, ' . date('d M Y', strtotime($closing)) : '') . '.', 0, 1000),
                'details' => implode("\n", $details),
                'source_url' => $pdfUrl,
                'apply_url' => self::ORA,
                'pdf_path' => $local,
                'guid_hash' => sha1('upsc|vacancy|' . $vacancyNo),
            ];
        }
        return $items;
    }

    /** Active Examinations: name, notification date, last date, exam date and notification PDF. */
    public static function examinations(): array
    {
        [$code, $html] = FeedFetcher::get(self::BASE . '/examinations/active-exams', 2_000_000);
        // Each exam: <div class="views-field-field-exam-name"> <a href="/examinations/…"><ul><li>Name</li></ul></a>
        if ($code !== 200 || !preg_match_all('#field-exam-name[^>]*>\s*<div[^>]*>\s*<a\s[^>]*href="(/examinations/[^"]+)"[^>]*>(.*?)</a>#is', self::content($html), $m, PREG_SET_ORDER)) {
            return [];
        }
        $items = [];
        $budget = self::NEW_PAGES_PER_RUN;
        foreach ($m as $r) {
            $url = self::abs(html_entity_decode($r[1]));
            $name = trim((string)preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($r[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if (mb_strlen($name) < 6) {
                continue;
            }
            $cache = self::cacheFile('exam', $url);
            $exam = is_file($cache) ? json_decode((string)file_get_contents($cache), true) : null;
            // Re-read a known exam page once a day (corrigenda, extended dates); new pages within the run budget.
            if (!$exam || filemtime($cache) < time() - 86400) {
                if (!$exam && $budget-- <= 0) {
                    continue; // new exam pages beyond this run's budget: next hour
                }
                if ($fresh = self::examPage($url)) {
                    $exam = $fresh;
                    @file_put_contents($cache, json_encode($exam, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                }
            }
            if (!$exam) {
                continue;
            }
            // The notification itself, not a time table or press note listed before it.
            $pdfs = array_values(array_filter($exam['pdfs'] ?? [], static fn($p) => !self::isQuestionPaper($p['url'])));
            $notif = array_filter($pdfs, static fn($p) => stripos($p['label'] . ' ' . $p['url'], 'notif') !== false);
            $pdf = reset($notif) ?: ($pdfs[0] ?? null);
            if ($pdf) {
                $pdf['local'] = self::savePdf($pdf['url']); // only this one is kept as a copy
            }
            $lines = array_filter([
                'Examination: ' . $name,
                $exam['notified'] ? 'Date of notification: ' . date('d M Y', strtotime($exam['notified'])) : '',
                $exam['last_date'] ? 'Last date for applications: ' . date('d M Y', strtotime($exam['last_date'])) . ($exam['last_time'] ? ' – ' . $exam['last_time'] : '') : '',
                $exam['exam_date'] ? 'Examination begins: ' . date('d M Y', strtotime($exam['exam_date'])) : '',
                $exam['duration'] ? 'Duration: ' . $exam['duration'] : '',
                'Read the official notification before applying. Apply only at upsconline.nic.in.',
            ]);
            $items[] = [
                'title' => mb_substr($name, 0, 255),
                'org_name' => self::ORG,
                'org_type' => 'central_govt',
                'last_date' => $exam['last_date'],
                'published_at' => $exam['notified'] ? $exam['notified'] . ' 09:00:00' : null,
                'summary' => "UPSC {$name}." . ($exam['last_date'] ? ' Apply online by ' . date('d M Y', strtotime($exam['last_date'])) . ($exam['last_time'] ? ' (' . $exam['last_time'] . ')' : '') . '.' : '')
                    . ($exam['exam_date'] ? ' Examination from ' . date('d M Y', strtotime($exam['exam_date'])) . '.' : ''),
                'details' => implode("\n", $lines),
                'source_url' => $pdf['url'] ?? $url,
                'apply_url' => self::ORA,
                'pdf_path' => $pdf['local'] ?? null,
                'guid_hash' => sha1('upsc|exam|' . mb_strtolower($name)),
            ];
        }
        return $items;
    }

    /** One examination page → dates and notification PDFs (downloaded). */
    private static function examPage(string $url): ?array
    {
        [$code, $html] = FeedFetcher::get($url, 2_000_000);
        if ($code !== 200) {
            return null;
        }
        $c = self::content($html);
        $cell = static function (string $label) use ($c): string {
            return preg_match('#' . $label . '\s*(?:</strong>)?\s*</th>\s*<t[hd][^>]*>(.*?)</t[hd]>#is', $c, $m)
                ? trim((string)preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) : '';
        };
        $dmy = static fn(string $s): ?string => preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $s, $m) ? self::ymd($m) : null;
        $last = $cell('Last Date for Receipt of Applications');
        return [
            'notified' => $dmy($cell('Date of Notification')),
            'exam_date' => $dmy($cell('Date of Commencement of Examination')),
            'duration' => mb_substr($cell('Duration of Examination'), 0, 60),
            'last_date' => $dmy($last),
            'last_time' => preg_match('/(\d{1,2}:\d{2}\s*[ap]m)/i', $last, $t) ? $t[1] : '',
            'pdfs' => self::pdfLinks($c, 10, false),
        ];
    }

    /** What's New → notices (results, recruitment tests, interviews …) with their PDFs. Returns how many were new. */
    public static function notices(int $sourceId): int
    {
        [$code, $html] = FeedFetcher::get(self::BASE . '/whats-new', 2_000_000);
        if ($code !== 200 || !preg_match_all('#<a\s[^>]*href="((?:https://www\.upsc\.gov\.in)?/?whats-new/[^"]+)"[^>]*>([^<]{4,300})</a>#i', self::content($html), $m, PREG_SET_ORDER)) {
            return 0;
        }
        $new = 0;
        $budget = self::NEW_PAGES_PER_RUN;
        foreach ($m as $r) {
            $text = trim(html_entity_decode($r[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $url = self::abs(html_entity_decode($r[1]));
            $hash = sha1('upsc|notice|' . mb_strtolower($text));
            if (ExternalJob::noticeExists($hash)) {
                continue;
            }
            if ($budget-- <= 0) {
                break; // the rest next hour
            }
            [$type, $title] = str_contains($text, ':') ? array_map('trim', explode(':', $text, 2)) : ['', $text];
            $documents = [];
            $link = null;
            [$dc, $page] = FeedFetcher::get($url, 2_000_000);
            if ($dc === 200) {
                $c = self::content($page);
                $documents = self::pdfLinks($c, 4);
                if (preg_match('#views-field-field-link-1[^>]*>\s*<a\s[^>]*href="(https?://[^"]+)"#i', $c, $l)) {
                    $link = html_entity_decode($l[1]);
                }
                // Recruitment tests etc. keep their PDFs (time table, admit card notice …) on the linked UPSC page.
                if (!$documents && $link && preg_match('#^https://(?:www\.)?upsc\.gov\.in/#i', $link)) {
                    [$lc, $linked] = FeedFetcher::get($link, 2_000_000);
                    if ($lc === 200) {
                        $documents = self::pdfLinks(self::content($linked), 4);
                    }
                }
            }
            $new += (int)ExternalJob::addNotice($sourceId, [
                'title' => $title, 'doc_type' => $type, 'page_url' => $url, 'link_url' => $link,
                'documents' => $documents, 'guid_hash' => $hash,
            ]);
        }
        return $new;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** PDF links in a page fragment → [['label', 'url', 'local']] (PDFs downloaded as copies unless $download is false). */
    private static function pdfLinks(string $html, int $max, bool $download = true): array
    {
        $out = [];
        // UPSC document tables: one row per document, its kind in the "Document Type" cell.
        if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $html, $rows)) {
            foreach ($rows[1] as $row) {
                if (!preg_match('#field-upload-doc[^>]*>(.*?)</td>#is', $row, $type) || !preg_match('#href="([^"]+\.pdf)"#i', $row, $pdf)) {
                    continue;
                }
                $url = self::abs(html_entity_decode($pdf[1]));
                $label = trim((string)preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($type[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                $out[$url] ??= ['label' => mb_substr($label !== '' ? $label : 'Document', 0, 120), 'url' => $url, 'local' => $download && !self::isQuestionPaper($url) ? self::savePdf($url) : null];
                if (count($out) >= $max) {
                    return array_values($out);
                }
            }
            if ($out) {
                return array_values($out);
            }
        }
        if (preg_match_all('#(?:<li>\s*([^<]{0,120}))?<a\s[^>]*href="([^"]+\.pdf)"[^>]*>(.*?)</a>#is', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $r) {
                $url = self::abs(html_entity_decode($r[2]));
                if (isset($out[$url])) {
                    continue;
                }
                $label = trim((string)preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($r[1] . ' ' . $r[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                $out[$url] = ['label' => $label !== '' ? mb_substr($label, 0, 120) : basename(parse_url($url, PHP_URL_PATH) ?: 'document.pdf'), 'url' => $url, 'local' => $download && !self::isQuestionPaper($url) ? self::savePdf($url) : null];
                if (count($out) >= $max) {
                    break;
                }
            }
        }
        return array_values($out);
    }

    /** Download an official PDF once into PDF_DIR; returns its public path or null. */
    public static function savePdf(string $url): ?string
    {
        if (!preg_match('#^https://(?:www\.)?upsc\.gov\.in/.+\.pdf$#i', $url)) {
            return null; // copies only from UPSC itself
        }
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', rawurldecode(basename((string)parse_url($url, PHP_URL_PATH))));
        $name = substr(sha1($url), 0, 8) . '-' . substr((string)$name, -120);
        $rel = self::PDF_DIR . '/' . $name;
        $abs = self::root() . '/' . $rel;
        if (is_file($abs) && filesize($abs) > 0) {
            return $rel;
        }
        [$code, $body] = FeedFetcher::get($url, self::PDF_MAX_BYTES);
        // Over the size limit the download is cut off: never keep a broken copy.
        if ($code !== 200 || !str_starts_with($body, '%PDF') || strlen($body) >= self::PDF_MAX_BYTES) {
            return null;
        }
        @mkdir(dirname($abs), 0775, true);
        return @file_put_contents($abs, $body) ? $rel : null;
    }

    /** Question papers (QP-… files, often 5–20 MB) stay links to UPSC; no local copy. */
    private static function isQuestionPaper(string $url): bool
    {
        return (bool)preg_match('/^QP[-_]/i', rawurldecode(basename((string)parse_url($url, PHP_URL_PATH))));
    }

    private static function pdfText(string $file): string
    {
        try {
            return (new \Smalot\PdfParser\Parser())->parseFile($file)->getText();
        } catch (\Throwable $e) {
            error_log('UPSC PDF ' . basename($file) . ': ' . $e->getMessage());
            return '';
        }
    }

    /** PDF text on one line: no line breaks, "7 th CPC" → "7th CPC". */
    private static function flat(string $text): string
    {
        $t = (string)preg_replace('/\s+/u', ' ', str_replace(["\u{2019}", "\u{2018}"], "'", $text));
        return (string)preg_replace('/(\d)\s+(st|nd|rd|th)\b/', '$1$2', $t);
    }

    /** "One", "Eight", "Twenty Five", "12", "Eight (08)" → int. */
    private static function count(string $s): ?int
    {
        if (preg_match('/\d+/', $s, $m)) {
            return (int)$m[0];
        }
        static $n = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10,
            'eleven' => 11, 'twelve' => 12, 'thirteen' => 13, 'fourteen' => 14, 'fifteen' => 15, 'sixteen' => 16, 'seventeen' => 17, 'eighteen' => 18, 'nineteen' => 19,
            'twenty' => 20, 'thirty' => 30, 'forty' => 40, 'fifty' => 50, 'sixty' => 60, 'seventy' => 70, 'eighty' => 80, 'ninety' => 90, 'hundred' => 100];
        $total = 0;
        foreach (preg_split('/[\s-]+/', mb_strtolower($s)) as $w) {
            if ($w === 'hundred') {
                $total = max(1, $total) * 100;
            } elseif (isset($n[$w])) {
                $total += $n[$w];
            }
        }
        return $total > 0 ? $total : null;
    }

    private static function ymd(array $m): ?string
    {
        return checkdate((int)$m[2], (int)$m[1], (int)$m[3]) ? sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]) : null;
    }

    /** Main content of a UPSC page (skips menus, sidebars and footer links). */
    private static function content(string $html): string
    {
        $i = strpos($html, 'id="main-content"');
        $html = $i !== false ? substr($html, $i) : $html;
        $j = strpos($html, '<footer');
        return $j !== false ? substr($html, 0, $j) : $html;
    }

    private static function abs(string $href): string
    {
        $href = trim($href);
        if (!preg_match('#^https?://#i', $href)) {
            $href = self::BASE . '/' . ltrim($href, '/');
        }
        // Paths on UPSC contain spaces and brackets: encode them, keep the URL readable otherwise.
        return (string)preg_replace_callback('#[^A-Za-z0-9\-._~:/?\#\[\]@!$&\'()*+,;=%]#', static fn($c) => rawurlencode($c[0]), $href);
    }

    private static function cacheFile(string $kind, string $url): string
    {
        $dir = self::root() . '/storage/cache/upsc';
        @mkdir($dir, 0775, true);
        return $dir . '/' . $kind . '_' . sha1($url) . '.json';
    }

    private static function root(): string
    {
        return dirname(__DIR__, 3);
    }
}
