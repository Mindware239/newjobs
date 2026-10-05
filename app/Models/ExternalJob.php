<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * "Jobs in India": job notifications collected from official sources (Railways, Army, Police,
 * State Govts, PSUs, banks, large companies) through their published RSS / Atom / JSON feeds,
 * or added by the Jobsence admin team. Every listing keeps a link to its official source.
 *
 * Tables: external_job_sources (where we read from) and external_jobs (the listings).
 */
class ExternalJob
{
    public const ORG_TYPES = ['railways', 'defence', 'police', 'central_govt', 'state_govt', 'psu', 'bank', 'private', 'other'];
    public const FEED_TYPES = ['rss', 'json', 'manual', 'employmentnews'];

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
        $existing = $db->fetchOne('SELECT id, title, summary, last_date FROM external_jobs WHERE guid_hash = ?', [$hash]);
        $cols = self::columns($job);

        if ($existing) {
            if ($existing['title'] === $cols['title'] && (string)$existing['summary'] === (string)$cols['summary'] && (string)$existing['last_date'] === (string)$cols['last_date']) {
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
            "SELECT * FROM external_job_sources WHERE enabled = 1 AND feed_type IN ('rss','json','employmentnews') AND feed_url IS NOT NULL AND feed_url <> ''
               AND (last_fetched_at IS NULL OR last_fetched_at < NOW() - INTERVAL 50 MINUTE)
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
            feed_type ENUM('rss','json','manual','employmentnews') NOT NULL DEFAULT 'manual',
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
            'external_job_sources' => ['kind' => "ENUM('job','internship','skill','apprenticeship') NOT NULL DEFAULT 'job'", 'suggested_feed' => 'VARCHAR(500) NULL', 'checked_at' => 'DATETIME NULL'],
            'external_jobs' => ['kind' => "ENUM('job','internship','skill','apprenticeship') NOT NULL DEFAULT 'job'"],
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
            if ($ft && !str_contains((string)$ft['t'], 'employmentnews')) {
                $pdo->exec("ALTER TABLE external_job_sources MODIFY feed_type ENUM('rss','json','manual','employmentnews') NOT NULL DEFAULT 'manual'");
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
