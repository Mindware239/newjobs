<?php

declare(strict_types=1);

namespace App\Services\IndiaJobs;

use App\Models\ExternalJob;

/**
 * Reads job notifications from the official RSS / Atom / JSON feeds that recruitment
 * organisations publish, and stores them as "Jobs in India" listings.
 *
 * Rules kept on purpose:
 *  - only feeds an admin has added and enabled; HTML pages are read only for the official
 *    Employment News table, UPSC (advertisements, examinations, What's New – see UpscFetcher) and
 *    the ICSIL Current Jobs table (IcsilFetcher) and the state boards' recruitment lists (GovtListFetcher);
 *  - robots.txt is honoured, one polite request per source per run, identified user agent;
 *  - public http(s) hosts only (no private / loopback addresses – SSRF guard);
 *  - every listing keeps the official source link; full text stays a short summary.
 */
class FeedFetcher
{
    private const UA = 'JobsenceBot/1.0 (+https://jobsence.com/india-jobs; gm@jobsence.com)';
    /** Hosts whose firewall rejects the full agent string (brackets / contact): still identified as Jobsence. */
    private const SHORT_UA_HOSTS = ['upsc.gov.in', 'www.upsc.gov.in'];
    private const SHORT_UA = 'Jobsence/1.0 jobsence.com';
    private const MAX_BYTES = 3_000_000;
    private const MAX_ITEMS = 200;

    /** Fetch every due source. Returns per-source result lines. */
    public static function runDue(int $limit = 50): array
    {
        $out = [];
        foreach (ExternalJob::dueSources($limit) as $source) {
            $out[] = $source['name'] . ': ' . self::fetchSource($source);
        }
        if ($out) {
            ExternalJob::clearTickerCache();
        }
        return $out;
    }

    /** Fetch one source now. Returns a short status line (also stored on the source). */
    public static function fetchSource(array $source): string
    {
        $url = (string)($source['feed_url'] ?? '');
        try {
            $problem = self::checkUrl($url) ?? (self::robotsAllowed($url) ? null : 'blocked by robots.txt');
            if ($problem !== null) {
                ExternalJob::markFetched((int)$source['id'], 'error: ' . $problem, 0);
                return 'error: ' . $problem;
            }
            [$code, $body] = self::get($url);
            if ($code !== 200 || $body === '') {
                ExternalJob::markFetched((int)$source['id'], "error: HTTP $code", 0);
                return "error: HTTP $code";
            }

            $type = $source['feed_type'] ?? 'rss';
            $items = match ($type) {
                'json' => self::parseJson($body),
                'employmentnews' => self::parseEmploymentNews($body, $url),
                'upsc' => UpscFetcher::listings($body, $url),
                'icsil' => IcsilFetcher::listings($body, $url),
                'govtlist' => GovtListFetcher::listings($body, $url),
                'htmllinks' => GovtListFetcher::genericLinks($body, $url, $source),
                default => self::parseXml($body),
            };
            $counts = ['new' => 0, 'updated' => 0, 'skipped' => 0];
            $skipped = 0;
            foreach (array_slice($items, 0, self::MAX_ITEMS) as $item) {
                if ($item['title'] === '') {
                    continue;
                }
                // Official sites often publish one feed for all news: keep only recruitment items
                // (the Employment News table and UPSC advertisements / examinations list only vacancies).
                if (!in_array($type, ['employmentnews', 'upsc', 'icsil', 'govtlist', 'htmllinks'], true) && !self::isRecruitment($item['title'] . ' ' . mb_substr($item['summary'], 0, 300))) {
                    $skipped++;
                    continue;
                }
                $counts[ExternalJob::upsert($item + [
                    'source_id' => (int)$source['id'],
                    'org_name' => $source['name'],
                    'org_type' => $source['org_type'],
                    'kind' => $source['kind'] ?? 'job',
                    'state' => $item['state'] ?? $source['state'] ?? null,
                ])]++;
            }
            $status = sprintf('ok: %d items, %d new, %d updated, %d non-recruitment skipped', count($items), $counts['new'], $counts['updated'], $skipped);
            if ($type === 'govtlist' || $type === 'htmllinks') {
                GovtListFetcher::retire((int)$source['id']); // no closing date: hide once too old
            }
            if ($type === 'upsc') {
                $status .= sprintf(', %d new notices', UpscFetcher::notices((int)$source['id']));
            }
            ExternalJob::markFetched((int)$source['id'], $status, count($items));
            return $status;
        } catch (\Throwable $e) {
            ExternalJob::markFetched((int)$source['id'], 'error: ' . $e->getMessage(), 0);
            return 'error: ' . $e->getMessage();
        }
    }

