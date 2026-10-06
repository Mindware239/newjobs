<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * "Jobs in India": job notifications collected from official sources (Railways, Army, Police,
 * State Govts, PSUs, banks, large companies) through their published RSS / Atom / JSON feeds,
 * or added by the Jobsence admin team. Every listing keeps a link to its official source.
 *
 * Tables: external_job_sources (where we read from), external_jobs (the listings) and
 * external_notices (official notices that are not openings – results, tests, interviews).
 */
class ExternalJob
{
    public const ORG_TYPES = ['railways', 'defence', 'police', 'central_govt', 'state_govt', 'psu', 'bank', 'private', 'other'];
    public const FEED_TYPES = ['rss', 'json', 'manual', 'employmentnews', 'upsc', 'icsil', 'govtlist', 'htmllinks'];
    private const FEED_ENUM = "ENUM('rss','json','manual','employmentnews','upsc','icsil','govtlist','htmllinks') NOT NULL DEFAULT 'manual'";

    /** Job sites the admin adds are re-read every 7 days for 5 years unless set otherwise. */
    public const ADDED_SITE_EVERY_DAYS = 7;
    public const ADDED_SITE_YEARS = 5;

    /** UPSC Recruitment Advertisements page (PDFs) – the UPSC reader also reads Active Examinations and What's New. */
    public const UPSC_ADVT_URL = 'https://www.upsc.gov.in/recruitment/recruitment-advertisement';

    /** ICSIL (Delhi Govt – TCIL joint venture) Current Jobs table. */
    public const ICSIL_JOBS_URL = 'https://www.icsil.in/requirement-careers';

    /** Employment News (Govt of India) "All Jobs" table – read hourly, new issue weekly. */
    public const EMPLOYMENT_NEWS_URL = 'https://employmentnews.gov.in/newemp/AllJobs.aspx?k=All';
    /** What a listing offers: a job, an internship, skill development training or an apprenticeship. */
    public const KINDS = ['job', 'internship', 'skill', 'apprenticeship'];

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne(
            'SELECT j.*, s.name AS source_name, s.website AS source_website FROM external_jobs j
             LEFT JOIN external_job_sources s ON s.id = j.source_id WHERE j.id = ?',
            [$id]
        );
    }

    /** @return array{rows: array, total: int} */
    public static function search(array $f, int $page = 1, int $perPage = 20, bool $activeOnly = true): array
    {
        self::ensureSchema();
        $where = [];
        $params = [];
        if ($activeOnly) {
            $where[] = 'j.is_active = 1 AND (j.last_date IS NULL OR j.last_date >= CURDATE())';
        }
        if (in_array($f['type'] ?? '', self::ORG_TYPES, true)) {
            $where[] = 'j.org_type = ?';
            $params[] = $f['type'];
        }
        if (!empty($f['state'])) {
            $where[] = '(j.state = ? OR j.state IS NULL OR j.state = \'\' OR j.state = \'All India\')';
            $params[] = $f['state'];
        }
        if (in_array($f['kind'] ?? '', self::KINDS, true)) {
            $where[] = 'j.kind = ?';
            $params[] = $f['kind'];
        }
        if (!empty($f['source_id'])) {
            $where[] = 'j.source_id = ?';
            $params[] = (int)$f['source_id'];
        }
        $q = trim((string)($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(j.title LIKE ? OR j.org_name LIKE ? OR j.location LIKE ? OR j.qualification LIKE ?)';
            array_push($params, ...array_fill(0, 4, '%' . $q . '%'));
        }
        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $db = Database::getInstance();
        $total = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM external_jobs j $sqlWhere", $params)['c'] ?? 0);
        $rows = $db->fetchAll(
            "SELECT j.* FROM external_jobs j $sqlWhere
             ORDER BY COALESCE(j.published_at, j.created_at) DESC, j.id DESC
             LIMIT " . (int)$perPage . ' OFFSET ' . max(0, ($page - 1) * $perPage),
            $params
        );
        return ['rows' => $rows, 'total' => $total];
    }

    /** Active listing counts per organisation type. */
    public static function countsByType(): array
    {
        self::ensureSchema();
        $out = [];
        foreach (Database::getInstance()->fetchAll(
            'SELECT org_type, COUNT(*) AS c FROM external_jobs
             WHERE is_active = 1 AND (last_date IS NULL OR last_date >= CURDATE()) GROUP BY org_type'
        ) as $r) {
            $out[$r['org_type']] = (int)$r['c'];
        }
        return $out;
    }

    /** Organisations with open listings (Railways, UP Police, universities, hospitals…), most first. */
    public static function topOrganisations(int $limit = 12): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            'SELECT org_name, MIN(org_type) AS org_type, COUNT(*) AS job_count FROM external_jobs
             WHERE is_active = 1 AND (last_date IS NULL OR last_date >= CURDATE())
             GROUP BY org_name ORDER BY job_count DESC, MAX(COALESCE(published_at, created_at)) DESC
             LIMIT ' . max(1, $limit)
        );
    }

    /**
     * Latest titles for the blinking "See Jobs in India" bar in the header.
     * Cached for 10 minutes in a file so the header never adds a query per page view.
     */
    public static function ticker(int $limit = 12): array
    {
        $file = dirname(__DIR__, 2) . '/storage/cache/india_jobs_ticker.json';
        if (is_file($file) && filemtime($file) > time() - 600) {
            $cached = json_decode((string)file_get_contents($file), true);
            if (is_array($cached)) {
                return $cached;
            }
        }
        $rows = [];
        try {
            self::ensureSchema();
            $rows = Database::getInstance()->fetchAll(
                "SELECT id, title, org_name, org_type, slug FROM external_jobs
                 WHERE is_active = 1 AND (last_date IS NULL OR last_date >= CURDATE())
                 ORDER BY FIELD(org_type, 'railways', 'defence', 'police', 'state_govt', 'central_govt', 'psu', 'bank', 'private', 'other'),
                          COALESCE(published_at, created_at) DESC
                 LIMIT " . (int)$limit
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('ExternalJob::ticker ' . $e->getMessage());
        }
        @mkdir(dirname($file), 0775, true);
        @file_put_contents($file, json_encode($rows, JSON_UNESCAPED_UNICODE));
        return $rows;
    }

    public static function clearTickerCache(): void
    {
        @unlink(dirname(__DIR__, 2) . '/storage/cache/india_jobs_ticker.json');
    }

    public static function url(array $job): string
    {
        return '/india-jobs/' . (int)$job['id'] . '-' . ($job['slug'] ?: 'job');
    }

    public static function slugify(string $title): string
    {
        $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $title), '-'));
        return substr($s !== '' ? $s : 'job', 0, 120);
    }

    /** Insert or update a listing by its unique hash. Returns 'new', 'updated' or 'skipped'. */
    public static function upsert(array $job): string
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $hash = $job['guid_hash'] ?? sha1((string)($job['source_url'] ?: $job['title'] . '|' . $job['org_name']));
        $existing = $db->fetchOne('SELECT id, title, summary, details, last_date, pdf_path FROM external_jobs WHERE guid_hash = ?', [$hash]);
        $cols = self::columns($job);

        if ($existing) {
            if ($existing['title'] === $cols['title'] && (string)$existing['summary'] === (string)$cols['summary'] && (string)$existing['last_date'] === (string)$cols['last_date']
                && (string)$existing['details'] === (string)$cols['details'] && (string)$existing['pdf_path'] === (string)$cols['pdf_path']) {
                return 'skipped';
            }
            unset($cols['published_at']);
            $set = implode(', ', array_map(static fn($k) => "$k = ?", array_keys($cols)));
            $db->execute("UPDATE external_jobs SET $set WHERE id = ?", [...array_values($cols), (int)$existing['id']]);
            return 'updated';
        }

        $cols['guid_hash'] = $hash;
        $cols['is_active'] = (int)($job['is_active'] ?? 1);
        $db->execute(
            'INSERT INTO external_jobs (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')',
            array_values($cols)
        );
        return 'new';
    }

    private static function columns(array $job): array
    {
        return [
            'source_id' => $job['source_id'] ?? null,
            'org_name' => mb_substr((string)$job['org_name'], 0, 190),
            'org_type' => in_array($job['org_type'] ?? '', self::ORG_TYPES, true) ? $job['org_type'] : 'other',
            'title' => mb_substr((string)$job['title'], 0, 255),
            'slug' => self::slugify((string)$job['title']),
            'location' => mb_substr((string)($job['location'] ?? ''), 0, 190) ?: null,
            'state' => mb_substr((string)($job['state'] ?? ''), 0, 80) ?: null,
            'qualification' => mb_substr((string)($job['qualification'] ?? ''), 0, 255) ?: null,
            'vacancies' => isset($job['vacancies']) && $job['vacancies'] !== '' ? (int)$job['vacancies'] : null,
            'salary' => mb_substr((string)($job['salary'] ?? ''), 0, 120) ?: null,
            'last_date' => !empty($job['last_date']) && strtotime((string)$job['last_date']) ? date('Y-m-d', strtotime((string)$job['last_date'])) : null,
            'summary' => mb_substr((string)($job['summary'] ?? ''), 0, 1000) ?: null,
            'details' => (string)($job['details'] ?? '') ?: null,
            'apply_url' => mb_substr((string)($job['apply_url'] ?? ''), 0, 500) ?: null,
            'source_url' => mb_substr((string)($job['source_url'] ?? ''), 0, 500) ?: null,
            'pdf_path' => mb_substr((string)($job['pdf_path'] ?? ''), 0, 255) ?: null,
            'kind' => in_array($job['kind'] ?? '', self::KINDS, true) ? $job['kind'] : 'job',
            'published_at' => !empty($job['published_at']) && strtotime((string)$job['published_at']) ? date('Y-m-d H:i:s', strtotime((string)$job['published_at'])) : date('Y-m-d H:i:s'),
        ];
    }

    /** Admin edit of an existing listing (keeps its hash so feed refreshes still match it). */
    public static function update(int $id, array $job): void
    {
        $cols = self::columns($job);
        $cols['is_active'] = (int)($job['is_active'] ?? 1);
        $set = implode(', ', array_map(static fn($k) => "$k = ?", array_keys($cols)));
        Database::getInstance()->execute("UPDATE external_jobs SET $set WHERE id = ?", [...array_values($cols), $id]);
    }

    /** Admin "feature on the homepage" star. */
    public static function setFeatured(int $id, bool $featured): void
    {
        self::ensureSchema();
        Database::getInstance()->execute('UPDATE external_jobs SET is_featured = ? WHERE id = ?', [$featured ? 1 : 0, $id]);
    }

    /**
     * Homepage "Featured Govt & PSU jobs": jobs the admin starred, topped up with the newest open
     * PSU / central / state Govt jobs so the box is never empty.
     */
    public static function featured(int $limit = 8): array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $open = "is_active = 1 AND (last_date IS NULL OR last_date >= CURDATE())";
        $rows = $db->fetchAll("SELECT * FROM external_jobs WHERE is_featured = 1 AND $open ORDER BY published_at DESC LIMIT " . (int)$limit);
        if (count($rows) < $limit) {
            $ids = array_column($rows, 'id') ?: [0];
            $more = $db->fetchAll(
                "SELECT * FROM external_jobs WHERE $open AND org_type IN ('psu','central_govt','state_govt','bank','railways','defence')
                 AND id NOT IN (" . implode(',', array_map('intval', $ids)) . ")
                 ORDER BY (last_date IS NULL), published_at DESC LIMIT " . ($limit - count($rows))
            );
            $rows = array_merge($rows, $more);
        }
        return $rows;
    }

    public static function setActive(int $id, bool $active): void
    {
        Database::getInstance()->execute('UPDATE external_jobs SET is_active = ? WHERE id = ?', [$active ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::getInstance()->execute('DELETE FROM external_jobs WHERE id = ?', [$id]);
    }

    // ------------------------------------------------------------------
    // Sources
    // ------------------------------------------------------------------

    public static function sources(array $f = []): array
    {
        self::ensureSchema();
        $where = [];
        $params = [];
        if (in_array($f['type'] ?? '', self::ORG_TYPES, true)) {
            $where[] = 's.org_type = ?';
            $params[] = $f['type'];
        }
        if (($f['feed'] ?? '') === 'yes') {
            $where[] = "s.feed_url IS NOT NULL AND s.feed_url <> ''";
        }
        $q = trim((string)($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(s.name LIKE ? OR s.website LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%');
        }
        return Database::getInstance()->fetchAll(
            'SELECT s.*, (SELECT COUNT(*) FROM external_jobs j WHERE j.source_id = s.id AND j.is_active = 1) AS jobs
             FROM external_job_sources s ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '
             ORDER BY FIELD(s.org_type, \'railways\', \'defence\', \'police\', \'central_govt\', \'state_govt\', \'psu\', \'bank\', \'private\', \'other\'), s.name',
            $params
        );
    }

    public static function source(int $id): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne('SELECT * FROM external_job_sources WHERE id = ?', [$id]);
    }

    /** Enabled sources with a feed, least recently fetched first. */
    public static function dueSources(int $limit = 50): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            "SELECT * FROM external_job_sources WHERE enabled = 1 AND feed_type IN ('rss','json','employmentnews','upsc','icsil','govtlist','htmllinks') AND feed_url IS NOT NULL AND feed_url <> ''
               AND (crawl_until IS NULL OR crawl_until >= CURDATE())
               AND (last_fetched_at IS NULL OR last_fetched_at < NOW() - INTERVAL IF(crawl_every_days > 0, crawl_every_days * 1440 - 10, 50) MINUTE)
             ORDER BY last_fetched_at IS NOT NULL, last_fetched_at ASC LIMIT " . (int)$limit
        );
    }

    public static function saveSource(?int $id, array $s): int
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $cols = [
            'name' => mb_substr(trim((string)$s['name']), 0, 190),
            'org_type' => in_array($s['org_type'] ?? '', self::ORG_TYPES, true) ? $s['org_type'] : 'other',
            'state' => mb_substr(trim((string)($s['state'] ?? '')), 0, 80) ?: null,
            'website' => mb_substr(trim((string)($s['website'] ?? '')), 0, 255) ?: null,
            'feed_url' => mb_substr(trim((string)($s['feed_url'] ?? '')), 0, 500) ?: null,
            'feed_type' => in_array($s['feed_type'] ?? '', self::FEED_TYPES, true) ? $s['feed_type'] : 'manual',
            'enabled' => !empty($s['enabled']) ? 1 : 0,
            'kind' => in_array($s['kind'] ?? '', self::KINDS, true) ? $s['kind'] : 'job',
            // How often to re-read the site (0 = every hour) and until when (empty = no end).
            'crawl_every_days' => max(0, min(365, (int)($s['crawl_every_days'] ?? ($id ? 0 : self::ADDED_SITE_EVERY_DAYS)))),
            'crawl_until' => !empty($s['crawl_until']) && strtotime((string)$s['crawl_until'])
                ? date('Y-m-d', strtotime((string)$s['crawl_until']))
                : ($id ? null : date('Y-m-d', strtotime('+' . self::ADDED_SITE_YEARS . ' years'))),
        ];
        if ($id) {
            $set = implode(', ', array_map(static fn($k) => "$k = ?", array_keys($cols)));
            $db->execute("UPDATE external_job_sources SET $set WHERE id = ?", [...array_values($cols), $id]);
            return $id;
        }
        $db->execute(
            'INSERT INTO external_job_sources (' . implode(', ', array_keys($cols)) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')',
            array_values($cols)
        );
        return (int)$db->lastInsertId();
    }

    // ------------------------------------------------------------------
    // Notices (results, recruitment tests, interview schedules …) – not openings
    // ------------------------------------------------------------------

    public static function noticeExists(string $hash): bool
    {
        self::ensureSchema();
        return (bool)Database::getInstance()->fetchOne('SELECT id FROM external_notices WHERE guid_hash = ?', [$hash]);
    }

    /**
     * Store a notice once (by hash). $n: title, doc_type, page_url, link_url, documents (list of
     * ['label', 'url' official, 'local' copy or null]), published_at. Returns true when it was new.
     */
    public static function addNotice(int $sourceId, array $n): bool
    {
        self::ensureSchema();
        $hash = (string)($n['guid_hash'] ?? sha1((string)$n['title'] . '|' . ($n['page_url'] ?? '')));
        if (self::noticeExists($hash)) {
            return false;
        }
        Database::getInstance()->execute(
            'INSERT INTO external_notices (source_id, title, doc_type, page_url, link_url, documents, guid_hash, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$sourceId, mb_substr((string)$n['title'], 0, 255), mb_substr((string)($n['doc_type'] ?? ''), 0, 80) ?: null,
             mb_substr((string)($n['page_url'] ?? ''), 0, 500) ?: null, mb_substr((string)($n['link_url'] ?? ''), 0, 500) ?: null,
             json_encode(array_values($n['documents'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $hash,
             $n['published_at'] ?? date('Y-m-d H:i:s')]
        );
        return true;
    }

    /** Latest notices with their source name; documents decoded. */
    public static function latestNotices(int $limit = 10): array
    {
        self::ensureSchema();
        $rows = Database::getInstance()->fetchAll(
            'SELECT n.*, s.name AS source_name FROM external_notices n LEFT JOIN external_job_sources s ON s.id = n.source_id
             ORDER BY n.published_at DESC, n.id DESC LIMIT ' . max(1, min(100, $limit))
        );
        foreach ($rows as &$r) {
            $r['documents'] = json_decode((string)$r['documents'], true) ?: [];
        }
        return $rows;
    }

    public static function markFetched(int $id, string $status, int $items): void
    {
        Database::getInstance()->execute(
            'UPDATE external_job_sources SET last_fetched_at = NOW(), last_status = ?, last_items = ? WHERE id = ?',
            [mb_substr($status, 0, 255), $items, $id]
        );
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $pdo = Database::getInstance()->getConnection();
        if (!$pdo) {
            return;
        }
        $types = "'" . implode("','", self::ORG_TYPES) . "'";

        $pdo->exec("CREATE TABLE IF NOT EXISTS external_job_sources (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(190) NOT NULL,
            org_type ENUM($types) NOT NULL DEFAULT 'other',
            state VARCHAR(80) NULL,
            website VARCHAR(255) NULL,
            feed_url VARCHAR(500) NULL,
            feed_type " . self::FEED_ENUM . ",
            kind ENUM('job','internship','skill','apprenticeship') NOT NULL DEFAULT 'job',
            suggested_feed VARCHAR(500) NULL,
            checked_at DATETIME NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 0,
            last_fetched_at DATETIME NULL,
            last_status VARCHAR(255) NULL,
            last_items INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_ejs_name (name),
            KEY idx_ejs_due (enabled, last_fetched_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS external_jobs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            source_id INT UNSIGNED NULL,
            org_name VARCHAR(190) NOT NULL,
            org_type ENUM($types) NOT NULL DEFAULT 'other',
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(130) NOT NULL,
            location VARCHAR(190) NULL,
            state VARCHAR(80) NULL,
            qualification VARCHAR(255) NULL,
            vacancies INT UNSIGNED NULL,
            salary VARCHAR(120) NULL,
            last_date DATE NULL,
            summary TEXT NULL,
            details MEDIUMTEXT NULL,
            apply_url VARCHAR(500) NULL,
            source_url VARCHAR(500) NULL,
            pdf_path VARCHAR(255) NULL,
            guid_hash CHAR(40) NOT NULL,
            kind ENUM('job','internship','skill','apprenticeship') NOT NULL DEFAULT 'job',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            published_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_ej_hash (guid_hash),
            KEY idx_ej_list (is_active, org_type, published_at),
            KEY idx_ej_state (state),
            KEY idx_ej_last_date (last_date),
            KEY idx_ej_source (source_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Older installs: add the columns introduced later.
        foreach ([
            'external_job_sources' => ['kind' => "ENUM('job','internship','skill','apprenticeship') NOT NULL DEFAULT 'job'", 'suggested_feed' => 'VARCHAR(500) NULL', 'checked_at' => 'DATETIME NULL',
                'crawl_every_days' => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0', 'crawl_until' => 'DATE NULL'],
            'external_jobs' => ['kind' => "ENUM('job','internship','skill','apprenticeship') NOT NULL DEFAULT 'job'", 'pdf_path' => 'VARCHAR(255) NULL', 'is_featured' => 'TINYINT(1) NOT NULL DEFAULT 0'],
        ] as $table => $cols) {
            foreach ($cols as $col => $def) {
                $has = Database::getInstance()->fetchOne(
                    'SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    [$table, $col]
                );
                if (!$has) {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$col} {$def}");
                }
            }
        }

        // Existing installs: allow the Employment News table reader and switch that source on once.
        try {
            $ft = Database::getInstance()->fetchOne(
                "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'external_job_sources' AND COLUMN_NAME = 'feed_type'"
            );
            if ($ft && !str_contains((string)$ft['t'], "'htmllinks'")) {
                $pdo->exec('ALTER TABLE external_job_sources MODIFY feed_type ' . self::FEED_ENUM);
            }
        } catch (\Throwable $e) {
            error_log('ExternalJob feed_type upgrade: ' . $e->getMessage());
        }
        $enMarker = dirname(__DIR__, 2) . '/storage/cache/employment_news.enabled';
        if (!is_file($enMarker)) {
            $pdo->exec("INSERT IGNORE INTO external_job_sources (name, org_type, website, kind) VALUES ('Employment News (Govt of India)', 'central_govt', 'https://www.employmentnews.gov.in', 'job')");
            $st = $pdo->prepare("UPDATE external_job_sources SET feed_url = ?, feed_type = 'employmentnews', enabled = 1
                                 WHERE name = 'Employment News (Govt of India)' AND (feed_url IS NULL OR feed_url = '')");
            $st->execute([self::EMPLOYMENT_NEWS_URL]);
            @mkdir(dirname($enMarker), 0775, true);
            @file_put_contents($enMarker, date('c'));
        }
        // UPSC: advertisements (PDF), active examinations and What's New notices – switched on once.
        $upscMarker = dirname(__DIR__, 2) . '/storage/cache/upsc.enabled';
        if (!is_file($upscMarker)) {
            $pdo->exec("INSERT IGNORE INTO external_job_sources (name, org_type, website, kind) VALUES ('Union Public Service Commission (UPSC)', 'central_govt', 'https://upsc.gov.in', 'job')");
            $st = $pdo->prepare("UPDATE external_job_sources SET feed_url = ?, feed_type = 'upsc', enabled = 1
                                 WHERE name = 'Union Public Service Commission (UPSC)' AND (feed_url IS NULL OR feed_url = '')");
            $st->execute([self::UPSC_ADVT_URL]);
            @mkdir(dirname($upscMarker), 0775, true);
            @file_put_contents($upscMarker, date('c'));
        }
        // State recruitment boards read by GovtListFetcher (DTC, PSSSB, HSSC, UKSSSC, UKPSC, UKMSSB) – added /
        // switched on whenever the profile list changes; an admin's later edits are kept.
        $glProfiles = \App\Services\IndiaJobs\GovtListFetcher::profiles();
        $glMarker = dirname(__DIR__, 2) . '/storage/cache/govtlist-' . substr(sha1(implode('|', array_keys($glProfiles))), 0, 12) . '.enabled';
        if (!is_file($glMarker)) {
            $ins = $pdo->prepare("INSERT IGNORE INTO external_job_sources (name, org_type, state, website, kind) VALUES (?, ?, ?, ?, 'job')");
            $upd = $pdo->prepare("UPDATE external_job_sources SET feed_url = ?, feed_type = 'govtlist', enabled = 1 WHERE name = ? AND (feed_url IS NULL OR feed_url = '')");
            foreach ($glProfiles as $url => $gp) {
                $ins->execute([$gp['name'], $gp['org_type'], $gp['state'], $gp['website']]);
                $upd->execute([$url, $gp['name']]);
            }
            @mkdir(dirname($glMarker), 0775, true);
            @file_put_contents($glMarker, date('c'));
        }
        // ICSIL (Delhi): Current Jobs table – switched on once.
        $icsilMarker = dirname(__DIR__, 2) . '/storage/cache/icsil.enabled';
        if (!is_file($icsilMarker)) {
            $pdo->exec("INSERT IGNORE INTO external_job_sources (name, org_type, state, website, kind) VALUES ('Intelligent Communication Systems India Ltd (ICSIL)', 'psu', 'Delhi', 'https://www.icsil.in', 'job')");
            $st = $pdo->prepare("UPDATE external_job_sources SET feed_url = ?, feed_type = 'icsil', enabled = 1
                                 WHERE name = 'Intelligent Communication Systems India Ltd (ICSIL)' AND (feed_url IS NULL OR feed_url = '')");
            $st->execute([self::ICSIL_JOBS_URL]);
            @mkdir(dirname($icsilMarker), 0775, true);
            @file_put_contents($icsilMarker, date('c'));
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS external_notices (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            source_id INT UNSIGNED NULL,
            title VARCHAR(255) NOT NULL,
            doc_type VARCHAR(80) NULL,
            page_url VARCHAR(500) NULL,
            link_url VARCHAR(500) NULL,
            documents TEXT NULL,
            guid_hash CHAR(40) NOT NULL,
            published_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_en_hash (guid_hash),
            KEY idx_en_source (source_id, published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Seed / top up the directory of official sources whenever the data file changes
        // (new sources are added disabled; existing rows edited by admin are never overwritten).
        $seed = dirname(__DIR__, 2) . '/resources/data/india_job_sources.php';
        $marker = dirname(__DIR__, 2) . '/storage/cache/india_job_sources.seeded';
        if (is_file($seed) && (!is_file($marker) || (string)file_get_contents($marker) !== md5_file($seed))) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO external_job_sources (name, org_type, state, website, kind) VALUES (?, ?, ?, ?, ?)');
            foreach (require $seed as $row) {
                [$name, $type, $state, $site] = $row;
                $stmt->execute([$name, $type, $state ?: null, $site ?: null, $row[4] ?? 'job']);
            }
            @mkdir(dirname($marker), 0775, true);
            @file_put_contents($marker, md5_file($seed));
        }
        $done = true;
    }
}