    /**
     * Look at a source's official homepage for a feed it advertises itself
     * (<link rel="alternate" type="application/rss+xml|atom+xml">). Saves it as a suggestion only;
     * an admin reviews and enables it. Returns a short status line.
     */
    public static function discover(array $source): string
    {
        $site = (string)($source['website'] ?? '');
        $status = 'no website';
        $found = null;
        if ($site !== '') {
            $problem = self::checkUrl($site) ?? (self::robotsAllowed($site) ? null : 'blocked by robots.txt');
            if ($problem !== null) {
                $status = $problem;
            } else {
                [$code, $html] = self::get($site, 1_500_000);
                if ($code !== 200) {
                    $status = "HTTP $code";
                } else {
                    preg_match_all('#<link\b[^>]*>#i', $html, $links);
                    foreach ($links[0] as $tag) {
                        if (preg_match('#type=["\']application/(rss|atom)\+xml["\']#i', $tag) && preg_match('#href=["\']([^"\']+)["\']#i', $tag, $m)) {
                            $href = html_entity_decode($m[1], ENT_QUOTES);
                            if (!preg_match('#^https?://#i', $href)) {
                                $p = parse_url($site);
                                $href = $p['scheme'] . '://' . $p['host'] . '/' . ltrim($href, '/');
                            }
                            // Prefer a feed that looks like recruitment / careers / notices.
                            if ($found === null || preg_match('#recruit|career|job|vacanc|notice|advert#i', $href . $tag)) {
                                $found = $href;
                            }
                        }
                    }
                    $status = $found ? 'feed found – review and enable' : 'reachable, no feed advertised – add jobs manually';
                }
            }
        }
        \App\Core\Database::getInstance()->execute(
            'UPDATE external_job_sources SET suggested_feed = ?, checked_at = NOW(), last_status = ? WHERE id = ?',
            [$found, mb_substr($status, 0, 255), (int)$source['id']]
        );
        return $status . ($found ? ': ' . $found : '');
    }

    /** RSS 2.0 / RSS 1.0 / Atom → normalised items. */
    public static function parseXml(string $xml): array
    {
        $prev = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        libxml_use_internal_errors($prev);
        if (!$doc) {
            return [];
        }

        $nodes = [];
        if (isset($doc->channel->item)) {
            $nodes = $doc->channel->item;
        } elseif (isset($doc->item)) {
            $nodes = $doc->item;
        } elseif (isset($doc->entry)) {
            $nodes = $doc->entry;
        }

        $items = [];
        foreach ($nodes as $n) {
            $link = (string)($n->link ?? '');
            if ($link === '' && isset($n->link['href'])) {
                $link = (string)$n->link['href'];
            }
            foreach ($n->link ?? [] as $l) {
                if ((string)($l['rel'] ?? 'alternate') === 'alternate' && (string)$l['href'] !== '') {
                    $link = (string)$l['href'];
                    break;
                }
            }
            $desc = (string)($n->description ?? $n->summary ?? $n->content ?? '');
            $items[] = self::normalise([
                'title' => (string)$n->title,
                'url' => $link,
                'guid' => (string)($n->guid ?? $n->id ?? ''),
                'description' => $desc,
                'published' => (string)($n->pubDate ?? $n->published ?? $n->updated ?? ''),
            ]);
        }
        return $items;
    }

    /**
     * JSON feeds: a list of jobs, or {"jobs": [...]} / {"items": [...]} (JSON Feed 1.1 also works).
     * Recognised keys: title, url|link, last_date, location, state, qualification, vacancies, salary,
     * description|summary|content_text, published|date_published.
     */
    /**
     * Employment News "All Jobs" table: issue date | organisation | post | method of appointment | last date.
     * Dates are DD/MM/YYYY (the header says MM/DD but the rows are day-first). Rows have no detail page,
     * so the official listing page is the source / apply link.
     */
    public static function parseEmploymentNews(string $html, string $pageUrl): array
    {
        $items = [];
        $date = static function (string $s): ?string {
            return preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', trim($s), $m) && checkdate((int)$m[2], (int)$m[1], (int)$m[3])
                ? sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]) : null;
        };
        if (!preg_match_all('#<tr[^>]*>(.*?)</tr>#si', $html, $rows)) {
            return [];
        }
        foreach ($rows[1] as $row) {
            if (!preg_match_all('#<td[^>]*>(.*?)</td>#si', $row, $c) || count($c[1]) < 5) {
                continue;
            }
            $cells = array_map(static fn($x) => trim((string)preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($x), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), $c[1]);
            [$issued, $org, $post, $method, $last] = array_slice($cells, 0, 5);
            $issuedDate = $date($issued);
            if ($org === '' || $post === '' || !$issuedDate) {
                continue;
            }
            $orgName = self::titleCase($org);
            $postName = self::titleCase($post);
            $lastDate = $date($last);
            $items[] = [
                'title' => $postName . ' – ' . $orgName,
                'org_name' => $orgName,
                'org_type' => self::guessOrgType($org),
                'summary' => "{$postName} at {$orgName}. Method of appointment: {$method}. Advertised in Employment News (Govt of India) on " . date('d M Y', strtotime($issuedDate))
                    . ($lastDate ? '; last date ' . date('d M Y', strtotime($lastDate)) : '') . '. Read the full advertisement in Employment News before applying.',
                'last_date' => $lastDate,
                'published_at' => $issuedDate . ' 09:00:00',
                'source_url' => $pageUrl,
                'apply_url' => $pageUrl,
                'guid_hash' => sha1('employmentnews|' . mb_strtolower($org . '|' . $post . '|' . $issuedDate)),
            ];
        }
        return $items;
    }

    /** "NALANDA UNIVERSITY" → "Nalanda University", keeping acronyms (DNS, AIIMS, M/O) and small words lower-case. */
    public static function titleCase(string $s): string
    {
        static $acronyms = ['AIIMS', 'ICMR', 'BHEL', 'ISRO', 'DRDO', 'NIPER', 'CSIR', 'ONGC', 'NTPC', 'GAIL', 'SAIL', 'UPSC', 'IBPS', 'NABARD', 'SIDBI', 'ICAR', 'BARC', 'HAL', 'BEL', 'IIT', 'NIT', 'IIM', 'IISER', 'NIELIT', 'ESIC', 'EPFO', 'CRPF', 'CISF', 'ITBP', 'NHAI', 'DMRC', 'FCI', 'LIC', 'SBI', 'RBI', 'NDMA'];
        static $small = ['and', 'of', 'the', 'for', 'in', 'at', 'on', 'to', 'by', 'or', 'an', 'a'];
        $out = [];
        foreach (preg_split('/(\s+)/', trim($s), -1, PREG_SPLIT_DELIM_CAPTURE) as $i => $w) {
            if (trim($w) === '') { $out[] = $w; continue; }
            $core = preg_replace('/[^A-Za-z]/', '', $w);
            $lower = mb_strtolower($w);
            if (in_array(strtoupper($core), $acronyms, true) || str_contains($w, '/') || ($core !== '' && strlen($core) <= 4 && !preg_match('/[aeiouAEIOU]/', $core) && strtoupper($w) === $w)) {
                $out[] = strtoupper($w);
            } elseif ($i > 0 && in_array($lower, $small, true)) {
                $out[] = $lower;
            } else {
                $out[] = mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
            }
        }
        return implode('', $out);
    }

    /** Organisation type from its name (Employment News lists every kind of Govt body). */
    public static function guessOrgType(string $org): string
    {
        $o = mb_strtolower($org);
        return match (true) {
            (bool)preg_match('/railway|rrb|metro rail/', $o) => 'railways',
            (bool)preg_match('/\b(army|navy|air force|indian coast guard|defence|drdo|ordnance)\b/', $o) => 'defence',
            (bool)preg_match('/police|crpf|cisf|bsf|itbp|ssb|assam rifles|nsg/', $o) => 'police',
            (bool)preg_match('/\bbank\b|nabard|sidbi|reserve bank/', $o) => 'bank',
            (bool)preg_match('/limited|ltd\b|corporation/', $o) => 'psu',
            (bool)preg_match('/state |government of [a-z]+ pradesh|govt\. of [a-z]+/', $o) => 'state_govt',
            default => 'central_govt',
        };
    }

    public static function parseJson(string $json): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }
        $list = $data['jobs'] ?? $data['items'] ?? $data;
        $items = [];
        foreach ((array)$list as $j) {
            if (!is_array($j)) {
                continue;
            }
            $items[] = self::normalise([
                'title' => (string)($j['title'] ?? ''),
                'url' => (string)($j['url'] ?? $j['link'] ?? ''),
                'guid' => (string)($j['id'] ?? ''),
                'description' => (string)($j['description'] ?? $j['summary'] ?? $j['content_text'] ?? ''),
                'published' => (string)($j['published'] ?? $j['date_published'] ?? ''),
                'last_date' => (string)($j['last_date'] ?? ''),
                'location' => (string)($j['location'] ?? ''),
                'state' => (string)($j['state'] ?? ''),
                'qualification' => (string)($j['qualification'] ?? ''),
                'vacancies' => $j['vacancies'] ?? null,
                'salary' => (string)($j['salary'] ?? ''),
                'apply_url' => (string)($j['apply_url'] ?? ''),
            ]);
        }
        return $items;
    }

    private static function normalise(array $r): array
    {
        $text = self::plainText($r['description'] ?? '');
        $url = self::safeLink((string)($r['url'] ?? ''));
        return [
            'title' => mb_substr(trim(html_entity_decode(strip_tags((string)$r['title']), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 0, 255),
            'source_url' => $url,
            'apply_url' => self::safeLink((string)($r['apply_url'] ?? '')) ?: $url,
            'guid_hash' => sha1(($r['guid'] ?? '') !== '' ? (string)$r['guid'] : ($url !== '' ? $url : (string)$r['title'])),
            'summary' => mb_substr($text, 0, 600),
            'details' => mb_substr($text, 0, 8000),
            'published_at' => (string)($r['published'] ?? ''),
            'last_date' => ($r['last_date'] ?? '') !== '' ? $r['last_date'] : self::guessLastDate($text . ' ' . $r['title']),
            'vacancies' => $r['vacancies'] ?? self::guessVacancies($text . ' ' . $r['title']),
            'location' => $r['location'] ?? null,
            'state' => ($r['state'] ?? '') !== '' ? $r['state'] : null,
            'qualification' => $r['qualification'] ?? null,
            'salary' => $r['salary'] ?? null,
        ];
    }

    /** Does a feed item look like a job / internship / apprenticeship / training notice? */
    public static function isRecruitment(string $text): bool
    {
        if (preg_match('/(tender|bids?|bidding|procurement|rfp|rfq|eoi|expression of interest|quotation|auction|e-?auction|supply of|service provider for|vendor)/i', $text)) {
            return false;
        }
        return (bool)preg_match('/recruit|vacanc|walk[\s-]?in|bharti|भर्ती|रिक्ति|apprentic|internship|intern\b|fellowship|engagement of|appointment of|post of|posts of|\bposts?\b.*\b(advt|advertisement)|employment notice|job opening|careers?\b|selection of candidates|hiring|empanelment of (?:doctors|consultants|faculty)|faculty position|project (?:assistant|associate|fellow)|junior research fellow|skill (?:training|development) programme/i', $text);
    }

    public static function plainText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(br|/p|/li|/div|/tr|/h\d)\b[^>]*>#i', "\n", (string)$html);
        $text = html_entity_decode(strip_tags((string)$html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text);
        return trim((string)preg_replace("/\n\s*\n+/", "\n\n", (string)$text));
    }

    /** "Last date: 15/11/2026", "last date of application 15-11-2026", "closing date 15 Nov 2026". */
    public static function guessLastDate(string $text): ?string
    {
        if (preg_match('/(?:last|closing|end)\s*date[^0-9a-z]{0,30}(?:of\s+\w+\s*)?[^0-9]{0,15}(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{2,4})/i', $text, $m)) {
            $y = strlen($m[3]) === 2 ? 2000 + (int)$m[3] : (int)$m[3];
            if (checkdate((int)$m[2], (int)$m[1], $y)) {
                return sprintf('%04d-%02d-%02d', $y, (int)$m[2], (int)$m[1]);
            }
        }
        if (preg_match('/(?:last|closing|end)\s*date[^0-9]{0,40}(\d{1,2}\s+[A-Za-z]{3,9},?\s+\d{4})/i', $text, $m) && ($t = strtotime($m[1]))) {
            return date('Y-m-d', $t);
        }
        return null;
    }

    public static function guessVacancies(string $text): ?int
    {
        if (preg_match('/(\d[\d,]{0,7})\s*(?:posts?|vacanc(?:y|ies)|pad)\b/i', $text, $m)) {
            $n = (int)str_replace(',', '', $m[1]);
            return $n > 0 && $n < 2_000_000 ? $n : null;
        }
        return null;
    }

    private static function safeLink(string $url): string
    {
        $url = trim($url);
        return preg_match('#^https?://#i', $url) ? mb_substr($url, 0, 500) : '';
    }

    /** null when the URL is a public http(s) URL, otherwise the reason it is refused. */
    public static function checkUrl(string $url): ?string
    {
        $p = parse_url($url);
        if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true) || empty($p['host'])) {
            return 'feed URL must start with http:// or https://';
        }
        $host = $p['host'];
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (!$ips) {
            return 'host not found';
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return 'private / reserved address not allowed';
            }
        }
        return null;
    }

    /** Minimal robots.txt check for "User-agent: *" and our own agent. */
    public static function robotsAllowed(string $url): bool
    {
        $p = parse_url($url);
        $base = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
        [$code, $robots] = self::get($base . '/robots.txt', 200_000);
        if ($code !== 200 || $robots === '') {
            return true;
        }
        $path = ($p['path'] ?? '/') . (isset($p['query']) ? '?' . $p['query'] : '');
        $applies = false;
        $disallow = [];
        $allow = [];
        foreach (preg_split('/\R/', $robots) as $line) {
            $line = trim(preg_replace('/#.*/', '', $line));
            if (!preg_match('/^([A-Za-z-]+)\s*:\s*(.*)$/', $line, $m)) {
                continue;
            }
            $k = strtolower($m[1]);
            $v = trim($m[2]);
            if ($k === 'user-agent') {
                $applies = $v === '*' || stripos($v, 'jobsencebot') !== false;
            } elseif ($applies && $k === 'disallow' && $v !== '') {
                $disallow[] = $v;
            } elseif ($applies && $k === 'allow' && $v !== '') {
                $allow[] = $v;
            }
        }
        $longest = static function (array $rules) use ($path): int {
            $best = -1;
            foreach ($rules as $r) {
                $re = '#^' . str_replace(['\*', '\$'], ['.*', '$'], preg_quote($r, '#')) . '#';
                if (preg_match($re, $path)) {
                    $best = max($best, strlen($r));
                }
            }
            return $best;
        };
        return $longest($allow) >= $longest($disallow);
    }

    /** @return array{0:int,1:string} */
    public static function get(string $url, int $maxBytes = self::MAX_BYTES): array
    {
        // Follow redirects by hand so every hop passes the public-host check.
        for ($hop = 0; $hop < 4; $hop++) {
            [$code, $body, $location] = self::request($url, $maxBytes);
            if ($code === 0) {
                // Government servers are often slow or drop the first connection: one retry with a longer wait.
                [$code, $body, $location] = self::request($url, $maxBytes, 45);
            }
            if ($code < 300 || $code >= 400 || $location === '') {
                return [$code, $body];
            }
            $next = preg_match('#^https?://#i', $location) ? $location
                : (parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/' . ltrim($location, '/'));
            if (self::checkUrl($next) !== null) {
                return [$code, ''];
            }
            $url = $next;
        }
        return [310, ''];
    }

    /** @return array{0:int,1:string,2:string} */
    private static function request(string $url, int $maxBytes, int $timeout = 20): array
    {
        $ch = curl_init($url);
        $body = '';
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => $maxBytes > self::MAX_BYTES ? 90 : $timeout, // official PDFs can be several MB
            CURLOPT_ENCODING => '', // accept gzip / deflate – some sites (e.g. SBI) compress even when not asked
            CURLOPT_USERAGENT => in_array(strtolower((string)parse_url($url, PHP_URL_HOST)), self::SHORT_UA_HOSTS, true) ? self::SHORT_UA : self::UA,
            CURLOPT_HTTPHEADER => ['Accept: application/rss+xml, application/atom+xml, application/json, text/xml;q=0.9, */*;q=0.5'],
            CURLOPT_WRITEFUNCTION => static function ($ch, string $chunk) use (&$body, $maxBytes): int {
                $body .= $chunk;
                return strlen($body) > $maxBytes ? 0 : strlen($chunk);
            },
        ]);
        // Current Mozilla root bundle shipped with the app (hosting bundles are often years old and miss newer
        // roots, e.g. Sectigo R46). Certificates are still fully verified.
        if (is_file($ca = dirname(__DIR__, 3) . '/resources/certs/cacert.pem')) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $location = (string)curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);
        return [$code, $body, $location];
    }
}
